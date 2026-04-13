<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Ticket;
use App\Services\SlaService;

class TicketController extends Controller
{
    public function index()
    {
        $this->authorize('tickets', 'view');
        $ticketModel = new Ticket();
        $page = max(1, (int) $this->input('page') ?: 1);

        // Owner-based data scoping: staff only sees own tickets
        $assignedToFilter = $this->input('assigned_to');
        if (!$this->isAdminOrManager()) {
            $assignedToFilter = $this->userId();
        }

        $tickets = $ticketModel->getWithRelations($page, 10, [
            'search' => $this->input('search'),
            'status' => $this->input('status'),
            'priority' => $this->input('priority'),
            'category_id' => $this->input('category_id'),
            'assigned_to' => $assignedToFilter,
        ]);

        $categories = $ticketModel->getCategories();
        $statusStats = $ticketModel->getStatsByStatus();

        return $this->view('tickets.index', [
            'tickets' => $tickets,
            'categories' => $categories,
            'statusStats' => $statusStats,
            'filters' => [
                'search' => $this->input('search'),
                'status' => $this->input('status'),
                'priority' => $this->input('priority'),
                'category_id' => $this->input('category_id'),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('tickets', 'create');
        $ticketModel = new Ticket();
        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts ORDER BY first_name");
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $categories = $ticketModel->getCategories();
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('tickets.create', [
            'contacts' => $contacts,
            'companies' => $companies,
            'categories' => $categories,
            'users' => $users,
            'selectedContactId' => (int) $this->input('contact_id'),
            'selectedCompanyId' => (int) $this->input('company_id'),
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('tickets');
        $this->authorize('tickets', 'create');

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');
        if (empty($title)) {
            $this->setFlash('error', 'Tiêu đề ticket không được để trống.');
            return $this->back();
        }

        $ticketModel = new Ticket();
        $ticketCode = $ticketModel->generateCode();

        $ticketId = Database::insert('tickets', [
            'ticket_code' => $ticketCode,
            'title' => $title,
            'content' => trim($data['content'] ?? ''),
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
            'assigned_to' => !empty($data['assigned_to']) ? $data['assigned_to'] : null,
            'contact_phone' => trim($data['contact_phone'] ?? ''),
            'contact_email' => trim($data['contact_email'] ?? ''),
            'due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
            'created_by' => $this->userId(),
        ]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Ticket mới: {$ticketCode} - {$title}",
            'user_id' => $this->userId(),
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
        ]);

        // Assign SLA policy based on ticket priority
        SlaService::assignSla($ticketId);

        $this->setFlash('success', "Ticket {$ticketCode} đã được tạo.");
        return $this->redirect('tickets/' . $ticketId);
    }

    public function show($id)
    {
        $this->authorize('tickets', 'view');
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
            $this->setFlash('error', 'Ticket không tồn tại.');
            return $this->redirect('tickets');
        }

        // Ownership check: staff can only view own tickets
        if (!$this->canAccessOwner($ticket['assigned_to'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('tickets');
        }

        $ticketModel = new Ticket();
        $comments = $ticketModel->getComments($id);
        $slaStatus = SlaService::getSlaStatus($ticket);

        return $this->view('tickets.show', [
            'ticket' => $ticket,
            'comments' => $comments,
            'slaStatus' => $slaStatus,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('tickets', 'edit');
        $ticket = Database::fetch("SELECT * FROM tickets WHERE id = ?", [$id]);
        if (!$ticket) {
            $this->setFlash('error', 'Ticket không tồn tại.');
            return $this->redirect('tickets');
        }

        // Ownership check: staff can only edit own tickets
        if (!$this->canAccessOwner($ticket['assigned_to'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('tickets');
        }

        $ticketModel = new Ticket();
        $contacts = Database::fetchAll("SELECT id, first_name, last_name FROM contacts ORDER BY first_name");
        $companies = Database::fetchAll("SELECT id, name FROM companies ORDER BY name");
        $categories = $ticketModel->getCategories();
        $users = Database::fetchAll("SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

        return $this->view('tickets.edit', [
            'ticket' => $ticket,
            'contacts' => $contacts,
            'companies' => $companies,
            'categories' => $categories,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) return $this->redirect('tickets/' . $id);
        $this->authorize('tickets', 'edit');

        $ticket = Database::fetch("SELECT * FROM tickets WHERE id = ?", [$id]);
        if (!$ticket) {
            $this->setFlash('error', 'Ticket không tồn tại.');
            return $this->redirect('tickets');
        }

        // Ownership check: staff can only update own tickets
        if (!$this->canAccessOwner($ticket['assigned_to'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('tickets');
        }

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');
        if (empty($title)) {
            $this->setFlash('error', 'Tiêu đề không được để trống.');
            return $this->back();
        }

        $updateData = [
            'title' => $title,
            'content' => trim($data['content'] ?? ''),
            'category_id' => !empty($data['category_id']) ? $data['category_id'] : null,
            'contact_id' => !empty($data['contact_id']) ? $data['contact_id'] : null,
            'company_id' => !empty($data['company_id']) ? $data['company_id'] : null,
            'priority' => $data['priority'] ?? 'medium',
            'status' => $data['status'] ?? $ticket['status'],
            'assigned_to' => !empty($data['assigned_to']) ? $data['assigned_to'] : null,
            'contact_phone' => trim($data['contact_phone'] ?? ''),
            'contact_email' => trim($data['contact_email'] ?? ''),
            'due_date' => !empty($data['due_date']) ? $data['due_date'] : null,
        ];

        $newStatus = $data['status'] ?? $ticket['status'];
        if ($newStatus === 'resolved' && $ticket['status'] !== 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }
        if ($newStatus === 'closed' && $ticket['status'] !== 'closed') {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        }

        Database::update('tickets', $updateData, 'id = ?', [$id]);

        $this->setFlash('success', 'Ticket đã được cập nhật.');
        return $this->redirect('tickets/' . $id);
    }

    public function comment($id)
    {
        if (!$this->isPost()) return $this->redirect('tickets/' . $id);
        $this->authorize('tickets', 'edit');

        $ticket = Database::fetch("SELECT * FROM tickets WHERE id = ?", [$id]);
        if (!$ticket) return $this->redirect('tickets');

        $content = trim($this->input('content') ?? '');
        if (empty($content)) {
            $this->setFlash('error', 'Nội dung bình luận không được để trống.');
            return $this->back();
        }

        Database::insert('ticket_comments', [
            'ticket_id' => $id,
            'content' => $content,
            'is_internal' => $this->input('is_internal') ? 1 : 0,
            'user_id' => $this->userId(),
        ]);

        // Record first response for SLA tracking (staff comment)
        SlaService::recordFirstResponse($id);

        $this->setFlash('success', 'Đã thêm bình luận.');
        return $this->redirect('tickets/' . $id);
    }

    public function quickUpdate($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('tickets', 'edit');

        $ticket = Database::fetch("SELECT * FROM tickets WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$ticket) {
            return $this->json(['error' => 'Ticket không tồn tại'], 404);
        }

        $field = $this->input('field');
        $value = $this->input('value');
        $allowed = ['status', 'assigned_to', 'priority'];

        if (!in_array($field, $allowed)) {
            return $this->json(['error' => 'Trường không được phép cập nhật'], 422);
        }

        $updateData = [$field => $value ?: null];
        if ($field === 'status' && $value === 'resolved' && $ticket['status'] !== 'resolved') {
            $updateData['resolved_at'] = date('Y-m-d H:i:s');
        }
        if ($field === 'status' && $value === 'closed' && $ticket['status'] !== 'closed') {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        }

        Database::update('tickets', $updateData, 'id = ?', [$id]);

        $display = $value;
        if ($field === 'status') {
            $labels = ['open' => 'Mở', 'in_progress' => 'Đang xử lý', 'resolved' => 'Đã giải quyết', 'closed' => 'Đã đóng'];
            $display = $labels[$value] ?? $value;
        } elseif ($field === 'assigned_to') {
            $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$value]);
            $display = $user ? htmlspecialchars($user['name']) : '-';
        } elseif ($field === 'priority') {
            $labels = ['low' => 'Thấp', 'medium' => 'Trung bình', 'high' => 'Cao', 'urgent' => 'Khẩn cấp'];
            $display = $labels[$value] ?? $value;
        }

        return $this->json(['success' => true, 'value' => $value, 'display' => $display]);
    }

    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('tickets');
        $this->authorize('tickets', 'delete');

        $ticket = $this->findSecure('tickets', (int)$id);
        if (!$ticket) {
            $this->setFlash('error', 'Ticket không tồn tại.');
            return $this->redirect('tickets');
        }

        // Ownership check: staff can only delete own tickets
        if (!$this->canAccessOwner($ticket['assigned_to'] ?? null)) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('tickets');
        }

        Database::delete('ticket_comments', 'ticket_id = ?', [$id]);
        Database::delete('tickets', 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'system',
            'title' => "Xóa ticket: {$ticket['ticket_code']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Ticket đã được xóa.');
        return $this->redirect('tickets');
    }
}
