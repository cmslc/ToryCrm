<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Order extends Model
{
    protected string $table = 'orders';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = '1=1';
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR comp.name LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        if (!empty($filters['type'])) {
            $where .= " AND o.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['status'])) {
            $where .= " AND o.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $where .= " AND o.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN companies comp ON o.company_id = comp.id
             WHERE {$where}",
            $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT o.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as owner_name,
                    d.title as deal_title
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN companies comp ON o.company_id = comp.id
             LEFT JOIN users u ON o.owner_id = u.id
             LEFT JOIN deals d ON o.deal_id = d.id
             WHERE {$where}
             ORDER BY o.created_at DESC
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

    public function getItems(int $orderId): array
    {
        return Database::fetchAll(
            "SELECT oi.*, p.sku as product_sku
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?
             ORDER BY oi.sort_order",
            [$orderId]
        );
    }

    public function generateOrderNumber(string $type = 'order'): string
    {
        $prefix = $type === 'quote' ? 'BG' : 'DH';
        $year = date('y');
        $month = date('m');

        $last = Database::fetch(
            "SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . $year . $month . '%']
        );

        if ($last) {
            $num = (int)substr($last['order_number'], -4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . $year . $month . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function recalculate(int $orderId): void
    {
        $items = $this->getItems($orderId);
        $subtotal = 0;
        $taxTotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
            $taxTotal += $item['tax_amount'];
        }

        $order = $this->find($orderId);
        $discountAmount = (float)($order['discount_amount'] ?? 0);
        $discountType = $order['discount_type'] ?? 'fixed';

        if ($discountType === 'percent') {
            $discount = $subtotal * $discountAmount / 100;
        } else {
            $discount = $discountAmount;
        }

        $total = $subtotal + $taxTotal - $discount;

        Database::update('orders', [
            'subtotal' => $subtotal,
            'tax_amount' => $taxTotal,
            'total' => max(0, $total),
        ], 'id = ?', [$orderId]);
    }

    public function getRevenueStats(): array
    {
        return Database::fetch(
            "SELECT
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = 'completed' THEN total ELSE 0 END) as total_revenue,
                SUM(CASE WHEN status = 'completed' AND MONTH(created_at) = MONTH(NOW()) THEN total ELSE 0 END) as month_revenue,
                SUM(CASE WHEN payment_status = 'unpaid' THEN total ELSE 0 END) as unpaid_total
             FROM orders WHERE type = 'order'"
        ) ?: [];
    }

    public function getMonthlyRevenue(int $year): array
    {
        return Database::fetchAll(
            "SELECT MONTH(created_at) as month, SUM(total) as revenue, COUNT(*) as count
             FROM orders WHERE type = 'order' AND status = 'completed' AND YEAR(created_at) = ?
             GROUP BY MONTH(created_at) ORDER BY month",
            [$year]
        );
    }
}
