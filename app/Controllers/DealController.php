<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class DealController extends Controller
{
    public function index()
    {
        $this->authorize('deals', 'view');
        $search = $this->input('search');
        $stageId = $this->input('stage_id');
        $status = $this->input('status');
        $ownerId = $this->input('owner_id');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ["d.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search) {
            $where[] = "(d.title LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ? OR comp.name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($stageId) {
            $where[] = "d.stage_id = ?";
            $params[] = $stageId;
        }

        if ($status) {
            $where[] = "d.status = ?";
            $params[] = $status;
        }

        if ($ownerId) {
            $where[] = "d.owner_id = ?";
            $params[] = $ownerId;
        }

        // Owner-based data scoping: staff only sees own records
        $ownerScope = $this->ownerScope('d', 'owner_id', 'deals');
        if ($ownerScope['where']) {
            $where[] = $ownerScope['where'];
            $params = array_merge($params, $ownerScope['params']);
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             WHERE {$whereClause}",
            $params
        )['count'];

        $deals = Database::fetchAll(
            "SELECT d.*, c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name, u.name as owner_name, ds.name as stage_name, ds.color as stage_color
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             LEFT JOIN users u ON d.owner_id = u.id
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             WHERE {$whereClause}
             ORDER BY d.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $stages = Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");
        $totalPages = ceil($total / $perPage);
        $displayColumns = \App\Services\ColumnService::getColumns('deals');

        return $this->view('deals.index', [
            'deals' => [
                'items' => $deals,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'stages' => $stages,
            'filters' => [
                'search' => $search,
                'stage_id' => $stageId,
                'status' => $status,
                'owner_id' => $ownerId,
            ],
            'displayColumns' => $displayColumns,
        ]);
    }

    public function pipeline()
    {
        $this->authorize('deals', 'view');
        $stages = Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");

        $pipeline = [];
        foreach ($stages as $stage) {
            $stage['deals'] = Database::fetchAll(
                "SELECT d.*, c.first_name as contact_first_name, c.last_name as contact_last_name,
                        comp.name as company_name, u.name as owner_name
                 FROM deals d
                 LEFT JOIN contacts c ON d.contact_id = c.id
                 LEFT JOIN companies comp ON d.company_id = comp.id
                 LEFT JOIN users u ON d.owner_id = u.id
                 WHERE d.stage_id = ? AND d.status = 'open'
                 ORDER BY d.created_at DESC",
                [$stage['id']]
            );
            $pipeline[] = $stage;
        }

        return $this->view('deals.pipeline', [
            'pipeline' => $pipeline,
        ]);
    }

    public function create()
    {
        $this->authorize('deals', 'create');
        $contacts = Database::fetchAll(
            "SELECT id, first_name, last_name FROM contacts ORDER BY first_name"
        );
        $companies = Database::fetchAll(
            "SELECT id, name FROM companies ORDER BY name"
        );
        $stages = Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('deals.create', [
            'contacts' => $contacts,
            'companies' => $companies,
            'stages' => $stages,
            'users' => $users,
            'selectedContactId' => (int) $this->input('contact_id'),
            'selectedCompanyId' => (int) $this->input('company_id'),
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('deals');
        }
        $this->authorize('deals', 'create');

        $data = $this->allInput();

        $title = trim($data['title'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Deal title is required.');
            return $this->back();
        }

        $dealId = Database::insert('deals', [
            'title' => $title,
            'value' => (float) ($data['value'] ?? 0),
            'stage_id' => (!empty($data['stage_id']) ? $data['stage_id'] : null),
            'status' => $data['status'] ?? 'open',
            'contact_id' => (!empty($data['contact_id']) ? $data['contact_id'] : null),
            'company_id' => (!empty($data['company_id']) ? $data['company_id'] : null),
            'owner_id' => (!empty($data['owner_id']) ? $data['owner_id'] : $this->userId()),
            'expected_close_date' => (!empty($data['expected_close_date']) ? $data['expected_close_date'] : null),
            'priority' => $data['priority'] ?? 'medium',
            'description' => trim($data['description'] ?? ''),
            'created_by' => $this->userId(),
        ]);

        // Log activity
        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Deal created: {$title}",
            'description' => "New deal {$title} was created.",
            'user_id' => $this->userId(),
            'deal_id' => $dealId,
        ]);

        $this->setFlash('success', 'Deal created successfully.');
        return $this->redirect('deals/' . $dealId);
    }

    public function show($id)
    {
        $this->authorize('deals', 'view');
        $deal = Database::fetch(
            "SELECT d.*, c.first_name as contact_first_name, c.last_name as contact_last_name, c.email as contact_email, c.phone as contact_phone,
                    comp.name as company_name, u.name as owner_name, ds.name as stage_name, ds.color as stage_color, ds.sort_order as stage_order
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             LEFT JOIN users u ON d.owner_id = u.id
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             WHERE d.id = ?",
            [$id]
        );

        if (!$deal) {
            $this->setFlash('error', 'Deal not found.');
            return $this->redirect('deals');
        }

        // Ownership check: staff can only view own records
        if (!$this->canAccessOwner($deal['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('deals');
        }

        $activities = Database::fetchAll(
            "SELECT a.*, u.name as user_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.deal_id = ?
             ORDER BY a.created_at DESC
             LIMIT 50",
            [$id]
        );

        $tasks = Database::fetchAll(
            "SELECT t.*, u.name as assigned_name
             FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             WHERE t.deal_id = ?
             ORDER BY t.due_date ASC",
            [$id]
        );

        $stages = Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");

        $dealProducts = Database::fetchAll(
            "SELECT dp.*, p.name as product_name, p.sku
             FROM deal_products dp
             LEFT JOIN products p ON dp.product_id = p.id
             WHERE dp.deal_id = ?
             ORDER BY dp.id",
            [$id]
        );

        $products = Database::fetchAll(
            "SELECT id, name, sku, price FROM products WHERE is_deleted = 0 ORDER BY name"
        );

        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('deals.show', [
            'deal' => $deal,
            'activities' => $activities,
            'tasks' => $tasks,
            'stages' => $stages,
            'dealProducts' => $dealProducts,
            'products' => $products,
            'users' => $users,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('deals', 'edit');
        $deal = Database::fetch("SELECT * FROM deals WHERE id = ?", [$id]);

        if (!$deal) {
            $this->setFlash('error', 'Deal not found.');
            return $this->redirect('deals');
        }

        // Ownership check: staff can only edit own records
        if (!$this->canAccessOwner($deal['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('deals');
        }

        $contacts = Database::fetchAll(
            "SELECT id, first_name, last_name FROM contacts ORDER BY first_name"
        );
        $companies = Database::fetchAll(
            "SELECT id, name FROM companies ORDER BY name"
        );
        $stages = Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('deals.edit', [
            'deal' => $deal,
            'contacts' => $contacts,
            'companies' => $companies,
            'stages' => $stages,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('deals/' . $id);
        }
        $this->authorize('deals', 'edit');

        $deal = Database::fetch("SELECT * FROM deals WHERE id = ?", [$id]);

        if (!$deal) {
            $this->setFlash('error', 'Deal not found.');
            return $this->redirect('deals');
        }

        // Ownership check: staff can only update own records
        if (!$this->canAccessOwner($deal['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('deals');
        }

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Deal title is required.');
            return $this->back();
        }

        $oldStageId = $deal['stage_id'];
        $newStageId = $data['stage_id'] ?? $oldStageId;
        $newStatus = $data['status'] ?? 'open';

        // Validate close reason when status changes to won/lost
        if (($newStatus === 'won' || $newStatus === 'lost') && $newStatus !== $deal['status']) {
            if (empty(trim($data['close_reason'] ?? ''))) {
                $this->setFlash('error', 'Vui lòng nhập lý do đóng cơ hội.');
                return $this->back();
            }
            if ($newStatus === 'lost' && empty(trim($data['loss_reason_category'] ?? ''))) {
                $this->setFlash('error', 'Vui lòng chọn phân loại lý do thua.');
                return $this->back();
            }
        }

        $updateData = [
            'title' => $title,
            'value' => (float) ($data['value'] ?? 0),
            'stage_id' => $newStageId ?: null,
            'status' => $newStatus,
            'contact_id' => (!empty($data['contact_id']) ? $data['contact_id'] : null),
            'company_id' => (!empty($data['company_id']) ? $data['company_id'] : null),
            'owner_id' => (!empty($data['owner_id']) ? $data['owner_id'] : null),
            'expected_close_date' => (!empty($data['expected_close_date']) ? $data['expected_close_date'] : null),
            'priority' => $data['priority'] ?? 'medium',
            'description' => trim($data['description'] ?? ''),
        ];

        if (($newStatus === 'won' || $newStatus === 'lost') && $newStatus !== $deal['status']) {
            $updateData['close_reason'] = trim($data['close_reason'] ?? '');
            $updateData['actual_close_date'] = date('Y-m-d H:i:s');
            if ($newStatus === 'lost') {
                $updateData['loss_reason_category'] = trim($data['loss_reason_category'] ?? '');
                $updateData['competitor'] = trim($data['competitor'] ?? '');
            }
        }

        Database::update('deals', $updateData, 'id = ?', [$id]);

        // Log activity
        $description = "Deal {$title} was updated.";
        if ($oldStageId != $newStageId) {
            $description .= " Stage was changed.";
        }

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Deal updated: {$title}",
            'description' => $description,
            'user_id' => $this->userId(),
            'deal_id' => $id,
        ]);

        $this->setFlash('success', 'Deal updated successfully.');
        return $this->redirect('deals/' . $id);
    }

    public function delete($id)
    {
        $this->authorize('deals', 'delete');
        $deal = Database::fetch("SELECT * FROM deals WHERE id = ?", [$id]);

        if (!$deal) {
            $this->setFlash('error', 'Deal not found.');
            return $this->redirect('deals');
        }

        // Ownership check: staff can only delete own records
        if (!$this->canAccessOwner($deal['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('deals');
        }

        Database::delete('deals', 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Deal deleted: {$deal['title']}",
            'description' => "Deal {$deal['title']} was deleted.",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Deal deleted successfully.');
        return $this->redirect('deals');
    }

    public function quickUpdate($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('deals', 'edit');

        $deal = Database::fetch("SELECT * FROM deals WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$deal) {
            return $this->json(['error' => 'Deal không tồn tại'], 404);
        }

        $field = $this->input('field');
        $value = $this->input('value');
        $allowed = ['status', 'owner_id', 'stage_id', 'priority'];

        if (!in_array($field, $allowed)) {
            return $this->json(['error' => 'Trường không được phép cập nhật'], 422);
        }

        Database::update('deals', [$field => $value ?: null], 'id = ?', [$id]);

        $display = $value;
        if ($field === 'status') {
            $labels = ['open' => 'Mở', 'won' => 'Thắng', 'lost' => 'Thua'];
            $display = $labels[$value] ?? $value;
        } elseif ($field === 'owner_id') {
            $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$value]);
            $display = $user ? htmlspecialchars($user['name']) : '-';
        } elseif ($field === 'stage_id') {
            $stage = Database::fetch("SELECT name FROM deal_stages WHERE id = ?", [$value]);
            $display = $stage ? htmlspecialchars($stage['name']) : '-';
        }

        return $this->json(['success' => true, 'value' => $value, 'display' => $display]);
    }

    public function updateStage($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('deals', 'edit');

        $deal = Database::fetch("SELECT * FROM deals WHERE id = ?", [$id]);

        if (!$deal) {
            return $this->json(['error' => 'Deal not found'], 404);
        }

        $newStageId = $this->input('stage_id');

        // Validate stage exists
        $stage = Database::fetch("SELECT * FROM deal_stages WHERE id = ?", [$newStageId]);
        if (!$stage) {
            return $this->json(['error' => 'Invalid stage'], 422);
        }

        $oldStageId = $deal['stage_id'];

        Database::update('deals', [
            'stage_id' => $newStageId,
        ], 'id = ?', [$id]);

        // Log activity
        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Deal stage changed: {$deal['title']}",
            'description' => "Deal {$deal['title']} moved to stage {$stage['name']}.",
            'user_id' => $this->userId(),
            'deal_id' => $id,
        ]);

        return $this->json(['success' => true, 'stage_id' => $newStageId]);
    }

    public function closeDeal($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('deals/' . $id);
        }
        $this->authorize('deals', 'edit');

        $deal = Database::fetch("SELECT * FROM deals WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$deal) {
            $this->setFlash('error', 'Cơ hội không tồn tại.');
            return $this->redirect('deals');
        }

        $status = $this->input('status');
        $closeReason = trim($this->input('close_reason') ?? '');

        if (!in_array($status, ['won', 'lost'])) {
            $this->setFlash('error', 'Trạng thái không hợp lệ.');
            return $this->back();
        }

        if (empty($closeReason)) {
            $this->setFlash('error', 'Vui lòng nhập lý do đóng cơ hội.');
            return $this->back();
        }

        $updateData = [
            'status' => $status,
            'close_reason' => $closeReason,
            'actual_close_date' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'lost') {
            $lossCategory = trim($this->input('loss_reason_category') ?? '');
            if (empty($lossCategory)) {
                $this->setFlash('error', 'Vui lòng chọn phân loại lý do thua.');
                return $this->back();
            }
            $updateData['loss_reason_category'] = $lossCategory;
            $updateData['competitor'] = trim($this->input('competitor') ?? '');
        }

        Database::update('deals', $updateData, 'id = ?', [$id]);

        $statusLabel = $status === 'won' ? 'Thắng' : 'Thua';
        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Đóng cơ hội: {$deal['title']} - {$statusLabel}",
            'description' => "Lý do: {$closeReason}",
            'user_id' => $this->userId(),
            'deal_id' => $id,
        ]);

        $this->setFlash('success', "Đã đóng cơ hội: {$statusLabel}.");
        return $this->redirect('deals/' . $id);
    }

    public function addProduct($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('deals/' . $id);
        }
        $this->authorize('deals', 'edit');

        $deal = Database::fetch("SELECT * FROM deals WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$deal) {
            $this->setFlash('error', 'Cơ hội không tồn tại.');
            return $this->redirect('deals');
        }

        $productId = $this->input('product_id');
        $quantity = max(1, (int) ($this->input('quantity') ?? 1));
        $unitPrice = (float) ($this->input('unit_price') ?? 0);
        $discount = (float) ($this->input('discount') ?? 0);

        if (empty($productId)) {
            $this->setFlash('error', 'Vui lòng chọn sản phẩm.');
            return $this->back();
        }

        $product = Database::fetch("SELECT * FROM products WHERE id = ?", [$productId]);
        if (!$product) {
            $this->setFlash('error', 'Sản phẩm không tồn tại.');
            return $this->back();
        }

        if ($unitPrice <= 0) {
            $unitPrice = (float) $product['price'];
        }

        $total = ($unitPrice * $quantity) - $discount;

        Database::insert('deal_products', [
            'deal_id' => $id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'total' => $total,
        ]);

        // Update deal value
        $sumTotal = Database::fetch("SELECT COALESCE(SUM(total), 0) as total FROM deal_products WHERE deal_id = ?", [$id]);
        Database::update('deals', ['value' => $sumTotal['total']], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Thêm sản phẩm: {$product['name']}",
            'description' => "Số lượng: {$quantity}, Đơn giá: " . number_format($unitPrice) . "đ",
            'user_id' => $this->userId(),
            'deal_id' => $id,
        ]);

        $this->setFlash('success', 'Đã thêm sản phẩm.');
        return $this->redirect('deals/' . $id . '#tab-products');
    }

    public function removeProduct($id, $productId)
    {
        if (!$this->isPost()) {
            return $this->redirect('deals/' . $id);
        }
        $this->authorize('deals', 'edit');

        $deal = Database::fetch("SELECT * FROM deals WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$deal) {
            $this->setFlash('error', 'Cơ hội không tồn tại.');
            return $this->redirect('deals');
        }

        $dp = Database::fetch("SELECT dp.*, p.name as product_name FROM deal_products dp LEFT JOIN products p ON dp.product_id = p.id WHERE dp.id = ? AND dp.deal_id = ?", [$productId, $id]);
        if (!$dp) {
            $this->setFlash('error', 'Sản phẩm không tồn tại trong cơ hội.');
            return $this->back();
        }

        Database::delete('deal_products', 'id = ? AND deal_id = ?', [$productId, $id]);

        // Update deal value
        $sumTotal = Database::fetch("SELECT COALESCE(SUM(total), 0) as total FROM deal_products WHERE deal_id = ?", [$id]);
        Database::update('deals', ['value' => $sumTotal['total']], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Xóa sản phẩm: {$dp['product_name']}",
            'description' => '',
            'user_id' => $this->userId(),
            'deal_id' => $id,
        ]);

        $this->setFlash('success', 'Đã xóa sản phẩm.');
        return $this->redirect('deals/' . $id . '#tab-products');
    }

    public function forecast()
    {
        $this->authorize('deals', 'view');
        $stages = Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");

        $forecastData = [];
        $totalWeighted = 0;
        $totalValue = 0;
        $totalDeals = 0;

        foreach ($stages as $stage) {
            $stats = Database::fetch(
                "SELECT COUNT(*) as deal_count, COALESCE(SUM(value), 0) as total_value
                 FROM deals
                 WHERE stage_id = ? AND status = 'open' AND tenant_id = ?",
                [$stage['id'], Database::tenantId()]
            );

            $probability = (float) ($stage['probability'] ?? 0);
            $weighted = ($stats['total_value'] ?? 0) * ($probability / 100);

            $forecastData[] = [
                'stage' => $stage,
                'deal_count' => (int) ($stats['deal_count'] ?? 0),
                'total_value' => (float) ($stats['total_value'] ?? 0),
                'probability' => $probability,
                'weighted_value' => $weighted,
            ];

            $totalWeighted += $weighted;
            $totalValue += (float) ($stats['total_value'] ?? 0);
            $totalDeals += (int) ($stats['deal_count'] ?? 0);
        }

        // Last month won deals for comparison
        $lastMonthWon = Database::fetch(
            "SELECT COUNT(*) as cnt, COALESCE(SUM(value), 0) as total
             FROM deals
             WHERE status = 'won' AND tenant_id = ?
             AND actual_close_date >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
             AND actual_close_date < DATE_FORMAT(NOW(), '%Y-%m-01')",
            [Database::tenantId()]
        );

        // This month won deals
        $thisMonthWon = Database::fetch(
            "SELECT COUNT(*) as cnt, COALESCE(SUM(value), 0) as total
             FROM deals
             WHERE status = 'won' AND tenant_id = ?
             AND actual_close_date >= DATE_FORMAT(NOW(), '%Y-%m-01')",
            [Database::tenantId()]
        );

        return $this->view('deals.forecast', [
            'forecastData' => $forecastData,
            'totalWeighted' => $totalWeighted,
            'totalValue' => $totalValue,
            'totalDeals' => $totalDeals,
            'stages' => $stages,
            'lastMonthWon' => $lastMonthWon,
            'thisMonthWon' => $thisMonthWon,
        ]);
    }
}
