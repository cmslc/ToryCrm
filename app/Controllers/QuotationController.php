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
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
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
             FROM quotations WHERE tenant_id = ?" . $this->getOwnerScopeSql('owner_id'),
            [$tid]
        );

        $where = ["q.tenant_id = ?"];
        $params = [$tid];

        $ownerScope = $this->ownerScope('q', 'owner_id', 'orders');
        if ($ownerScope['where']) { $where[] = $ownerScope['where']; $params = array_merge($params, $ownerScope['params']); }

        if ($search) {
            $where[] = "(q.quote_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company_name LIKE ?)";
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

        $ownerId = $this->input('owner_id');
        if ($ownerId) {
            $where[] = "q.owner_id = ?";
            $params[] = $ownerId;
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
                    c.avatar as contact_avatar, c.company_name,
                    u.name as owner_name, u.avatar as owner_avatar,
                    u2.name as creator_name, u2.avatar as creator_avatar
             FROM quotations q
             LEFT JOIN contacts c ON q.contact_id = c.id
             LEFT JOIN users u ON q.owner_id = u.id
             LEFT JOIN users u2 ON q.created_by = u2.id
             WHERE {$whereClause}
             ORDER BY q.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        $contacts = Database::fetchAll(
            "SELECT DISTINCT c.id, c.first_name, c.last_name, c.company_name
             FROM contacts c INNER JOIN quotations q ON q.contact_id = c.id AND q.tenant_id = ?
             ORDER BY c.first_name LIMIT 200", [$tid]
        );
        $displayColumns = \App\Services\ColumnService::getColumns('quotations');

        return $this->view('quotations.index', [
            'quotations' => [
                'items' => $quotations,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'stats' => $stats,
            'contacts' => $contacts,
            'displayColumns' => $displayColumns,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'contact_id' => $contactId,
                'owner_id' => $ownerId,
                'date_period' => $datePeriod,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
            'users' => $this->getVisibleUsersWithAvatar(),
        ]);
    }

    /**
     * Create quotation form
     */
    public function create()
    {
        $tid = Database::tenantId();
        $quoteNumber = $this->generateQuoteNumber();
        $preContactId = (int)($this->input('contact_id') ?: ($_GET['contact_id'] ?? 0));

        // Chỉ load contact nếu có preContactId
        $preContact = null;
        if ($preContactId) {
            $preContact = Database::fetch(
                "SELECT id, first_name, last_name, full_name, company_name, account_code, company_phone, company_email, phone, email, address
                 FROM contacts WHERE id = ? AND tenant_id = ?", [$preContactId, $tid]
            );
        }

        $users = Database::fetchAll("SELECT u.id, u.name, u.avatar, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [$tid]);

        $templates = Database::fetchAll("SELECT id, name, content, is_default FROM document_templates WHERE tenant_id = ? AND type = 'quotation' AND is_active = 1 ORDER BY is_default DESC, name", [$tid]);

        return $this->view('quotations.create', [
            'quoteNumber' => $quoteNumber,
            'preContact' => $preContact,
            'users' => $users,
            'preContactId' => $preContactId,
            'templates' => $templates,
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
                'status' => $data['action'] === 'send' ? 'sent' : 'draft',
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'contact_person_id' => !empty($data['contact_person_id']) ? $data['contact_person_id'] : null,
                'address' => trim($data['address'] ?? '') ?: null,
                'contact_phone' => trim($data['contact_phone'] ?? '') ?: null,
                'contact_email' => trim($data['contact_email'] ?? '') ?: null,
                'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
                'deal_id' => !empty($data['deal_id']) ? $data['deal_id'] : null,
                'valid_until' => !empty($data['valid_until']) ? $data['valid_until'] : null,
                'notes' => trim($data['notes'] ?? ''),
                'terms' => trim($data['terms'] ?? ''),
                'description' => trim($data['description'] ?? '') ?: null,
                'project' => trim($data['project'] ?? '') ?: null,
                'location' => trim($data['location'] ?? '') ?: null,
                'revision' => (int)($data['revision'] ?? 1),
                'content' => $data['content'] ?? null,
                'campaign_id' => !empty($data['campaign_id']) ? $data['campaign_id'] : null,
                'currency' => 'VND',
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'discount_percent' => (float)($data['discount_percent'] ?? 0),
                'discount_after_tax' => !empty($data['discount_after_tax']) ? 1 : 0,
                'shipping_fee' => (float)($data['shipping_fee'] ?? 0),
                'shipping_percent' => (float)($data['shipping_percent'] ?? 0),
                'shipping_after_tax' => !empty($data['shipping_after_tax']) ? 1 : 0,
                'shipping_note' => trim($data['shipping_note'] ?? '') ?: null,
                'tax_rate' => (float)($data['tax_rate'] ?? 0),
                'installation_fee' => (float)($data['installation_fee'] ?? 0),
                'installation_percent' => (float)($data['installation_percent'] ?? 0),
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
                        'discount_percent' => (float)($item['discount_percent'] ?? 0),
                        'discount' => $discount,
                        'total' => $lineTotal,
                        'sort_order' => $sort++,
                    ]);

                    $subtotal += $lineSubtotal;
                    $totalTax += $lineTax;
                }
            }

            // Calculate totals
            $taxRate = (float)($data['tax_rate'] ?? 0);
            $taxAmount = $subtotal * $taxRate / 100;
            $discountAmount = (float)($data['discount_amount'] ?? 0);
            $shippingFee = (float)($data['shipping_fee'] ?? 0);
            $installFee = (float)($data['installation_fee'] ?? 0);
            $total = $subtotal + $taxAmount - $discountAmount + $shippingFee + $installFee;

            Database::update('quotations', [
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => max(0, $total),
            ], 'id = ?', [$quotationId]);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi tạo báo giá: ' . $e->getMessage());
            return $this->back();
        }

        // Handle attachments
        $this->handleAttachments($quotationId);

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
                    c.company_name as c_company_name, c.full_name as c_full_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    c.company_phone as c_company_phone, c.company_email as c_company_email,
                    c.phone as c_phone, c.email as c_email,
                    c.address as c_address, c.tax_code as c_tax_code, c.account_code as c_account_code,
                    u.name as owner_name,
                    d.title as deal_title,
                    uc.name as created_by_name
             FROM quotations q
             LEFT JOIN contacts c ON q.contact_id = c.id
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

        $attachments = Database::fetchAll(
            "SELECT a.*, u.name as user_name FROM quotation_attachments a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.quotation_id = ? ORDER BY a.created_at DESC",
            [$id]
        );

        $pdfTemplates = Database::fetchAll(
            "SELECT id, name, is_default FROM document_templates WHERE tenant_id = ? AND type = 'quotation' AND is_active = 1 ORDER BY is_default DESC, name",
            [Database::tenantId()]
        );

        return $this->view('quotations.show', [
            'quotation' => $quotation,
            'items' => $items,
            'attachments' => $attachments,
            'pdfTemplates' => $pdfTemplates,
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

        // Load contact info for pre-fill
        $editContact = null;
        if ($quotation['contact_id']) {
            $editContact = Database::fetch(
                "SELECT id, first_name, last_name, full_name, company_name, account_code, company_phone, company_email, phone, email, address
                 FROM contacts WHERE id = ?", [$quotation['contact_id']]
            );
        }

        $users = Database::fetchAll("SELECT u.id, u.name, u.avatar, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [$tid]);

        $attachments = Database::fetchAll(
            "SELECT * FROM quotation_attachments WHERE quotation_id = ? ORDER BY created_at DESC",
            [$id]
        );

        $templates = Database::fetchAll("SELECT id, name, content, is_default FROM document_templates WHERE tenant_id = ? AND type = 'quotation' AND is_active = 1 ORDER BY is_default DESC, name", [$tid]);

        return $this->view('quotations.edit', [
            'quotation' => $quotation,
            'items' => $items,
            'editContact' => $editContact,
            'users' => $users,
            'attachments' => $attachments,
            'templates' => $templates,
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
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'contact_person_id' => !empty($data['contact_person_id']) ? $data['contact_person_id'] : null,
                'address' => trim($data['address'] ?? '') ?: null,
                'contact_phone' => trim($data['contact_phone'] ?? '') ?: null,
                'contact_email' => trim($data['contact_email'] ?? '') ?: null,
                'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
                'deal_id' => !empty($data['deal_id']) ? $data['deal_id'] : null,
                'valid_until' => !empty($data['valid_until']) ? $data['valid_until'] : null,
                'notes' => trim($data['notes'] ?? ''),
                'terms' => trim($data['terms'] ?? ''),
                'description' => trim($data['description'] ?? '') ?: null,
                'project' => trim($data['project'] ?? '') ?: null,
                'location' => trim($data['location'] ?? '') ?: null,
                'revision' => (int)($data['revision'] ?? 1),
                'content' => $data['content'] ?? null,
                'campaign_id' => !empty($data['campaign_id']) ? $data['campaign_id'] : null,
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'discount_percent' => (float)($data['discount_percent'] ?? 0),
                'discount_after_tax' => !empty($data['discount_after_tax']) ? 1 : 0,
                'shipping_fee' => (float)($data['shipping_fee'] ?? 0),
                'shipping_percent' => (float)($data['shipping_percent'] ?? 0),
                'shipping_after_tax' => !empty($data['shipping_after_tax']) ? 1 : 0,
                'shipping_note' => trim($data['shipping_note'] ?? '') ?: null,
                'tax_rate' => (float)($data['tax_rate'] ?? 0),
                'installation_fee' => (float)($data['installation_fee'] ?? 0),
                'installation_percent' => (float)($data['installation_percent'] ?? 0),
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
                        'discount_percent' => (float)($item['discount_percent'] ?? 0),
                        'discount' => $discount,
                        'total' => $lineTotal,
                        'sort_order' => $sort++,
                    ]);

                    $subtotal += $lineSubtotal;
                    $totalTax += $lineTax;
                }
            }

            $taxRate = (float)($data['tax_rate'] ?? 0);
            $taxAmount = $subtotal * $taxRate / 100;
            $discountAmount = (float)($data['discount_amount'] ?? 0);
            $shippingFee = (float)($data['shipping_fee'] ?? 0);
            $installFee = (float)($data['installation_fee'] ?? 0);
            $total = $subtotal + $taxAmount - $discountAmount + $shippingFee + $installFee;

            Database::update('quotations', [
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => max(0, $total),
            ], 'id = ?', [$id]);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi cập nhật báo giá: ' . $e->getMessage());
            return $this->back();
        }

        // Handle attachments
        $this->handleAttachments($id);

        $this->setFlash('success', 'Cập nhật báo giá thành công.');
        return $this->redirect('quotations/' . $id);
    }

    /**
     * Mark quotation as sent
     */
    public function submitForApproval($id)
    {
        if (!$this->isPost()) return $this->redirect('quotations/' . $id);
        $q = Database::fetch("SELECT * FROM quotations WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$q) { $this->setFlash('error', 'Không tìm thấy.'); return $this->redirect('quotations'); }

        Database::update('quotations', ['status' => 'pending'], 'id = ?', [$id]);
        $this->setFlash('success', 'Đã gửi duyệt báo giá.');
        return $this->redirect('quotations/' . $id);
    }

    public function approve($id)
    {
        if (!$this->isPost()) return $this->redirect('quotations/' . $id);
        $q = Database::fetch("SELECT * FROM quotations WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$q) { $this->setFlash('error', 'Không tìm thấy.'); return $this->redirect('quotations'); }

        Database::update('quotations', ['status' => 'approved'], 'id = ?', [$id]);
        $this->setFlash('success', 'Đã duyệt báo giá.');
        return $this->redirect('quotations/' . $id);
    }

    public function rejectApproval($id)
    {
        if (!$this->isPost()) return $this->redirect('quotations/' . $id);
        $q = Database::fetch("SELECT * FROM quotations WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$q) { $this->setFlash('error', 'Không tìm thấy.'); return $this->redirect('quotations'); }

        Database::update('quotations', [
            'status' => 'rejected',
            'rejection_reason' => trim($this->input('reason') ?? ''),
            'rejected_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);
        $this->setFlash('success', 'Đã từ chối duyệt báo giá.');
        return $this->redirect('quotations/' . $id);
    }

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
                'discount_type' => 'fixed',
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
                    'tax_amount' => (float)$item['quantity'] * (float)$item['unit_price'] * (float)$item['tax_rate'] / 100,
                    'discount' => $item['discount'],
                    'total' => $item['total'],
                    'sort_order' => $item['sort_order'],
                ]);
            }

            // Link order to quotation (don't change status - user can still create contract)
            Database::update('quotations', [
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

    public function convertToContract($id)
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
            $prefix = 'HD-' . date('ym');
            $last = Database::fetch("SELECT contract_number FROM contracts WHERE contract_number LIKE ? ORDER BY id DESC LIMIT 1", [$prefix . '%']);
            $seq = $last ? ((int) substr($last['contract_number'], -4)) + 1 : 1;
            $contractNumber = $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $branding = \App\Services\BrandingService::get();

            $contractId = Database::insert('contracts', [
                'contract_number' => $contractNumber,
                'title' => 'HĐ từ BG ' . $quotation['quote_number'],
                'type' => 'Mới',
                'status' => 'pending',
                'value' => $quotation['total'],
                'subtotal' => $quotation['subtotal'],
                'tax_amount' => $quotation['tax_amount'],
                'discount_amount' => $quotation['discount_amount'],
                'discount_percent' => $quotation['discount_percent'],
                'discount_after_tax' => $quotation['discount_after_tax'],
                'shipping_fee' => $quotation['shipping_fee'],
                'shipping_fee_percent' => $quotation['shipping_percent'] ?? 0,
                'shipping_after_tax' => $quotation['shipping_after_tax'],
                'installation_fee' => $quotation['installation_fee'],
                'vat_percent' => $quotation['tax_rate'] ?? 0,
                'vat_amount' => $quotation['tax_amount'],
                'apply_vat' => $quotation['tax_amount'] > 0 ? 1 : 0,
                'contact_id' => $quotation['contact_id'],
                'company_id' => $quotation['company_id'],
                'deal_id' => $quotation['deal_id'],
                'quote_id' => $quotation['id'],
                'owner_id' => $quotation['owner_id'],
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+1 year')),
                'created_date' => date('Y-m-d'),
                'payment_method' => 'bank_transfer',
                'notes' => $quotation['notes'],
                'terms' => $quotation['terms'],
                'party_a_name' => $branding['name'] ?? '',
                'party_a_address' => $branding['address'] ?? '',
                'party_a_phone' => $branding['phone'] ?? '',
                'party_a_fax' => $branding['fax'] ?? '',
                'party_a_representative' => $branding['representative'] ?? '',
                'party_a_position' => $branding['representative_title'] ?? '',
                'party_a_bank_account' => $branding['bank_account'] ?? '',
                'party_a_bank_name' => $branding['bank_name'] ?? '',
                'party_a_tax_code' => $branding['tax_code'] ?? '',
                'created_by' => $this->userId(),
                'tenant_id' => Database::tenantId(),
                'is_deleted' => 0,
            ]);

            foreach ($items as $item) {
                Database::insert('contract_items', [
                    'contract_id' => $contractId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'description' => $item['description'] ?? '',
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount' => $item['discount'],
                    'total' => $item['total'],
                    'sort_order' => $item['sort_order'],
                ]);
            }

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi tạo hợp đồng: ' . $e->getMessage());
            return $this->back();
        }

        $this->setFlash('success', "Đã tạo hợp đồng {$contractNumber} từ báo giá.");
        return $this->redirect('contracts/' . $contractId);
    }

    /**
     * PDF / printable view
     */
    public function pdf($id)
    {
        $quotation = Database::fetch(
            "SELECT q.*,
                    c.company_name as c_company_name, c.full_name as c_full_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    c.company_phone as c_company_phone, c.company_email as c_company_email,
                    c.phone as c_phone, c.email as c_email,
                    c.address as c_address, c.tax_code as c_tax_code, c.account_code as c_account_code,
                    u.name as owner_name, u.email as owner_email, u.phone as owner_phone
             FROM quotations q
             LEFT JOIN contacts c ON q.contact_id = c.id
             LEFT JOIN users u ON q.owner_id = u.id
             WHERE q.id = ? AND q.tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$quotation) {
            $this->setFlash('error', 'Báo giá không tồn tại.');
            return $this->redirect('quotations');
        }

        $items = Database::fetchAll("SELECT * FROM quotation_items WHERE quotation_id = ? ORDER BY sort_order", [$id]);
        $templateId = (int)($this->input('template_id') ?: ($_GET['template_id'] ?? 0));

        // Nếu có template_id → render từ document_templates
        if ($templateId) {
            $branding = \App\Services\BrandingService::get();
            $customerName = $quotation['c_company_name'] ?: ($quotation['c_full_name'] ?: trim(($quotation['contact_first_name'] ?? '') . ' ' . ($quotation['contact_last_name'] ?? '')));
            $customerPhone = $quotation['contact_phone'] ?: ($quotation['c_company_phone'] ?: $quotation['c_phone'] ?? '');
            $customerEmail = $quotation['contact_email'] ?: ($quotation['c_company_email'] ?: $quotation['c_email'] ?? '');
            $customerAddress = $quotation['address'] ?: ($quotation['c_address'] ?? '');

            $summary = [
                ['label' => '<strong>Tổng tiền hàng</strong>', 'value' => number_format((float)($quotation['subtotal'] ?? 0))],
            ];
            if (($quotation['shipping_fee'] ?? 0) > 0) $summary[] = ['label' => 'Phí vận chuyển', 'value' => number_format((float)$quotation['shipping_fee'])];
            if (($quotation['discount_amount'] ?? 0) > 0) $summary[] = ['label' => 'Chiết khấu', 'value' => '-' . number_format((float)$quotation['discount_amount'])];
            if (($quotation['tax_amount'] ?? 0) > 0) $summary[] = ['label' => 'Thuế VAT', 'value' => number_format((float)$quotation['tax_amount'])];
            if (($quotation['installation_fee'] ?? 0) > 0) $summary[] = ['label' => 'Phí lắp đặt', 'value' => number_format((float)$quotation['installation_fee'])];
            $summary[] = ['label' => '<strong>TỔNG CỘNG</strong>', 'value' => '<strong>' . number_format((float)($quotation['total'] ?? 0)) . '</strong>'];

            // Load contact person
            $cp = null;
            if ($quotation['contact_person_id'] ?? null) {
                $cp = Database::fetch("SELECT * FROM contact_persons WHERE id = ?", [$quotation['contact_person_id']]);
            } elseif ($quotation['contact_id']) {
                $cp = Database::fetch("SELECT * FROM contact_persons WHERE contact_id = ? ORDER BY is_primary DESC, id LIMIT 1", [$quotation['contact_id']]);
            }
            $contactName = $cp ? (($cp['title'] ? ucfirst($cp['title']) . ' ' : '') . $cp['full_name']) : '';
            $contactHonorific = $cp['title'] ?? '';

            // Build items HTML cho từng sản phẩm (Getfly style: {{p.name}}, {{p.qty}}, etc)
            $itemsHtml = '';
            $salesTotal = 0;
            foreach ($items as $i => $item) {
                $rowHtml = '<tr>';
                $rowHtml .= '<td style="text-align:center">' . ($i + 1) . '</td>';
                $rowHtml .= '<td>' . htmlspecialchars($item['product_name'] ?? '') . '</td>';
                $rowHtml .= '<td></td>'; // ảnh SP placeholder
                $rowHtml .= '<td style="text-align:center">' . htmlspecialchars($item['unit'] ?? '') . '</td>';
                $rowHtml .= '<td style="text-align:right">' . number_format((float)($item['quantity'] ?? 0), 2) . '</td>';
                $rowHtml .= '<td style="text-align:right">' . number_format((float)($item['unit_price'] ?? 0)) . '</td>';
                $rowHtml .= '<td style="text-align:right">' . number_format((float)($item['total'] ?? 0)) . '</td>';
                $rowHtml .= '</tr>';
                $itemsHtml .= $rowHtml;
                $salesTotal += (float)($item['total'] ?? 0);
            }

            $replacements = [
                // ToryCRM variables
                '{{company_name}}' => $branding['name'] ?? '',
                '{{company_address}}' => $branding['address'] ?? '',
                '{{company_phone}}' => $branding['phone'] ?? '',
                '{{company_tax_code}}' => $branding['tax_code'] ?? '',
                '{{company_representative}}' => $branding['representative'] ?? '',
                '{{company_position}}' => $branding['representative_title'] ?? '',
                '{{company_bank_account}}' => $branding['bank_account'] ?? '',
                '{{company_bank_name}}' => $branding['bank_name'] ?? '',
                '{{customer_name}}' => $customerName,
                '{{customer_address}}' => $customerAddress,
                '{{customer_phone}}' => $customerPhone,
                '{{customer_tax_code}}' => $quotation['c_tax_code'] ?? '',
                '{{customer_representative}}' => $contactName,
                '{{customer_position}}' => $cp['position'] ?? '',
                '{{items_table}}' => \App\Services\DocumentService::buildItemsTable($items, $summary),
                '{{subtotal}}' => number_format((float)($quotation['subtotal'] ?? 0)),
                '{{discount}}' => number_format((float)($quotation['discount_amount'] ?? 0)),
                '{{vat}}' => number_format((float)($quotation['tax_amount'] ?? 0)),
                '{{total}}' => number_format((float)($quotation['total'] ?? 0)),
                '{{today}}' => date('d/m/Y'),
                '{{owner_name}}' => $quotation['owner_name'] ?? '',
                '{{quote_number}}' => $quotation['quote_number'] ?? '',
                '{{valid_until}}' => !empty($quotation['valid_until']) ? date('d/m/Y', strtotime($quotation['valid_until'])) : '',
                '{{notes}}' => $quotation['notes'] ?? '',
                '{{terms}}' => $quotation['terms'] ?? '',

                // Getfly variables
                '{{a.account_name}}' => $customerName,
                '{{a.last_contact_honorific}}' => $contactHonorific,
                '{{contact_name}}' => $contactName,
                '{{contact_phone}}' => $cp['phone'] ?? $customerPhone,
                '{{contact_email}}' => $cp['email'] ?? $customerEmail,
                '{{quote_account_address}}' => $customerAddress,
                '{{quote_project_address}}' => $quotation['location'] ?? $customerAddress,
                '{{quote_date}}' => date('d/m/Y', strtotime($quotation['created_at'])),
                '{{quote_code}}' => $quotation['quote_number'] ?? '',
                '{{assigned_name}}' => $quotation['owner_name'] ?? '',
                '{{assigned_email}}' => $quotation['owner_email'] ?? '',
                '{{assigned_phone}}' => $quotation['owner_phone'] ?? '',
                '{{sales}}' => number_format($salesTotal),
                '{{vat_amount}}' => number_format((float)($quotation['tax_amount'] ?? 0)),
                '{{revenue}}' => number_format((float)($quotation['total'] ?? 0)),
                '{{content}}' => $quotation['content'] ?? '',
                '{{quote_content}}' => $quotation['content'] ?? '',
                '{{quote_terms}}' => $quotation['terms'] ?? '',
                '{{quote_notes}}' => $quotation['notes'] ?? '',
            ];

            $html = \App\Services\DocumentService::render('quotation', $templateId, $replacements);

            // Xử lý biến sản phẩm theo dòng (Getfly style)
            if ($html && preg_match('/(<tr[^>]*>(?:(?!<\/tr>).)*\{\{p\.\w+\}\}.*?<\/tr>)/si', $html, $rowMatch)) {
                $rowTemplate = $rowMatch[1];
                $rowsHtml = '';
                foreach ($items as $i => $item) {
                    $row = $rowTemplate;
                    $row = str_replace('{{p.no}}', $i + 1, $row);
                    $row = str_replace('{{p.name}}', htmlspecialchars($item['product_name'] ?? ''), $row);
                    $row = str_replace('{{p.desc}}', htmlspecialchars($item['description'] ?? ''), $row);
                    $row = str_replace('{{p.l_desc}}', htmlspecialchars($item['description'] ?? ''), $row);
                    $row = str_replace('{{p.kich_thuoc}}', $item['dimensions'] ?? '', $row);
                    // Xóa dòng "KT:  mm" nếu không có kích thước
                    $row = preg_replace('/KT:\s*mm/', '', $row);
                    $row = str_replace('{{p.unit}}', htmlspecialchars($item['unit'] ?? ''), $row);
                    $row = str_replace('{{p.qty}}', number_format((float)($item['quantity'] ?? 0), 2), $row);
                    $row = str_replace('{{p.cost}}', number_format((float)($item['unit_price'] ?? 0)), $row);
                    $row = str_replace('{{p.revenue}}', number_format((float)($item['total'] ?? 0)), $row);
                    $row = str_replace('{{p.discount}}', number_format((float)($item['discount'] ?? 0)), $row);
                    $row = str_replace('{{p.vat}}', number_format((float)($item['tax_rate'] ?? 0), 2) . '%', $row);
                    $row = str_replace('{{p.vatp}}', number_format((float)($item['tax_rate'] ?? 0), 0) . '%', $row);
                    $row = preg_replace('/\{\{p\.avatar[^}]*\}\}/', '', $row);
                    $rowsHtml .= $row;
                }
                $html = str_replace($rowTemplate, $rowsHtml, $html);
            }

            if ($html) {
                echo '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"><title>Báo giá ' . e($quotation['quote_number']) . '</title>';
                echo '<style>body{font-family:"DejaVu Sans",Arial,sans-serif;font-size:13px;line-height:1.3;color:#333;padding:15px 30px;margin:0}p{margin:2px 0}table{border-collapse:collapse}td,th{padding:4px 6px}@page{margin:10mm 15mm;size:A4}@media print{body{padding:0}}</style>';
                echo '</head><body>';
                echo $html;
                echo '<script>window.onload=function(){window.print()}</script></body></html>';
                return;
            }
        }

        // Fallback: mẫu mặc định hệ thống
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

    private function handleAttachments($quotationId)
    {
        if (empty($_FILES['attachments']['name'][0])) return;

        $uploadDir = BASE_PATH . '/public/uploads/quotations/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $maxSize = 10 * 1024 * 1024;

        foreach ($_FILES['attachments']['name'] as $i => $name) {
            if ($_FILES['attachments']['error'][$i] !== UPLOAD_ERR_OK || !$name) continue;
            if ($_FILES['attachments']['size'][$i] > $maxSize) continue;

            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $filename = 'q' . $quotationId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

            if (move_uploaded_file($_FILES['attachments']['tmp_name'][$i], $uploadDir . $filename)) {
                Database::insert('quotation_attachments', [
                    'tenant_id' => Database::tenantId(),
                    'quotation_id' => $quotationId,
                    'user_id' => $this->userId(),
                    'filename' => $filename,
                    'original_name' => $name,
                    'file_size' => $_FILES['attachments']['size'][$i],
                    'mime_type' => $_FILES['attachments']['type'][$i],
                ]);
            }
        }
    }

    public function uploadAttachment($id)
    {
        if (!$this->isPost()) return $this->redirect('quotations/' . $id);

        $quotation = Database::fetch("SELECT id FROM quotations WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$quotation) {
            $this->setFlash('error', 'Báo giá không tồn tại.');
            return $this->redirect('quotations');
        }

        if (empty($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Vui lòng chọn file để tải lên.');
            return $this->redirect('quotations/' . $id);
        }

        $file = $_FILES['attachment'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            $this->setFlash('error', 'File quá lớn (tối đa 10MB).');
            return $this->redirect('quotations/' . $id);
        }

        $uploadDir = BASE_PATH . '/public/uploads/quotations/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'q' . $id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            $this->setFlash('error', 'Lỗi tải file lên.');
            return $this->redirect('quotations/' . $id);
        }

        Database::insert('quotation_attachments', [
            'tenant_id' => Database::tenantId(),
            'quotation_id' => $id,
            'user_id' => $this->userId(),
            'filename' => $filename,
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
        ]);

        $this->setFlash('success', 'Đã tải lên tài liệu.');
        return $this->redirect('quotations/' . $id);
    }

    public function deleteAttachment($id, $attachId)
    {
        if (!$this->isPost()) return $this->redirect('quotations/' . $id);

        $attach = Database::fetch(
            "SELECT * FROM quotation_attachments WHERE id = ? AND quotation_id = ? AND tenant_id = ?",
            [$attachId, $id, Database::tenantId()]
        );

        if ($attach) {
            $path = BASE_PATH . '/public/uploads/quotations/' . $attach['filename'];
            if (file_exists($path)) unlink($path);
            Database::delete('quotation_attachments', 'id = ?', [$attachId]);
            $this->setFlash('success', 'Đã xóa tài liệu.');
        }

        return $this->redirect('quotations/' . $id);
    }
}
