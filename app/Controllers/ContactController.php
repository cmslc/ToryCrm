<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ContactController extends Controller
{
    public function index()
    {
        $this->authorize('contacts', 'view');
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

        $customerGroup = $this->input('customer_group');
        if ($customerGroup) {
            $where[] = "c.customer_group = ?";
            $params[] = $customerGroup;
        }

        // Owner-based data scoping: staff only sees own records
        $ownerScope = $this->ownerScope('c', 'owner_id');
        if ($ownerScope['where']) {
            $where[] = $ownerScope['where'];
            $params = array_merge($params, $ownerScope['params']);
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM contacts c WHERE {$whereClause}",
            $params
        )['count'];

        $contacts = Database::fetchAll(
            "SELECT c.*, comp.name as company_name, u.name as owner_name,
                    cs.name as source_name, cs.color as source_color,
                    (SELECT MAX(a.created_at) FROM activities a WHERE a.contact_id = c.id) as last_activity_at
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
        $users = Database::fetchAll("SELECT u.id, u.name, u.role, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");
        $statusCountsWhere = "is_deleted = 0 AND tenant_id = ?";
        $statusCountsParams = [Database::tenantId()];
        if (!$this->isAdminOrManager()) {
            $statusCountsWhere .= " AND owner_id = ?";
            $statusCountsParams[] = $this->userId();
        }
        $statusCounts = Database::fetchAll("SELECT status, COUNT(*) as count FROM contacts WHERE {$statusCountsWhere} GROUP BY status", $statusCountsParams);

        $totalPages = ceil($total / $perPage);

        $contactStatuses = Database::fetchAll(
            "SELECT * FROM contact_statuses WHERE tenant_id = ? ORDER BY sort_order",
            [Database::tenantId()]
        );

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
            'contactStatuses' => $contactStatuses,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'source_id' => $sourceId,
                'owner_id' => $ownerId,
                'customer_group' => $customerGroup,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('contacts', 'create');
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $sources = Database::fetchAll("SELECT * FROM contact_sources ORDER BY sort_order, name");
        $users = Database::fetchAll("SELECT u.id, u.name, u.role, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

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
        $this->authorize('contacts', 'create');

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
            'customer_group' => $data['customer_group'] ?? null ?: null,
            'owner_id' => (!empty($data['owner_id']) ? $data['owner_id'] : $this->userId()),
            'created_by' => $this->userId(),
        ]);

        // Avatar upload
        $avatar = $_FILES['avatar'] ?? null;
        if ($avatar && $avatar['error'] === UPLOAD_ERR_OK && $avatar['size'] > 0) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $avatar['size'] <= 5 * 1024 * 1024) {
                $uploadDir = BASE_PATH . '/public/uploads/avatars';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $fileName = 'contact_' . $contactId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($avatar['tmp_name'], $uploadDir . '/' . $fileName)) {
                    Database::update('contacts', ['avatar' => $fileName], 'id = ?', [$contactId]);
                }
            }
        }

        // Log activity
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Contact created: {$firstName} {$lastName}",
            'description' => "New contact {$firstName} {$lastName} was created.",
            'user_id' => $this->userId(),
            'contact_id' => $contactId,
        ]);

        // Auto-follow: thêm admin + managers làm người theo dõi mặc định
        $defaultFollowers = Database::fetchAll(
            "SELECT id FROM users WHERE tenant_id = ? AND is_active = 1 AND role IN ('admin', 'manager')",
            [Database::tenantId()]
        );
        foreach ($defaultFollowers as $df) {
            Database::query(
                "INSERT IGNORE INTO contact_followers (contact_id, user_id) VALUES (?, ?)",
                [$contactId, $df['id']]
            );
        }

        $this->setFlash('success', 'Đã tạo khách hàng thành công.');
        return $this->redirect('contacts/' . $contactId);
    }

    public function show($id)
    {
        $this->authorize('contacts', 'view');
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

        // Ownership check: staff can only view own records
        if (!$this->canAccessOwner($contact['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
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

        // Split view partial (no layout)
        if ($this->input('partial')) {
            return $this->view('contacts.partial-show', [
                'contact' => $contact,
                'activities' => $activities,
                'noLayout' => true,
            ]);
        }

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

        $contactStatuses = Database::fetchAll(
            "SELECT * FROM contact_statuses WHERE tenant_id = ? ORDER BY sort_order",
            [Database::tenantId()]
        );

        $followers = Database::fetchAll(
            "SELECT cf.user_id, u.name FROM contact_followers cf JOIN users u ON cf.user_id = u.id WHERE cf.contact_id = ? ORDER BY cf.created_at",
            [$id]
        );

        return $this->view('contacts.show', [
            'contact' => $contact,
            'activities' => $activities,
            'deals' => $deals,
            'tasks' => $tasks,
            'contactStatuses' => $contactStatuses,
            'followers' => $followers,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('contacts', 'edit');
        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$id]);

        if (!$contact) {
            $this->setFlash('error', 'Contact not found.');
            return $this->redirect('contacts');
        }

        // Ownership check: staff can only edit own records
        if (!$this->canAccessOwner($contact['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('contacts');
        }

        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $sources = Database::fetchAll("SELECT * FROM contact_sources ORDER BY sort_order, name");
        $users = Database::fetchAll("SELECT u.id, u.name, u.role, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

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
        $this->authorize('contacts', 'edit');

        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$id]);

        if (!$contact) {
            $this->setFlash('error', 'Contact not found.');
            return $this->redirect('contacts');
        }

        // Ownership check: staff can only update own records
        if (!$this->canAccessOwner($contact['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
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

    public function followers($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $this->authorize('contacts', 'edit');

        $userId = (int) $this->input('user_id');
        $action = $this->input('action');

        if (!$userId) return $this->json(['error' => 'User ID required'], 400);

        if ($action === 'add') {
            Database::query(
                "INSERT IGNORE INTO contact_followers (contact_id, user_id) VALUES (?, ?)",
                [(int)$id, $userId]
            );
            return $this->json(['success' => true]);
        } elseif ($action === 'remove') {
            Database::query(
                "DELETE FROM contact_followers WHERE contact_id = ? AND user_id = ?",
                [(int)$id, $userId]
            );
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Invalid action'], 400);
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('contacts');
        $this->authorize('contacts', 'delete');

        $contact = $this->findSecure('contacts', (int)$id);
        if (!$contact) {
            $this->setFlash('error', 'Khách hàng không tồn tại.');
            return $this->redirect('contacts');
        }

        // Ownership check: staff can only delete own records
        if (!$this->canAccessOwner($contact['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
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
        $this->authorize('contacts', 'delete');
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
        $this->authorize('contacts', 'delete');

        Database::restore('contacts', 'id = ?', [$id]);

        $this->setFlash('success', 'Đã khôi phục khách hàng.');
        return $this->redirect('contacts/trash');
    }

    // ---- Quick Update (inline edit) ----
    public function quickUpdate($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('contacts', 'edit');

        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$contact) {
            return $this->json(['error' => 'Khách hàng không tồn tại'], 404);
        }

        $field = $this->input('field');
        $value = $this->input('value');
        $allowed = ['status', 'owner_id', 'source_id'];

        if (!in_array($field, $allowed)) {
            return $this->json(['error' => 'Trường không được phép cập nhật'], 422);
        }

        Database::update('contacts', [$field => $value ?: null], 'id = ?', [$id]);

        $display = $value;
        if ($field === 'status') {
            $statusLabels = ['new' => 'Mới', 'contacted' => 'Đã liên hệ', 'qualified' => 'Tiềm năng', 'converted' => 'Chuyển đổi', 'lost' => 'Mất'];
            $statusColors = ['new' => 'info', 'contacted' => 'primary', 'qualified' => 'warning', 'converted' => 'success', 'lost' => 'danger'];
            $label = $statusLabels[$value] ?? $value;
            $color = $statusColors[$value] ?? 'secondary';
            $display = '<span class="badge bg-' . $color . '-subtle text-' . $color . '">' . $label . '</span>';
        } elseif ($field === 'owner_id') {
            $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$value]);
            $display = $user ? htmlspecialchars($user['name']) : '-';
        } elseif ($field === 'source_id') {
            $source = Database::fetch("SELECT name FROM contact_sources WHERE id = ?", [$value]);
            $display = $source ? '<span class="badge bg-secondary-subtle text-secondary">' . htmlspecialchars($source['name']) . '</span>' : '-';
        }

        return $this->json(['success' => true, 'value' => $value, 'display' => $display]);
    }

    // ---- Bulk Actions ----
    public function bulk()
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('contacts', 'edit');

        $ids = $_POST['ids'] ?? [];
        $action = $this->input('action');
        $value = $this->input('value');

        if (empty($ids) || !is_array($ids)) {
            return $this->json(['error' => 'Chưa chọn mục nào'], 422);
        }

        // Sanitize IDs
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $tenantId = Database::tenantId();

        $count = 0;

        switch ($action) {
            case 'assign':
                if (empty($value)) {
                    return $this->json(['error' => 'Vui lòng chọn người phụ trách'], 422);
                }
                foreach ($ids as $id) {
                    Database::update('contacts', ['owner_id' => (int)$value], 'id = ? AND tenant_id = ?', [$id, $tenantId]);
                    $count++;
                }
                break;

            case 'status':
                $validStatuses = ['new', 'contacted', 'qualified', 'converted', 'lost'];
                if (!in_array($value, $validStatuses)) {
                    return $this->json(['error' => 'Trạng thái không hợp lệ'], 422);
                }
                foreach ($ids as $id) {
                    Database::update('contacts', ['status' => $value], 'id = ? AND tenant_id = ?', [$id, $tenantId]);
                    $count++;
                }
                break;

            case 'delete':
                foreach ($ids as $id) {
                    Database::softDelete('contacts', 'id = ? AND tenant_id = ?', [$id, $tenantId]);
                    $count++;
                }
                break;

            default:
                return $this->json(['error' => 'Hành động không hợp lệ'], 422);
        }

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Thao tác hàng loạt ({$action}) trên {$count} khách hàng",
            'user_id' => $this->userId(),
        ]);

        return $this->json(['success' => true, 'count' => $count]);
    }

    // ---- Đổi người phụ trách ----
    public function updateAvatar($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $contact = Database::fetch("SELECT avatar FROM contacts WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$contact) return $this->json(['error' => 'Không tồn tại'], 404);

        $filename = upload_avatar('avatar', 'avatars', $contact['avatar'] ?? null);
        if ($filename) {
            Database::update('contacts', ['avatar' => $filename], 'id = ?', [$id]);
            return $this->json(['success' => true, 'url' => url('uploads/avatars/' . $filename)]);
        }
        return $this->json(['error' => 'Không thể tải ảnh'], 422);
    }

    public function changeOwner($id)
    {
        if (!$this->isPost()) return $this->redirect('contacts/' . $id);
        $this->authorize('contacts', 'edit');

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
