<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class CompanyController extends Controller
{
    public function index()
    {
        $search = $this->input('search');
        $industry = $this->input('industry');
        $ownerId = $this->input('owner_id');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ["c.is_deleted = 0", "c.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search) {
            $where[] = "(c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.website LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if ($industry) {
            $where[] = "c.industry = ?";
            $params[] = $industry;
        }

        if ($ownerId) {
            $where[] = "c.owner_id = ?";
            $params[] = $ownerId;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM companies c WHERE {$whereClause}",
            $params
        )['count'];

        $companies = Database::fetchAll(
            "SELECT c.*, u.name as owner_name,
                    (SELECT COUNT(*) FROM contacts WHERE company_id = c.id) as contact_count,
                    (SELECT COUNT(*) FROM deals WHERE company_id = c.id) as deal_count
             FROM companies c
             LEFT JOIN users u ON c.owner_id = u.id
             WHERE {$whereClause}
             ORDER BY c.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        return $this->view('companies.index', [
            'companies' => [
                'items' => $companies,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'filters' => [
                'search' => $search,
                'industry' => $industry,
                'owner_id' => $ownerId,
            ],
        ]);
    }

    public function create()
    {
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        return $this->view('companies.create', [
            'users' => $users,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('companies');
        }

        $data = $this->allInput();

        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Company name is required.');
            return $this->back();
        }

        $companyId = Database::insert('companies', [
            'name' => $name,
            'email' => trim($data['email'] ?? ''),
            'phone' => trim($data['phone'] ?? ''),
            'website' => trim($data['website'] ?? ''),
            'industry' => trim($data['industry'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'city' => trim($data['city'] ?? ''),
            'tax_code' => trim($data['tax_code'] ?? ''),
            'company_size' => trim($data['company_size'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'owner_id' => (!empty($data['owner_id']) ? $data['owner_id'] : $this->userId()),
            'created_by' => $this->userId(),
        ]);

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

    public function show($id)
    {
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

        $contacts = Database::fetchAll(
            "SELECT * FROM contacts WHERE company_id = ? ORDER BY first_name",
            [$id]
        );

        $deals = Database::fetchAll(
            "SELECT d.*, ds.name as stage_name
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
             LIMIT 20",
            [$id]
        );

        return $this->view('companies.show', [
            'company' => $company,
            'contacts' => $contacts,
            'deals' => $deals,
            'activities' => $activities,
        ]);
    }

    public function edit($id)
    {
        $company = Database::fetch("SELECT * FROM companies WHERE id = ?", [$id]);

        if (!$company) {
            $this->setFlash('error', 'Company not found.');
            return $this->redirect('companies');
        }

        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

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

        $company = Database::fetch("SELECT * FROM companies WHERE id = ?", [$id]);

        if (!$company) {
            $this->setFlash('error', 'Company not found.');
            return $this->redirect('companies');
        }

        $data = $this->allInput();
        $name = trim($data['name'] ?? '');

        if (empty($name)) {
            $this->setFlash('error', 'Company name is required.');
            return $this->back();
        }

        Database::update('companies', [
            'name' => $name,
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
        ], 'id = ?', [$id]);

        // Log activity
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Company updated: {$name}",
            'description' => "Company {$name} was updated.",
            'user_id' => $this->userId(),
            'company_id' => $id,
        ]);

        $this->setFlash('success', 'Company updated successfully.');
        return $this->redirect('companies/' . $id);
    }

    public function delete($id)
    {
        $company = Database::fetch("SELECT * FROM companies WHERE id = ?", [$id]);

        if (!$company) {
            $this->setFlash('error', 'Company not found.');
            return $this->redirect('companies');
        }

        Database::delete('companies', 'id = ?', [$id]);

        // Log activity
        Database::insert('activities', [
            'type' => 'system',
            'title' => "Company deleted: {$company['name']}",
            'description' => "Company {$company['name']} was deleted.",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Company deleted successfully.');
        return $this->redirect('companies');
    }
}
