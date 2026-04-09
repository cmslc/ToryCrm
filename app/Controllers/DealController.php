<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class DealController extends Controller
{
    public function index()
    {
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
        ]);
    }

    public function pipeline()
    {
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
        $contacts = Database::fetchAll(
            "SELECT id, first_name, last_name FROM contacts ORDER BY first_name"
        );
        $companies = Database::fetchAll(
            "SELECT id, name FROM companies ORDER BY name"
        );
        $stages = Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

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
        $deal = Database::fetch(
            "SELECT d.*, c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name, u.name as owner_name, ds.name as stage_name, ds.color as stage_color
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

        $activities = Database::fetchAll(
            "SELECT a.*, u.name as user_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.deal_id = ?
             ORDER BY a.created_at DESC
             LIMIT 20",
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

        return $this->view('deals.show', [
            'deal' => $deal,
            'activities' => $activities,
            'tasks' => $tasks,
        ]);
    }

    public function edit($id)
    {
        $deal = Database::fetch("SELECT * FROM deals WHERE id = ?", [$id]);

        if (!$deal) {
            $this->setFlash('error', 'Deal not found.');
            return $this->redirect('deals');
        }

        $contacts = Database::fetchAll(
            "SELECT id, first_name, last_name FROM contacts ORDER BY first_name"
        );
        $companies = Database::fetchAll(
            "SELECT id, name FROM companies ORDER BY name"
        );
        $stages = Database::fetchAll("SELECT * FROM deal_stages ORDER BY sort_order");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

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

        $deal = Database::fetch("SELECT * FROM deals WHERE id = ?", [$id]);

        if (!$deal) {
            $this->setFlash('error', 'Deal not found.');
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

        Database::update('deals', [
            'title' => $title,
            'value' => (float) ($data['value'] ?? 0),
            'stage_id' => $newStageId ?: null,
            'status' => $data['status'] ?? 'open',
            'contact_id' => (!empty($data['contact_id']) ? $data['contact_id'] : null),
            'company_id' => (!empty($data['company_id']) ? $data['company_id'] : null),
            'owner_id' => (!empty($data['owner_id']) ? $data['owner_id'] : null),
            'expected_close_date' => (!empty($data['expected_close_date']) ? $data['expected_close_date'] : null),
            'priority' => $data['priority'] ?? 'medium',
            'description' => trim($data['description'] ?? ''),
        ], 'id = ?', [$id]);

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
        $deal = Database::fetch("SELECT * FROM deals WHERE id = ?", [$id]);

        if (!$deal) {
            $this->setFlash('error', 'Deal not found.');
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
}
