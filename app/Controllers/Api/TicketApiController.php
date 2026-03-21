<?php

namespace App\Controllers\Api;

use Core\Controller;
use Core\Database;

class TicketApiController extends Controller
{
    public function categories()
    {
        $categories = Database::fetchAll(
            "SELECT * FROM ticket_categories ORDER BY sort_order, name"
        );

        return $this->json(['data' => $categories]);
    }

    public function statuses()
    {
        $statuses = [
            ['key' => 'open', 'label' => 'Mở'],
            ['key' => 'in_progress', 'label' => 'Đang xử lý'],
            ['key' => 'waiting', 'label' => 'Chờ phản hồi'],
            ['key' => 'resolved', 'label' => 'Đã giải quyết'],
            ['key' => 'closed', 'label' => 'Đã đóng'],
        ];

        return $this->json(['data' => $statuses]);
    }

    public function list()
    {
        $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $sort = $_GET['sort'] ?? 'created_at';
        $order = strtoupper($_GET['order'] ?? 'DESC');

        $allowedSorts = ['id', 'title', 'status', 'priority', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        $where = ['1=1'];
        $params = [];

        if (!empty($_GET['status'])) {
            $where[] = "t.status = ?";
            $params[] = $_GET['status'];
        }

        if (!empty($_GET['priority'])) {
            $where[] = "t.priority = ?";
            $params[] = $_GET['priority'];
        }

        if (!empty($_GET['category_id'])) {
            $where[] = "t.category_id = ?";
            $params[] = (int) $_GET['category_id'];
        }

        if (!empty($_GET['assigned_to'])) {
            $where[] = "t.assigned_to = ?";
            $params[] = (int) $_GET['assigned_to'];
        }

        if (!empty($_GET['search'])) {
            $where[] = "(t.title LIKE ? OR t.ticket_code LIKE ?)";
            $s = "%" . $_GET['search'] . "%";
            $params[] = $s;
            $params[] = $s;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM tickets t WHERE {$whereClause}",
            $params
        )['count'];

        $tickets = Database::fetchAll(
            "SELECT t.*,
                    tc.name as category_name, tc.color as category_color,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    u.name as assigned_name
             FROM tickets t
             LEFT JOIN ticket_categories tc ON t.category_id = tc.id
             LEFT JOIN contacts c ON t.contact_id = c.id
             LEFT JOIN users u ON t.assigned_to = u.id
             WHERE {$whereClause}
             ORDER BY t.{$sort} {$order}
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        return $this->json([
            'data' => $tickets,
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

        $ticket = Database::fetch(
            "SELECT t.*,
                    tc.name as category_name, tc.color as category_color,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    comp.name as company_name,
                    u.name as assigned_name,
                    uc.name as created_by_name
             FROM tickets t
             LEFT JOIN ticket_categories tc ON t.category_id = tc.id
             LEFT JOIN contacts c ON t.contact_id = c.id
             LEFT JOIN companies comp ON t.company_id = comp.id
             LEFT JOIN users u ON t.assigned_to = u.id
             LEFT JOIN users uc ON t.created_by = uc.id
             WHERE t.id = ?",
            [$id]
        );

        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], 404);
        }

        // Get comments
        $ticket['comments'] = Database::fetchAll(
            "SELECT tc.*, u.name as user_name
             FROM ticket_comments tc
             LEFT JOIN users u ON tc.user_id = u.id
             WHERE tc.ticket_id = ?
             ORDER BY tc.created_at ASC",
            [$id]
        );

        return $this->json(['data' => $ticket]);
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

        // Generate ticket code
        $count = Database::fetch("SELECT COUNT(*) as count FROM tickets")['count'];
        $ticketCode = 'TK-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);

        $ticketId = Database::insert('tickets', [
            'tenant_id' => $_SESSION['tenant_id'] ?? 1,
            'ticket_code' => $ticketCode,
            'title' => $title,
            'content' => trim($input['content'] ?? ''),
            'category_id' => !empty($input['category_id']) ? (int) $input['category_id'] : null,
            'contact_id' => !empty($input['contact_id']) ? (int) $input['contact_id'] : null,
            'company_id' => !empty($input['company_id']) ? (int) $input['company_id'] : null,
            'priority' => $input['priority'] ?? 'medium',
            'status' => 'open',
            'assigned_to' => !empty($input['assigned_to']) ? (int) $input['assigned_to'] : null,
            'contact_phone' => trim($input['contact_phone'] ?? ''),
            'contact_email' => trim($input['contact_email'] ?? ''),
            'due_date' => !empty($input['due_date']) ? $input['due_date'] : null,
            'created_by' => $_SESSION['api_user']['user_id'] ?? null,
        ]);

        return $this->json([
            'message' => 'Thêm mới thành công',
            'id' => $ticketId,
            'ticket_code' => $ticketCode,
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

        $ticket = Database::fetch("SELECT * FROM tickets WHERE id = ?", [$id]);

        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], 404);
        }

        $updateData = [];
        $allowedFields = [
            'title', 'content', 'category_id', 'contact_id', 'company_id',
            'priority', 'status', 'assigned_to', 'contact_phone', 'contact_email', 'due_date',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $updateData[$field] = is_string($input[$field]) ? trim($input[$field]) : $input[$field];
            }
        }

        if (empty($updateData)) {
            return $this->json(['error' => 'No fields to update'], 422);
        }

        // Handle status transitions
        $newStatus = $updateData['status'] ?? $ticket['status'];
        if ($newStatus === 'resolved' && $ticket['status'] !== 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }
        if ($newStatus === 'closed' && $ticket['status'] !== 'closed') {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        Database::update('tickets', $updateData, 'id = ?', [$id]);

        return $this->json([
            'message' => 'Cập nhật thành công',
            'id' => $id,
        ]);
    }
}
