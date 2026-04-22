<?php

namespace App\Controllers\Api;

use Core\Controller;
use Core\Database;

/**
 * Read-only API surface for the KT Accounting integration.
 *
 * All endpoints follow the same shape as Api/OrderApiController:
 *   - GET /api/v1/<resource>?limit=&offset=&updated_since=&sort=&order=
 *   - Response: { data: [...], total: N, limit, offset }
 *   - Tenant-scoped via $_SESSION['tenant_id'] (set by ApiAuthMiddleware)
 *   - updated_since=YYYY-MM-DD HH:MM:SS filters rows with updated_at >= that
 *     (where the table has an updated_at column)
 *
 * Pagination is capped at 500/page; KT sync uses 500 per chunk.
 */
class AccountingApiController extends Controller
{
    // Hard cap higher than standard 100 — accounting syncs large batches at night
    private const MAX_LIMIT = 500;
    private const DEFAULT_LIMIT = 200;

    // ========== Fund (cash/bank accounts) ==========

    public function funds()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');

        return $this->paginated('fund_accounts', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'name', 'balance', 'created_at', 'updated_at']));
    }

    public function fundTransactions()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');
        $this->filterEq($where, $params, 'type', $_GET['type'] ?? null);
        $this->filterEq($where, $params, 'status', $_GET['status'] ?? null);
        $this->filterEq($where, $params, 'fund_account_id', $_GET['fund_account_id'] ?? null);

        return $this->paginated('fund_transactions', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'transaction_date', 'amount', 'created_at', 'updated_at']));
    }

    // ========== Warehouse / stock ==========

    public function warehouses()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');

        return $this->paginated('warehouses', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'name', 'code', 'created_at', 'updated_at']));
    }

    public function stockMovements()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');
        $this->filterEq($where, $params, 'type', $_GET['type'] ?? null);
        $this->filterEq($where, $params, 'warehouse_id', $_GET['warehouse_id'] ?? null);
        $this->filterEq($where, $params, 'status', $_GET['status'] ?? null);

        return $this->paginated('stock_movements', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'created_at', 'updated_at', 'confirmed_at']));
    }

    // ========== Product master data ==========

    public function productCategories()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        // Include legacy rows where tenant_id IS NULL (shared seed data)
        $where = ['(tenant_id = ? OR tenant_id IS NULL)'];
        $params = [$tid];

        return $this->paginated('product_categories', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'sort_order', 'name', 'created_at', 'updated_at']));
    }

    // ========== Orders & items ==========

    public function orderItems()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');
        $this->filterEq($where, $params, 'order_id', $_GET['order_id'] ?? null);
        $this->filterEq($where, $params, 'product_id', $_GET['product_id'] ?? null);

        return $this->paginated('order_items', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'order_id', 'updated_at']));
    }

    // ========== Purchase orders ==========

    public function purchaseOrders()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');
        $this->filterEq($where, $params, 'status', $_GET['status'] ?? null);
        $this->filterEq($where, $params, 'payment_status', $_GET['payment_status'] ?? null);
        $this->filterEq($where, $params, 'supplier_id', $_GET['supplier_id'] ?? null);

        return $this->paginated('purchase_orders', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'order_code', 'created_at', 'updated_at', 'received_date']));
    }

    public function purchaseOrderItems()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');
        $this->filterEq($where, $params, 'purchase_order_id', $_GET['purchase_order_id'] ?? null);

        return $this->paginated('purchase_order_items', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'purchase_order_id', 'updated_at']));
    }

    // ========== HR / Payroll ==========

    public function payrolls()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');
        $this->filterEq($where, $params, 'user_id', $_GET['user_id'] ?? null);
        $this->filterEq($where, $params, 'month', $_GET['month'] ?? null);
        $this->filterEq($where, $params, 'year', $_GET['year'] ?? null);
        $this->filterEq($where, $params, 'status', $_GET['status'] ?? null);

        return $this->paginated('payrolls', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'year', 'month', 'user_id', 'updated_at', 'created_at']));
    }

    public function attendances()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');
        $this->filterEq($where, $params, 'user_id', $_GET['user_id'] ?? null);
        if (!empty($_GET['date_from'])) { $where[] = 'date >= ?'; $params[] = $_GET['date_from']; }
        if (!empty($_GET['date_to']))   { $where[] = 'date <= ?'; $params[] = $_GET['date_to']; }

        return $this->paginated('attendances', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'date', 'user_id', 'updated_at']));
    }

    // ========== Debts ==========

    public function debts()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');
        $this->filterEq($where, $params, 'type', $_GET['type'] ?? null);
        $this->filterEq($where, $params, 'status', $_GET['status'] ?? null);
        $this->filterEq($where, $params, 'contact_id', $_GET['contact_id'] ?? null);

        return $this->paginated('debts', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'due_date', 'amount', 'created_at', 'updated_at']));
    }

    public function debtPayments()
    {
        [$limit, $offset] = $this->page();
        $tid = $this->tid();
        $where = ['tenant_id = ?'];
        $params = [$tid];
        $this->appendUpdatedSince($where, $params, 'updated_at');
        $this->filterEq($where, $params, 'debt_id', $_GET['debt_id'] ?? null);

        return $this->paginated('debt_payments', $where, $params, $limit, $offset,
            $this->allowedSorts(['id', 'payment_date', 'created_at', 'updated_at']));
    }

    // ---------- shared helpers ----------

    private function tid(): int
    {
        return (int) ($_SESSION['tenant_id'] ?? 1);
    }

    /** @return array{0:int,1:int} */
    private function page(): array
    {
        $limit = max(1, min(self::MAX_LIMIT, (int)($_GET['limit'] ?? self::DEFAULT_LIMIT)));
        $offset = max(0, (int)($_GET['offset'] ?? 0));
        return [$limit, $offset];
    }

    private function filterEq(array &$where, array &$params, string $col, $val): void
    {
        if ($val === null || $val === '') return;
        $where[] = "{$col} = ?";
        $params[] = $val;
    }

    private function appendUpdatedSince(array &$where, array &$params, string $col): void
    {
        $since = $_GET['updated_since'] ?? null;
        if (!$since) return;
        // Accept "YYYY-MM-DD" or "YYYY-MM-DD HH:MM:SS"
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $since)) return;
        $where[] = "{$col} >= ?";
        $params[] = $since;
    }

    /** Resolve sort/order from $_GET against a whitelist; returns "col ASC|DESC". */
    private function allowedSorts(array $allowed): string
    {
        $sort = $_GET['sort'] ?? $allowed[0];
        if (!in_array($sort, $allowed, true)) $sort = $allowed[0];
        $order = strtoupper($_GET['order'] ?? 'DESC');
        if (!in_array($order, ['ASC', 'DESC'], true)) $order = 'DESC';
        return "{$sort} {$order}";
    }

    /** Run the count + fetch + json response for a paginated list. */
    private function paginated(string $table, array $where, array $params, int $limit, int $offset, string $orderBy)
    {
        $whereClause = implode(' AND ', $where);
        $total = (int) (Database::fetch(
            "SELECT COUNT(*) AS c FROM `{$table}` WHERE {$whereClause}",
            $params
        )['c'] ?? 0);

        $rows = Database::fetchAll(
            "SELECT * FROM `{$table}` WHERE {$whereClause} ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->json([
            'data' => $rows,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }
}
