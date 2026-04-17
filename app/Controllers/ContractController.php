<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ContractController extends Controller
{
    public function index()
    {
        $status = $this->input('status');
        $type = $this->input('type');
        $contactId = $this->input('contact_id');
        $dateFrom = $this->input('date_from');
        $dateTo = $this->input('date_to');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
        $offset = ($page - 1) * $perPage;

        $where = ["ct.is_deleted = 0"];
        $params = [];

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

        $ownerScope = $this->ownerScope('ct', 'owner_id', 'fund');
        if ($ownerScope['where']) { $where[] = $ownerScope['where']; $params = array_merge($params, $ownerScope['params']); }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM contracts ct WHERE {$whereClause}",
            $params
        )['count'];

        $contracts = Database::fetchAll(
            "SELECT ct.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as owner_name
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

        // Stats per status
        $statusCounts = Database::fetchAll(
            "SELECT status, COUNT(*) as cnt FROM contracts WHERE is_deleted = 0 GROUP BY status"
        );
        $stats = [];
        $totalAll = 0;
        foreach ($statusCounts as $sc2) { $stats[$sc2['status']] = (int)$sc2['cnt']; $totalAll += (int)$sc2['cnt']; }
        $stats['expiring_soon'] = (int)(Database::fetch(
            "SELECT COUNT(*) as cnt FROM contracts WHERE is_deleted = 0 AND status = 'active' AND end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND end_date >= CURDATE()"
        )['cnt'] ?? 0);

        $contacts = Database::fetchAll("SELECT id, first_name, last_name, company_name FROM contacts WHERE is_deleted = 0 ORDER BY first_name LIMIT 500");

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
            'contacts' => $contacts,
            'filters' => [
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
        $contractNumber = $this->generateContractNumber();
        $contacts = Database::fetchAll("SELECT id, first_name, last_name, company_name FROM contacts WHERE is_deleted = 0 ORDER BY first_name LIMIT 500");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");
        $products = Database::fetchAll("SELECT id, sku, name, price, unit FROM products WHERE is_deleted = 0 ORDER BY name LIMIT 500");

        return $this->view('contracts.create', [
            'contractNumber' => $contractNumber,
            'contacts' => $contacts,
            'users' => $users,
            'products' => $products,
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

        $contractNumber = $this->generateContractNumber();

        Database::beginTransaction();
        try {
            $id = Database::insert('contracts', [
                'contract_number' => $contractNumber,
                'title' => trim($data['title']),
                'type' => $data['type'] ?? 'service',
                'status' => 'draft',
                'value' => (float) ($data['value'] ?? 0),
                'recurring_value' => (float) ($data['recurring_value'] ?? 0),
                'recurring_cycle' => $data['recurring_cycle'] ?? null,
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
                'contact_name' => trim($data['contact_name'] ?? '') ?: null,
                'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : $this->userId(),
                'start_date' => !empty($data['start_date']) ? $data['start_date'] : date('Y-m-d'),
                'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
                'auto_renew' => !empty($data['auto_renew']) ? 1 : 0,
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'shipping_fee' => (float)($data['shipping_fee'] ?? 0),
                'installation_fee' => (float)($data['installation_fee'] ?? 0),
                'installation_address' => trim($data['installation_address'] ?? '') ?: null,
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
                    if (empty($item['product_name'])) continue;
                    $qty = (float)($item['quantity'] ?? 1);
                    $unitPrice = (float)($item['unit_price'] ?? 0);
                    $taxRate = (float)($item['tax_rate'] ?? 0);
                    $discount = (float)($item['discount'] ?? 0);
                    $lineSub = $qty * $unitPrice;
                    $lineTax = $lineSub * $taxRate / 100;
                    $lineTotal = $lineSub + $lineTax - $discount;

                    Database::insert('contract_items', [
                        'contract_id' => $id,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'],
                        'description' => $item['description'] ?? '',
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? 'Cái',
                        'unit_price' => $unitPrice,
                        'cost_price' => (float)($item['cost_price'] ?? 0),
                        'tax_rate' => $taxRate,
                        'discount_percent' => (float)($item['discount_percent'] ?? 0),
                        'discount' => $discount,
                        'total' => $lineTotal,
                        'sort_order' => $sort++,
                    ]);
                    $subtotal += $lineSub;
                    $totalTax += $lineTax;
                }
            }

            $discountAmt = (float)($data['discount_amount'] ?? 0);
            $shippingFee = (float)($data['shipping_fee'] ?? 0);
            $installFee = (float)($data['installation_fee'] ?? 0);
            $totalValue = $subtotal + $totalTax - $discountAmt + $shippingFee + $installFee;

            Database::update('contracts', [
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'value' => max(0, $totalValue),
            ], 'id = ?', [$id]);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi tạo hợp đồng: ' . $e->getMessage());
            return $this->back();
        }

        $this->setFlash('success', "Hợp đồng {$contractNumber} đã được tạo.");
        return $this->redirect('contracts/' . $id);
    }

    public function show($id)
    {
        $contract = Database::fetch(
            "SELECT ct.*,
                    c.first_name as contact_first_name, c.last_name as contact_last_name, c.email as contact_email, c.phone as contact_phone,
                    comp.name as company_name,
                    d.title as deal_title, d.id as deal_id,
                    u.name as owner_name,
                    uc.name as created_by_name
             FROM contracts ct
             LEFT JOIN contacts c ON ct.contact_id = c.id
             LEFT JOIN companies comp ON ct.company_id = comp.id
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

        // Related orders
        $orders = Database::fetchAll(
            "SELECT id, order_number, total, status FROM orders WHERE contract_id = ? AND is_deleted = 0 ORDER BY created_at DESC",
            [$id]
        );

        $items = Database::fetchAll(
            "SELECT ci.*, p.sku as product_sku FROM contract_items ci LEFT JOIN products p ON ci.product_id = p.id WHERE ci.contract_id = ? ORDER BY ci.sort_order", [$id]
        );

        return $this->view('contracts.show', [
            'contract' => $contract,
            'items' => $items,
            'orders' => $orders,
        ]);
    }

    public function edit($id)
    {
        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND is_deleted = 0", [$id]);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        $contacts = Database::fetchAll("SELECT id, first_name, last_name, company_name FROM contacts WHERE is_deleted = 0 ORDER BY first_name LIMIT 500");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");
        $products = Database::fetchAll("SELECT id, sku, name, price, unit FROM products WHERE is_deleted = 0 ORDER BY name LIMIT 500");

        $items = Database::fetchAll(
            "SELECT ci.*, p.sku as product_sku FROM contract_items ci LEFT JOIN products p ON ci.product_id = p.id WHERE ci.contract_id = ? ORDER BY ci.sort_order", [$id]
        );

        return $this->view('contracts.edit', [
            'contract' => $contract,
            'items' => $items,
            'contacts' => $contacts,
            'users' => $users,
            'products' => $products,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND is_deleted = 0", [$id]);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        $data = $this->allInput();

        Database::beginTransaction();
        try {
            Database::update('contracts', [
                'title' => trim($data['title'] ?? $contract['title']),
                'type' => $data['type'] ?? $contract['type'],
                'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
                'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
                'contact_name' => trim($data['contact_name'] ?? '') ?: null,
                'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : $contract['owner_id'],
                'start_date' => !empty($data['start_date']) ? $data['start_date'] : $contract['start_date'],
                'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
                'auto_renew' => !empty($data['auto_renew']) ? 1 : 0,
                'discount_amount' => (float)($data['discount_amount'] ?? 0),
                'shipping_fee' => (float)($data['shipping_fee'] ?? 0),
                'installation_fee' => (float)($data['installation_fee'] ?? 0),
                'installation_address' => trim($data['installation_address'] ?? '') ?: null,
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
                    if (empty($item['product_name'])) continue;
                    $qty = (float)($item['quantity'] ?? 1);
                    $unitPrice = (float)($item['unit_price'] ?? 0);
                    $taxRate = (float)($item['tax_rate'] ?? 0);
                    $discount = (float)($item['discount'] ?? 0);
                    $lineSub = $qty * $unitPrice;
                    $lineTax = $lineSub * $taxRate / 100;
                    $lineTotal = $lineSub + $lineTax - $discount;

                    Database::insert('contract_items', [
                        'contract_id' => $id,
                        'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                        'product_name' => $item['product_name'],
                        'description' => $item['description'] ?? '',
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? 'Cái',
                        'unit_price' => $unitPrice,
                        'cost_price' => (float)($item['cost_price'] ?? 0),
                        'tax_rate' => $taxRate,
                        'discount_percent' => (float)($item['discount_percent'] ?? 0),
                        'discount' => $discount,
                        'total' => $lineTotal,
                        'sort_order' => $sort++,
                    ]);
                    $subtotal += $lineSub;
                    $totalTax += $lineTax;
                }
            }

            $discountAmt = (float)($data['discount_amount'] ?? 0);
            $shippingFee = (float)($data['shipping_fee'] ?? 0);
            $installFee = (float)($data['installation_fee'] ?? 0);
            $totalValue = $subtotal + $totalTax - $discountAmt + $shippingFee + $installFee;

            Database::update('contracts', [
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'value' => max(0, $totalValue),
            ], 'id = ?', [$id]);

            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            $this->setFlash('error', 'Lỗi cập nhật: ' . $e->getMessage());
            return $this->back();
        }

        $this->setFlash('success', 'Hợp đồng đã được cập nhật.');
        return $this->redirect('contracts/' . $id);
    }

    public function sign($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND is_deleted = 0", [$id]);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        Database::update('contracts', [
            'status' => 'signed',
            'signed_date' => date('Y-m-d'),
        ], 'id = ?', [$id]);

        // If start_date is today or past, also set active
        if (!empty($contract['start_date']) && $contract['start_date'] <= date('Y-m-d')) {
            Database::update('contracts', ['status' => 'active'], 'id = ?', [$id]);
        }

        $this->setFlash('success', 'Hợp đồng đã được ký.');
        return $this->redirect('contracts/' . $id);
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
            'type' => $contract['type'],
            'status' => 'draft',
            'value' => $contract['value'],
            'recurring_value' => $contract['recurring_value'],
            'recurring_cycle' => $contract['recurring_cycle'],
            'contact_id' => $contract['contact_id'],
            'company_id' => $contract['company_id'],
            'deal_id' => $contract['deal_id'],
            'owner_id' => $contract['owner_id'],
            'start_date' => $newStart->format('Y-m-d'),
            'end_date' => $newEnd->format('Y-m-d'),
            'auto_renew' => $contract['auto_renew'],
            'notes' => $contract['notes'],
            'terms' => $contract['terms'],
            'parent_contract_id' => $id,
            'created_by' => $this->userId(),
            'is_deleted' => 0,
        ]);

        // Mark old contract as expired
        Database::update('contracts', ['status' => 'expired'], 'id = ?', [$id]);

        $this->setFlash('success', "Hợp đồng mới {$newNumber} đã được tạo từ gia hạn.");
        return $this->redirect('contracts/' . $newId);
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('contracts/' . $id);

        $contract = Database::fetch("SELECT * FROM contracts WHERE id = ? AND is_deleted = 0", [$id]);
        if (!$contract) {
            $this->setFlash('error', 'Hợp đồng không tồn tại.');
            return $this->redirect('contracts');
        }

        Database::update('contracts', [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Hợp đồng đã được xóa.');
        return $this->redirect('contracts');
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
}
