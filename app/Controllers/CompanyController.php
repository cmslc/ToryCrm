<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class CompanyController extends Controller
{
    public function index()
    {
        $this->authorize('companies', 'view');
        $search = $this->input('search');
        $industry = $this->input('industry');
        $companySize = $this->input('company_size');
        $city = $this->input('city');
        $ownerId = $this->input('owner_id');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ["c.is_deleted = 0", "c.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search) {
            $where[] = "(c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.website LIKE ? OR c.tax_code LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($industry) { $where[] = "c.industry = ?"; $params[] = $industry; }
        if ($companySize) { $where[] = "c.company_size = ?"; $params[] = $companySize; }
        if ($city) { $where[] = "c.city = ?"; $params[] = $city; }
        if ($ownerId) { $where[] = "c.owner_id = ?"; $params[] = $ownerId; }

        // Owner-based data scoping: staff only sees own records
        $ownerScope = $this->ownerScope('c', 'owner_id');
        if ($ownerScope['where']) {
            $where[] = $ownerScope['where'];
            $params = array_merge($params, $ownerScope['params']);
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch("SELECT COUNT(*) as count FROM companies c WHERE {$whereClause}", $params)['count'];

        $companies = Database::fetchAll(
            "SELECT c.*, u.name as owner_name,
                    (SELECT COUNT(*) FROM contacts ct WHERE ct.company_id = c.id AND ct.is_deleted = 0) as contact_count,
                    (SELECT COUNT(*) FROM deals d WHERE d.company_id = c.id AND d.status = 'open') as deal_count,
                    (SELECT COALESCE(SUM(d2.value),0) FROM deals d2 WHERE d2.company_id = c.id AND d2.status = 'won') as total_revenue,
                    (SELECT COUNT(*) FROM orders o WHERE o.company_id = c.id AND o.is_deleted = 0) as order_count,
                    (SELECT MAX(a.created_at) FROM activities a WHERE a.company_id = c.id AND a.type != 'system') as last_activity_at
             FROM companies c
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE {$whereClause}
             ORDER BY c.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");
        $cities = Database::fetchAll("SELECT DISTINCT city FROM companies WHERE tenant_id = ? AND city IS NOT NULL AND city != '' ORDER BY city", [Database::tenantId()]);
        $totalPages = ceil($total / $perPage);

        return $this->view('companies.index', [
            'companies' => [
                'items' => $companies,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'users' => $users,
            'cities' => $cities,
            'filters' => [
                'search' => $search,
                'industry' => $industry,
                'company_size' => $companySize,
                'city' => $city,
                'owner_id' => $ownerId,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('companies', 'create');
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('companies.create', [
            'users' => $users,
        ]);
    }

    private function buildCompanyData(array $data): array
    {
        return [
            'name' => trim($data['name'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'website' => trim($data['website'] ?? ''),
            'industry' => trim($data['industry'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'tax_code' => trim($data['tax_code'] ?? ''),
            'company_size' => trim($data['company_size'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'owner_id' => (!empty($data['owner_id']) ? $data['owner_id'] : null),
        ];
    }

    private function handleLogoUpload(int $companyId, ?string $oldLogo = null): void
    {
        $logo = $_FILES['logo'] ?? null;
        if (!$logo || $logo['error'] !== UPLOAD_ERR_OK || $logo['size'] <= 0) return;

        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($logo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed) || $logo['size'] > 5 * 1024 * 1024) return;

        $uploadDir = BASE_PATH . '/public/uploads/logos';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if ($oldLogo) {
            $oldFile = $uploadDir . '/' . $oldLogo;
            if (file_exists($oldFile)) unlink($oldFile);
        }

        $fileName = 'company_' . $companyId . '_' . time() . '.' . $ext;
        if (move_uploaded_file($logo['tmp_name'], $uploadDir . '/' . $fileName)) {
            Database::update('companies', ['logo' => $fileName], 'id = ?', [$companyId]);
        }
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('companies');
        }
        $this->authorize('companies', 'create');

        $data = $this->allInput();
        $companyData = $this->buildCompanyData($data);

        if (empty($companyData['name'])) {
            $this->setFlash('error', 'Company name is required.');
            return $this->back();
        }

        $companyData['owner_id'] = $companyData['owner_id'] ?: $this->userId();
        $companyData['created_by'] = $this->userId();

        $companyId = Database::insert('companies', $companyData);

        $this->handleLogoUpload($companyId);

        // Log activity
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Company created: {$name}",
            'description' => "New company {$name} was created.",
            'user_id' => $this->userId(),
            'company_id' => $companyId,
        ]);

        $this->setFlash('success', 'Company created successfully.');
        return $this->redirect('companies/' . $companyId);
    }

    public function quickStore()
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            return $this->json(['error' => 'Tên doanh nghiệp không được để trống.'], 422);
        }

        $companyId = Database::insert('companies', [
            'name' => $name,
            'phone' => trim($data['phone'] ?? ''),
            'email' => trim($data['email'] ?? ''),
            'tax_code' => trim($data['tax_code'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'owner_id' => $this->userId(),
            'created_by' => $this->userId(),
        ]);

        return $this->json([
            'success' => true,
            'company' => ['id' => $companyId, 'name' => $name],
        ]);
    }

    public function show($id)
    {
        $this->authorize('companies', 'view');
        $company = Database::fetch(
            "SELECT c.*, u.name as owner_name
             FROM companies c
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE c.id = ?",
            [$id]
        );

        if (!$company) {
            $this->setFlash('error', 'Company not found.');
            return $this->redirect('companies');
        }

        // Ownership check: staff can only view own records
        if (!$this->canAccessOwner($company['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('companies');
        }

        $contacts = Database::fetchAll(
            "SELECT * FROM contacts WHERE company_id = ? AND is_deleted = 0 ORDER BY first_name",
            [$id]
        );

        $deals = Database::fetchAll(
            "SELECT d.*, ds.name as stage_name, ds.color as stage_color
             FROM deals d
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             WHERE d.company_id = ?
             ORDER BY d.created_at DESC",
            [$id]
        );

        $activities = Database::fetchAll(
            "SELECT a.*, u.name as user_name
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.company_id = ?
             ORDER BY a.created_at DESC
             LIMIT 50",
            [$id]
        );

        $tickets = Database::fetchAll(
            "SELECT t.*, u.name as assigned_name
             FROM tickets t
             LEFT JOIN users u ON t.assigned_to = u.id
             WHERE t.company_id = ?
             ORDER BY t.created_at DESC
             LIMIT 20",
            [$id]
        );

        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('companies.show', [
            'company' => $company,
            'contacts' => $contacts,
            'deals' => $deals,
            'activities' => $activities,
            'tickets' => $tickets,
            'users' => $users,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('companies', 'edit');
        $company = Database::fetch("SELECT * FROM companies WHERE id = ?", [$id]);

        if (!$company) {
            $this->setFlash('error', 'Company not found.');
            return $this->redirect('companies');
        }

        // Ownership check: staff can only edit own records
        if (!$this->canAccessOwner($company['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('companies');
        }

        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('companies.edit', [
            'company' => $company,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('companies/' . $id);
        }
        $this->authorize('companies', 'edit');

        $company = Database::fetch("SELECT * FROM companies WHERE id = ?", [$id]);

        if (!$company) {
            $this->setFlash('error', 'Company not found.');
            return $this->redirect('companies');
        }

        // Ownership check: staff can only update own records
        if (!$this->canAccessOwner($company['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('companies');
        }

        $data = $this->allInput();
        $companyData = $this->buildCompanyData($data);

        if (empty($companyData['name'])) {
            $this->setFlash('error', 'Company name is required.');
            return $this->back();
        }

        Database::update('companies', $companyData, 'id = ?', [$id]);

        $this->handleLogoUpload($id, $company['logo'] ?? null);

        // Log activity
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Company updated: {$companyData['name']}",
            'description' => "Company {$companyData['name']} was updated.",
            'user_id' => $this->userId(),
            'company_id' => $id,
        ]);

        $this->setFlash('success', 'Company updated successfully.');
        return $this->redirect('companies/' . $id);
    }

    public function delete($id)
    {
        $this->authorize('companies', 'delete');
        $company = Database::fetch("SELECT * FROM companies WHERE id = ?", [$id]);

        if (!$company) {
            $this->setFlash('error', 'Company not found.');
            return $this->redirect('companies');
        }

        // Ownership check: staff can only delete own records
        if (!$this->canAccessOwner($company['owner_id'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('companies');
        }

        // Soft delete
        Database::update('companies', [
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        // Log activity
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Company deleted: {$company['name']}",
            'description' => "Company {$company['name']} was deleted.",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã xóa doanh nghiệp.');
        return $this->redirect('companies');
    }

    public function trash()
    {
        $this->authorize('companies', 'delete');
        $tid = Database::tenantId();
        $companies = Database::fetchAll(
            "SELECT c.*, u.name as owner_name
             FROM companies c
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE c.is_deleted = 1 AND c.tenant_id = ?
             ORDER BY c.deleted_at DESC",
            [$tid]
        );

        return $this->view('companies.trash', ['companies' => $companies]);
    }

    public function restore($id)
    {
        if (!$this->isPost()) return $this->redirect('companies/trash');
        $this->authorize('companies', 'delete');

        Database::restore('companies', 'id = ?', [$id]);

        $this->setFlash('success', 'Đã khôi phục doanh nghiệp.');
        return $this->redirect('companies/trash');
    }

    public function updateLogo($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $company = Database::fetch("SELECT logo FROM companies WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$company) return $this->json(['error' => 'Không tồn tại'], 404);

        $filename = upload_avatar('avatar', 'logos', $company['logo'] ?? null);
        if ($filename) {
            Database::update('companies', ['logo' => $filename], 'id = ?', [$id]);
            return $this->json(['success' => true, 'url' => url('uploads/logos/' . $filename)]);
        }
        return $this->json(['error' => 'Không thể tải ảnh'], 422);
    }

    public function changeOwner($id)
    {
        if (!$this->isPost()) return $this->redirect('companies/' . $id);
        $this->authorize('companies', 'edit');

        $company = Database::fetch("SELECT * FROM companies WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$company) {
            $this->setFlash('error', 'Doanh nghiệp không tồn tại.');
            return $this->redirect('companies');
        }

        $newOwnerId = $this->input('owner_id');
        if (empty($newOwnerId)) {
            $this->setFlash('error', 'Vui lòng chọn người phụ trách.');
            return $this->back();
        }

        $oldOwner = Database::fetch("SELECT name FROM users WHERE id = ?", [$company['owner_id'] ?? 0]);
        $newOwner = Database::fetch("SELECT name FROM users WHERE id = ?", [$newOwnerId]);

        Database::update('companies', ['owner_id' => $newOwnerId], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Đổi người phụ trách: {$company['name']}",
            'description' => ($oldOwner['name'] ?? 'Chưa gán') . ' → ' . ($newOwner['name'] ?? ''),
            'user_id' => $this->userId(),
            'company_id' => $id,
        ]);

        $this->setFlash('success', 'Đã đổi người phụ trách.');
        return $this->redirect('companies/' . $id);
    }

    public function quickUpdate($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('companies', 'edit');

        $company = Database::fetch("SELECT * FROM companies WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$company) {
            return $this->json(['error' => 'Doanh nghiệp không tồn tại'], 404);
        }

        $field = $this->input('field');
        $value = $this->input('value');
        $allowed = ['industry', 'company_size', 'owner_id'];

        if (!in_array($field, $allowed)) {
            return $this->json(['error' => 'Trường không được phép cập nhật'], 422);
        }

        Database::update('companies', [$field => $value ?: null], 'id = ?', [$id]);

        $display = $value;
        if ($field === 'owner_id') {
            $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$value]);
            $display = $user ? htmlspecialchars($user['name']) : '-';
        }

        return $this->json(['success' => true, 'value' => $value, 'display' => $display]);
    }
}
