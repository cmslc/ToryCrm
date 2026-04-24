<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Order;
use App\Controllers\WarehouseController;

class OrderController extends Controller
{
    use \App\Traits\HasFollowers;

    public function followers($id) {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        return $this->json($this->handleFollowers('order', (int)$id));
    }

    public function changeOwner($id) {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        return $this->json($this->handleChangeOwner('orders', (int)$id));
    }
    public function index()
    {
        $this->authorize('orders', 'view');
        $search = $this->input('search');
        $type = $this->input('type');
        $status = $this->input('status');
        $paymentStatus = $this->input('payment_status');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
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

        $ownerId = $this->input('owner_id');
        if ($ownerId) {
            $where[] = "o.owner_id = ?";
            $params[] = $ownerId;
        }

        // Date period filter
        $datePeriod = $this->input('date_period');
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        switch ($datePeriod) {
            case 'today':
                $where[] = "DATE(o.created_at) = CURDATE()";
                break;
            case 'yesterday':
                $where[] = "DATE(o.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
                break;
            case 'this_week':
                $where[] = "YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'this_month':
                $where[] = "YEAR(o.created_at) = YEAR(CURDATE()) AND MONTH(o.created_at) = MONTH(CURDATE())";
                break;
            case 'last_month':
                $where[] = "YEAR(o.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(o.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
                break;
            case 'this_year':
                $where[] = "YEAR(o.created_at) = YEAR(CURDATE())";
                break;
            case 'custom':
                if ($dateFrom) { $where[] = "DATE(o.created_at) >= ?"; $params[] = $dateFrom; }
                if ($dateTo) { $where[] = "DATE(o.created_at) <= ?"; $params[] = $dateTo; }
                break;
        }

        // Owner-based data scoping: staff only sees own records
        $ownerScope = $this->ownerScope('o', 'owner_id', 'orders');
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
                    c.company_name, c.avatar as contact_avatar,
                    c.account_code as contact_account_code,
                    u.name as owner_name, u.avatar as owner_avatar,
                    u2.name as creator_name, u2.avatar as creator_avatar
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN users u ON o.owner_id = u.id
             LEFT JOIN users u2 ON o.created_by = u2.id
             WHERE {$whereClause}
             ORDER BY o.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        // Status counts for pills
        $scopeWhere = ["o.is_deleted = 0", "o.tenant_id = ?"];
        $scopeParams = [Database::tenantId()];
        $os = $this->ownerScope('o', 'owner_id', 'orders');
        if ($os['where']) { $scopeWhere[] = $os['where']; $scopeParams = array_merge($scopeParams, $os['params']); }
        $scopeClause = implode(' AND ', $scopeWhere);
        $statusCounts = Database::fetchAll("SELECT status, COUNT(*) as count FROM orders o WHERE {$scopeClause} GROUP BY status", $scopeParams);
        $paymentCounts = Database::fetchAll("SELECT payment_status as status, COUNT(*) as count FROM orders o WHERE {$scopeClause} GROUP BY payment_status", $scopeParams);
        $allCounts = array_merge($statusCounts, $paymentCounts);
        $totalAll = 0;
        foreach ($statusCounts as $sc) $totalAll += $sc['count'];

        $displayColumns = \App\Services\ColumnService::getColumns('orders');

        return $this->view('orders.index', [
            'orders' => [
                'items' => $orders,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'statusCounts' => $allCounts,
            'totalAll' => $totalAll,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'owner_id' => $ownerId,
                'date_period' => $datePeriod,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
            'displayColumns' => $displayColumns,
            'users' => $this->getVisibleUsersWithAvatar(),
        ]);
    }

    public function create()
    {
        $this->authorize('orders', 'create');
        $tid = Database::tenantId();
        $orderModel = new Order();
        $type = $this->input('type') ?: 'order';
        $orderNumber = $orderModel->generateOrderNumber($type);

        $preContactId = (int)($this->input('contact_id') ?: ($_GET['contact_id'] ?? 0));
        $preContact = null;
        if ($preContactId) {
            $preContact = Database::fetch(
                "SELECT id, first_name, last_name, full_name, company_name, account_code, tax_code, company_phone, company_email, phone, email, address
                 FROM contacts WHERE id = ? AND tenant_id = ?", [$preContactId, $tid]
            );
        }

        $users = Database::fetchAll("SELECT u.id, u.name, u.avatar, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [$tid]);

        return $this->view('orders.create', [
            'orderNumber' => $orderNumber,
            'type' => $type,
            'preContact' => $preContact,
            'preContactId' => $preContactId,
            'users' => $users,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('orders');
        }
        $this->authorize('orders', 'create');

        $data = $this->allInput();
        if (!empty($data['contact_id'])) {
            $contactOk = Database::fetch("SELECT id FROM contacts WHERE id = ? AND tenant_id = ?", [$data['contact_id'], Database::tenantId()]);
            if (!$contactOk) { $this->setFlash('error', 'Khách hàng không hợp lệ.'); return $this->back(); }
        }
        $type = $data['type'] ?? 'order';

        $orderModel = new Order();
        $orderNumber = $orderModel->generateOrderNumber($type);

        $shipContact = trim($data['shipping_contact'] ?? '');
        $shipPhone = trim($data['shipping_phone'] ?? '');
        if ((!$shipContact || !$shipPhone) && !empty($data['contact_person_id'])) {
            $cp = Database::fetch(
                "SELECT cp.full_name, cp.phone FROM contact_persons cp
                 JOIN contacts c ON cp.contact_id = c.id
                 WHERE cp.id = ? AND c.tenant_id = ?",
                [$data['contact_person_id'], Database::tenantId()]
            );
            if ($cp) {
                if (!$shipContact) $shipContact = $cp['full_name'] ?? '';
                if (!$shipPhone) $shipPhone = $cp['phone'] ?? '';
            }
        }

        Database::beginTransaction();
        try {
            $orderId = Database::insert('orders', [
                'order_number' => $orderNumber,
                'type' => $type,
                'status' => $data['status'] ?? 'draft',
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'contact_person_id' => !empty($data['contact_person_id']) ? $data['contact_person_id'] : null,
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
                'shipping_address' => trim($data['shipping_address'] ?? '') ?: null,
                'shipping_contact' => $shipContact ?: null,
                'shipping_phone' => $shipPhone ?: null,
                'shipping_province' => trim($data['shipping_province'] ?? '') ?: null,
                'shipping_district' => trim($data['shipping_district'] ?? '') ?: null,
                'delivery_type' => in_array($data['delivery_type'] ?? '', ['self','partner'], true) ? $data['delivery_type'] : 'self',
                'delivery_date' => !empty($data['delivery_date']) ? $data['delivery_date'] : null,
                'delivery_partner' => trim($data['delivery_partner'] ?? '') ?: null,
                'delivery_notes' => trim($data['delivery_notes'] ?? '') ?: null,
                'lading_code' => trim($data['lading_code'] ?? '') ?: null,
                'lading_status' => trim($data['lading_status'] ?? '') ?: null,
                'commission_amount' => (float)($data['commission_amount'] ?? 0),
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
                        'cost_price' => (float)($item['cost_price'] ?? 0),
                        'tax_rate' => $taxRate,
                        'tax_amount' => $taxAmount,
                        'discount_percent' => (float)($item['discount_percent'] ?? 0),
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
            'tenant_id' => Database::tenantId(),
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
                    c.full_name as c_full_name, c.company_name as c_company_name,
                    c.account_code as c_account_code, c.tax_code as c_tax_code,
                    c.address as c_address,
                    c.email as contact_email, c.phone as contact_phone,
                    c.company_email as c_company_email, c.company_phone as c_company_phone,
                    comp.name as company_name, u.name as owner_name,
                    cp.full_name as cp_full_name, cp.phone as cp_phone, cp.email as cp_email, cp.position as cp_position
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN companies comp ON o.company_id = comp.id
             LEFT JOIN users u ON o.owner_id = u.id
             LEFT JOIN contact_persons cp ON o.contact_person_id = cp.id
             WHERE o.id = ?",
            [$id]
        );

        if (!$order) {
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect('orders');
        }

        $items = Database::fetchAll(
            "SELECT oi.*, p.sku FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ? ORDER BY oi.sort_order",
            [$id]
        );

        $noLayout = true;

        $templateId = (int)($this->input('template_id') ?: 0);
        $copies = max(1, min(10, (int)($this->input('copies') ?: 2)));
        $baseReplacements = \App\Services\DocumentService::orderReplacements($order, $items);

        $pages = [];
        for ($i = 1; $i <= $copies; $i++) {
            $baseReplacements['{{lien_so}}'] = $i . '/' . $copies;
            $page = \App\Services\DocumentService::render('order', $templateId ?: null, $baseReplacements);
            if ($page === null) break;
            $pages[] = $page;
        }

        if (!empty($pages)) {
            $rendered = '';
            foreach ($pages as $idx => $p) {
                if ($idx > 0) $rendered .= '<div style="page-break-before:always"></div>';
                $rendered .= '<div class="liên">' . $p . '</div>';
            }

            $title = 'ĐƠN HÀNG ' . ($order['order_number'] ?? '');
            echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>'
                . '<style>body{font-family:"Segoe UI",Arial,sans-serif;font-size:13px;color:#333;padding:40px;max-width:900px;margin:0 auto}'
                . 'table{width:100%;border-collapse:collapse;margin:10px 0}th,td{padding:8px;border:1px solid #ddd;font-size:12px}'
                . 'h2{color:#405189}.no-print{text-align:center;margin-bottom:20px}@media print{.no-print{display:none}body{padding:20px}}</style>'
                . '</head><body><div class="no-print"><button onclick="window.print()" style="padding:10px 30px;background:#405189;color:#fff;border:none;border-radius:4px;cursor:pointer">In / Lưu PDF (' . count($pages) . ' liên)</button></div>'
                . $rendered
                . '<script>setTimeout(function(){window.print();},400);</script>'
                . '</body></html>';
            return;
        }

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
                    c.company_name as c_company_name, c.full_name as c_full_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    c.company_phone as c_company_phone, c.company_email as c_company_email,
                    c.phone as c_phone, c.email as c_email,
                    c.address as c_address, c.tax_code as c_tax_code, c.account_code as c_account_code,
                    u.name as owner_name,
                    d.title as deal_title,
                    uc.name as created_by_name,
                    os.name as order_source_name,
                    cp.name as campaign_name
             FROM orders o
             LEFT JOIN contacts c ON o.contact_id = c.id
             LEFT JOIN users u ON o.owner_id = u.id
             LEFT JOIN deals d ON o.deal_id = d.id
             LEFT JOIN users uc ON o.created_by = uc.id
             LEFT JOIN order_sources os ON o.order_source_id = os.id
             LEFT JOIN campaigns cp ON o.campaign_id = cp.id
             WHERE o.id = ?",
            [$id]
        );

        if (!$order) {
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect('orders');
        }

        // Ownership check: staff can only view own records
        if (!$this->canAccessEntity('order', (int)$order['id'], $order['owner_id'] ?? null)) {
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

        // Activities loaded by plugin (activity-exchange) in view

        $attachments = Database::fetchAll(
            "SELECT a.*, u.name as user_name
             FROM order_attachments a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.order_id = ?
             ORDER BY a.created_at DESC",
            [$id]
        );

        return $this->view('orders.show', [
            'order' => $order,
            'items' => $items,
            'attachments' => $attachments,
        ]);
    }

    public function uploadAttachment($id)
    {
        if (!$this->isPost()) return $this->redirect('orders/' . $id);
        $this->authorize('orders', 'edit');

        $order = Database::fetch("SELECT id, owner_id FROM orders WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$order) {
            $this->setFlash('error', 'Đơn hàng không tồn tại.');
            return $this->redirect('orders');
        }
        if (!$this->canAccessEntity('order', (int)$id, (int)($order['owner_id'] ?? 0))) {
            $this->setFlash('error', 'Không có quyền.');
            return $this->redirect('orders');
        }

        if (empty($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Vui lòng chọn file để tải lên.');
            return $this->redirect('orders/' . $id);
        }

        $file = $_FILES['attachment'];
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $this->setFlash('error', 'File quá lớn (tối đa 10MB).');
            return $this->redirect('orders/' . $id);
        }

        $uploadDir = BASE_PATH . '/public/uploads/orders/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'o' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        // Sanitize original name — strip path components + keep only basename
        $safeOriginalName = mb_substr(basename((string)$file['name']), 0, 200);

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $this->setFlash('error', 'Lỗi tải file lên.');
            return $this->redirect('orders/' . $id);
        }

        Database::insert('order_attachments', [
            'tenant_id' => Database::tenantId(),
            'order_id' => $id,
            'user_id' => $this->userId(),
            'filename' => $filename,
            'original_name' => $safeOriginalName,
            'file_size' => $file['size'],
            'mime_type' => @mime_content_type($uploadDir . $filename) ?: $file['type'],
        ]);

        $this->setFlash('success', 'Đã tải lên tài liệu.');
        return $this->redirect('orders/' . $id);
    }

    public function deleteAttachment($id, $attachId)
    {
        if (!$this->isPost()) return $this->redirect('orders/' . $id);
        $this->authorize('orders', 'edit');

        $order = Database::fetch("SELECT owner_id FROM orders WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$order || !$this->canAccessEntity('order', (int)$id, (int)($order['owner_id'] ?? 0))) {
            $this->setFlash('error', 'Không có quyền.');
            return $this->redirect('orders');
        }

        $attach = Database::fetch(
            "SELECT * FROM order_attachments WHERE id = ? AND order_id = ? AND tenant_id = ?",
            [$attachId, $id, Database::tenantId()]
        );

        if ($attach) {
            $path = BASE_PATH . '/public/uploads/orders/' . $attach['filename'];
            if (file_exists($path)) unlink($path);
            Database::delete('order_attachments', 'id = ?', [$attachId]);
            $this->setFlash('success', 'Đã xóa tài liệu.');
        }

        return $this->redirect('orders/' . $id);
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
        if (!$this->canAccessEntity('order', (int)$order['id'], $order['owner_id'] ?? null)) {
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

        $tid = Database::tenantId();
        $editContact = null;
        if ($order['contact_id']) {
            $editContact = Database::fetch(
                "SELECT id, first_name, last_name, full_name, company_name, account_code, tax_code, company_phone, company_email, phone, email, address
                 FROM contacts WHERE id = ?", [$order['contact_id']]
            );
        }
        $users = Database::fetchAll("SELECT u.id, u.name, u.avatar, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [$tid]);

        return $this->view('orders.edit', [
            'order' => $order,
            'items' => $items,
            'editContact' => $editContact,
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
        if (!$this->canAccessEntity('order', (int)$order['id'], $order['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('orders');
        }

        $data = $this->allInput();

        $shipContact = trim($data['shipping_contact'] ?? '');
        $shipPhone = trim($data['shipping_phone'] ?? '');
        if ((!$shipContact || !$shipPhone) && !empty($data['contact_person_id'])) {
            $cp = Database::fetch(
                "SELECT cp.full_name, cp.phone FROM contact_persons cp
                 JOIN contacts c ON cp.contact_id = c.id
                 WHERE cp.id = ? AND c.tenant_id = ?",
                [$data['contact_person_id'], Database::tenantId()]
            );
            if ($cp) {
                if (!$shipContact) $shipContact = $cp['full_name'] ?? '';
                if (!$shipPhone) $shipPhone = $cp['phone'] ?? '';
            }
        }

        Database::update('orders', [
            'status' => $data['status'] ?? $order['status'],
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'contact_person_id' => !empty($data['contact_person_id']) ? $data['contact_person_id'] : null,
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
            'shipping_address' => trim($data['shipping_address'] ?? '') ?: null,
            'shipping_contact' => $shipContact ?: null,
            'shipping_phone' => $shipPhone ?: null,
            'shipping_province' => trim($data['shipping_province'] ?? '') ?: null,
            'shipping_district' => trim($data['shipping_district'] ?? '') ?: null,
            'delivery_type' => in_array($data['delivery_type'] ?? '', ['self','partner'], true) ? $data['delivery_type'] : 'self',
            'delivery_date' => !empty($data['delivery_date']) ? $data['delivery_date'] : null,
            'delivery_partner' => trim($data['delivery_partner'] ?? '') ?: null,
            'delivery_notes' => trim($data['delivery_notes'] ?? '') ?: null,
            'lading_code' => trim($data['lading_code'] ?? '') ?: null,
            'lading_status' => trim($data['lading_status'] ?? '') ?: null,
            'commission_amount' => (float)($data['commission_amount'] ?? 0),
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
                    'cost_price' => (float)($item['cost_price'] ?? 0),
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'discount_percent' => (float)($item['discount_percent'] ?? 0),
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
        if (!$this->canAccessEntity('order', (int)$id, (int)($order['owner_id'] ?? 0))) { $this->setFlash('error', 'Không có quyền.'); return $this->redirect('orders'); }

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
            'tenant_id' => Database::tenantId(),
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
        if (!$this->canAccessEntity('order', (int)$id, (int)($order['owner_id'] ?? 0))) { $this->setFlash('error', 'Không có quyền.'); return $this->redirect('orders'); }

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
            'tenant_id' => Database::tenantId(),
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
        if (!$this->canAccessEntity('order', (int)$order['id'], $order['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('orders');
        }

        Database::softDelete('orders', 'id = ?', [$id]);

        Database::insert('activities', [
            'tenant_id' => Database::tenantId(),
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
        $this->authorize('orders', 'payment');

        $order = $this->findSecure('orders', (int)$id);
        if (!$order) { $this->setFlash('error', 'Đơn hàng không tồn tại.'); return $this->redirect('orders'); }
        if (!$this->canAccessEntity('order', (int)$id, (int)($order['owner_id'] ?? 0))) { $this->setFlash('error', 'Không có quyền.'); return $this->redirect('orders'); }

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
            'tenant_id' => Database::tenantId(),
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

        $order = Database::fetch("SELECT * FROM orders WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$order) return $this->json(['error' => 'Order not found'], 404);
        if (!$this->canAccessEntity('order', (int)$id, (int)($order['owner_id'] ?? 0))) {
            return $this->json(['error' => 'Không có quyền'], 403);
        }

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
