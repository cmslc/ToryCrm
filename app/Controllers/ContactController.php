<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ContactController extends Controller
{
    public function index()
    {
        $search = $this->input('search');
        $status = $this->input('status');
        $sourceId = $this->input('source_id');
        $ownerId = $this->input('owner_id');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ["c.is_deleted = 0", "c.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search) {
            $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($status) {
            $where[] = "c.status = ?";
            $params[] = $status;
        }

        if ($sourceId) {
            $where[] = "c.source_id = ?";
            $params[] = $sourceId;
        }

        if ($ownerId) {
            $where[] = "c.owner_id = ?";
            $params[] = $ownerId;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM contacts c WHERE {$whereClause}",
            $params
        )['count'];

        $contacts = Database::fetchAll(
            "SELECT c.*, comp.name as company_name, u.name as owner_name,
                    cs.name as source_name, cs.color as source_color
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             LEFT JOIN users u ON c.owner_id = u.id
             LEFT JOIN contact_sources cs ON c.source_id = cs.id
             WHERE {$whereClause}
             ORDER BY c.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $sources = Database::fetchAll("SELECT * FROM contact_sources ORDER BY sort_order, name");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");
        $statusCounts = Database::fetchAll("SELECT status, COUNT(*) as count FROM contacts WHERE is_deleted = 0 AND tenant_id = ? GROUP BY status", [Database::tenantId()]);

        $totalPages = ceil($total / $perPage);

        return $this->view('contacts.index', [
            'contacts' => [
                'items' => $contacts,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'sources' => $sources,
            'users' => $users,
            'statusCounts' => $statusCounts,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'source_id' => $sourceId,
                'owner_id' => $ownerId,
            ],
        ]);
    }

    public function create()
    {
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $sources = Database::fetchAll("SELECT * FROM contact_sources ORDER BY sort_order, name");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        return $this->view('contacts.create', [
            'companies' => $companies,
            'sources' => $sources,
            'users' => $users,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('contacts');
        }

        $data = $this->allInput();

        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = trim($data['email'] ?? '');

        if (empty($firstName)) {
            $this->setFlash('error', 'First name is required.');
            return $this->back();
        }

        $contactId = Database::insert('contacts', [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => trim($data['phone'] ?? ''),
            'mobile' => trim($data['mobile'] ?? ''),
            'position' => trim($data['position'] ?? ''),
            'company_id' => (!empty($data['company_id']) ? $data['company_id'] : null),
            'source_id' => (!empty($data['source_id']) ? $data['source_id'] : null),
            'address' => trim($data['address'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'new',
            'owner_id' => (!empty($data['owner_id']) ? $data['owner_id'] : $this->userId()),
            'created_by' => $this->userId(),
        ]);

        // Log activity
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Contact created: {$firstName} {$lastName}",
            'description' => "New contact {$firstName} {$lastName} was created.",
            'user_id' => $this->userId(),
            'contact_id' => $contactId,
        ]);

        $this->setFlash('success', 'Contact created successfully.');
        return $this->redirect('contacts/' . $contactId);
    }

    public function show($id)
    {
        $contact = Database::fetch(
            "SELECT c.*, comp.name as company_name, u.name as owner_name, cs.name as source_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             LEFT JOIN users u ON c.owner_id = u.id
             LEFT JOIN contact_sources cs ON c.source_id = cs.id
             WHERE c.id = ?",
            [$id]
        );

        if (!$contact) {
            $this->setFlash('error', 'Contact not found.');
            return $this->redirect('contacts');
        }

        $activities = Database::fetchAll(
            "SELECT a.*, u.name as user_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.contact_id = ?
             ORDER BY a.created_at DESC
             LIMIT 50",
            [$id]
        );

        $deals = Database::fetchAll(
            "SELECT d.*, ds.name as stage_name
             FROM deals d
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             WHERE d.contact_id = ?
             ORDER BY d.created_at DESC",
            [$id]
        );

        $tasks = Database::fetchAll(
            "SELECT * FROM tasks WHERE contact_id = ? ORDER BY due_date ASC",
            [$id]
        );

        return $this->view('contacts.show', [
            'contact' => $contact,
            'activities' => $activities,
            'deals' => $deals,
            'tasks' => $tasks,
        ]);
    }

    public function edit($id)
    {
        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$id]);

        if (!$contact) {
            $this->setFlash('error', 'Contact not found.');
            return $this->redirect('contacts');
        }

        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $sources = Database::fetchAll("SELECT * FROM contact_sources ORDER BY sort_order, name");
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        return $this->view('contacts.edit', [
            'contact' => $contact,
            'companies' => $companies,
            'sources' => $sources,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('contacts/' . $id);
        }

        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$id]);

        if (!$contact) {
            $this->setFlash('error', 'Contact not found.');
            return $this->redirect('contacts');
        }

        $data = $this->allInput();

        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');

        if (empty($firstName)) {
            $this->setFlash('error', 'First name is required.');
            return $this->back();
        }

        Database::update('contacts', [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => trim($data['email'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'mobile' => trim($data['mobile'] ?? ''),
            'position' => trim($data['position'] ?? ''),
            'company_id' => (!empty($data['company_id']) ? $data['company_id'] : null),
            'source_id' => (!empty($data['source_id']) ? $data['source_id'] : null),
            'address' => trim($data['address'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'new',
            'owner_id' => (!empty($data['owner_id']) ? $data['owner_id'] : null),
        ], 'id = ?', [$id]);

        // Log activity
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Contact updated: {$firstName} {$lastName}",
            'description' => "Contact {$firstName} {$lastName} was updated.",
            'user_id' => $this->userId(),
            'contact_id' => $id,
        ]);

        $this->setFlash('success', 'Contact updated successfully.');
        return $this->redirect('contacts/' . $id);
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('contacts');

        $contact = $this->findSecure('contacts', (int)$id);
        if (!$contact) {
            $this->setFlash('error', 'Khách hàng không tồn tại.');
            return $this->redirect('contacts');
        }

        // Soft delete
        Database::softDelete('contacts', 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Xóa khách hàng: {$contact['first_name']} {$contact['last_name']}",
            'user_id' => $this->userId(),
            'contact_id' => (int)$id,
        ]);

        $this->setFlash('success', 'Đã xóa khách hàng.');
        return $this->redirect('contacts');
    }

    // ---- Khôi phục khách hàng đã xóa ----
    public function trash()
    {
        $tid = Database::tenantId();
        $contacts = Database::fetchAll(
            "SELECT c.*, comp.name as company_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             WHERE c.is_deleted = 1 AND c.tenant_id = ?
             ORDER BY c.deleted_at DESC",
            [$tid]
        );

        return $this->view('contacts.trash', ['contacts' => $contacts]);
    }

    public function restore($id)
    {
        if (!$this->isPost()) return $this->redirect('contacts/trash');

        Database::restore('contacts', 'id = ?', [$id]);

        $this->setFlash('success', 'Đã khôi phục khách hàng.');
        return $this->redirect('contacts/trash');
    }

    // ---- Đổi người phụ trách ----
    public function changeOwner($id)
    {
        if (!$this->isPost()) return $this->redirect('contacts/' . $id);

        $contact = $this->findSecure('contacts', (int)$id);
        if (!$contact) {
            $this->setFlash('error', 'Khách hàng không tồn tại.');
            return $this->redirect('contacts');
        }

        $newOwnerId = $this->input('owner_id');
        if (empty($newOwnerId)) {
            $this->setFlash('error', 'Vui lòng chọn người phụ trách.');
            return $this->back();
        }

        $oldOwner = Database::fetch("SELECT name FROM users WHERE id = ?", [$contact['owner_id'] ?? 0]);
        $newOwner = Database::fetch("SELECT name FROM users WHERE id = ?", [$newOwnerId]);

        Database::update('contacts', ['owner_id' => $newOwnerId], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Đổi người phụ trách: {$contact['first_name']} {$contact['last_name']}",
            'description' => ($oldOwner['name'] ?? 'Chưa gán') . ' → ' . ($newOwner['name'] ?? ''),
            'user_id' => $this->userId(),
            'contact_id' => (int)$id,
        ]);

        $this->setFlash('success', 'Đã đổi người phụ trách.');
        return $this->redirect('contacts/' . $id);
    }

}
