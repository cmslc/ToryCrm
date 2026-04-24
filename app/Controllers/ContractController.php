<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ContractController extends Controller
{
    use \App\Traits\HasFollowers;

    public function followers($id) {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        return $this->json($this->handleFollowers('contract', (int)$id));
    }

    public function changeOwner($id) {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        return $this->json($this->handleChangeOwner('contracts', (int)$id));
    }

    public function index()
    {
        $this->authorize('contracts', 'view');
        $tid = Database::tenantId();
        $search = trim($this->input('search') ?? '');
        $status = $this->input('status');
        $type = $this->input('type');
        $contactId = $this->input('contact_id');
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
        $offset = ($page - 1) * $perPage;

        $where = ["ct.is_deleted = 0", "ct.tenant_id = ?"];
        $params = [$tid];

        if ($search) {
            $where[] = "(ct.contract_number LIKE ? OR ct.title LIKE ? OR ct.contract_code LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company_name LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s, $s]);
        }
        if ($status) {
            $where[] = "ct.status = ?";
            $params[] = $status;
        }
        if ($type) {
            $where[] = "ct.type = ?";
            $params[] = $type;
        }
        if ($contactId) {
            $where[] = "ct.contact_id = ?";
            $params[] = $contactId;
        }
        if ($dateFrom) {
            $where[] = "ct.start_date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where[] = "ct.end_date <= ?";
            $params[] = $dateTo;
        }

        $ownerScope = $this->ownerScope('ct', 'owner_id', 'contracts');
        if ($ownerScope['where']) { $where[] = $ownerScope['where']; $params = array_merge($params, $ownerScope['params']); }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM contracts ct LEFT JOIN contacts c ON ct.contact_id = c.id WHERE {$whereClause}",
            $params
        )['count'];

        $contracts = Database::fetchAll(
            "SELECT ct.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    c.company_name as c_company_name, c.full_name as c_full_name, c.avatar as contact_avatar,
                    comp.name as company_name,
                    u.name as owner_name, u.avatar as owner_avatar
             FROM contracts ct
             LEFT JOIN contacts c ON ct.contact_id = c.id
             LEFT JOIN companies comp ON ct.company_id = comp.id
             LEFT JOIN users u ON ct.owner_id = u.id
             WHERE {$whereClause}
             ORDER BY ct.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        // Stats per status — tenant-scoped + owner-scoped
        $ownerScopeSql = $this->getOwnerScopeSql('owner_id', 'contracts');
        $statusCounts = Database::fetchAll(
            "SELECT status, COUNT(*) as cnt FROM contracts WHERE is_deleted = 0 AND tenant_id = ?{$ownerScopeSql} GROUP BY status",
            [$tid]
        );
        $stats = [];
        $totalAll = 0;
        foreach ($statusCounts as $sc2) { $stats[$sc2['status']] = (int)$sc2['cnt']; $totalAll += (int)$sc2['cnt']; }
        $stats['expiring_soon'] = (int)(Database::fetch(
            "SELECT COUNT(*) as cnt FROM contracts WHERE is_deleted = 0 AND tenant_id = ? AND status IN ('in_progress','active') AND end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND end_date >= CURDATE(){$ownerScopeSql}",
            [$tid]
        )['cnt'] ?? 0);

        $contactList = Database::fetchAll("SELECT id, first_name, last_name, company_name FROM contacts WHERE is_deleted = 0 AND tenant_id = ? ORDER BY first_name LIMIT 500", [$tid]);

        $displayColumns = \App\Services\ColumnService::getColumns('contracts');

        return $this->view('contracts.index', [
            'contracts' => [
                'items' => $contracts,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'displayColumns' => $displayColumns,
            'stats' => $stats,
            'totalAll' => $totalAll,
            'contacts' => $contactList,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type,
                'contact_id' => $contactId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('contracts', 'create');
        $tid = Database::tenantId();
        $contractNumber = $this->generateContractNumber();
        $contacts = Database::fetchAll("SELECT id, first_name, last_name, company_name, address, phone, fax, tax_code FROM contacts WHERE is_deleted = 0 AND tenant_id = ? ORDER BY first_name LIMIT 500", [$tid]);
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 AND u.tenant_id = ? ORDER BY d.name, u.name", [$tid]);
        $products = Database::fetchAll("SELECT id, sku, name, price, unit, tax_rate FROM products WHERE is_deleted = 0 AND tenant_id = ? ORDER BY name LIMIT 500", [$tid]);
        $companies = Database::fetchAll("SELECT id, name, address, phone, tax_code FROM companies WHERE is_deleted = 0 AND tenant_id = ? ORDER BY name LIMIT 500", [$tid]);
        $allContracts = Database::fetchAll("SELECT id, contract_number, title FROM contracts WHERE is_deleted = 0 AND tenant_id = ? ORDER BY created_at DESC LIMIT 200", [$tid]);
        $quotes = Database::fetchAll("SELECT q.id, q.quote_number as order_number, CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,'')) as contact_name FROM quotations q LEFT JOIN contacts c ON q.contact_id = c.id WHERE q.tenant_id = ? ORDER BY q.created_at DESC LIMIT 100", [$tid]);

        $branding = \App\Services\BrandingService::get();

        $tid = Database::tenantId();
        $docTemplates = Database::fetchAll("SELECT id, name, content, is_default FROM document_templates WHERE type = 'contract' AND tenant_id = ? AND is_active = 1 ORDER BY is_default DESC, name", [$tid]);
        $defaultTemplate = '';
        foreach ($docTemplates as $dt) { if ($dt['is_default']) { $defaultTemplate = $dt['content']; break; } }
        if (!$defaultTemplate && !empty($docTemplates)) $defaultTemplate = $docTemplates[0]['content'];

        return $this->view('contracts.create', [
            'contractNumber' => $contractNumber,
            'contacts' => $contacts,
            'users' => $users,
            'products' => $products,
            'companies' => $companies,
            'allContracts' => $allContracts,
            'quotes' => $quotes,
            'branding' => $branding,
            'defaultTemplate' => $defaultTemplate,
            'docTemplates' => $docTemplates,
            'companyProfiles' => Database::fetchAll("SELECT * FROM company_profiles WHERE tenant_id = ? AND is_active = 1 ORDER BY is_default DESC, name", [$tid]),
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('contracts');

        $data = $this->allInput();

        if (empty($data['title'])) {
            $this->setFlash('error', 'Vui lòng nhập tiêu đề hợp đồng.');
            return $this->back();
        }

        if (!empty($data['contact_id'])) {
            $contactOk = Database::fetch("SELECT id FROM contacts WHERE id = ? AND tenant_id = ?", [$data['contact_id'], Database::tenantId()]);
            if (!$contactOk) { $this->setFlash('error', 'Khách hàng không hợp lệ.'); return $this->back(); }
        }

        $contractNumber = !empty($data['contract_number']) ? $data['contract_number'] : $this->generateContractNumber();

        Database::beginTransaction();
        try {
            $id = Database::insert('contracts', [
                'contract_number' => $contractNumber,
                'contract_code' => trim($data['contract_code'] ?? '') ?: null,
                'title' => trim($data['title']),
                'type' => $data['type'] ?? 'Mới',
                'status' => $data['status'] ?? 'pending',
                'payment_method' => $data['payment_method'] ?? null,
                'usage_type' => $data['usage_type'] ?? 'one_time',
                'value' => (float) ($data['value'] ?? 0),
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
                'contact_name' => trim($data['contact_name'] ?? '') ?: null,
                'quote_id' => !empty($data['quote_id']) ? $data['quote_id'] : null,
                'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : $this->userId(),
                'start_date' => !empty($data['start_date']) ? $data['start_date'] : date('Y-m-d'),
                'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
                'created_date' => !empty($data['created_date']) ? $data['created_date'] : date('Y-m-d'),
                'actual_start_date' => !empty($data['actual_start_date']) ? $data['actual_start_date'] : null,
                'actual_end_date' => !empty($data['actual_end_date']) ? $data['actual_end_date'] : null,
                'auto_renew' => !empty($data['auto_renew']) ? 1 : 0,
                'auto_create_order' => !empty($data['auto_create_order']) ? 1 : 0,
                'auto_notify_expiry' => !empty($data['auto_notify_expiry']) ? 1 : 0,
                'auto_send_sms' => !empty($data['auto_send_sms']) ? 1 : 0,
                'auto_send_email' => !empty($data['auto_send_email']) ? 1 : 0,
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'discount_percent' => (float)($data['discount_percent'] ?? 0),
                'discount_after_tax' => !empty($data['discount_after_tax']) ? 1 : 0,
                'shipping_fee' => (float)($data['shipping_fee'] ?? 0),
                'shipping_fee_percent' => (float)($data['shipping_fee_percent'] ?? 0),
                'shipping_after_tax' => !empty($data['shipping_after_tax']) ? 1 : 0,
                'apply_vat' => !empty($data['apply_vat']) ? 1 : 0,
                'vat_percent' => (float)($data['vat_percent'] ?? 0),
                'vat_amount' => (float)($data['vat_amount'] ?? 0),
                'installation_fee' => (float)($data['installation_fee'] ?? 0),
                'installation_fee_percent' => (float)($data['installation_fee_percent'] ?? 0),
                'installation_address' => trim($data['installation_address'] ?? '') ?: null,
                'location' => trim($data['location'] ?? '') ?: null,
                'project' => trim($data['project'] ?? '') ?: null,
                'executed_amount' => (float)($data['executed_amount'] ?? 0),
                'actual_value' => (float)($data['actual_value'] ?? 0),
                'parent_contract_id' => !empty($data['parent_contract_id']) ? $data['parent_contract_id'] : null,
                'related_contract_id' => !empty($data['related_contract_id']) ? $data['related_contract_id'] : null,
                // Party A
                'party_a_company_id' => !empty($data['party_a_company_id']) ? $data['party_a_company_id'] : null,
                'party_a_name' => trim($data['party_a_name'] ?? '') ?: null,
                'party_a_address' => trim($data['party_a_address'] ?? '') ?: null,
                'party_a_phone' => trim($data['party_a_phone'] ?? '') ?: null,
                'party_a_fax' => trim($data['party_a_fax'] ?? '') ?: null,
                'party_a_representative' => trim($data['party_a_representative'] ?? '') ?: null,
                'party_a_position' => trim($data['party_a_position'] ?? '') ?: null,
                'party_a_bank_account' => trim($data['party_a_bank_account'] ?? '') ?: null,
                'party_a_bank_name' => trim($data['party_a_bank_name'] ?? '') ?: null,
                'party_a_tax_code' => trim($data['party_a_tax_code'] ?? '') ?: null,
                // Party B
                'party_b_name' => trim($data['party_b_name'] ?? '') ?: null,
                'party_b_address' => trim($data['party_b_address'] ?? '') ?: null,
                'party_b_phone' => trim($data['party_b_phone'] ?? '') ?: null,
                'party_b_fax' => trim($data['party_b_fax'] ?? '') ?: null,
                'party_b_representative' => trim($data['party_b_representative'] ?? '') ?: null,
                'party_b_position' => trim($data['party_b_position'] ?? '') ?: null,
                'party_b_bank_account' => trim($data['party_b_bank_account'] ?? '') ?: null,
                'party_b_bank_name' => trim($data['party_b_bank_name'] ?? '') ?: null,
                'party_b_tax_code' => trim($data['party_b_tax_code'] ?? '') ?: null,
                'notes' => trim($data['notes'] ?? ''),
                'terms' => trim($data['terms'] ?? ''),
                'created_by' => $this->userId(),
                'tenant_id' => Database::tenantId(),
                'is_deleted' => 0,
            ]);

            // Insert items
            $subtotal = 0;
            $totalTax = 0;
            if (!empty($data['items']) && is_array($data['items'])) {
                $sort = 0;
                foreach ($data['items'] as $item) {
                    if (empty($item['product_name']) && empty($item['product_id'])) continue;
                    $qty = (float)($item['quantity'] ?? 1);
                    $unitPrice = (float)($item['unit_price'] ?? 0);
                    $taxRate = (float)($item['tax_rate'] ?? 0);
                    $discountPct = (float)($item['discount_percent'] ?? 0);
                    $discount = (float)($item['discount'] ?? 0);
                    $lineSub = $qty * $unitPrice;
                    $lineTax = $lineSub * $taxRate / 100;
                    $lineTotal = $lineSub + $lineTax - $discount;

                    Database::insert('contract_items', [
                        'contract_id' => $id,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'] ?? '',
                        'description' => $item['description'] ?? '',
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? 'Cái',
                        'unit_price' => $unitPrice,
                        'cost_price' => (float)($item['cost_price'] ?? 0),
                        'tax_rate' => $taxRate,
                        'discount_percent' => $discountPct,
                        'discount' => $discount,
                        'total' => max(0, $lineTotal),
                        'sort_order' => $sort++,
                    ]);
                    $subtotal += $lineSub;
                    $totalTax += $lineTax;
                }
            }

            $discountAmt = (float)($data['discount_amount'] ?? 0);
            $shippingFee = (float)($data['shipping_fee'] ?? 0);
            $installFee = (float)($data['installation_fee'] ?? 0);
            $vatAmt = (float)($data['vat_amount'] ?? 0);
            $totalValue = $subtotal + $shippingFee - $discountAmt + $vatAmt + $installFee;

            Database::update('contracts', [
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'value' => max(0, $totalValue),
            ], 'id = ?', [$id]);

            // Insert related users
            $this->saveRelatedUsers($id, $data['related_users'] ?? []);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi tạo hợp đồng: ' . $e->getMessage());
            return $this->back();
        }

        $this->setFlash('success', "Hợp đồng {$contractNumber} đã được tạo.");
        return $this->redirect('contracts/' . $id);
    }

    public function export()
    {
        $this->authorize('contracts', 'view');
        $tid = Database::tenantId();
        $where = ["ct.tenant_id = ?"]; $params = [$tid];

        if ($s = $this->input('search')) {
            $where[] = "(ct.contract_number LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR c.company_name LIKE ?)";
            $like = "%{$s}%"; $params = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($st = $this->input('status')) { $where[] = "ct.status = ?"; $params[] = $st; }
        if ($t = $this->input('type')) { $where[] = "ct.type = ?"; $params[] = $t; }
        if ($oid = $this->input('owner_id')) { $where[] = "ct.owner_id = ?"; $params[] = $oid; }
        $scope = $this->ownerScope('ct', 'owner_id', 'contracts');
        if ($scope['where']) { $where[] = $scope['where']; $params = array_merge($params, $scope['params']); }

        $rows = Database::fetchAll(
            "SELECT ct.*,
                    TRIM(CONCAT(COALESCE(c.first_name,''),' ',COALESCE(c.last_name,''))) as contact_name,
                    c.company_name,
                    u.name as owner_name
             FROM contracts ct
             LEFT JOIN contacts c ON ct.contact_id = c.id
             LEFT JOIN users u ON ct.owner_id = u.id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY ct.created_at DESC",
            $params
        );

        $columns = [
            'contract_number' => ['label' => 'Số hợp đồng'],
            'title'           => ['label' => 'Tiêu đề'],
            'contact_name'    => ['label' => 'Khách hàng'],
            'company_name'    => ['label' => 'Công ty'],
            'type'            => ['label' => 'Loại'],
            'status'          => ['label' => 'Trạng thái'],
            'value'           => ['label' => 'Giá trị'],
            'start_date'      => ['label' => 'Ngày bắt đầu'],
            'end_date'        => ['label' => 'Ngày kết thúc'],
            'owner_name'      => ['label' => 'Phụ trách'],
            'notes'           => ['label' => 'Ghi chú'],
            'created_at'      => ['label' => 'Ngày tạo'],
        ];
        $selected = \App\Services\CsvExporter::parseColumnsParam((string)$this->input('columns', ''), $columns);
        \App\Services\CsvExporter::download($rows, $columns, 'contracts_' . date('Ymd_His') . '.csv', $selected);
    }

    public function show($id)
    {
        $this->authorize('contracts', 'view');
        $ownerCheck = Database::fetch("SELECT owner_id FROM contracts WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if ($ownerCheck && !$this->canAccessEntity('contract', (int)$id, (int)($ownerCheck['owner_id'] ?? 0))) {
            $this->setFlash('error', 'Bạn không có quyền xem hợp đồng này.');
            return $this->redirect('contracts');
        }
        $contract = Database::fetch(
            "SELECT ct.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name, c.email as contact_email, c.phone as contact_phone,
                    c.company_name as contact_company_name, c.address as contact_address, c.tax_code as contact_tax_code, c.fax as contact_fax,
                    comp.name as company_name, comp.address as comp_address, comp.phone as comp_phone, comp.tax_code as comp_tax_code,
                    pa_comp.name as party_a_comp_name, pa_comp.address as party_a_comp_address, pa_comp.phone as party_a_comp_phone, pa_comp.tax_code as party_a_comp_tax_code,
                    d.title as deal_title, d.id as deal_id,
                    u.name as owner_name,
                    uc.name as created_by_name
             FROM contracts ct
             LEFT JOIN contacts c ON ct.contact_id = c.id
             LEFT JOIN companies comp ON ct.company_id = comp.id
             LEFT JOIN companies pa_comp ON ct.party_a_company_id = pa_comp.id
             LEFT JOIN deals d ON ct.deal_id = d.id
             LEFT JOIN users u ON ct.owner_id = u.id
             LEFT JOIN users uc ON ct.created_by = uc.id
             WHERE ct.id = ? AND ct.is_deleted = 0",
            [$id]
        );

        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        // Fallback: Party A from branding (thương hiệu) settings
        if (empty($contract['party_a_name'])) {
            $branding = \App\Services\BrandingService::get();
            $contract['party_a_name'] = $branding['name'] ?? '';
            $contract['party_a_address'] = $contract['party_a_address'] ?: ($branding['address'] ?? '');
            $contract['party_a_phone'] = $contract['party_a_phone'] ?: ($branding['phone'] ?? '');
            $contract['party_a_fax'] = $contract['party_a_fax'] ?: ($branding['fax'] ?? '');
            $contract['party_a_tax_code'] = $contract['party_a_tax_code'] ?: ($branding['tax_code'] ?? '');
            $contract['party_a_representative'] = $contract['party_a_representative'] ?: ($branding['representative'] ?? '');
            $contract['party_a_position'] = $contract['party_a_position'] ?: ($branding['representative_title'] ?? '');
            $contract['party_a_bank_account'] = $contract['party_a_bank_account'] ?: ($branding['bank_account'] ?? '');
            $contract['party_a_bank_name'] = $contract['party_a_bank_name'] ?: ($branding['bank_name'] ?? '');
        }

        // Fallback: Party B from contact
        if (empty($contract['party_b_name'])) {
            $contract['party_b_name'] = $contract['contact_company_name'] ?: trim(($contract['contact_first_name'] ?? '') . ' ' . ($contract['contact_last_name'] ?? ''));
            $contract['party_b_address'] = $contract['party_b_address'] ?? $contract['contact_address'] ?? '';
            $contract['party_b_phone'] = $contract['party_b_phone'] ?? $contract['contact_phone'] ?? '';
            $contract['party_b_tax_code'] = $contract['party_b_tax_code'] ?? $contract['contact_tax_code'] ?? '';
            $contract['party_b_fax'] = $contract['party_b_fax'] ?? $contract['contact_fax'] ?? '';
        }

        $orders = Database::fetchAll(
            "SELECT id, order_number, total, status FROM orders WHERE contract_id = ? AND is_deleted = 0 ORDER BY created_at DESC",
            [$id]
        );

        $items = Database::fetchAll(
            "SELECT ci.*, p.sku as product_sku FROM contract_items ci LEFT JOIN products p ON ci.product_id = p.id WHERE ci.contract_id = ? ORDER BY ci.sort_order", [$id]
        );

        $relatedUsers = Database::fetchAll(
            "SELECT cru.*, u.name as user_name FROM contract_related_users cru LEFT JOIN users u ON cru.user_id = u.id WHERE cru.contract_id = ?", [$id]
        );

        // Parent contract number
        if (!empty($contract['parent_contract_id'])) {
            $parent = Database::fetch("SELECT contract_number FROM contracts WHERE id = ?", [$contract['parent_contract_id']]);
            $contract['parent_contract_number'] = $parent['contract_number'] ?? '';
        }

        // Quote number
        if (!empty($contract['quote_id'])) {
            $quote = Database::fetch("SELECT order_number FROM orders WHERE id = ?", [$contract['quote_id']]);
            $contract['quote_number'] = $quote['order_number'] ?? '';
        }

        // Related contracts (parent/child + related_contract_id)
        $relatedContracts = Database::fetchAll(
            "SELECT ct.*, u.name as owner_name, CONCAT(c.first_name, ' ', COALESCE(c.last_name,'')) as contact_name
             FROM contracts ct
             LEFT JOIN users u ON ct.owner_id = u.id
             LEFT JOIN contacts c ON ct.contact_id = c.id
             WHERE ct.is_deleted = 0 AND (ct.parent_contract_id = ? OR ct.id = ? OR ct.related_contract_id = ? OR ct.id = ?)
             AND ct.id != ?
             ORDER BY ct.created_at DESC",
            [$id, $contract['parent_contract_id'] ?? 0, $id, $contract['related_contract_id'] ?? 0, $id]
        );

        // Comments
        $comments = Database::fetchAll(
            "SELECT cc.*, u.name as user_name, u.avatar as user_avatar
             FROM contract_comments cc
             LEFT JOIN users u ON cc.user_id = u.id
             WHERE cc.contract_id = ?
             ORDER BY cc.created_at DESC",
            [$id]
        );

        return $this->view('contracts.show', [
            'contract' => $contract,
            'items' => $items,
            'orders' => $orders,
            'relatedUsers' => $relatedUsers,
            'relatedContracts' => $relatedContracts,
            'comments' => $comments,
            'attachments' => Database::fetchAll("SELECT * FROM contract_attachments WHERE contract_id = ? ORDER BY created_at DESC", [$id]),
        ]);
    }

    public function edit($id)
    {
        $this->authorize('contracts', 'edit');
        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND tenant_id = ? AND is_deleted = 0", [$id, Database::tenantId()]);
        if ($contract && !$this->canAccessEntity('contract', (int)$id, (int)($contract['owner_id'] ?? 0))) {
            $this->setFlash('error', 'Bạn không có quyền sửa hợp đồng này.');
            return $this->redirect('contracts');
        }
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        $contacts = Database::fetchAll("SELECT id, first_name, last_name, company_name, address, phone, fax, tax_code FROM contacts WHERE is_deleted = 0 ORDER BY first_name LIMIT 500");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");
        $products = Database::fetchAll("SELECT id, sku, name, price, unit, tax_rate FROM products WHERE is_deleted = 0 ORDER BY name LIMIT 500");
        $companies = Database::fetchAll("SELECT id, name, address, phone, tax_code FROM companies WHERE is_deleted = 0 ORDER BY name LIMIT 500");
        $allContracts = Database::fetchAll("SELECT id, contract_number, title FROM contracts WHERE is_deleted = 0 AND id != ? ORDER BY created_at DESC LIMIT 200", [$id]);
        $quotes = Database::fetchAll("SELECT o.id, o.order_number, CONCAT(c.first_name, ' ', COALESCE(c.last_name,'')) as contact_name FROM orders o LEFT JOIN contacts c ON o.contact_id = c.id WHERE o.type = 'quote' AND o.is_deleted = 0 ORDER BY o.created_at DESC LIMIT 100");

        $items = Database::fetchAll(
            "SELECT ci.*, p.sku as product_sku FROM contract_items ci LEFT JOIN products p ON ci.product_id = p.id WHERE ci.contract_id = ? ORDER BY ci.sort_order", [$id]
        );

        $relatedUsers = Database::fetchAll(
            "SELECT * FROM contract_related_users WHERE contract_id = ?", [$id]
        );

        $branding = \App\Services\BrandingService::get();

        // Fallback: fill party_a from branding if empty
        if (empty($contract['party_a_name'])) {
            $contract['party_a_name'] = $branding['name'] ?? '';
            $contract['party_a_address'] = $contract['party_a_address'] ?: ($branding['address'] ?? '');
            $contract['party_a_phone'] = $contract['party_a_phone'] ?: ($branding['phone'] ?? '');
            $contract['party_a_fax'] = $contract['party_a_fax'] ?: ($branding['fax'] ?? '');
            $contract['party_a_tax_code'] = $contract['party_a_tax_code'] ?: ($branding['tax_code'] ?? '');
            $contract['party_a_representative'] = $contract['party_a_representative'] ?: ($branding['representative'] ?? '');
            $contract['party_a_position'] = $contract['party_a_position'] ?: ($branding['representative_title'] ?? '');
            $contract['party_a_bank_account'] = $contract['party_a_bank_account'] ?: ($branding['bank_account'] ?? '');
            $contract['party_a_bank_name'] = $contract['party_a_bank_name'] ?: ($branding['bank_name'] ?? '');
        }

        // Fallback: fill party_b from contact if empty
        if (empty($contract['party_b_name'])) {
            $contact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$contract['contact_id'] ?? 0]);
            if ($contact) {
                $contract['party_b_name'] = $contact['company_name'] ?: trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? ''));
                $contract['party_b_address'] = $contract['party_b_address'] ?: ($contact['address'] ?? '');
                $contract['party_b_phone'] = $contract['party_b_phone'] ?: ($contact['phone'] ?? '');
                $contract['party_b_fax'] = $contract['party_b_fax'] ?: ($contact['fax'] ?? '');
                $contract['party_b_tax_code'] = $contract['party_b_tax_code'] ?: ($contact['tax_code'] ?? '');
            }
        }

        return $this->view('contracts.edit', [
            'contract' => $contract,
            'items' => $items,
            'contacts' => $contacts,
            'users' => $users,
            'products' => $products,
            'companies' => $companies,
            'allContracts' => $allContracts,
            'quotes' => $quotes,
            'relatedUsers' => $relatedUsers,
            'companyProfiles' => Database::fetchAll("SELECT * FROM company_profiles WHERE tenant_id = ? AND is_active = 1 ORDER BY is_default DESC, name", [Database::tenantId()]),
            'docTemplates' => Database::fetchAll("SELECT id, name, content, is_default FROM document_templates WHERE type = 'contract' AND tenant_id = ? AND is_active = 1 ORDER BY is_default DESC, name", [Database::tenantId()]),
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);
        $this->authorize('contracts', 'edit');

        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND tenant_id = ? AND is_deleted = 0", [$id, Database::tenantId()]);
        if ($contract && !$this->canAccessEntity('contract', (int)$id, (int)($contract['owner_id'] ?? 0))) {
            $this->setFlash('error', 'Bạn không có quyền sửa hợp đồng này.');
            return $this->redirect('contracts');
        }
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        $data = $this->allInput();

        Database::beginTransaction();
        try {
            Database::update('contracts', [
                'title' => trim($data['title'] ?? $contract['title']),
                'contract_code' => trim($data['contract_code'] ?? '') ?: null,
                'type' => $data['type'] ?? $contract['type'],
                'status' => $data['status'] ?? $contract['status'],
                'payment_method' => $data['payment_method'] ?? $contract['payment_method'],
                'usage_type' => $data['usage_type'] ?? $contract['usage_type'],
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
                'contact_name' => trim($data['contact_name'] ?? '') ?: null,
                'quote_id' => !empty($data['quote_id']) ? $data['quote_id'] : null,
                'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : $contract['owner_id'],
                'start_date' => !empty($data['start_date']) ? $data['start_date'] : $contract['start_date'],
                'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
                'created_date' => !empty($data['created_date']) ? $data['created_date'] : $contract['created_date'],
                'actual_start_date' => !empty($data['actual_start_date']) ? $data['actual_start_date'] : null,
                'actual_end_date' => !empty($data['actual_end_date']) ? $data['actual_end_date'] : null,
                'auto_renew' => !empty($data['auto_renew']) ? 1 : 0,
                'auto_create_order' => !empty($data['auto_create_order']) ? 1 : 0,
                'auto_notify_expiry' => !empty($data['auto_notify_expiry']) ? 1 : 0,
                'auto_send_sms' => !empty($data['auto_send_sms']) ? 1 : 0,
                'auto_send_email' => !empty($data['auto_send_email']) ? 1 : 0,
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'discount_percent' => (float)($data['discount_percent'] ?? 0),
                'discount_after_tax' => !empty($data['discount_after_tax']) ? 1 : 0,
                'shipping_fee' => (float)($data['shipping_fee'] ?? 0),
                'shipping_fee_percent' => (float)($data['shipping_fee_percent'] ?? 0),
                'shipping_after_tax' => !empty($data['shipping_after_tax']) ? 1 : 0,
                'apply_vat' => !empty($data['apply_vat']) ? 1 : 0,
                'vat_percent' => (float)($data['vat_percent'] ?? 0),
                'vat_amount' => (float)($data['vat_amount'] ?? 0),
                'installation_fee' => (float)($data['installation_fee'] ?? 0),
                'installation_fee_percent' => (float)($data['installation_fee_percent'] ?? 0),
                'installation_address' => trim($data['installation_address'] ?? '') ?: null,
                'location' => trim($data['location'] ?? '') ?: null,
                'project' => trim($data['project'] ?? '') ?: null,
                'executed_amount' => (float)($data['executed_amount'] ?? 0),
                'actual_value' => (float)($data['actual_value'] ?? 0),
                'parent_contract_id' => !empty($data['parent_contract_id']) ? $data['parent_contract_id'] : null,
                'related_contract_id' => !empty($data['related_contract_id']) ? $data['related_contract_id'] : null,
                // Party A
                'party_a_company_id' => !empty($data['party_a_company_id']) ? $data['party_a_company_id'] : null,
                'party_a_name' => trim($data['party_a_name'] ?? '') ?: null,
                'party_a_address' => trim($data['party_a_address'] ?? '') ?: null,
                'party_a_phone' => trim($data['party_a_phone'] ?? '') ?: null,
                'party_a_fax' => trim($data['party_a_fax'] ?? '') ?: null,
                'party_a_representative' => trim($data['party_a_representative'] ?? '') ?: null,
                'party_a_position' => trim($data['party_a_position'] ?? '') ?: null,
                'party_a_bank_account' => trim($data['party_a_bank_account'] ?? '') ?: null,
                'party_a_bank_name' => trim($data['party_a_bank_name'] ?? '') ?: null,
                'party_a_tax_code' => trim($data['party_a_tax_code'] ?? '') ?: null,
                // Party B
                'party_b_name' => trim($data['party_b_name'] ?? '') ?: null,
                'party_b_address' => trim($data['party_b_address'] ?? '') ?: null,
                'party_b_phone' => trim($data['party_b_phone'] ?? '') ?: null,
                'party_b_fax' => trim($data['party_b_fax'] ?? '') ?: null,
                'party_b_representative' => trim($data['party_b_representative'] ?? '') ?: null,
                'party_b_position' => trim($data['party_b_position'] ?? '') ?: null,
                'party_b_bank_account' => trim($data['party_b_bank_account'] ?? '') ?: null,
                'party_b_bank_name' => trim($data['party_b_bank_name'] ?? '') ?: null,
                'party_b_tax_code' => trim($data['party_b_tax_code'] ?? '') ?: null,
                'notes' => trim($data['notes'] ?? ''),
                'terms' => trim($data['terms'] ?? ''),
            ], 'id = ?', [$id]);

            // Re-insert items
            Database::delete('contract_items', 'contract_id = ?', [$id]);
            $subtotal = 0;
            $totalTax = 0;
            if (!empty($data['items']) && is_array($data['items'])) {
                $sort = 0;
                foreach ($data['items'] as $item) {
                    if (empty($item['product_name']) && empty($item['product_id'])) continue;
                    $qty = (float)($item['quantity'] ?? 1);
                    $unitPrice = (float)($item['unit_price'] ?? 0);
                    $taxRate = (float)($item['tax_rate'] ?? 0);
                    $discountPct = (float)($item['discount_percent'] ?? 0);
                    $discount = (float)($item['discount'] ?? 0);
                    $lineSub = $qty * $unitPrice;
                    $lineTax = $lineSub * $taxRate / 100;
                    $lineTotal = $lineSub + $lineTax - $discount;

                    Database::insert('contract_items', [
                        'contract_id' => $id,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'] ?? '',
                        'description' => $item['description'] ?? '',
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? 'Cái',
                        'unit_price' => $unitPrice,
                        'cost_price' => (float)($item['cost_price'] ?? 0),
                        'tax_rate' => $taxRate,
                        'discount_percent' => $discountPct,
                        'discount' => $discount,
                        'total' => max(0, $lineTotal),
                        'sort_order' => $sort++,
                    ]);
                    $subtotal += $lineSub;
                    $totalTax += $lineTax;
                }
            }

            $discountAmt = (float)($data['discount_amount'] ?? 0);
            $shippingFee = (float)($data['shipping_fee'] ?? 0);
            $installFee = (float)($data['installation_fee'] ?? 0);
            $vatAmt = (float)($data['vat_amount'] ?? 0);
            $totalValue = $subtotal + $shippingFee - $discountAmt + $vatAmt + $installFee;

            Database::update('contracts', [
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'value' => max(0, $totalValue),
            ], 'id = ?', [$id]);

            // Re-insert related users
            $this->saveRelatedUsers($id, $data['related_users'] ?? []);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi cập nhật: ' . $e->getMessage());
            return $this->back();
        }

        $this->setFlash('success', 'Hợp đồng đã được cập nhật.');
        return $this->redirect('contracts/' . $id);
    }

    public function approve($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND is_deleted = 0", [$id]);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        Database::update('contracts', [
            'status' => 'approved',
            'signed_date' => date('Y-m-d'),
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Hợp đồng đã được duyệt.');
        return $this->redirect('contracts/' . $id);
    }

    public function start($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        Database::update('contracts', ['status' => 'in_progress'], 'id = ? AND is_deleted = 0', [$id]);
        $this->setFlash('success', 'Hợp đồng đang thực hiện.');
        return $this->redirect('contracts/' . $id);
    }

    public function complete($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        Database::update('contracts', ['status' => 'completed'], 'id = ? AND is_deleted = 0', [$id]);
        $this->setFlash('success', 'Hợp đồng đã hoàn thành.');
        return $this->redirect('contracts/' . $id);
    }

    public function cancel($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        Database::update('contracts', ['status' => 'cancelled'], 'id = ? AND is_deleted = 0', [$id]);
        $this->setFlash('success', 'Hợp đồng đã hủy.');
        return $this->redirect('contracts/' . $id);
    }

    public function createOrder($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND is_deleted = 0", [$id]);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        $items = Database::fetchAll("SELECT * FROM contract_items WHERE contract_id = ? ORDER BY sort_order", [$id]);

        Database::beginTransaction();
        try {
            $prefix = 'DH' . date('ym');
            $last = Database::fetch("SELECT order_number FROM orders WHERE order_number LIKE ? ORDER BY id DESC LIMIT 1", [$prefix . '%']);
            $seq = $last ? ((int) substr($last['order_number'], -4)) + 1 : 1;
            $orderNumber = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);

            $orderId = Database::insert('orders', [
                'order_number' => $orderNumber,
                'type' => 'order',
                'status' => 'pending',
                'contact_id' => $contract['contact_id'],
                'company_id' => $contract['company_id'],
                'deal_id' => $contract['deal_id'],
                'contract_id' => $id,
                'subtotal' => $contract['subtotal'],
                'tax_amount' => $contract['vat_amount'] ?? $contract['tax_amount'],
                'discount_amount' => $contract['discount_amount'],
                'discount_type' => 'fixed',
                'transport_amount' => $contract['shipping_fee'],
                'installation_amount' => $contract['installation_fee'],
                'total' => $contract['value'],
                'currency' => 'VND',
                'notes' => $contract['notes'],
                'payment_method' => $contract['payment_method'],
                'owner_id' => $contract['owner_id'],
                'created_by' => $this->userId(),
                'tenant_id' => Database::tenantId(),
                'issued_date' => date('Y-m-d'),
            ]);

            foreach ($items as $item) {
                Database::insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'description' => $item['description'] ?? '',
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

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi tạo đơn hàng: ' . $e->getMessage());
            return $this->back();
        }

        $this->setFlash('success', "Đã tạo đơn hàng {$orderNumber} từ hợp đồng.");
        return $this->redirect('orders/' . $orderId);
    }

    public function renew($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND is_deleted = 0", [$id]);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        // Calculate new dates
        $oldStart = new \DateTime($contract['start_date']);
        $oldEnd = $contract['end_date'] ? new \DateTime($contract['end_date']) : new \DateTime();
        $duration = $oldStart->diff($oldEnd);

        $newStart = new \DateTime();
        $newEnd = clone $newStart;
        $newEnd->add($duration);

        $newNumber = $this->generateContractNumber();

        $newId = Database::insert('contracts', [
            'contract_number' => $newNumber,
            'title' => $contract['title'] . ' (Gia hạn)',
            'type' => 'Gia hạn',
            'status' => 'pending',
            'value' => $contract['value'],
            'payment_method' => $contract['payment_method'],
            'usage_type' => $contract['usage_type'],
            'contact_id' => $contract['contact_id'],
            'company_id' => $contract['company_id'],
            'deal_id' => $contract['deal_id'],
            'quote_id' => $contract['quote_id'],
            'owner_id' => $contract['owner_id'],
            'start_date' => $newStart->format('Y-m-d'),
            'end_date' => $newEnd->format('Y-m-d'),
            'created_date' => date('Y-m-d'),
            'auto_renew' => $contract['auto_renew'],
            'notes' => $contract['notes'],
            'terms' => $contract['terms'],
            'parent_contract_id' => $id,
            'installation_address' => $contract['installation_address'],
            'location' => $contract['location'],
            'project' => $contract['project'],
            // Party A
            'party_a_company_id' => $contract['party_a_company_id'],
            'party_a_name' => $contract['party_a_name'],
            'party_a_address' => $contract['party_a_address'],
            'party_a_phone' => $contract['party_a_phone'],
            'party_a_fax' => $contract['party_a_fax'],
            'party_a_representative' => $contract['party_a_representative'],
            'party_a_position' => $contract['party_a_position'],
            'party_a_bank_account' => $contract['party_a_bank_account'],
            'party_a_bank_name' => $contract['party_a_bank_name'],
            'party_a_tax_code' => $contract['party_a_tax_code'],
            // Party B
            'party_b_name' => $contract['party_b_name'],
            'party_b_address' => $contract['party_b_address'],
            'party_b_phone' => $contract['party_b_phone'],
            'party_b_fax' => $contract['party_b_fax'],
            'party_b_representative' => $contract['party_b_representative'],
            'party_b_position' => $contract['party_b_position'],
            'party_b_bank_account' => $contract['party_b_bank_account'],
            'party_b_bank_name' => $contract['party_b_bank_name'],
            'party_b_tax_code' => $contract['party_b_tax_code'],
            'created_by' => $this->userId(),
            'tenant_id' => Database::tenantId(),
            'is_deleted' => 0,
        ]);

        // Copy items
        $oldItems = Database::fetchAll("SELECT * FROM contract_items WHERE contract_id = ? ORDER BY sort_order", [$id]);
        foreach ($oldItems as $item) {
            Database::insert('contract_items', [
                'contract_id' => $newId,
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'cost_price' => $item['cost_price'],
                'tax_rate' => $item['tax_rate'],
                'discount_percent' => $item['discount_percent'],
                'discount' => $item['discount'],
                'total' => $item['total'],
                'sort_order' => $item['sort_order'],
            ]);
        }

        // Mark old contract as completed
        Database::update('contracts', ['status' => 'renewed'], 'id = ?', [$id]);

        $this->setFlash('success', "Hợp đồng mới {$newNumber} đã được tạo từ gia hạn.");
        return $this->redirect('contracts/' . $newId);
    }

    public function delete($id)
    {
        $this->authorize('contracts', 'delete');
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND tenant_id = ? AND is_deleted = 0", [$id, Database::tenantId()]);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }
        if (!$this->canAccessEntity('contract', (int)$id, (int)($contract['owner_id'] ?? 0))) {
            $this->setFlash('error', 'Không có quyền.');
            return $this->redirect('contracts');
        }

        Database::update('contracts', [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Hợp đồng đã được xóa.');
        return $this->redirect('contracts');
    }

    public function comment($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $content = trim($this->input('content') ?? '');
        if (empty($content)) {
            $this->setFlash('error', 'Vui lòng nhập nội dung.');
            return $this->back();
        }

        Database::insert('contract_comments', [
            'contract_id' => (int)$id,
            'user_id' => $this->userId(),
            'content' => $content,
        ]);

        $this->setFlash('success', 'Đã gửi bình luận.');
        return $this->redirect('contracts/' . $id);
    }

    public function print($id)
    {
        $contract = $this->loadContractWithFallbacks($id);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        $items = Database::fetchAll(
            "SELECT ci.*, p.sku as product_sku FROM contract_items ci LEFT JOIN products p ON ci.product_id = p.id WHERE ci.contract_id = ? ORDER BY ci.sort_order", [$id]
        );

        $templateId = $this->input('template_id') ? (int)$this->input('template_id') : null;
        $replacements = \App\Services\DocumentService::contractReplacements($contract, $items);
        $html = \App\Services\DocumentService::render('contract', $templateId, $replacements);

        if (!$html) {
            $this->setFlash('error', 'Chưa có mẫu hợp đồng. Vui lòng tạo mẫu trong Cài đặt → Mẫu báo giá & HĐ.');
            return $this->redirect('contracts/' . $id);
        }

        // Watermark for non-approved contracts
        $watermark = '';
        if (in_array($contract['status'], ['pending', 'draft'])) $watermark = 'BẢN NHÁP';
        elseif ($contract['status'] === 'cancelled') $watermark = 'ĐÃ HỦY';

        $templates = \App\Services\DocumentService::getTemplates('contract');

        return $this->view('contracts.print', [
            'contract' => $contract,
            'html' => $html,
            'watermark' => $watermark,
            'templates' => $templates,
            'selectedTemplateId' => $templateId,
        ]);
    }

    public function pdf($id)
    {
        $contract = $this->loadContractWithFallbacks($id);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        $items = Database::fetchAll(
            "SELECT ci.*, p.sku as product_sku FROM contract_items ci LEFT JOIN products p ON ci.product_id = p.id WHERE ci.contract_id = ? ORDER BY ci.sort_order", [$id]
        );

        $templateId = $this->input('template_id') ? (int)$this->input('template_id') : null;
        $replacements = \App\Services\DocumentService::contractReplacements($contract, $items);
        $html = \App\Services\DocumentService::render('contract', $templateId, $replacements);

        if (!$html) {
            $this->setFlash('error', 'Chưa có mẫu hợp đồng.');
            return $this->redirect('contracts/' . $id);
        }

        $pdfContent = \App\Services\DocumentService::generatePdf($html, 'HĐ ' . $contract['contract_number']);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="HD-' . $contract['contract_number'] . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }

    public function downloadPdf($id)
    {
        $contract = $this->loadContractWithFallbacks($id);
        if (!$contract) return $this->redirect('contracts');

        $items = Database::fetchAll(
            "SELECT ci.*, p.sku as product_sku FROM contract_items ci LEFT JOIN products p ON ci.product_id = p.id WHERE ci.contract_id = ? ORDER BY ci.sort_order", [$id]
        );

        $replacements = \App\Services\DocumentService::contractReplacements($contract, $items);
        $html = \App\Services\DocumentService::render('contract', null, $replacements);
        if (!$html) return $this->redirect('contracts/' . $id);

        $pdfContent = \App\Services\DocumentService::generatePdf($html);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="HD-' . $contract['contract_number'] . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        echo $pdfContent;
        exit;
    }

    public function emailPdf($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $contract = $this->loadContractWithFallbacks($id);
        if (!$contract) return $this->redirect('contracts');

        $email = trim($this->input('email') ?? '');
        if (empty($email)) {
            $this->setFlash('error', 'Vui lòng nhập email.');
            return $this->back();
        }

        $items = Database::fetchAll("SELECT ci.* FROM contract_items ci WHERE ci.contract_id = ? ORDER BY ci.sort_order", [$id]);
        $replacements = \App\Services\DocumentService::contractReplacements($contract, $items);
        $html = \App\Services\DocumentService::render('contract', null, $replacements);
        if (!$html) {
            $this->setFlash('error', 'Chưa có mẫu hợp đồng.');
            return $this->back();
        }

        $pdfContent = \App\Services\DocumentService::generatePdf($html);
        $fileName = 'HD-' . $contract['contract_number'] . '.pdf';
        $tempPath = sys_get_temp_dir() . '/' . $fileName;
        file_put_contents($tempPath, $pdfContent);

        try {
            $branding = BrandingService::get();
            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mailer->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
            $mailer->SMTPAuth = true;
            $mailer->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $mailer->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $mailer->SMTPSecure = 'tls';
            $mailer->CharSet = 'UTF-8';
            $fromEmail = tenant_setting('email_from_email') ?: ($_ENV['MAIL_FROM'] ?? 'noreply@torycrm.com');
            $fromName  = tenant_setting('email_from_name')  ?: ($branding['name'] ?? 'ToryCRM');
            $mailer->setFrom($fromEmail, $fromName);
            $mailer->addAddress($email);
            $mailer->Subject = 'Hợp đồng ' . $contract['contract_number'];
            $mailer->Body = 'Kính gửi Quý khách,\n\nĐính kèm hợp đồng ' . $contract['contract_number'] . '.\n\nTrân trọng,\n' . ($branding['name'] ?? '');
            $mailer->isHTML(false);
            $mailer->addAttachment($tempPath, $fileName);
            $mailer->send();

            $this->setFlash('success', 'Đã gửi email hợp đồng đến ' . $email);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Lỗi gửi email: ' . $e->getMessage());
        }

        @unlink($tempPath);
        return $this->redirect('contracts/' . $id);
    }

    public function uploadAttachment($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $contract = Database::fetch("SELECT id FROM contracts WHERE id = ? AND is_deleted = 0", [$id]);
        if (!$contract) return $this->redirect('contracts');

        if (empty($_FILES['file']['name'])) {
            $this->setFlash('error', 'Vui lòng chọn file.');
            return $this->back();
        }

        $file = $_FILES['file'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            $this->setFlash('error', 'File quá lớn (tối đa 10MB).');
            return $this->back();
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png','gif'];
        if (!in_array($ext, $allowed)) {
            $this->setFlash('error', 'Định dạng file không được phép.');
            return $this->back();
        }

        $dir = 'uploads/contracts/' . $id;
        if (!is_dir(BASE_PATH . '/public/' . $dir)) {
            mkdir(BASE_PATH . '/public/' . $dir, 0755, true);
        }

        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
        $filePath = $dir . '/' . $fileName;
        move_uploaded_file($file['tmp_name'], BASE_PATH . '/public/' . $filePath);

        Database::insert('contract_attachments', [
            'contract_id' => $id,
            'file_name' => $file['name'],
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'file_type' => $file['type'],
            'uploaded_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tải lên file.');
        return $this->redirect('contracts/' . $id);
    }

    public function deleteAttachment($id, $attachId)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $att = Database::fetch("SELECT * FROM contract_attachments WHERE id = ? AND contract_id = ?", [$attachId, $id]);
        if ($att) {
            @unlink(BASE_PATH . '/public/' . $att['file_path']);
            Database::delete('contract_attachments', 'id = ?', [$attachId]);
        }

        $this->setFlash('success', 'Đã xóa file.');
        return $this->redirect('contracts/' . $id);
    }

    /**
     * Load contract with party A/B fallbacks from branding/contact.
     */
    private function loadContractWithFallbacks($id): ?array
    {
        $contract = Database::fetch(
            "SELECT ct.*, c.first_name as contact_first_name, c.last_name as contact_last_name,
                    c.company_name as contact_company_name, c.address as contact_address, c.phone as contact_phone, c.tax_code as contact_tax_code,
                    u.name as owner_name
             FROM contracts ct
             LEFT JOIN contacts c ON ct.contact_id = c.id
             LEFT JOIN users u ON ct.owner_id = u.id
             WHERE ct.id = ? AND ct.is_deleted = 0", [$id]
        );
        if (!$contract) return null;

        if (empty($contract['party_a_name'])) {
            $b = \App\Services\BrandingService::get();
            $contract['party_a_name'] = $b['name'] ?? '';
            $contract['party_a_address'] = $contract['party_a_address'] ?: ($b['address'] ?? '');
            $contract['party_a_phone'] = $contract['party_a_phone'] ?: ($b['phone'] ?? '');
            $contract['party_a_fax'] = $contract['party_a_fax'] ?: ($b['fax'] ?? '');
            $contract['party_a_tax_code'] = $contract['party_a_tax_code'] ?: ($b['tax_code'] ?? '');
            $contract['party_a_representative'] = $contract['party_a_representative'] ?: ($b['representative'] ?? '');
            $contract['party_a_position'] = $contract['party_a_position'] ?: ($b['representative_title'] ?? '');
            $contract['party_a_bank_account'] = $contract['party_a_bank_account'] ?: ($b['bank_account'] ?? '');
            $contract['party_a_bank_name'] = $contract['party_a_bank_name'] ?: ($b['bank_name'] ?? '');
        }
        if (empty($contract['party_b_name'])) {
            $contract['party_b_name'] = $contract['contact_company_name'] ?: trim(($contract['contact_first_name'] ?? '') . ' ' . ($contract['contact_last_name'] ?? ''));
            $contract['party_b_address'] = $contract['party_b_address'] ?: ($contract['contact_address'] ?? '');
            $contract['party_b_phone'] = $contract['party_b_phone'] ?: ($contract['contact_phone'] ?? '');
            $contract['party_b_tax_code'] = $contract['party_b_tax_code'] ?: ($contract['contact_tax_code'] ?? '');
        }
        return $contract;
    }

    private function generateContractNumber(): string
    {
        $prefix = 'HD-' . date('ym');
        $last = Database::fetch(
            "SELECT contract_number FROM contracts WHERE contract_number LIKE ? ORDER BY id DESC LIMIT 1",
            [$prefix . '%']
        );

        if ($last) {
            $num = (int) substr($last['contract_number'], -4) + 1;
        } else {
            $num = 1;
        }

        return $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    private function saveRelatedUsers(int $contractId, array $relatedUsers): void
    {
        Database::delete('contract_related_users', 'contract_id = ?', [$contractId]);
        foreach ($relatedUsers as $ru) {
            if (empty($ru['user_id'])) continue;
            Database::insert('contract_related_users', [
                'contract_id' => $contractId,
                'user_id' => (int)$ru['user_id'],
                'commission' => (float)($ru['commission'] ?? 0),
            ]);
        }
    }
}
