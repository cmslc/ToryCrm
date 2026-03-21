<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Campaign;

class CampaignController extends Controller
{
    public function index()
    {
        $campaignModel = new Campaign();
        $page = max(1, (int) $this->input('page') ?: 1);

        $campaigns = $campaignModel->getWithRelations($page, 10, [
            'search' => $this->input('search'),
            'type' => $this->input('type'),
            'status' => $this->input('status'),
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
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");
        return $this->view('campaigns.create', ['users' => $users]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('campaigns');

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên chiến dịch không được để trống.');
            return $this->back();
        }

        $campaignModel = new Campaign();
        $code = $campaignModel->generateCode();

        $id = Database::insert('campaigns', [
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
        $campaign = Database::fetch(
            "SELECT c.*, u.name as owner_name, uc.name as created_by_name
             FROM campaigns c
             LEFT JOIN users u ON c.owner_id = u.id
             LEFT JOIN users uc ON c.created_by = uc.id
             WHERE c.id = ?",
            [$id]
        );

        if (!$campaign) {
            $this->setFlash('error', 'Chiến dịch không tồn tại.');
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
        $campaign = Database::fetch("SELECT * FROM campaigns WHERE id = ?", [$id]);
        if (!$campaign) {
            $this->setFlash('error', 'Chiến dịch không tồn tại.');
            return $this->redirect('campaigns');
        }

        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        return $this->view('campaigns.edit', [
            'campaign' => $campaign,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('campaigns/' . $id);

        $campaign = Database::fetch("SELECT * FROM campaigns WHERE id = ?", [$id]);
        if (!$campaign) {
            $this->setFlash('error', 'Chiến dịch không tồn tại.');
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

        $contactId = $this->input('contact_id');
        if (empty($contactId)) {
            $this->setFlash('error', 'Vui lòng chọn khách hàng.');
            return $this->back();
        }

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
        $campaign = Database::fetch("SELECT * FROM campaigns WHERE id = ?", [$id]);
        if (!$campaign) {
            $this->setFlash('error', 'Chiến dịch không tồn tại.');
            return $this->redirect('campaigns');
        }

        Database::delete('campaign_contacts', 'campaign_id = ?', [$id]);
        Database::delete('campaigns', 'id = ?', [$id]);

        $this->setFlash('success', 'Chiến dịch đã được xóa.');
        return $this->redirect('campaigns');
    }
}
