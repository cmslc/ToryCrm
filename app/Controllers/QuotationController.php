<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class QuotationController extends Controller
{
    /**
     * List quotations with filters and stats
     */
    public function index()
    {
        $search = $this->input('search');
        $status = $this->input('status');
        $contactId = $this->input('contact_id');
        $datePeriod = $this->input('date_period');
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 10;
        $offset = ($page - 1) * $perPage;

        $tid = Database::tenantId();

        // Stats
        $stats = Database::fetch(
            "SELECT
                SUM(status = 'draft' OR status = 'sent') as pending,
                SUM(status = 'accepted') as approved,
                SUM(status = 'converted') as has_order,
                SUM(status NOT IN ('draft','sent','accepted','converted','rejected') OR status IS NULL) as no_order,
                SUM(status = 'rejected' OR status = 'expired') as deleted
             FROM quotations WHERE tenant_id = ?" . (!$this->isAdminOrManager() && !$this->getDeptMemberIds() ? " AND owner_id = " . (int)$this->userId() : ($this->getDeptMemberIds() ? " AND owner_id IN (" . implode(',', $this->getDeptMemberIds()) . ")" : '')),
            [$tid]
        );

        $where = ["q.tenant_id = ?"];
        $params = [$tid];

        $ownerScope = $this->ownerScope('q', 'owner_id', 'orders');
        if ($ownerScope['where']) { $where[] = $ownerScope['where']; $params = array_merge($params, $ownerScope['params']); }

        if ($search) {
            $where[] = "(q.quote_number LIKE ? OR q.title LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        if ($status) {
            $where[] = "q.status = ?";
            $params[] = $status;
        }

        if ($contactId) {
            $where[] = "q.contact_id = ?";
            $params[] = $contactId;
        }

        switch ($datePeriod) {
            case 'today': $where[] = "DATE(q.created_at) = CURDATE()"; break;
            case 'yesterday': $where[] = "DATE(q.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)"; break;
            case 'this_week': $where[] = "YEARWEEK(q.created_at, 1) = YEARWEEK(CURDATE(), 1)"; break;
            case 'this_month': $where[] = "YEAR(q.created_at) = YEAR(CURDATE()) AND MONTH(q.created_at) = MONTH(CURDATE())"; break;
            case 'last_month': $where[] = "YEAR(q.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(q.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"; break;
            case 'this_year': $where[] = "YEAR(q.created_at) = YEAR(CURDATE())"; break;
            case 'custom':
                if ($dateFrom) { $where[] = "DATE(q.created_at) >= ?"; $params[] = $dateFrom; }
                if ($dateTo) { $where[] = "DATE(q.created_at) <= ?"; $params[] = $dateTo; }
                break;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count
             FROM quotations q
             LEFT JOIN contacts c ON q.contact_id = c.id
             WHERE {$whereClause}",
            $params
        )['count'];

        $quotations = Database::fetchAll(
            "SELECT q.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as owner_name
             FROM quotations q
             LEFT JOIN contacts c ON q.contact_id = c.id
             LEFT JOIN companies comp ON q.company_id = comp.id
             LEFT JOIN users u ON q.owner_id = u.id
             WHERE {$whereClause}
             ORDER BY q.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts WHERE tenant_id = ? ORDER BY first_name", [$tid]);

        return $this->view('quotations.index', [
            'quotations' => [
                'items' => $quotations,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'stats' => $stats,
            'contacts' => $contacts,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'contact_id' => $contactId,
                'date_period' => $datePeriod,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Create quotation form
     */
    public function create()
    {
        $tid = Database::tenantId();
        $quoteNumber = $this->generateQuoteNumber();

        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts WHERE tenant_id = ? ORDER BY first_name", [$tid]);
        $companies = Database::fetchAll("SELECT id, name FROM companies WHERE tenant_id = ? ORDER BY name", [$tid]);
        $deals = Database::fetchAll("SELECT id, title FROM deals WHERE tenant_id = ? AND status = 'open' ORDER BY title", [$tid]);
        $products = Database::fetchAll("SELECT id, name, sku, price, unit, tax_rate FROM products WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$tid]);
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [$tid]);

        return $this->view('quotations.create', [
            'quoteNumber' => $quoteNumber,
            'contacts' => $contacts,
            'companies' => $companies,
            'deals' => $deals,
            'products' => $products,
            'users' => $users,
        ]);
    }

    /**
     * Store new quotation
     */
    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('quotations');
        }

        $data = $this->allInput();
        $quoteNumber = $this->generateQuoteNumber();
        $portalToken = bin2hex(random_bytes(32));

        Database::beginTransaction();
        try {
            $quotationId = Database::insert('quotations', [
                'quote_number' => $quoteNumber,
                'title' => trim($data['title'] ?? ''),
                'status' => $data['action'] === 'send' ? 'sent' : 'draft',
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
                'deal_id' => !empty($data['deal_id']) ? $data['deal_id'] : null,
                'valid_until' => !empty($data['valid_until']) ? $data['valid_until'] : null,
                'notes' => trim($data['notes'] ?? ''),
                'terms' => trim($data['terms'] ?? ''),
                'currency' => 'VND',
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'portal_token' => $portalToken,
                'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : $this->userId(),
                'created_by' => $this->userId(),
                'tenant_id' => Database::tenantId(),
            ]);

            // Add items
            $subtotal = 0;
            $totalTax = 0;
            if (!empty($data['items']) && is_array($data['items'])) {
                $sort = 0;
                foreach ($data['items'] as $item) {
                    if (empty($item['product_name'])) continue;

                    $qty = (float)($item['quantity'] ?? 1);
                    $unitPrice = (float)($item['unit_price'] ?? 0);
                    $taxRate = (float)($item['tax_rate'] ?? 0);
                    $discount = (float)($item['discount'] ?? 0);
                    $lineSubtotal = $qty * $unitPrice;
                    $lineTax = $lineSubtotal * $taxRate / 100;
                    $lineTotal = $lineSubtotal + $lineTax - $discount;

                    Database::insert('quotation_items', [
                        'quotation_id' => $quotationId,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'],
                        'description' => $item['description'] ?? '',
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? 'Cái',
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $lineTax,
                        'discount' => $discount,
                        'total' => $lineTotal,
                        'sort_order' => $sort++,
                    ]);

                    $subtotal += $lineSubtotal;
                    $totalTax += $lineTax;
                }
            }

            // Calculate totals
            $discountAmount = (float)($data['discount_amount'] ?? 0);
            $discountType = $data['discount_type'] ?? 'fixed';
            $totalDiscount = $discountType === 'percent' ? $subtotal * $discountAmount / 100 : $discountAmount;
            $total = $subtotal + $totalTax - $totalDiscount;

            Database::update('quotations', [
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total' => max(0, $total),
            ], 'id = ?', [$quotationId]);

            if ($data['action'] === 'send') {
                Database::update('quotations', [
                    'sent_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$quotationId]);
            }

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi tạo báo giá: ' . $e->getMessage());
            return $this->back();
        }

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Báo giá tạo mới: {$quoteNumber}",
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        $this->setFlash('success', "Báo giá {$quoteNumber} đã được tạo.");
        return $this->redirect('quotations/' . $quotationId);
    }

    /**
     * Show quotation detail
     */
    public function show($id)
    {
        $quotation = Database::fetch(
            "SELECT q.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name, c.email as contact_email, c.phone as contact_phone,
                    comp.name as company_name, comp.address as company_address, comp.tax_code as company_tax_code,
                    u.name as owner_name,
                    d.title as deal_title,
                    uc.name as created_by_name
             FROM quotations q
             LEFT JOIN contacts c ON q.contact_id = c.id
             LEFT JOIN companies comp ON q.company_id = comp.id
             LEFT JOIN users u ON q.owner_id = u.id
             LEFT JOIN deals d ON q.deal_id = d.id
             LEFT JOIN users uc ON q.created_by = uc.id
             WHERE q.id = ? AND q.tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$quotation) {
            $this->setFlash('error', 'Báo giá không tồn tại.');
            return $this->redirect('quotations');
        }

        $items = Database::fetchAll(
            "SELECT qi.*, p.sku as product_sku
             FROM quotation_items qi
             LEFT JOIN products p ON qi.product_id = p.id
             WHERE qi.quotation_id = ?
             ORDER BY qi.sort_order",
            [$id]
        );

        return $this->view('quotations.show', [
            'quotation' => $quotation,
            'items' => $items,
        ]);
    }

    /**
     * Edit quotation form
     */
    public function edit($id)
    {
        $tid = Database::tenantId();
        $quotation = Database::fetch("SELECT * FROM quotations WHERE id = ? AND tenant_id = ?", [$id, $tid]);

        if (!$quotation) {
            $this->setFlash('error', 'Báo giá không tồn tại.');
            return $this->redirect('quotations');
        }

        $items = Database::fetchAll(
            "SELECT qi.*, p.sku as product_sku
             FROM quotation_items qi
             LEFT JOIN products p ON qi.product_id = p.id
             WHERE qi.quotation_id = ?
             ORDER BY qi.sort_order",
            [$id]
        );

        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts WHERE tenant_id = ? ORDER BY first_name", [$tid]);
        $companies = Database::fetchAll("SELECT id, name FROM companies WHERE tenant_id = ? ORDER BY name", [$tid]);
        $deals = Database::fetchAll("SELECT id, title FROM deals WHERE tenant_id = ? AND status = 'open' ORDER BY title", [$tid]);
        $products = Database::fetchAll("SELECT id, name, sku, price, unit, tax_rate FROM products WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$tid]);
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [$tid]);

        return $this->view('quotations.edit', [
            'quotation' => $quotation,
            'items' => $items,
            'contacts' => $contacts,
            'companies' => $companies,
            'deals' => $deals,
            'products' => $products,
            'users' => $users,
        ]);
    }

    /**
     * Update quotation
     */
    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('quotations/' . $id);
        }

        $quotation = Database::fetch("SELECT * FROM quotations WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);

        if (!$quotation) {
            $this->setFlash('error', 'Báo giá không tồn tại.');
            return $this->redirect('quotations');
        }

        $data = $this->allInput();

        Database::beginTransaction();
        try {
            Database::update('quotations', [
                'title' => trim($data['title'] ?? ''),
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
                'deal_id' => !empty($data['deal_id']) ? $data['deal_id'] : null,
                'valid_until' => !empty($data['valid_until']) ? $data['valid_until'] : null,
                'notes' => trim($data['notes'] ?? ''),
                'terms' => trim($data['terms'] ?? ''),
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : null,
            ], 'id = ?', [$id]);

            // Re-insert items
            Database::delete('quotation_items', 'quotation_id = ?', [$id]);

            $subtotal = 0;
            $totalTax = 0;
            if (!empty($data['items']) && is_array($data['items'])) {
                $sort = 0;
                foreach ($data['items'] as $item) {
                    if (empty($item['product_name'])) continue;

                    $qty = (float)($item['quantity'] ?? 1);
                    $unitPrice = (float)($item['unit_price'] ?? 0);
                    $taxRate = (float)($item['tax_rate'] ?? 0);
                    $discount = (float)($item['discount'] ?? 0);
                    $lineSubtotal = $qty * $unitPrice;
                    $lineTax = $lineSubtotal * $taxRate / 100;
                    $lineTotal = $lineSubtotal + $lineTax - $discount;

                    Database::insert('quotation_items', [
                        'quotation_id' => $id,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'],
                        'description' => $item['description'] ?? '',
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? 'Cái',
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'tax_amount' => $lineTax,
                        'discount' => $discount,
                        'total' => $lineTotal,
                        'sort_order' => $sort++,
                    ]);

                    $subtotal += $lineSubtotal;
                    $totalTax += $lineTax;
                }
            }

            $discountAmount = (float)($data['discount_amount'] ?? 0);
            $discountType = $data['discount_type'] ?? 'fixed';
            $totalDiscount = $discountType === 'percent' ? $subtotal * $discountAmount / 100 : $discountAmount;
            $total = $subtotal + $totalTax - $totalDiscount;

            Database::update('quotations', [
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total' => max(0, $total),
            ], 'id = ?', [$id]);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi cập nhật báo giá: ' . $e->getMessage());
            return $this->back();
        }

        $this->setFlash('success', 'Cập nhật báo giá thành công.');
        return $this->redirect('quotations/' . $id);
    }

    /**
     * Mark quotation as sent
     */
    public function send($id)
    {
        if (!$this->isPost()) return $this->redirect('quotations/' . $id);

        $quotation = Database::fetch("SELECT * FROM quotations WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$quotation) {
            $this->setFlash('error', 'Báo giá không tồn tại.');
            return $this->redirect('quotations');
        }

        Database::update('quotations', [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Gửi báo giá: {$quotation['quote_number']}",
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        $portalUrl = url('quote/' . $quotation['portal_token']);
        $this->setFlash('success', "Báo giá đã được gửi. Link khách hàng: {$portalUrl}");
        return $this->redirect('quotations/' . $id);
    }

    /**
     * Delete quotation
     */
    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('quotations');

        $quotation = Database::fetch("SELECT * FROM quotations WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$quotation) {
            $this->setFlash('error', 'Báo giá không tồn tại.');
            return $this->redirect('quotations');
        }

        Database::delete('quotation_items', 'quotation_id = ?', [$id]);
        Database::delete('quotations', 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Xóa báo giá: {$quotation['quote_number']}",
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        $this->setFlash('success', 'Đã xóa báo giá.');
        return $this->redirect('quotations');
    }

    /**
     * Convert quotation to order
     */
    public function convertToOrder($id)
    {
        if (!$this->isPost()) return $this->redirect('quotations/' . $id);

        $quotation = Database::fetch("SELECT * FROM quotations WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$quotation) {
            $this->setFlash('error', 'Báo giá không tồn tại.');
            return $this->redirect('quotations');
        }

        $items = Database::fetchAll("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY sort_order", [$id]);

        Database::beginTransaction();
        try {
            // Generate order number
            $prefix = 'DH' . date('ym');
            $last = Database::fetch("SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1", [$prefix . '%']);
            $seq = $last ? ((int) substr($last['order_number'], -4)) + 1 : 1;
            $orderNumber = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $orderId = Database::insert('orders', [
                'order_number' => $orderNumber,
                'type' => 'order',
                'status' => 'draft',
                'contact_id' => $quotation['contact_id'],
                'company_id' => $quotation['company_id'],
                'deal_id' => $quotation['deal_id'],
                'subtotal' => $quotation['subtotal'],
                'tax_amount' => $quotation['tax_amount'],
                'discount_amount' => $quotation['discount_amount'],
                'discount_type' => $quotation['discount_type'],
                'total' => $quotation['total'],
                'currency' => 'VND',
                'notes' => $quotation['notes'],
                'owner_id' => $quotation['owner_id'],
                'created_by' => $this->userId(),
                'tenant_id' => Database::tenantId(),
                'issued_date' => date('Y-m-d'),
            ]);

            foreach ($items as $item) {
                Database::insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'tax_amount' => $item['tax_amount'],
                    'discount' => $item['discount'],
                    'total' => $item['total'],
                    'sort_order' => $item['sort_order'],
                ]);
            }

            // Mark quotation as converted
            Database::update('quotations', [
                'status' => 'accepted',
                'converted_order_id' => $orderId,
            ], 'id = ?', [$id]);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi chuyển đổi: ' . $e->getMessage());
            return $this->back();
        }

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Chuyển báo giá {$quotation['quote_number']} thành đơn hàng {$orderNumber}",
            'user_id' => $this->userId(),
            'tenant_id' => Database::tenantId(),
        ]);

        $this->setFlash('success', "Đã chuyển báo giá thành đơn hàng {$orderNumber}.");
        return $this->redirect('orders/' . $orderId);
    }

    /**
     * PDF / printable view
     */
    public function pdf($id)
    {
        $quotation = Database::fetch(
            "SELECT q.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name, c.email as contact_email, c.phone as contact_phone,
                    comp.name as company_name, comp.address as company_address, comp.tax_code as company_tax_code,
                    u.name as owner_name
             FROM quotations q
             LEFT JOIN contacts c ON q.contact_id = c.id
             LEFT JOIN companies comp ON q.company_id = comp.id
             LEFT JOIN users u ON q.owner_id = u.id
             WHERE q.id = ? AND q.tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$quotation) {
            $this->setFlash('error', 'Báo giá không tồn tại.');
            return $this->redirect('quotations');
        }

        $items = Database::fetchAll("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY sort_order", [$id]);

        $noLayout = true;
        $branding = \App\Services\BrandingService::get();
        include BASE_PATH . '/resources/views/quotations/pdf.php';
    }

    /**
     * Public view via portal token (no auth)
     */
    public function publicView($token)
    {
        $quotation = Database::fetch(
            "SELECT q.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name, c.email as contact_email, c.phone as contact_phone,
                    comp.name as company_name, comp.address as company_address, comp.tax_code as company_tax_code,
                    u.name as owner_name
             FROM quotations q
             LEFT JOIN contacts c ON q.contact_id = c.id
             LEFT JOIN companies comp ON q.company_id = comp.id
             LEFT JOIN users u ON q.owner_id = u.id
             WHERE q.portal_token = ?",
            [$token]
        );

        if (!$quotation) {
            http_response_code(404);
            echo '<h1>Báo giá không tồn tại</h1>';
            return;
        }

        // Increment view count
        Database::query("UPDATE quotations SET view_count = view_count + 1 WHERE id = ?", [$quotation['id']]);

        $items = Database::fetchAll("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY sort_order", [$quotation['id']]);

        $branding = \App\Services\BrandingService::get();
        $noLayout = true;
        include BASE_PATH . '/resources/views/quotations/public.php';
    }

    /**
     * Public accept quotation
     */
    public function publicAccept($token)
    {
        $quotation = Database::fetch("SELECT * FROM quotations WHERE portal_token = ?", [$token]);

        if (!$quotation) {
            http_response_code(404);
            echo json_encode(['error' => 'Không tìm thấy báo giá']);
            return;
        }

        if ($quotation['status'] === 'accepted') {
            echo json_encode(['success' => true, 'message' => 'Báo giá đã được chấp nhận trước đó.']);
            return;
        }

        if ($quotation['valid_until'] && $quotation['valid_until'] < date('Y-m-d')) {
            echo json_encode(['error' => 'Báo giá đã hết hiệu lực.']);
            return;
        }

        Database::update('quotations', [
            'status' => 'accepted',
            'accepted_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$quotation['id']]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn! Báo giá đã được chấp nhận.']);
    }

    /**
     * Public reject quotation
     */
    public function publicReject($token)
    {
        $quotation = Database::fetch("SELECT * FROM quotations WHERE portal_token = ?", [$token]);

        if (!$quotation) {
            http_response_code(404);
            echo json_encode(['error' => 'Không tìm thấy báo giá']);
            return;
        }

        $reason = trim($_POST['reason'] ?? '');

        Database::update('quotations', [
            'status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'reject_reason' => $reason,
        ], 'id = ?', [$quotation['id']]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Báo giá đã bị từ chối.']);
    }

    /**
     * Generate quote number: BG + YYMM + sequence
     */
    private function generateQuoteNumber(): string
    {
        $prefix = 'BG' . date('ym');
        $last = Database::fetch(
            "SELECT quote_number FROM quotations WHERE quote_number LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . '%']
        );
        $seq = $last ? ((int) substr($last['quote_number'], -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
