<?php

namespace App\Services;

use PDO;
use PDOException;

/**
 * Read-only connection to the KT Accounting database.
 *
 * Activate by filling these env vars in .env:
 *   KT_DB_HOST     = 45.119.87.135
 *   KT_DB_PORT     = 3306
 *   KT_DB_NAME     = kt
 *   KT_DB_USER     = crm_read
 *   KT_DB_PASSWORD = <from KT team>
 *
 * If any of those is empty, every method returns null/[] silently — so UI
 * that calls this service degrades gracefully when KT connection isn't
 * configured yet (useful during the rollout period).
 *
 * Results are cached in an in-memory static for the request, so the same
 * order detail page doesn't hit KT twice. Longer-term cache (file or redis)
 * can be layered on top later.
 */
class KtAccountingService
{
    private static ?PDO $conn = null;
    private static bool $attempted = false;
    private static array $cache = [];

    /** Lazy PDO connection. Returns null if KT is not configured or down. */
    private static function pdo(): ?PDO
    {
        if (self::$conn) return self::$conn;
        if (self::$attempted) return null;
        self::$attempted = true;

        $host = $_ENV['KT_DB_HOST'] ?? '';
        $port = $_ENV['KT_DB_PORT'] ?? '3306';
        $name = $_ENV['KT_DB_NAME'] ?? 'kt';
        $user = $_ENV['KT_DB_USER'] ?? '';
        $pass = $_ENV['KT_DB_PASSWORD'] ?? '';

        if ($host === '' || $user === '' || $pass === '') return null;

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            self::$conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 3, // fast fail if KT is down
            ]);
            return self::$conn;
        } catch (PDOException $e) {
            if ($_ENV['APP_DEBUG'] ?? false) error_log("KT DB connect failed: " . $e->getMessage());
            return null;
        }
    }

    public static function isConfigured(): bool
    {
        return ($_ENV['KT_DB_HOST'] ?? '') !== '' && ($_ENV['KT_DB_USER'] ?? '') !== '';
    }

    /** Run a prepared SELECT and return rows; return [] on any error. */
    private static function query(string $sql, array $params = []): array
    {
        $pdo = self::pdo();
        if (!$pdo) return [];
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            if ($_ENV['APP_DEBUG'] ?? false) error_log("KT query failed: " . $e->getMessage());
            return [];
        }
    }

    /** Per-request memoization so the same page doesn't hit KT twice. */
    private static function cached(string $key, callable $fn)
    {
        if (array_key_exists($key, self::$cache)) return self::$cache[$key];
        return self::$cache[$key] = $fn();
    }

    // ========== Public helpers used by views ==========

    /**
     * VAT invoice info for a given CRM order id.
     * Returns ['invoice_number', 'invoice_date', 'invoice_amount',
     *          'accounting_status', 'posted_at'] or null.
     */
    public static function fetchInvoiceForOrder(int $crmOrderId): ?array
    {
        if ($crmOrderId <= 0) return null;
        return self::cached("inv:{$crmOrderId}", function () use ($crmOrderId) {
            $rows = self::query(
                "SELECT d.doc_number   AS invoice_number,
                        d.doc_date     AS invoice_date,
                        d.total_amount AS invoice_amount,
                        d.status       AS accounting_status,
                        d.posted_at
                 FROM acc_documents d
                 JOIN acc_document_types dt ON dt.id = d.document_type_id
                 WHERE dt.code = 'HDBH'
                   AND d.crm_order_id = ?
                   AND d.status = 'POSTED'
                 LIMIT 1",
                [$crmOrderId]
            );
            return $rows[0] ?? null;
        });
    }

    /**
     * Receivable balance (TK 131) for a given CRM contact id.
     * Returns ['receivable_balance', 'transaction_count', 'last_transaction_date'] or null.
     */
    public static function fetchCustomerBalance(int $crmContactId): ?array
    {
        if ($crmContactId <= 0) return null;
        return self::cached("bal:{$crmContactId}", function () use ($crmContactId) {
            $rows = self::query(
                "SELECT COALESCE(SUM(jl.debit - jl.credit), 0) AS receivable_balance,
                        COUNT(DISTINCT jl.journal_entry_id)    AS transaction_count,
                        MAX(jl.entry_date)                     AS last_transaction_date
                 FROM acc_partners p
                 LEFT JOIN acc_journal_lines jl ON jl.partner_id = p.id
                 LEFT JOIN acc_accounts a       ON a.id = jl.account_id
                 WHERE p.crm_contact_id = ?
                   AND a.code LIKE '131%'",
                [$crmContactId]
            );
            return $rows[0] ?? null;
        });
    }

    /**
     * Recent accounting transactions for a CRM contact (PT/PC/HDBH/…).
     * @return array<int,array>
     */
    public static function fetchCustomerTransactions(int $crmContactId, int $limit = 50): array
    {
        if ($crmContactId <= 0) return [];
        $limit = max(1, min(500, $limit));
        return self::cached("tx:{$crmContactId}:{$limit}", function () use ($crmContactId, $limit) {
            return self::query(
                "SELECT d.doc_number, d.doc_date, dt.code AS doc_type,
                        dt.name AS doc_type_name, d.total_amount, d.description
                 FROM acc_documents d
                 JOIN acc_document_types dt ON dt.id = d.document_type_id
                 JOIN acc_partners p        ON p.id = d.partner_id
                 WHERE p.crm_contact_id = ?
                   AND d.status = 'POSTED'
                   AND dt.code IN ('PT','PC','UNC_THU','UNC_CHI','HDBH','CAN_TRU_CN')
                 ORDER BY d.doc_date DESC, d.id DESC
                 LIMIT {$limit}",
                [$crmContactId]
            );
        });
    }

    /**
     * P&L for a CRM contract (project).
     * Returns ['revenue', 'expense', 'profit'] or null.
     */
    public static function fetchProjectPnL(int $crmContractId): ?array
    {
        if ($crmContractId <= 0) return null;
        return self::cached("pnl:{$crmContractId}", function () use ($crmContractId) {
            $rows = self::query(
                "SELECT COALESCE(SUM(CASE WHEN a.code LIKE '5%' THEN jl.credit - jl.debit ELSE 0 END), 0) AS revenue,
                        COALESCE(SUM(CASE WHEN a.code LIKE '6%' THEN jl.debit - jl.credit ELSE 0 END), 0) AS expense,
                        COALESCE(SUM(CASE WHEN a.code LIKE '5%' THEN jl.credit - jl.debit
                                          WHEN a.code LIKE '6%' THEN -(jl.debit - jl.credit)
                                          ELSE 0 END), 0) AS profit
                 FROM acc_projects pr
                 LEFT JOIN acc_journal_lines jl ON jl.project_id = pr.id
                 LEFT JOIN acc_accounts a       ON a.id = jl.account_id
                 WHERE pr.crm_contract_id = ?",
                [$crmContractId]
            );
            return $rows[0] ?? null;
        });
    }
}
