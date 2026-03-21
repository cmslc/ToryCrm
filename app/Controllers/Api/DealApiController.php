<?php

namespace App\Controllers\Api;

use Core\Controller;
use Core\Database;

class DealApiController extends Controller
{
    public function list()
    {
        $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $sort = $_GET['sort'] ?? 'created_at';
        $order = strtoupper($_GET['order'] ?? 'DESC');

        $allowedSorts = ['id', 'title', 'value', 'status', 'created_at', 'updated_at', 'expected_close_date'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $where = ['1=1'];
        $params = [];

        if (!empty($_GET['status'])) {
            $where[] = "d.status = ?";
            $params[] = $_GET['status'];
        }

        if (!empty($_GET['stage_id'])) {
            $where[] = "d.stage_id = ?";
            $params[] = (int) $_GET['stage_id'];
        }

        if (!empty($_GET['owner_id'])) {
            $where[] = "d.owner_id = ?";
            $params[] = (int) $_GET['owner_id'];
        }

        if (!empty($_GET['search'])) {
            $where[] = "(d.title LIKE ? OR c.first_name LIKE ? OR c.last_name LIKE ?)";
            $s = "%" . $_GET['search'] . "%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             WHERE {$whereClause}",
            $params
        )['count'];

        $deals = Database::fetchAll(
            "SELECT d.*, c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name, u.name as owner_name,
                    ds.name as stage_name, ds.color as stage_color
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             LEFT JOIN users u ON d.owner_id = u.id
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             WHERE {$whereClause}
             ORDER BY d.{$sort} {$order}
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->json([
            'data' => $deals,
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

        $deal = Database::fetch(
            "SELECT d.*, c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name, u.name as owner_name,
                    ds.name as stage_name, ds.color as stage_color
             FROM deals d
             LEFT JOIN contacts c ON d.contact_id = c.id
             LEFT JOIN companies comp ON d.company_id = comp.id
             LEFT JOIN users u ON d.owner_id = u.id
             LEFT JOIN deal_stages ds ON d.stage_id = ds.id
             WHERE d.id = ?",
            [$id]
        );

        if (!$deal) {
            return $this->json(['error' => 'Deal not found'], 404);
        }

        return $this->json(['data' => $deal]);
    }

    public function create()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $title = trim($input['title'] ?? '');

        if (empty($title)) {
            return $this->json(['error' => 'title is required'], 422);
        }

        $dealId = Database::insert('deals', [
            'tenant_id' => $_SESSION['tenant_id'] ?? 1,
            'title' => $title,
            'value' => (float) ($input['value'] ?? 0),
            'stage_id' => !empty($input['stage_id']) ? (int) $input['stage_id'] : null,
            'status' => $input['status'] ?? 'open',
            'contact_id' => !empty($input['contact_id']) ? (int) $input['contact_id'] : null,
            'company_id' => !empty($input['company_id']) ? (int) $input['company_id'] : null,
            'owner_id' => !empty($input['owner_id']) ? (int) $input['owner_id'] : null,
            'expected_close_date' => !empty($input['expected_close_date']) ? $input['expected_close_date'] : null,
            'priority' => $input['priority'] ?? 'medium',
            'description' => trim($input['description'] ?? ''),
            'created_by' => $_SESSION['api_user']['user_id'] ?? null,
        ]);

        return $this->json([
            'message' => 'Thêm mới thành công',
            'id' => $dealId,
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

        $deal = Database::fetch("SELECT * FROM deals WHERE id = ?", [$id]);

        if (!$deal) {
            return $this->json(['error' => 'Deal not found'], 404);
        }

        $updateData = [];
        $allowedFields = [
            'title', 'value', 'stage_id', 'status', 'contact_id',
            'company_id', 'owner_id', 'expected_close_date', 'priority', 'description',
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

        Database::update('deals', $updateData, 'id = ?', [$id]);

        return $this->json([
            'message' => 'Cập nhật thành công',
            'id' => $id,
        ]);
    }

    public function updateStage()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);
        $stageId = (int) ($input['stage_id'] ?? 0);

        if (!$id) {
            return $this->json(['error' => 'Missing id parameter'], 400);
        }

        if (!$stageId) {
            return $this->json(['error' => 'stage_id is required'], 422);
        }

        $deal = Database::fetch("SELECT * FROM deals WHERE id = ?", [$id]);

        if (!$deal) {
            return $this->json(['error' => 'Deal not found'], 404);
        }

        $stage = Database::fetch("SELECT * FROM deal_stages WHERE id = ?", [$stageId]);

        if (!$stage) {
            return $this->json(['error' => 'Invalid stage_id'], 422);
        }

        Database::update('deals', [
            'stage_id' => $stageId,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        // Log activity
        Database::insert('activities', [
            'type' => 'deal',
            'title' => "Deal stage changed: {$deal['title']}",
            'description' => "Deal {$deal['title']} moved to stage {$stage['name']}.",
            'user_id' => $_SESSION['api_user']['user_id'] ?? null,
            'deal_id' => $id,
        ]);

        return $this->json([
            'message' => 'Cập nhật giai đoạn thành công',
            'id' => $id,
            'stage_id' => $stageId,
            'stage_name' => $stage['name'],
        ]);
    }
}
