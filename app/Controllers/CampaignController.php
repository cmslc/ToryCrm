<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Campaign;

class CampaignController extends Controller
{
    public function index()
    {
        $this->authorize('campaigns', 'view');
        $campaignModel = new Campaign();
        $page = max(1, (int) $this->input('page') ?: 1);

        // Owner-based data scoping
        $ownerFilter = null;
        if (!$this->isSystemAdmin()) {
            $ownerFilter = $this->getVisibleUserIds() ?: [$this->userId()];
        }

        $campaigns = $campaignModel->getWithRelations($page, 10, [
            'search' => $this->input('search'),
            'type' => $this->input('type'),
            'status' => $this->input('status'),
            'owner_id' => $ownerFilter,
        ]);

        return $this->view('campaigns.index', [
            'campaigns' => $campaigns,
            'filters' => [
                'search' => $this->input('search'),
                'type' => $this->input('type'),
                'status' => $this->input('status'),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('campaigns', 'create');
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");
        return $this->view('campaigns.create', ['users' => $users]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('campaigns');
        $this->authorize('campaigns', 'create');

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên chiến dịch không được để trống.');
            return $this->back();
        }

        $campaignModel = new Campaign();
        $code = $campaignModel->generateCode();

        $id = Database::insert('campaigns', [
            'tenant_id' => Database::tenantId(),
            'campaign_code' => $code,
            'name' => $name,
            'type' => $data['type'] ?? 'email',
            'status' => $data['status'] ?? 'draft',
            'description' => trim($data['description'] ?? ''),
            'start_date' => !empty($data['start_date']) ? $data['start_date'] : null,
            'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
            'budget' => (float)($data['budget'] ?? 0),
            'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : $this->userId(),
            'created_by' => $this->userId(),
        ]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Chiến dịch mới: {$name}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', "Chiến dịch {$code} đã được tạo.");
        return $this->redirect('campaigns/' . $id);
    }

    public function show($id)
    {
        $this->authorize('campaigns', 'view');
        $campaign = Database::fetch(
            "SELECT c.*, u.name as owner_name, uc.name as created_by_name
             FROM campaigns c
             LEFT JOIN users u ON c.owner_id = u.id
             LEFT JOIN users uc ON c.created_by = uc.id
             WHERE c.id = ? AND c.tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$campaign) {
            $this->setFlash('error', 'Chiến dịch không tồn tại.');
            return $this->redirect('campaigns');
        }

        // Ownership check: staff can only view own campaigns
        if (!$this->canAccessOwner($campaign['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('campaigns');
        }

        $campaignModel = new Campaign();
        $contacts = $campaignModel->getContacts($id);
        $contactStats = $campaignModel->getContactStats($id);

        return $this->view('campaigns.show', [
            'campaign' => $campaign,
            'contacts' => $contacts,
            'contactStats' => $contactStats,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('campaigns', 'edit');
        $campaign = Database::fetch("SELECT * FROM campaigns WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$campaign) {
            $this->setFlash('error', 'Chiến dịch không tồn tại.');
            return $this->redirect('campaigns');
        }

        // Ownership check: staff can only edit own campaigns
        if (!$this->canAccessOwner($campaign['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('campaigns');
        }

        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('campaigns.edit', [
            'campaign' => $campaign,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('campaigns/' . $id);
        $this->authorize('campaigns', 'edit');

        $campaign = Database::fetch("SELECT * FROM campaigns WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$campaign) {
            $this->setFlash('error', 'Chiến dịch không tồn tại.');
            return $this->redirect('campaigns');
        }

        // Ownership check: staff can only update own campaigns
        if (!$this->canAccessOwner($campaign['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('campaigns');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên chiến dịch không được để trống.');
            return $this->back();
        }

        Database::update('campaigns', [
            'name' => $name,
            'type' => $data['type'] ?? $campaign['type'],
            'status' => $data['status'] ?? $campaign['status'],
            'description' => trim($data['description'] ?? ''),
            'start_date' => !empty($data['start_date']) ? $data['start_date'] : null,
            'end_date' => !empty($data['end_date']) ? $data['end_date'] : null,
            'budget' => (float)($data['budget'] ?? 0),
            'actual_cost' => (float)($data['actual_cost'] ?? 0),
            'owner_id' => !empty($data['owner_id']) ? $data['owner_id'] : null,
            'is_locked' => isset($data['is_locked']) ? 1 : 0,
        ], 'id = ?', [$id]);

        $this->setFlash('success', 'Chiến dịch đã được cập nhật.');
        return $this->redirect('campaigns/' . $id);
    }

    public function addContact($id)
    {
        if (!$this->isPost()) return $this->redirect('campaigns/' . $id);
        $this->authorize('campaigns', 'edit');
        $tid = Database::tenantId();

        // Verify campaign belongs to tenant + user can access
        $campaign = Database::fetch("SELECT owner_id FROM campaigns WHERE id = ? AND tenant_id = ?", [$id, $tid]);
        if (!$campaign) { $this->setFlash('error', 'Chiến dịch không tồn tại.'); return $this->redirect('campaigns'); }
        if (!$this->canAccessOwner((int)($campaign['owner_id'] ?? 0), 'campaigns')) {
            $this->setFlash('error', 'Không có quyền.'); return $this->redirect('campaigns');
        }

        $contactId = $this->input('contact_id');
        if (empty($contactId)) {
            $this->setFlash('error', 'Vui lòng chọn khách hàng.');
            return $this->back();
        }

        // Verify contact belongs to tenant
        $contactOk = Database::fetch("SELECT id FROM contacts WHERE id = ? AND tenant_id = ?", [$contactId, $tid]);
        if (!$contactOk) { $this->setFlash('error', 'Khách hàng không hợp lệ.'); return $this->back(); }

        $exists = Database::fetch(
            "SELECT id FROM campaign_contacts WHERE campaign_id = ? AND contact_id = ?",
            [$id, $contactId]
        );

        if ($exists) {
            $this->setFlash('warning', 'Khách hàng đã có trong chiến dịch.');
            return $this->redirect('campaigns/' . $id);
        }

        Database::insert('campaign_contacts', [
            'campaign_id' => $id,
            'contact_id' => $contactId,
            'status' => 'pending',
        ]);

        Database::query("UPDATE campaigns SET target_count = target_count + 1 WHERE id = ?", [$id]);

        $this->setFlash('success', 'Đã thêm khách hàng vào chiến dịch.');
        return $this->redirect('campaigns/' . $id);
    }

    public function delete($id)
    {
        $this->authorize('campaigns', 'delete');
        $campaign = Database::fetch("SELECT * FROM campaigns WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$campaign) {
            $this->setFlash('error', 'Chiến dịch không tồn tại.');
            return $this->redirect('campaigns');
        }

        // Ownership check: staff can only delete own campaigns
        if (!$this->canAccessOwner($campaign['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('campaigns');
        }

        Database::delete('campaign_contacts', 'campaign_id = ?', [$id]);
        Database::delete('campaigns', 'id = ?', [$id]);

        $this->setFlash('success', 'Chiến dịch đã được xóa.');
        return $this->redirect('campaigns');
    }
}
