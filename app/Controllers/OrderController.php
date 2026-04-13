<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Order;
use App\Controllers\WarehouseController;

class OrderController extends Controller
{
    public function index()
    {
        $this->authorize('orders', 'view');
        $search = $this->input('search');
        $type = $this->input('type');
        $status = $this->input('status');
        $paymentStatus = $this->input('payment_status');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ["o.is_deleted = 0", "o.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search) {
            $where[] = "(o.order_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR comp.name LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        if ($type) {
            $where[] = "o.type = ?";
            $params[] = $type;
        }

        if ($status) {
            $where[] = "o.status = ?";
            $params[] = $status;
        }

        if ($paymentStatus) {
            $where[] = "o.payment_status = ?";
            $params[] = $paymentStatus;
        }

        // Owner-based data scoping: staff only sees own records
        $ownerScope = $this->ownerScope('o', 'owner_id');
        if ($ownerScope['where']) {
            $where[] = $ownerScope['where'];
            $params = array_merge($params, $ownerScope['params']);
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN companies comp ON o.company_id = comp.id
             WHERE {$whereClause}",
            $params
        )['count'];

        $orders = Database::fetchAll(
            "SELECT o.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as owner_name
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN companies comp ON o.company_id = comp.id
             LEFT JOIN users u ON o.owner_id = u.id
             WHERE {$whereClause}
             ORDER BY o.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        return $this->view('orders.index', [
            'orders' => [
                'items' => $orders,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status,
                'payment_status' => $paymentStatus,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('orders', 'create');
        $orderModel = new Order();
        $type = $this->input('type') ?: 'order';
        $orderNumber = $orderModel->generateOrderNumber($type);

        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts ORDER BY first_name");
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $deals = Database::fetchAll("SELECT id, title FROM deals WHERE status = 'open' ORDER BY title");
        $products = Database::fetchAll("SELECT id, name, sku, price, unit, tax_rate FROM products WHERE is_active = 1 ORDER BY name");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        $contactId = (int) $this->input('contact_id');
        $companyId = (int) $this->input('company_id');
        $dealId = (int) $this->input('deal_id');

        return $this->view('orders.create', [
            'orderNumber' => $orderNumber,
            'type' => $type,
            'contacts' => $contacts,
            'companies' => $companies,
            'deals' => $deals,
            'products' => $products,
            'users' => $users,
            'selectedContactId' => $contactId,
            'selectedCompanyId' => $companyId,
            'selectedDealId' => $dealId,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('orders');
        }
        $this->authorize('orders', 'create');

        $data = $this->allInput();
        $type = $data['type'] ?? 'order';

        $orderModel = new Order();
        $orderNumber = $orderModel->generateOrderNumber($type);

        Database::beginTransaction();
        try {
            $orderId = Database::insert('orders', [
                'order_number' => $orderNumber,
                'type' => $type,
                'status' => $data['status'] ?? 'draft',
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
                'deal_id' => !empty($data['deal_id']) ? $data['deal_id'] : null,
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'currency' => 'VND',
                'notes' => trim($data['notes'] ?? ''),
                'payment_method' => trim($data['payment_method'] ?? ''),
                'due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
                'issued_date' => !empty($data['issued_date']) ? $data['issued_date'] : date('Y-m-d'),
                'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : $this->userId(),
                'created_by' => $this->userId(),
            ]);

            // Add order items
            if (!empty($data['items']) && is_array($data['items'])) {
                $sort = 0;
                foreach ($data['items'] as $item) {
                    if (empty($item['product_name'])) continue;

                    $qty = (float)($item['quantity'] ?? 1);
                    $unitPrice = (float)($item['unit_price'] ?? 0);
                    $taxRate = (float)($item['tax_rate'] ?? 0);
                    $taxAmount = $qty * $unitPrice * $taxRate / 100;
                    $itemTotal = $qty * $unitPrice + $taxAmount;

                    Database::insert('order_items', [
                        'order_id' => $orderId,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'],
                        'description' => $item['description'] ?? '',
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? 'Cái',
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $taxAmount,
                        'discount' => (float)($item['discount'] ?? 0),
                        'total' => $itemTotal,
                        'sort_order' => $sort++,
                    ]);
                }
            }

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi tạo đơn hàng: ' . $e->getMessage());
            return $this->back();
        }

        // Recalculate totals
        $orderModel->recalculate($orderId);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => ($type === 'quote' ? 'Báo giá' : 'Đơn hàng') . " tạo mới: {$orderNumber}",
            'user_id' => $this->userId(),
        ]);

        $typeLabel = $type === 'quote' ? 'Báo giá' : 'Đơn hàng';
        $this->setFlash('success', "{$typeLabel} {$orderNumber} đã được tạo.");
        return $this->redirect('orders/' . $orderId);
    }

    public function pdf($id)
    {
        $this->authorize('orders', 'view');
        $order = Database::fetch(
            "SELECT o.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name, c.email as contact_email, c.phone as contact_phone,
                    comp.name as company_name, u.name as owner_name
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN companies comp ON o.company_id = comp.id
             LEFT JOIN users u ON o.owner_id = u.id
             WHERE o.id = ?",
            [$id]
        );

        if (!$order) {
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect('orders');
        }

        $items = Database::fetchAll(
            "SELECT oi.* FROM order_items oi WHERE oi.order_id = ? ORDER BY oi.sort_order",
            [$id]
        );

        $noLayout = true;
        echo \App\Services\PdfService::orderHtml($order, $items);
    }

    /**
     * Invoice PDF - detailed invoice template
     */
    public function invoicePdf($id)
    {
        $this->authorize('orders', 'view');
        $html = \App\Services\PdfService::generateInvoicePdf((int) $id);

        if (empty($html)) {
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect('orders');
        }

        $noLayout = true;
        echo $html;
    }

    /**
     * Quotation PDF - quotation template with validity period
     */
    public function quotationPdf($id)
    {
        $this->authorize('orders', 'view');
        $html = \App\Services\PdfService::generateQuotationPdf((int) $id);

        if (empty($html)) {
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect('orders');
        }

        $noLayout = true;
        echo $html;
    }

    public function show($id)
    {
        $this->authorize('orders', 'view');
        $order = Database::fetch(
            "SELECT o.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name, c.email as contact_email, c.phone as contact_phone,
                    comp.name as company_name, comp.address as company_address, comp.tax_code as company_tax_code,
                    u.name as owner_name,
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
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect('orders');
        }

        // Ownership check: staff can only view own records
        if (!$this->canAccessOwner($order['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('orders');
        }

        $items = Database::fetchAll(
            "SELECT oi.*, p.sku as product_sku
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?
             ORDER BY oi.sort_order",
            [$id]
        );

        return $this->view('orders.show', [
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('orders', 'edit');
        $order = Database::fetch("SELECT * FROM orders WHERE id = ?", [$id]);

        if (!$order) {
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect('orders');
        }

        // Ownership check: staff can only edit own records
        if (!$this->canAccessOwner($order['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('orders');
        }

        $items = Database::fetchAll(
            "SELECT oi.*, p.sku as product_sku
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?
             ORDER BY oi.sort_order",
            [$id]
        );

        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts ORDER BY first_name");
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $deals = Database::fetchAll("SELECT id, title FROM deals WHERE status = 'open' ORDER BY title");
        $products = Database::fetchAll("SELECT id, name, sku, price, unit, tax_rate FROM products WHERE is_active = 1 ORDER BY name");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('orders.edit', [
            'order' => $order,
            'items' => $items,
            'contacts' => $contacts,
            'companies' => $companies,
            'deals' => $deals,
            'products' => $products,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('orders/' . $id);
        }
        $this->authorize('orders', 'edit');

        $order = Database::fetch("SELECT * FROM orders WHERE id = ?", [$id]);

        if (!$order) {
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect('orders');
        }

        // Ownership check: staff can only update own records
        if (!$this->canAccessOwner($order['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('orders');
        }

        $data = $this->allInput();

        Database::update('orders', [
            'status' => $data['status'] ?? $order['status'],
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
            'deal_id' => !empty($data['deal_id']) ? $data['deal_id'] : null,
            'discount_amount' => (float)($data['discount_amount'] ?? 0),
            'discount_type' => $data['discount_type'] ?? 'fixed',
            'notes' => trim($data['notes'] ?? ''),
            'payment_status' => $data['payment_status'] ?? $order['payment_status'],
            'payment_method' => trim($data['payment_method'] ?? ''),
            'paid_amount' => (float)($data['paid_amount'] ?? 0),
            'due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            'issued_date' => !empty($data['issued_date']) ? $data['issued_date'] : null,
            'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : null,
        ], 'id = ?', [$id]);

        // Update order items - delete old and re-insert
        Database::delete('order_items', 'order_id = ?', [$id]);

        if (!empty($data['items']) && is_array($data['items'])) {
            $sort = 0;
            foreach ($data['items'] as $item) {
                if (empty($item['product_name'])) continue;

                $qty = (float)($item['quantity'] ?? 1);
                $unitPrice = (float)($item['unit_price'] ?? 0);
                $taxRate = (float)($item['tax_rate'] ?? 0);
                $taxAmount = $qty * $unitPrice * $taxRate / 100;
                $itemTotal = $qty * $unitPrice + $taxAmount;

                Database::insert('order_items', [
                    'order_id' => $id,
                    'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'] ?? '',
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'Cái',
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'discount' => (float)($item['discount'] ?? 0),
                    'total' => $itemTotal,
                    'sort_order' => $sort++,
                ]);
            }
        }

        $orderModel = new Order();
        $orderModel->recalculate($id);

        $this->setFlash('success', 'Cập nhật đơn hàng thành công.');
        return $this->redirect('orders/' . $id);
    }

    // ---- Duyệt đơn hàng ----
    public function approve($id)
    {
        if (!$this->isPost()) return $this->redirect('orders/' . $id);
        $this->authorize('orders', 'approve');

        $order = $this->findSecure('orders', (int)$id);
        if (!$order) { $this->setFlash('error', 'Đơn hàng không tồn tại.'); return $this->redirect('orders'); }

        if (!in_array($order['status'], ['draft', 'sent'])) {
            $this->setFlash('error', 'Chỉ duyệt được đơn ở trạng thái Nháp hoặc Đã gửi.');
            return $this->redirect('orders/' . $id);
        }

        Database::update('orders', [
            'status' => 'confirmed',
            'approved_by' => $this->userId(),
            'approved_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Duyệt đơn hàng: {$order['order_number']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', "Đã duyệt đơn hàng {$order['order_number']}.");
        return $this->redirect('orders/' . $id);
    }

    // ---- Hủy đơn hàng ----
    public function cancel($id)
    {
        if (!$this->isPost()) return $this->redirect('orders/' . $id);
        $this->authorize('orders', 'edit');

        $order = $this->findSecure('orders', (int)$id);
        if (!$order) { $this->setFlash('error', 'Đơn hàng không tồn tại.'); return $this->redirect('orders'); }

        if ($order['status'] === 'completed') {
            $this->setFlash('error', 'Không thể hủy đơn đã hoàn thành.');
            return $this->redirect('orders/' . $id);
        }

        Database::update('orders', [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'cancelled_reason' => trim($this->input('reason') ?? ''),
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Hủy đơn hàng: {$order['order_number']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', "Đã hủy đơn hàng {$order['order_number']}.");
        return $this->redirect('orders/' . $id);
    }

    // ---- Khôi phục đơn hàng ----
    public function trash()
    {
        $this->authorize('orders', 'delete');
        $tid = Database::tenantId();
        $orders = Database::fetchAll(
            "SELECT o.*, c.first_name as contact_first_name, c.last_name as contact_last_name
             FROM orders o LEFT JOIN contacts c ON o.contact_id = c.id
             WHERE o.is_deleted = 1 AND o.tenant_id = ?
             ORDER BY o.deleted_at DESC",
            [$tid]
        );
        return $this->view('orders.trash', ['orders' => $orders]);
    }

    public function restore($id)
    {
        if (!$this->isPost()) return $this->redirect('orders/trash');
        $this->authorize('orders', 'delete');
        Database::restore('orders', 'id = ?', [$id]);
        $this->setFlash('success', 'Đã khôi phục đơn hàng.');
        return $this->redirect('orders/trash');
    }

    // ---- Xóa đơn hàng (soft delete) ----
    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('orders');
        $this->authorize('orders', 'delete');

        $order = $this->findSecure('orders', (int)$id);
        if (!$order) { $this->setFlash('error', 'Đơn hàng không tồn tại.'); return $this->redirect('orders'); }

        // Ownership check: staff can only delete own records
        if (!$this->canAccessOwner($order['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('orders');
        }

        Database::softDelete('orders', 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Xóa đơn hàng: {$order['order_number']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã xóa đơn hàng.');
        return $this->redirect('orders');
    }

    // ---- Thanh toán đơn hàng ----
    public function payment($id)
    {
        if (!$this->isPost()) return $this->redirect('orders/' . $id);
        $this->authorize('orders', 'edit');

        $order = $this->findSecure('orders', (int)$id);
        if (!$order) { $this->setFlash('error', 'Đơn hàng không tồn tại.'); return $this->redirect('orders'); }

        $amount = (float)$this->input('amount');
        $method = trim($this->input('payment_method') ?? '');
        $description = trim($this->input('description') ?? '');
        $payDate = $this->input('pay_date') ?: date('Y-m-d');

        if ($amount <= 0) {
            $this->setFlash('error', 'Số tiền thanh toán phải lớn hơn 0.');
            return $this->back();
        }

        Database::insert('order_payments', [
            'order_id' => $id,
            'payment_method' => $method,
            'payment_via' => 'direct',
            'amount' => $amount,
            'description' => $description,
            'pay_date' => $payDate,
            'created_by' => $this->userId(),
        ]);

        // Update paid_amount and payment_status
        $totalPaid = (float)(Database::fetch(
            "SELECT COALESCE(SUM(amount),0) as total FROM order_payments WHERE order_id = ?", [$id]
        )['total'] ?? 0);

        $paymentStatus = 'unpaid';
        if ($totalPaid >= (float)$order['total']) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        Database::update('orders', [
            'paid_amount' => $totalPaid,
            'payment_status' => $paymentStatus,
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Thanh toán " . format_money($amount) . " cho {$order['order_number']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã ghi nhận thanh toán ' . format_money($amount) . '.');
        return $this->redirect('orders/' . $id);
    }

    public function quickUpdate($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('orders', 'edit');

        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$order) {
            return $this->json(['error' => 'Đơn hàng không tồn tại'], 404);
        }

        $field = $this->input('field');
        $value = $this->input('value');
        $allowed = ['status', 'owner_id', 'payment_status'];

        if (!in_array($field, $allowed)) {
            return $this->json(['error' => 'Trường không được phép cập nhật'], 422);
        }

        Database::update('orders', [$field => $value ?: null], 'id = ?', [$id]);

        $display = $value;
        if ($field === 'status') {
            $labels = ['draft' => 'Nháp', 'sent' => 'Đã gửi', 'confirmed' => 'Đã duyệt', 'processing' => 'Đang xử lý', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
            $display = $labels[$value] ?? $value;
        } elseif ($field === 'owner_id') {
            $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$value]);
            $display = $user ? htmlspecialchars($user['name']) : '-';
        } elseif ($field === 'payment_status') {
            $labels = ['unpaid' => 'Chưa thanh toán', 'partial' => 'Thanh toán 1 phần', 'paid' => 'Đã thanh toán'];
            $display = $labels[$value] ?? $value;
        }

        return $this->json(['success' => true, 'value' => $value, 'display' => $display]);
    }

    public function updateStatus($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $this->authorize('orders', 'edit');

        $order = Database::fetch("SELECT * FROM orders WHERE id = ?", [$id]);
        if (!$order) return $this->json(['error' => 'Order not found'], 404);

        $newStatus = $this->input('status');
        if (!in_array($newStatus, ['draft','sent','confirmed','processing','completed','cancelled'])) {
            return $this->json(['error' => 'Invalid status'], 422);
        }

        Database::update('orders', ['status' => $newStatus], 'id = ?', [$id]);

        // Auto export stock when order completed (if enabled in settings)
        if ($newStatus === 'completed' && $order['status'] !== 'completed' && WarehouseController::isEnabled('auto_export_on_order')) {
            $this->autoExportStock((int)$id, 'order');
        }

        return $this->json(['success' => true, 'status' => $newStatus]);
    }

    private function autoExportStock(int $orderId, string $type): void
    {
        try {
            // Use settings default warehouse or fallback to is_default
            $tenant = Database::fetch("SELECT settings FROM tenants WHERE id = ?", [Database::tenantId()]);
            $whSettings = json_decode($tenant['settings'] ?? '{}', true)['warehouse'] ?? [];
            $defaultWhId = $whSettings['default_warehouse_id'] ?? 0;
            $defaultWh = $defaultWhId ? Database::fetch("SELECT id FROM warehouses WHERE id = ?", [$defaultWhId]) : null;
            if (!$defaultWh) $defaultWh = Database::fetch("SELECT id FROM warehouses WHERE tenant_id = ? AND is_default = 1 LIMIT 1", [Database::tenantId()]);
            if (!$defaultWh) return;

            $items = Database::fetchAll("SELECT product_id, quantity FROM order_items WHERE order_id = ?", [$orderId]);
            if (empty($items)) return;

            $code = ($type === 'order' ? 'XK' : 'NK') . '-DH' . $orderId;
            $movementId = Database::insert('stock_movements', [
                'code' => $code,
                'type' => $type === 'order' ? 'export' : 'import',
                'warehouse_id' => $defaultWh['id'],
                'reference_type' => $type,
                'reference_id' => $orderId,
                'status' => 'confirmed',
                'note' => 'Tự động từ đơn hàng #' . $orderId,
                'created_by' => $this->userId(),
                'confirmed_by' => $this->userId(),
                'confirmed_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($items as $item) {
                Database::insert('stock_movement_items', [
                    'movement_id' => $movementId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);

                // Update stock
                $qty = $type === 'order' ? -$item['quantity'] : $item['quantity'];
                $existing = Database::fetch("SELECT id, quantity FROM stock WHERE warehouse_id = ? AND product_id = ?", [$defaultWh['id'], $item['product_id']]);
                if ($existing) {
                    Database::update('stock', ['quantity' => (float)$existing['quantity'] + $qty], 'id = ?', [$existing['id']]);
                } else {
                    Database::query("INSERT INTO stock (warehouse_id, product_id, quantity) VALUES (?, ?, ?)", [$defaultWh['id'], $item['product_id'], max(0, $qty)]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if warehouse tables don't exist yet
        }
    }
}
