<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class FundTransaction extends Model
{
    protected string $table = 'fund_transactions';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = 'ft.tenant_id = ?';
        $params = [Database::tenantId()];

        if (!empty($filters['search'])) {
            $where .= " AND (ft.transaction_code LIKE ? OR ft.description LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['type'])) {
            $where .= " AND ft.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where .= " AND ft.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['fund_account_id'])) {
            $where .= " AND ft.fund_account_id = ?";
            $params[] = $filters['fund_account_id'];
        }

        if (!empty($filters['date_from'])) {
            $where .= " AND ft.transaction_date >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where .= " AND ft.transaction_date <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['created_by'])) {
            $where .= " AND ft.created_by = ?";
            $params[] = $filters['created_by'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM fund_transactions ft WHERE {$where}", $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT ft.*,
                    fa.name as fund_account_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as created_by_name
             FROM fund_transactions ft
             LEFT JOIN fund_accounts fa ON ft.fund_account_id = fa.id
             LEFT JOIN contacts c ON ft.contact_id = c.id
             LEFT JOIN companies comp ON ft.company_id = comp.id
             LEFT JOIN users u ON ft.created_by = u.id
             WHERE {$where}
             ORDER BY ft.transaction_date DESC, ft.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'items' => $items,
            'total' => (int)$total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getAccounts(): array
    {
        return Database::fetchAll("SELECT * FROM fund_accounts WHERE is_active = 1 ORDER BY name");
    }

    public function generateCode(string $type): string
    {
        $prefix = $type === 'receipt' ? 'PT' : 'PC';
        $year = date('y');
        $month = date('m');
        $last = Database::fetch(
            "SELECT transaction_code FROM fund_transactions WHERE transaction_code LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . $year . $month . '%']
        );
        $num = $last ? (int)substr($last['transaction_code'], -4) + 1 : 1;
        return $prefix . $year . $month . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function getSummary(string $dateFrom = null, string $dateTo = null): array
    {
        $where = 'status = ? AND tenant_id = ?';
        $params = ['confirmed', Database::tenantId()];

        if ($dateFrom) {
            $where .= " AND transaction_date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where .= " AND transaction_date <= ?";
            $params[] = $dateTo;
        }

        $result = Database::fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN type = 'receipt' THEN amount ELSE 0 END), 0) as total_receipt,
                COALESCE(SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END), 0) as total_payment,
                COUNT(CASE WHEN type = 'receipt' THEN 1 END) as receipt_count,
                COUNT(CASE WHEN type = 'payment' THEN 1 END) as payment_count
             FROM fund_transactions WHERE {$where}",
            $params
        );

        $result['balance'] = ($result['total_receipt'] ?? 0) - ($result['total_payment'] ?? 0);
        return $result;
    }
}
