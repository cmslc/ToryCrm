<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class TaskController extends Controller
{
    public function index()
    {
        $search = $this->input('search');
        $status = $this->input('status');
        $priority = $this->input('priority');
        $assignedTo = $this->input('assigned_to');
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $where = ["t.is_deleted = 0", "t.tenant_id = ?"];
        $params = [Database::tenantId()];

        if ($search) {
            $where[] = "(t.title LIKE ? OR t.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }

        if ($status) {
            $where[] = "t.status = ?";
            $params[] = $status;
        }

        if ($priority) {
            $where[] = "t.priority = ?";
            $params[] = $priority;
        }

        if ($assignedTo) {
            $where[] = "t.assigned_to = ?";
            $params[] = $assignedTo;
        }

        $whereClause = implode(' AND ', $where);

        $total = Database::fetch(
            "SELECT COUNT(*) as count FROM tasks t WHERE {$whereClause}",
            $params
        )['count'];

        $tasks = Database::fetchAll(
            "SELECT t.*, u.name as assigned_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    d.title as deal_title
             FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             LEFT JOIN contacts c ON t.contact_id = c.id
             LEFT JOIN deals d ON t.deal_id = d.id
             WHERE {$whereClause}
             ORDER BY t.due_date ASC, t.priority DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalPages = ceil($total / $perPage);

        return $this->view('tasks.index', [
            'tasks' => [
                'items' => $tasks,
                'total' => $total,
                'page' => $page,
                'total_pages' => $totalPages,
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'priority' => $priority,
                'assigned_to' => $assignedTo,
            ],
        ]);
    }

    public function kanban()
    {
        $statuses = ['todo', 'in_progress', 'review', 'done'];
        $board = [];

        foreach ($statuses as $status) {
            $board[$status] = Database::fetchAll(
                "SELECT t.*, u.name as assigned_name,
                        c.first_name as contact_first_name, c.last_name as contact_last_name,
                        d.title as deal_title
                 FROM tasks t
                 LEFT JOIN users u ON t.assigned_to = u.id
                 LEFT JOIN contacts c ON t.contact_id = c.id
                 LEFT JOIN deals d ON t.deal_id = d.id
                 WHERE t.status = ?
                 ORDER BY t.priority DESC, t.due_date ASC",
                [$status]
            );
        }

        return $this->view('tasks.kanban', [
            'board' => $board,
            'statuses' => $statuses,
        ]);
    }

    public function create()
    {
        $contacts = Database::fetchAll(
            "SELECT id, first_name, last_name FROM contacts ORDER BY first_name"
        );
        $deals = Database::fetchAll(
            "SELECT id, title FROM deals ORDER BY title"
        );
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        return $this->view('tasks.create', [
            'contacts' => $contacts,
            'deals' => $deals,
            'users' => $users,
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) {
            return $this->redirect('tasks');
        }

        $data = $this->allInput();

        $title = trim($data['title'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Task title is required.');
            return $this->back();
        }

        $taskId = Database::insert('tasks', [
            'title' => $title,
            'description' => trim($data['description'] ?? ''),
            'status' => $data['status'] ?? 'todo',
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => (!empty($data['due_date']) ? $data['due_date'] : null),
            'assigned_to' => (!empty($data['assigned_to']) ? $data['assigned_to'] : $this->userId()),
            'contact_id' => (!empty($data['contact_id']) ? $data['contact_id'] : null),
            'deal_id' => (!empty($data['deal_id']) ? $data['deal_id'] : null),
            'company_id' => (!empty($data['company_id']) ? $data['company_id'] : null),
            'created_by' => $this->userId(),
        ]);

        // Log activity
        Database::insert('activities', [
            'type' => 'task',
            'title' => "Task created: {$title}",
            'description' => "New task {$title} was created.",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Task created successfully.');
        return $this->redirect('tasks/' . $taskId);
    }

    public function show($id)
    {
        $task = Database::fetch(
            "SELECT t.*, u.name as assigned_name, creator.name as creator_name,
                    c.first_name as contact_first_name, c.last_name as contact_last_name,
                    d.title as deal_title
             FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             LEFT JOIN users creator ON t.created_by = creator.id
             LEFT JOIN contacts c ON t.contact_id = c.id
             LEFT JOIN deals d ON t.deal_id = d.id
             WHERE t.id = ?",
            [$id]
        );

        if (!$task) {
            $this->setFlash('error', 'Task not found.');
            return $this->redirect('tasks');
        }

        return $this->view('tasks.show', [
            'task' => $task,
        ]);
    }

    public function edit($id)
    {
        $task = Database::fetch("SELECT * FROM tasks WHERE id = ?", [$id]);

        if (!$task) {
            $this->setFlash('error', 'Task not found.');
            return $this->redirect('tasks');
        }

        $contacts = Database::fetchAll(
            "SELECT id, first_name, last_name FROM contacts ORDER BY first_name"
        );
        $deals = Database::fetchAll(
            "SELECT id, title FROM deals ORDER BY title"
        );
        $users = Database::fetchAll("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name");

        return $this->view('tasks.edit', [
            'task' => $task,
            'contacts' => $contacts,
            'deals' => $deals,
            'users' => $users,
        ]);
    }

    public function update($id)
    {
        if (!$this->isPost()) {
            return $this->redirect('tasks/' . $id);
        }

        $task = Database::fetch("SELECT * FROM tasks WHERE id = ?", [$id]);

        if (!$task) {
            $this->setFlash('error', 'Task not found.');
            return $this->redirect('tasks');
        }

        $data = $this->allInput();
        $title = trim($data['title'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Task title is required.');
            return $this->back();
        }

        $oldStatus = $task['status'];
        $newStatus = $data['status'] ?? $oldStatus;

        $updateData = [
            'title' => $title,
            'description' => trim($data['description'] ?? ''),
            'status' => $newStatus,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => (!empty($data['due_date']) ? $data['due_date'] : null),
            'assigned_to' => (!empty($data['assigned_to']) ? $data['assigned_to'] : null),
            'contact_id' => (!empty($data['contact_id']) ? $data['contact_id'] : null),
            'deal_id' => (!empty($data['deal_id']) ? $data['deal_id'] : null),
            'company_id' => (!empty($data['company_id']) ? $data['company_id'] : null),
        ];

        // Set completed_at when task moves to done
        if ($newStatus === 'done' && $oldStatus !== 'done') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($newStatus !== 'done') {
            $updateData['completed_at'] = null;
        }

        Database::update('tasks', $updateData, 'id = ?', [$id]);

        // Log activity
        $description = "Task {$title} was updated.";
        if ($oldStatus !== $newStatus) {
            $description .= " Status changed from {$oldStatus} to {$newStatus}.";
        }

        Database::insert('activities', [
            'type' => 'task',
            'title' => "Task updated: {$title}",
            'description' => $description,
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Task updated successfully.');
        return $this->redirect('tasks/' . $id);
    }

    // ---- Hoàn thành công việc ----
    public function complete($id)
    {
        if (!$this->isPost()) return $this->redirect('tasks/' . $id);

        $task = $this->findSecure('tasks', (int)$id);
        if (!$task) {
            $this->setFlash('error', 'Công việc không tồn tại.');
            return $this->redirect('tasks');
        }

        Database::update('tasks', [
            'status' => 'done',
            'completed_at' => date('Y-m-d H:i:s'),
            'progress' => 100,
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'task',
            'title' => "Hoàn thành: {$task['title']}",
            'user_id' => $this->userId(),
            'deal_id' => $task['deal_id'] ?? null,
            'contact_id' => $task['contact_id'] ?? null,
        ]);

        $this->setFlash('success', 'Đã hoàn thành công việc.');
        return $this->redirect('tasks/' . $id);
    }

    // ---- Hủy công việc ----
    public function cancel($id)
    {
        if (!$this->isPost()) return $this->redirect('tasks/' . $id);

        $task = $this->findSecure('tasks', (int)$id);
        if (!$task) {
            $this->setFlash('error', 'Công việc không tồn tại.');
            return $this->redirect('tasks');
        }

        Database::update('tasks', [
            'status' => 'todo',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'task',
            'title' => "Hủy công việc: {$task['title']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã hủy công việc.');
        return $this->redirect('tasks');
    }

    // ---- Khôi phục công việc ----
    public function trash()
    {
        $tid = Database::tenantId();
        $tasks = Database::fetchAll(
            "SELECT t.*, u.name as assigned_name
             FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id
             WHERE t.is_deleted = 1 AND t.tenant_id = ?
             ORDER BY t.deleted_at DESC",
            [$tid]
        );

        return $this->view('tasks.trash', ['tasks' => $tasks]);
    }

    public function restore($id)
    {
        if (!$this->isPost()) return $this->redirect('tasks/trash');

        Database::restore('tasks', 'id = ?', [$id]);
        Database::update('tasks', ['cancelled_at' => null, 'status' => 'todo'], 'id = ?', [$id]);

        $this->setFlash('success', 'Đã khôi phục công việc.');
        return $this->redirect('tasks/trash');
    }

    // ---- Xóa công việc (soft delete) ----
    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('tasks');

        $task = $this->findSecure('tasks', (int)$id);
        if (!$task) {
            $this->setFlash('error', 'Công việc không tồn tại.');
            return $this->redirect('tasks');
        }

        Database::softDelete('tasks', 'id = ?', [$id]);

        Database::insert('activities', [
            'type' => 'task',
            'title' => "Xóa công việc: {$task['title']}",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã xóa công việc.');
        return $this->redirect('tasks');
    }

    public function updateStatus($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $task = Database::fetch("SELECT * FROM tasks WHERE id = ?", [$id]);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $newStatus = $this->input('status');
        $validStatuses = ['todo', 'in_progress', 'review', 'done'];

        if (!in_array($newStatus, $validStatuses)) {
            return $this->json(['error' => 'Invalid status'], 422);
        }

        $oldStatus = $task['status'];

        $updateData = ['status' => $newStatus];
        if ($newStatus === 'done' && $oldStatus !== 'done') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($newStatus !== 'done') {
            $updateData['completed_at'] = null;
        }

        Database::update('tasks', $updateData, 'id = ?', [$id]);

        // Log activity
        Database::insert('activities', [
            'type' => 'task',
            'title' => "Task status changed: {$task['title']}",
            'description' => "Task {$task['title']} moved from {$oldStatus} to {$newStatus}.",
            'user_id' => $this->userId(),
        ]);

        return $this->json(['success' => true, 'status' => $newStatus]);
    }
}
