<?php

namespace App\Services;

use Core\Database;

/**
 * Tool/function definitions the AI assistant can call on demand.
 * Scope: Khách hàng (contacts) + Bán hàng (orders, quotations, products, revenue).
 *
 * Each tool:
 *   - has an OpenAI-compatible JSON schema (tenants(), contacts() etc)
 *   - has a PHP executor (exec*) that runs it against the CRM DB
 *   - is tenant-scoped and optionally owner-scoped via $visibleUserIds
 *
 * Exec methods return associative arrays that will be JSON-encoded back
 * to the LLM. Keep them compact — large responses eat the context window.
 */
class AiToolService
{
    /** Tool definitions in OpenAI function-calling format. */
    public static function definitions(): array
    {
        return [
            // ========== KHÁCH HÀNG ==========
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_contacts',
                    'description' => 'Tìm khách hàng theo tên, SĐT, email, hoặc mã KH (account_code). Trả về tối đa 10 kết quả.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Từ khóa tìm (tên/SĐT/email/mã KH/MST)'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_contact_detail',
                    'description' => 'Lấy chi tiết 1 khách hàng gồm thông tin cơ bản + 5 đơn hàng gần nhất + tổng doanh thu.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'contact_id' => ['type' => 'integer'],
                        ],
                        'required' => ['contact_id'],
                    ],
                ],
            ],

            // ========== BÁN HÀNG — ĐƠN HÀNG ==========
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_orders',
                    'description' => 'Tìm đơn hàng theo lọc. Trả về tối đa 15 đơn gần nhất match.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'enum' => ['draft','pending','approved','processing','completed','cancelled'], 'description' => 'Trạng thái'],
                            'payment_status' => ['type' => 'string', 'enum' => ['unpaid','partial','paid']],
                            'contact_id' => ['type' => 'integer'],
                            'from_date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                            'to_date' => ['type' => 'string', 'description' => 'YYYY-MM-DD'],
                            'order_number' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_order_detail',
                    'description' => 'Chi tiết 1 đơn hàng + danh sách sản phẩm + lịch sử thanh toán.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'order_id' => ['type' => 'integer'],
                        ],
                        'required' => ['order_id'],
                    ],
                ],
            ],

            // ========== BÁN HÀNG — BÁO GIÁ ==========
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_quotations',
                    'description' => 'Tìm báo giá theo lọc.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'enum' => ['draft','pending','approved','rejected','expired','converted']],
                            'contact_id' => ['type' => 'integer'],
                            'from_date' => ['type' => 'string'],
                            'to_date' => ['type' => 'string'],
                            'quote_number' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_quotation_detail',
                    'description' => 'Chi tiết 1 báo giá + danh sách sản phẩm.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'quotation_id' => ['type' => 'integer'],
                        ],
                        'required' => ['quotation_id'],
                    ],
                ],
            ],

            // ========== BÁN HÀNG — SẢN PHẨM ==========
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_products',
                    'description' => 'Tìm sản phẩm theo từ khóa hoặc SKU.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],

            // ========== BÁN HÀNG — THỐNG KÊ ==========
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_revenue',
                    'description' => 'Doanh thu theo khoảng thời gian. period="today|week|month|quarter|year" hoặc truyền from_date+to_date.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'period' => ['type' => 'string', 'enum' => ['today','week','month','quarter','year']],
                            'from_date' => ['type' => 'string'],
                            'to_date' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_top_customers',
                    'description' => 'Top 10 khách hàng mua nhiều nhất trong khoảng thời gian.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'period' => ['type' => 'string', 'enum' => ['today','week','month','quarter','year']],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_top_sellers',
                    'description' => 'Top 10 nhân viên bán hàng theo doanh số.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'period' => ['type' => 'string', 'enum' => ['today','week','month','quarter','year']],
                        ],
                    ],
                ],
            ],
        ];
    }

    /** Dispatch a tool call. Returns array to JSON-encode back to the LLM. */
    public static function execute(string $name, array $args, int $tenantId, ?array $visibleUserIds = null): array
    {
        try {
            return match ($name) {
                'search_contacts'      => self::searchContacts($args, $tenantId, $visibleUserIds),
                'get_contact_detail'   => self::getContactDetail($args, $tenantId, $visibleUserIds),
                'search_orders'        => self::searchOrders($args, $tenantId, $visibleUserIds),
                'get_order_detail'     => self::getOrderDetail($args, $tenantId, $visibleUserIds),
                'search_quotations'    => self::searchQuotations($args, $tenantId, $visibleUserIds),
                'get_quotation_detail' => self::getQuotationDetail($args, $tenantId, $visibleUserIds),
                'search_products'      => self::searchProducts($args, $tenantId),
                'get_revenue'          => self::getRevenue($args, $tenantId, $visibleUserIds),
                'get_top_customers'    => self::getTopCustomers($args, $tenantId, $visibleUserIds),
                'get_top_sellers'      => self::getTopSellers($args, $tenantId, $visibleUserIds),
                default                => ['error' => "Unknown tool: {$name}"],
            };
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // ---- helpers ----

    private static function scopeClause(string $col, ?array $ids): array
    {
        if ($ids === null) return ['', []];
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) return [" AND {$col} = 0", []];
        $ph = implode(',', array_fill(0, count($ids), '?'));
        return [" AND {$col} IN ({$ph})", $ids];
    }

    private static function periodRange(?string $period, ?string $from = null, ?string $to = null): array
    {
        if ($from && $to) return [$from, $to];
        $today = date('Y-m-d');
        return match ($period) {
            'today'   => [$today, $today],
            'week'    => [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))],
            'month'   => [date('Y-m-01'), date('Y-m-t')],
            'quarter' => [date('Y-m-01', strtotime('first day of -' . (((int)date('n') - 1) % 3) . ' months')), $today],
            'year'    => [date('Y-01-01'), date('Y-12-31')],
            default   => [date('Y-m-01'), date('Y-m-t')],
        };
    }

    // ---- tool implementations ----

    private static function searchContacts(array $a, int $tid, ?array $vids): array
    {
        $q = '%' . trim($a['query'] ?? '') . '%';
        [$sc, $sp] = self::scopeClause('c.owner_id', $vids);
        $rows = Database::fetchAll(
            "SELECT c.id, c.full_name, c.company_name, c.phone, c.mobile, c.email, c.company_email,
                    c.account_code, c.tax_code, c.status, c.total_revenue
             FROM contacts c
             WHERE c.tenant_id = ? AND c.is_deleted = 0
             AND (c.full_name LIKE ? OR c.company_name LIKE ? OR c.phone LIKE ?
                  OR c.mobile LIKE ? OR c.email LIKE ? OR c.account_code LIKE ? OR c.tax_code LIKE ?){$sc}
             ORDER BY c.total_revenue DESC LIMIT 10",
            array_merge([$tid, $q, $q, $q, $q, $q, $q, $q], $sp)
        );
        return ['count' => count($rows), 'contacts' => $rows];
    }

    private static function getContactDetail(array $a, int $tid, ?array $vids): array
    {
        $id = (int)($a['contact_id'] ?? 0);
        [$sc, $sp] = self::scopeClause('c.owner_id', $vids);
        $contact = Database::fetch(
            "SELECT c.id, c.full_name, c.company_name, c.phone, c.mobile, c.email,
                    c.address, c.province, c.district, c.ward,
                    c.account_code, c.tax_code, c.position, c.status,
                    c.total_revenue, c.created_at, u.name as owner_name
             FROM contacts c LEFT JOIN users u ON c.owner_id = u.id
             WHERE c.id = ? AND c.tenant_id = ? AND c.is_deleted = 0{$sc}",
            array_merge([$id, $tid], $sp)
        );
        if (!$contact) return ['error' => 'Không tìm thấy khách hàng hoặc không có quyền xem.'];

        $orders = Database::fetchAll(
            "SELECT id, order_number, status, payment_status, total, created_at
             FROM orders WHERE contact_id = ? AND tenant_id = ? AND is_deleted = 0
             ORDER BY created_at DESC LIMIT 5",
            [$id, $tid]
        );
        $quotes = Database::fetchAll(
            "SELECT id, quote_number, status, total, created_at
             FROM quotations WHERE contact_id = ? AND tenant_id = ?
             ORDER BY created_at DESC LIMIT 5",
            [$id, $tid]
        );
        return ['contact' => $contact, 'recent_orders' => $orders, 'recent_quotations' => $quotes];
    }

    private static function searchOrders(array $a, int $tid, ?array $vids): array
    {
        $where = ['o.tenant_id = ?', 'o.is_deleted = 0', "o.type = 'order'"];
        $params = [$tid];
        foreach (['status', 'payment_status', 'contact_id'] as $f) {
            if (!empty($a[$f])) { $where[] = "o.{$f} = ?"; $params[] = $a[$f]; }
        }
        if (!empty($a['order_number'])) { $where[] = "o.order_number LIKE ?"; $params[] = '%' . $a['order_number'] . '%'; }
        if (!empty($a['from_date'])) { $where[] = "DATE(o.created_at) >= ?"; $params[] = $a['from_date']; }
        if (!empty($a['to_date']))   { $where[] = "DATE(o.created_at) <= ?"; $params[] = $a['to_date']; }
        [$sc, $sp] = self::scopeClause('o.owner_id', $vids);
        $params = array_merge($params, $sp);
        $rows = Database::fetchAll(
            "SELECT o.id, o.order_number, o.status, o.payment_status, o.total,
                    o.paid_amount, o.created_at, c.full_name as customer, c.company_name
             FROM orders o LEFT JOIN contacts c ON o.contact_id = c.id
             WHERE " . implode(' AND ', $where) . $sc . "
             ORDER BY o.created_at DESC LIMIT 15",
            $params
        );
        return ['count' => count($rows), 'orders' => $rows];
    }

    private static function getOrderDetail(array $a, int $tid, ?array $vids): array
    {
        $id = (int)($a['order_id'] ?? 0);
        [$sc, $sp] = self::scopeClause('o.owner_id', $vids);
        $order = Database::fetch(
            "SELECT o.*, c.full_name as customer, c.company_name, u.name as owner_name
             FROM orders o LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN users u ON o.owner_id = u.id
             WHERE o.id = ? AND o.tenant_id = ? AND o.is_deleted = 0{$sc}",
            array_merge([$id, $tid], $sp)
        );
        if (!$order) return ['error' => 'Đơn hàng không tồn tại hoặc không có quyền xem.'];
        unset($order['tenant_id']);
        $items = Database::fetchAll(
            "SELECT product_name, quantity, unit, unit_price, discount, total FROM order_items WHERE order_id = ? ORDER BY sort_order",
            [$id]
        );
        $payments = Database::fetchAll(
            "SELECT amount, payment_method, payment_date FROM order_payments WHERE order_id = ? ORDER BY payment_date DESC",
            [$id]
        );
        return ['order' => $order, 'items' => $items, 'payments' => $payments];
    }

    private static function searchQuotations(array $a, int $tid, ?array $vids): array
    {
        $where = ['q.tenant_id = ?'];
        $params = [$tid];
        foreach (['status', 'contact_id'] as $f) {
            if (!empty($a[$f])) { $where[] = "q.{$f} = ?"; $params[] = $a[$f]; }
        }
        if (!empty($a['quote_number'])) { $where[] = "q.quote_number LIKE ?"; $params[] = '%' . $a['quote_number'] . '%'; }
        if (!empty($a['from_date'])) { $where[] = "DATE(q.created_at) >= ?"; $params[] = $a['from_date']; }
        if (!empty($a['to_date']))   { $where[] = "DATE(q.created_at) <= ?"; $params[] = $a['to_date']; }
        [$sc, $sp] = self::scopeClause('q.owner_id', $vids);
        $params = array_merge($params, $sp);
        $rows = Database::fetchAll(
            "SELECT q.id, q.quote_number, q.status, q.total, q.valid_until, q.created_at,
                    c.full_name as customer, c.company_name
             FROM quotations q LEFT JOIN contacts c ON q.contact_id = c.id
             WHERE " . implode(' AND ', $where) . $sc . "
             ORDER BY q.created_at DESC LIMIT 15",
            $params
        );
        return ['count' => count($rows), 'quotations' => $rows];
    }

    private static function getQuotationDetail(array $a, int $tid, ?array $vids): array
    {
        $id = (int)($a['quotation_id'] ?? 0);
        [$sc, $sp] = self::scopeClause('q.owner_id', $vids);
        $quote = Database::fetch(
            "SELECT q.*, c.full_name as customer, c.company_name, u.name as owner_name
             FROM quotations q LEFT JOIN contacts c ON q.contact_id = c.id
             LEFT JOIN users u ON q.owner_id = u.id
             WHERE q.id = ? AND q.tenant_id = ?{$sc}",
            array_merge([$id, $tid], $sp)
        );
        if (!$quote) return ['error' => 'Báo giá không tồn tại hoặc không có quyền xem.'];
        unset($quote['tenant_id'], $quote['content'], $quote['portal_token']);
        $items = Database::fetchAll(
            "SELECT product_name, quantity, unit, unit_price, discount, total FROM quotation_items WHERE quotation_id = ? ORDER BY sort_order",
            [$id]
        );
        return ['quotation' => $quote, 'items' => $items];
    }

    private static function searchProducts(array $a, int $tid): array
    {
        $q = '%' . trim($a['query'] ?? '') . '%';
        $rows = Database::fetchAll(
            "SELECT id, sku, name, unit, price, stock_quantity, category_id
             FROM products
             WHERE tenant_id = ? AND is_deleted = 0 AND is_active = 1
             AND (name LIKE ? OR sku LIKE ? OR barcode LIKE ?)
             ORDER BY name LIMIT 15",
            [$tid, $q, $q, $q]
        );
        return ['count' => count($rows), 'products' => $rows];
    }

    private static function getRevenue(array $a, int $tid, ?array $vids): array
    {
        [$from, $to] = self::periodRange($a['period'] ?? 'month', $a['from_date'] ?? null, $a['to_date'] ?? null);
        [$sc, $sp] = self::scopeClause('o.owner_id', $vids);
        $row = Database::fetch(
            "SELECT COUNT(*) as order_count, COALESCE(SUM(o.total),0) as revenue,
                    COALESCE(SUM(o.paid_amount),0) as collected
             FROM orders o
             WHERE o.tenant_id = ? AND o.is_deleted = 0 AND o.type = 'order'
             AND DATE(o.created_at) BETWEEN ? AND ?{$sc}",
            array_merge([$tid, $from, $to], $sp)
        );
        return [
            'from' => $from, 'to' => $to,
            'order_count' => (int)($row['order_count'] ?? 0),
            'revenue' => (float)($row['revenue'] ?? 0),
            'collected' => (float)($row['collected'] ?? 0),
        ];
    }

    private static function getTopCustomers(array $a, int $tid, ?array $vids): array
    {
        [$from, $to] = self::periodRange($a['period'] ?? 'month');
        [$sc, $sp] = self::scopeClause('o.owner_id', $vids);
        $rows = Database::fetchAll(
            "SELECT c.id, c.full_name, c.company_name, c.account_code,
                    COUNT(o.id) as orders, COALESCE(SUM(o.total),0) as revenue
             FROM orders o LEFT JOIN contacts c ON o.contact_id = c.id
             WHERE o.tenant_id = ? AND o.is_deleted = 0 AND o.type = 'order'
             AND DATE(o.created_at) BETWEEN ? AND ?{$sc}
             GROUP BY c.id, c.full_name, c.company_name, c.account_code
             ORDER BY revenue DESC LIMIT 10",
            array_merge([$tid, $from, $to], $sp)
        );
        return ['from' => $from, 'to' => $to, 'top_customers' => $rows];
    }

    private static function getTopSellers(array $a, int $tid, ?array $vids): array
    {
        [$from, $to] = self::periodRange($a['period'] ?? 'month');
        [$sc, $sp] = self::scopeClause('o.owner_id', $vids);
        $rows = Database::fetchAll(
            "SELECT u.id, u.name, COUNT(o.id) as orders, COALESCE(SUM(o.total),0) as revenue
             FROM orders o LEFT JOIN users u ON o.owner_id = u.id
             WHERE o.tenant_id = ? AND o.is_deleted = 0 AND o.type = 'order'
             AND DATE(o.created_at) BETWEEN ? AND ?{$sc}
             GROUP BY u.id, u.name
             ORDER BY revenue DESC LIMIT 10",
            array_merge([$tid, $from, $to], $sp)
        );
        return ['from' => $from, 'to' => $to, 'top_sellers' => $rows];
    }
}
