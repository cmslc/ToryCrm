<?php

namespace App\Controllers\Api;

use Core\Controller;
use Core\Database;

class OrderApiController extends Controller
{
    public function list()
    {
        $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $sort = $_GET['sort'] ?? 'created_at';
        $order = strtoupper($_GET['order'] ?? 'DESC');

        $allowedSorts = ['id', 'order_number', 'status', 'total_amount', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $where = ['1=1'];
        $params = [];

        if (!empty($_GET['status'])) {
            $where[] = "o.status = ?";
            $params[] = $_GET['status'];
        }

        if (!empty($_GET['type'])) {
            $where[] = "o.type = ?";
            $params[] = $_GET['type'];
        }

        if (!empty($_GET['payment_status'])) {
            $where[] = "o.payment_status = ?";
            $params[] = $_GET['payment_status'];
        }

        if (!empty($_GET['contact_id'])) {
            $where[] = "o.contact_id = ?";
            $params[] = (int) $_GET['contact_id'];
        }

        if (!empty($_GET['search'])) {
            $where[] = "(o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
            $s = "%" . $_GET['search'] . "%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             WHERE {$whereClause}",
            $params
        )['count'];

        $orders = Database::fetchAll(
            "SELECT o.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name, u.name as owner_name
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN companies comp ON o.company_id = comp.id
             LEFT JOIN users u ON o.owner_id = u.id
             WHERE {$whereClause}
             ORDER BY o.{$sort} {$order}
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->json([
            'data' => $orders,
            'total' => (int) $total,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function detail()
    {
        $id = (int) ($_GET['id'] ?? 0);

        if (!$id) {
            return $this->json(['error' => 'Missing id parameter'], 400);
        }

        $order = Database::fetch(
            "SELECT o.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    c.email as contact_email, c.phone as contact_phone,
                    comp.name as company_name, u.name as owner_name,
                    d.title as deal_title
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN companies comp ON o.company_id = comp.id
             LEFT JOIN users u ON o.owner_id = u.id
             LEFT JOIN deals d ON o.deal_id = d.id
             WHERE o.id = ?",
            [$id]
        );

        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        // Get line items
        $order['items'] = Database::fetchAll(
            "SELECT oi.*, p.sku as product_sku
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?
             ORDER BY oi.sort_order",
            [$id]
        );

        // Get payments
        $order['payments'] = Database::fetchAll(
            "SELECT * FROM order_payments WHERE order_id = ? ORDER BY created_at DESC",
            [$id]
        );

        return $this->json(['data' => $order]);
    }

    public function create()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $type = $input['type'] ?? 'order';

        // Generate order number
        $prefix = $type === 'quote' ? 'QT' : 'ORD';
        $count = Database::fetch(
            "SELECT COUNT(*) as count FROM orders WHERE type = ?",
            [$type]
        )['count'];
        $orderNumber = $prefix . '-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);

        Database::beginTransaction();

        try {
            $orderId = Database::insert('orders', [
                'tenant_id' => $_SESSION['tenant_id'] ?? 1,
                'order_number' => $orderNumber,
                'type' => $type,
                'status' => $input['status'] ?? 'draft',
                'contact_id' => !empty($input['contact_id']) ? (int) $input['contact_id'] : null,
                'company_id' => !empty($input['company_id']) ? (int) $input['company_id'] : null,
                'deal_id' => !empty($input['deal_id']) ? (int) $input['deal_id'] : null,
                'discount_amount' => (float) ($input['discount_amount'] ?? 0),
                'discount_type' => $input['discount_type'] ?? 'fixed',
                'currency' => $input['currency'] ?? 'VND',
                'notes' => trim($input['notes'] ?? ''),
                'payment_method' => trim($input['payment_method'] ?? ''),
                'due_date' => !empty($input['due_date']) ? $input['due_date'] : null,
                'issued_date' => !empty($input['issued_date']) ? $input['issued_date'] : date('Y-m-d'),
                'owner_id' => !empty($input['owner_id']) ? (int) $input['owner_id'] : null,
                'created_by' => $_SESSION['api_user']['user_id'] ?? null,
            ]);

            // Add line items
            $subtotal = 0;
            $totalTax = 0;

            if (!empty($input['saved_order_details']) && is_array($input['saved_order_details'])) {
                $sort = 0;
                foreach ($input['saved_order_details'] as $item) {
                    if (empty($item['product_name'])) continue;

                    $qty = (float) ($item['quantity'] ?? 1);
                    $unitPrice = (float) ($item['unit_price'] ?? 0);
                    $taxRate = (float) ($item['tax_rate'] ?? 0);
                    $discount = (float) ($item['discount'] ?? 0);
                    $taxAmount = $qty * $unitPrice * $taxRate / 100;
                    $itemTotal = $qty * $unitPrice + $taxAmount - $discount;

                    Database::insert('order_items', [
                        'order_id' => $orderId,
                        'product_id' => !empty($item['product_id']) ? (int) $item['product_id'] : null,
                        'product_name' => $item['product_name'],
                        'description' => $item['description'] ?? '',
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? 'Cái',
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $taxAmount,
                        'discount' => $discount,
                        'total' => $itemTotal,
                        'sort_order' => $sort++,
                    ]);

                    $subtotal += $qty * $unitPrice;
                    $totalTax += $taxAmount;
                }
            }

            // Update order totals
            $discountAmount = (float) ($input['discount_amount'] ?? 0);
            $totalAmount = $subtotal + $totalTax - $discountAmount;

            Database::update('orders', [
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
            ], 'id = ?', [$orderId]);

            Database::commit();

            return $this->json([
                'message' => 'Thêm mới thành công',
                'id' => $orderId,
                'order_number' => $orderNumber,
            ], 201);
        } catch (\Exception $e) {
            Database::rollback();
            return $this->json(['error' => 'Failed to create order'], 500);
        }
    }

    public function approve()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);

        if (!$id) {
            return $this->json(['error' => 'Missing id parameter'], 400);
        }

        $order = Database::fetch("SELECT * FROM orders WHERE id = ?", [$id]);

        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        Database::update('orders', [
            'status' => 'confirmed',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        // Log activity
        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Duyệt đơn hàng: {$order['order_number']}",
            'user_id' => $_SESSION['api_user']['user_id'] ?? null,
        ]);

        return $this->json([
            'message' => 'Duyệt đơn hàng thành công',
            'id' => $id,
            'status' => 'confirmed',
        ]);
    }

    public function payment()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $orderId = (int) ($input['order_id'] ?? $_GET['id'] ?? 0);

        if (!$orderId) {
            return $this->json(['error' => 'Missing order_id parameter'], 400);
        }

        $order = Database::fetch("SELECT * FROM orders WHERE id = ?", [$orderId]);

        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        $amount = (float) ($input['amount'] ?? 0);

        if ($amount <= 0) {
            return $this->json(['error' => 'amount must be greater than 0'], 422);
        }

        $paymentId = Database::insert('order_payments', [
            'order_id' => $orderId,
            'amount' => $amount,
            'payment_method' => trim($input['payment_method'] ?? ''),
            'payment_date' => !empty($input['payment_date']) ? $input['payment_date'] : date('Y-m-d'),
            'reference' => trim($input['reference'] ?? ''),
            'notes' => trim($input['notes'] ?? ''),
            'created_by' => $_SESSION['api_user']['user_id'] ?? null,
        ]);

        // Update paid_amount on order
        $totalPaid = Database::fetch(
            "SELECT COALESCE(SUM(amount), 0) as total FROM order_payments WHERE order_id = ?",
            [$orderId]
        )['total'];

        $paymentStatus = 'unpaid';
        if ($totalPaid >= (float) $order['total_amount']) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        Database::update('orders', [
            'paid_amount' => $totalPaid,
            'payment_status' => $paymentStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$orderId]);

        return $this->json([
            'message' => 'Ghi nhận thanh toán thành công',
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'paid_amount' => (float) $totalPaid,
            'payment_status' => $paymentStatus,
        ], 201);
    }

    /**
     * Write-back endpoint for KT Accounting. Called after KT posts the
     * journal entry for an order — sends back the issued VAT invoice number
     * and the legal entity that issued it.
     *
     * Accepts PATCH-style payload (POST body):
     *   { "order_id": 123, "vat_invoice_number": "0001234",
     *     "accounting_entity": "VNT-HN", "synced_at": "2026-04-22 14:30:00" }
     *
     * Only updates the three accounting fields — never the order content.
     */
    public function accountingUpdate()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $orderId = (int) ($input['order_id'] ?? 0);
        if ($orderId <= 0) {
            return $this->json(['error' => 'order_id bắt buộc'], 422);
        }

        $tid = (int) ($_SESSION['tenant_id'] ?? 1);
        $order = Database::fetch(
            "SELECT id, order_number FROM orders WHERE id = ? AND tenant_id = ?",
            [$orderId, $tid]
        );
        if (!$order) {
            return $this->json(['error' => 'Order không tồn tại'], 404);
        }

        $invoiceNumber = trim((string) ($input['vat_invoice_number'] ?? ''));
        if ($invoiceNumber === '') {
            return $this->json(['error' => 'vat_invoice_number bắt buộc'], 422);
        }
        if (mb_strlen($invoiceNumber) > 50) {
            return $this->json(['error' => 'vat_invoice_number quá dài (max 50)'], 422);
        }

        $entity = trim((string) ($input['accounting_entity'] ?? ''));
        if (mb_strlen($entity) > 100) $entity = mb_substr($entity, 0, 100);

        // Allow KT to pass a custom timestamp; fall back to server now
        $syncedAt = $input['synced_at'] ?? $input['accounting_synced_at'] ?? null;
        if ($syncedAt && !preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}(:\d{2})?)?$/', $syncedAt)) {
            $syncedAt = null;
        }
        if (!$syncedAt) $syncedAt = date('Y-m-d H:i:s');

        // Detect duplicate invoice number across tenant — common accounting bug guard
        $clash = Database::fetch(
            "SELECT id, order_number FROM orders
             WHERE tenant_id = ? AND vat_invoice_number = ? AND id != ?
             LIMIT 1",
            [$tid, $invoiceNumber, $orderId]
        );
        if ($clash) {
            return $this->json([
                'error' => 'Số hóa đơn VAT đã gắn cho order khác',
                'existing_order_id' => (int) $clash['id'],
                'existing_order_number' => $clash['order_number'],
            ], 409);
        }

        Database::update('orders', [
            'vat_invoice_number' => $invoiceNumber,
            'accounting_synced_at' => $syncedAt,
            'accounting_entity' => $entity ?: null,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$orderId]);

        // Audit trail
        try {
            Database::insert('activities', [
                'type' => 'system',
                'title' => "KT Accounting: HDBH {$invoiceNumber} gắn cho {$order['order_number']}",
                'description' => "Entity: {$entity}; synced_at: {$syncedAt}",
                'user_id' => $_SESSION['api_user']['user_id'] ?? null,
                'tenant_id' => $tid,
            ]);
        } catch (\Exception $e) { /* non-fatal */ }

        return $this->json([
            'message' => 'Write-back thành công',
            'order_id' => $orderId,
            'order_number' => $order['order_number'],
            'vat_invoice_number' => $invoiceNumber,
            'accounting_synced_at' => $syncedAt,
            'accounting_entity' => $entity ?: null,
        ]);
    }
}
