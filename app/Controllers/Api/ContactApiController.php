<?php

namespace App\Controllers\Api;

use Core\Controller;
use Core\Database;

class ContactApiController extends Controller
{
    public function list()
    {
        $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $sort = $_GET['sort'] ?? 'created_at';
        $order = strtoupper($_GET['order'] ?? 'DESC');

        // Whitelist sort fields
        $allowedSorts = ['id', 'first_name', 'last_name', 'email', 'phone', 'status', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $where = ['1=1'];
        $params = [];

        if (!empty($_GET['account_name'])) {
            $where[] = "(c.first_name LIKE ? OR c.last_name LIKE ?)";
            $s = "%" . $_GET['account_name'] . "%";
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($_GET['email'])) {
            $where[] = "c.email LIKE ?";
            $params[] = "%" . $_GET['email'] . "%";
        }

        if (!empty($_GET['phone'])) {
            $where[] = "(c.phone LIKE ? OR c.mobile LIKE ?)";
            $s = "%" . $_GET['phone'] . "%";
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($_GET['status'])) {
            $where[] = "c.status = ?";
            $params[] = $_GET['status'];
        }

        if (!empty($_GET['account_manager'])) {
            $where[] = "c.owner_id = ?";
            $params[] = (int) $_GET['account_manager'];
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM contacts c WHERE {$whereClause}",
            $params
        )['count'];

        $contacts = Database::fetchAll(
            "SELECT c.*, comp.name as company_name, u.name as owner_name, cs.name as source_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             LEFT JOIN users u ON c.owner_id = u.id
             LEFT JOIN contact_sources cs ON c.source_id = cs.id
             WHERE {$whereClause}
             ORDER BY c.{$sort} {$order}
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->json([
            'data' => $contacts,
            'total' => (int) $total,
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function detail()
    {
        $id = (int) ($_GET['id'] ?? 0);

        if (!$id) {
            return $this->json(['error' => 'Missing id parameter'], 400);
        }

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
            return $this->json(['error' => 'Contact not found'], 404);
        }

        // Get tags
        $contact['tags'] = Database::fetchAll(
            "SELECT t.id, t.name FROM tags t
             INNER JOIN contact_tags ct ON t.id = ct.tag_id
             WHERE ct.contact_id = ?",
            [$id]
        );

        // Get custom fields
        $contact['custom_fields'] = Database::fetchAll(
            "SELECT cf.field_name, cfv.field_value
             FROM custom_field_values cfv
             INNER JOIN custom_fields cf ON cfv.custom_field_id = cf.id
             WHERE cfv.entity_type = 'contact' AND cfv.entity_id = ?",
            [$id]
        );

        return $this->json(['data' => $contact]);
    }

    public function create()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $firstName = trim($input['first_name'] ?? '');

        if (empty($firstName)) {
            return $this->json(['error' => 'first_name is required'], 422);
        }

        // Generate account_code
        $accountCode = 'C' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        $contactId = Database::insert('contacts', [
            'tenant_id' => $_SESSION['tenant_id'] ?? 1,
            'account_code' => $accountCode,
            'first_name' => $firstName,
            'last_name' => trim($input['last_name'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'phone' => trim($input['phone'] ?? ''),
            'mobile' => trim($input['mobile'] ?? ''),
            'position' => trim($input['position'] ?? ''),
            'company_id' => !empty($input['company_id']) ? (int) $input['company_id'] : null,
            'source_id' => !empty($input['source_id']) ? (int) $input['source_id'] : null,
            'address' => trim($input['address'] ?? ''),
            'city' => trim($input['city'] ?? ''),
            'description' => trim($input['description'] ?? ''),
            'status' => $input['status'] ?? 'new',
            'owner_id' => !empty($input['owner_id']) ? (int) $input['owner_id'] : null,
            'created_by' => $_SESSION['api_user']['user_id'] ?? null,
        ]);

        return $this->json([
            'message' => 'Thêm mới thành công',
            'id' => $contactId,
            'account_code' => $accountCode,
        ], 201);
    }

    public function update()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);

        if (!$id) {
            return $this->json(['error' => 'Missing id parameter'], 400);
        }

        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$id]);

        if (!$contact) {
            return $this->json(['error' => 'Contact not found'], 404);
        }

        $updateData = [];
        $allowedFields = [
            'first_name', 'last_name', 'email', 'phone', 'mobile',
            'position', 'company_id', 'source_id', 'address', 'city',
            'description', 'status', 'owner_id',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $updateData[$field] = is_string($input[$field]) ? trim($input[$field]) : $input[$field];
            }
        }

        if (empty($updateData)) {
            return $this->json(['error' => 'No fields to update'], 422);
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        Database::update('contacts', $updateData, 'id = ?', [$id]);

        return $this->json([
            'message' => 'Cập nhật thành công',
            'id' => $id,
        ]);
    }

    public function delete()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);

        if (!$id) {
            return $this->json(['error' => 'Missing id parameter'], 400);
        }

        $contact = Database::fetch("SELECT * FROM contacts WHERE id = ?", [$id]);

        if (!$contact) {
            return $this->json(['error' => 'Contact not found'], 404);
        }

        // Soft delete
        Database::update('contacts', [
            'is_deleted' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        return $this->json(['message' => 'Xóa thành công', 'id' => $id]);
    }

    public function search()
    {
        $q = trim($_GET['q'] ?? '');

        if (empty($q)) {
            return $this->json(['data' => [], 'total' => 0]);
        }

        $searchTerm = "%{$q}%";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];

        $contacts = Database::fetchAll(
            "SELECT c.id, c.first_name, c.last_name, c.email, c.phone, c.status,
                    comp.name as company_name
             FROM contacts c
             LEFT JOIN companies comp ON c.company_id = comp.id
             WHERE (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)
             ORDER BY c.first_name ASC
             LIMIT 50",
            $params
        );

        return $this->json([
            'data' => $contacts,
            'total' => count($contacts),
        ]);
    }
}
