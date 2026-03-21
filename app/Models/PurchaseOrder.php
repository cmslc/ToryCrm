<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class PurchaseOrder extends Model
{
    protected string $table = 'purchase_orders';

    public function getWithRelations(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $where = 'po.tenant_id = ?';
        $params = [Database::tenantId()];

        if (!empty($filters['search'])) {
            $where .= " AND (po.order_code LIKE ? OR s.name LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['status'])) {
            $where .= " AND po.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['payment_status'])) {
            $where .= " AND po.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        $total = Database::fetch(
            "SELECT COUNT(*) as total FROM purchase_orders po
             LEFT JOIN companies s ON po.supplier_id = s.id
             WHERE {$where}", $params
        )['total'];

        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT po.*, s.name as supplier_name, u.name as owner_name
             FROM purchase_orders po
             LEFT JOIN companies s ON po.supplier_id = s.id
             LEFT JOIN users u ON po.owner_id = u.id
             WHERE {$where}
             ORDER BY po.created_at DESC
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
            "SELECT poi.*, p.sku as product_sku
             FROM purchase_order_items poi
             LEFT JOIN products p ON poi.product_id = p.id
             WHERE poi.purchase_order_id = ?
             ORDER BY poi.sort_order",
            [$orderId]
        );
    }

    public function generateCode(): string
    {
        $prefix = 'PO';
        $year = date('y');
        $month = date('m');
        $last = Database::fetch(
            "SELECT order_code FROM purchase_orders WHERE order_code LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . $year . $month . '%']
        );
        $num = $last ? (int)substr($last['order_code'], -4) + 1 : 1;
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
        $discount = (float)($order['discount_amount'] ?? 0);
        $total = $subtotal + $taxTotal - $discount;

        Database::update('purchase_orders', [
            'subtotal' => $subtotal,
            'tax_amount' => $taxTotal,
            'total' => max(0, $total),
        ], 'id = ?', [$orderId]);
    }
}
