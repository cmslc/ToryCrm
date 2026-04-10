<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class TaskController extends Controller
{
    public function index()
    {
        $this->authorize('tasks', 'view');
        $search = $this->input('search');
        $status = $this->input('status');
        $priority = $this->input('priority');
        $assignedTo = $this->input('assigned_to');
        $dueFrom = $this->input('due_from');
        $dueTo = $this->input('due_to');
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

        if ($dueFrom) {
            $where[] = "t.due_date >= ?";
            $params[] = $dueFrom . ' 00:00:00';
        }
        if ($dueTo) {
            $where[] = "t.due_date <= ?";
            $params[] = $dueTo . ' 23:59:59';
        }

        // Owner-based data scoping: staff only sees own tasks
        $ownerScope = $this->ownerScope('t', 'assigned_to');
        if ($ownerScope['where']) {
            $where[] = $ownerScope['where'];
            $params = array_merge($params, $ownerScope['params']);
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

        // Status counts for tabs (respect owner scope)
        $scopeWhere = ["t.is_deleted = 0", "t.tenant_id = ?"];
        $scopeParams = [Database::tenantId()];
        if ($ownerScope['where']) {
            $scopeWhere[] = $ownerScope['where'];
            $scopeParams = array_merge($scopeParams, $ownerScope['params']);
        }
        $scopeClause = implode(' AND ', $scopeWhere);
        $statusCounts = Database::fetchAll(
            "SELECT t.status, COUNT(*) as count FROM tasks t WHERE {$scopeClause} GROUP BY t.status",
            $scopeParams
        );

        // Users for filter dropdown
        $users = Database::fetchAll(
            "SELECT id, name FROM users WHERE tenant_id = ? AND is_active = 1 ORDER BY name",
            [Database::tenantId()]
        );

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
                'due_from' => $dueFrom,
                'due_to' => $dueTo,
            ],
            'statusCounts' => $statusCounts,
            'users' => $users,
        ]);
    }

    public function kanban()
    {
        $this->authorize('tasks', 'view');
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
        $this->authorize('tasks', 'create');
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
        $this->authorize('tasks', 'create');

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

        // Auto-add followers: creator + all admins
        $followerIds = [$this->userId()];
        $admins = Database::fetchAll("SELECT id FROM users WHERE tenant_id = ? AND role = 'admin' AND is_active = 1", [Database::tenantId()]);
        foreach ($admins as $a) $followerIds[] = $a['id'];
        // Add assigned user
        if (!empty($data['assigned_to'])) $followerIds[] = (int)$data['assigned_to'];
        $followerIds = array_unique($followerIds);
        foreach ($followerIds as $fid) {
            try { Database::query("INSERT INTO task_followers (task_id, user_id) VALUES (?, ?)", [$taskId, $fid]); } catch (\Exception $e) {}
        }

        // Log activity
        Database::insert('activities', [
            'type' => 'task',
            'title' => "Task created: {$title}",
            'description' => "New task {$title} was created.",
            'user_id' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tạo công việc.');
        return $this->redirect('tasks/' . $taskId);
    }

    public function show($id)
    {
        $this->authorize('tasks', 'view');
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

        // Ownership check: staff can only view own tasks
        if (!$this->isAdminOrManager() && ($task['assigned_to'] ?? null) != $this->userId()) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
            return $this->redirect('tasks');
        }

        // Subtasks
        $subtasks = Database::fetchAll(
            "SELECT t.*, u.name as assigned_name FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.parent_id = ? AND t.is_deleted = 0 ORDER BY t.created_at",
            [$id]
        );

        // Comments
        $comments = Database::fetchAll(
            "SELECT c.*, u.name as user_name FROM task_comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.task_id = ? ORDER BY c.created_at ASC",
            [$id]
        );

        // Time logs
        $timeLogs = Database::fetchAll(
            "SELECT tl.*, u.name as user_name FROM task_time_logs tl LEFT JOIN users u ON tl.user_id = u.id WHERE tl.task_id = ? ORDER BY tl.started_at DESC",
            [$id]
        );
        $totalTime = Database::fetch("SELECT COALESCE(SUM(duration),0) as total FROM task_time_logs WHERE task_id = ? AND ended_at IS NOT NULL", [$id]);
        $runningTimer = Database::fetch("SELECT * FROM task_time_logs WHERE task_id = ? AND user_id = ? AND ended_at IS NULL", [$id, $this->userId()]);

        // Attachments
        $attachments = Database::fetchAll(
            "SELECT a.*, u.name as user_name FROM task_attachments a LEFT JOIN users u ON a.user_id = u.id WHERE a.task_id = ? ORDER BY a.created_at DESC",
            [$id]
        );

        // Dependencies
        $dependencies = Database::fetchAll(
            "SELECT td.*, t.title as dep_title, t.status as dep_status FROM task_dependencies td JOIN tasks t ON td.depends_on_id = t.id WHERE td.task_id = ?",
            [$id]
        );

        // All tasks for dependency picker (exclude self and subtasks)
        $allTasks = Database::fetchAll(
            "SELECT id, title FROM tasks WHERE tenant_id = ? AND is_deleted = 0 AND id != ? AND parent_id IS NULL ORDER BY title",
            [Database::tenantId(), $id]
        );

        // Followers
        $followers = Database::fetchAll(
            "SELECT tf.user_id, u.name FROM task_followers tf JOIN users u ON tf.user_id = u.id WHERE tf.task_id = ? ORDER BY u.name",
            [$id]
        );

        // All users for follower picker
        $allUsers = Database::fetchAll(
            "SELECT id, name, role FROM users WHERE tenant_id = ? AND is_active = 1 ORDER BY name",
            [Database::tenantId()]
        );

        return $this->view('tasks.show', [
            'task' => $task,
            'subtasks' => $subtasks,
            'comments' => $comments,
            'timeLogs' => $timeLogs,
            'totalTime' => (int)($totalTime['total'] ?? 0),
            'runningTimer' => $runningTimer,
            'attachments' => $attachments,
            'dependencies' => $dependencies,
            'allTasks' => $allTasks,
            'followers' => $followers,
            'allUsers' => $allUsers,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('tasks', 'edit');
        $task = Database::fetch("SELECT * FROM tasks WHERE id = ?", [$id]);

        if (!$task) {
            $this->setFlash('error', 'Task not found.');
            return $this->redirect('tasks');
        }

        // Ownership check: staff can only edit own tasks
        if (!$this->isAdminOrManager() && ($task['assigned_to'] ?? null) != $this->userId()) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
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
        $this->authorize('tasks', 'edit');

        $task = Database::fetch("SELECT * FROM tasks WHERE id = ?", [$id]);

        if (!$task) {
            $this->setFlash('error', 'Task not found.');
            return $this->redirect('tasks');
        }

        // Ownership check: staff can only update own tasks
        if (!$this->isAdminOrManager() && ($task['assigned_to'] ?? null) != $this->userId()) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
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
        $this->authorize('tasks', 'edit');

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
        $this->authorize('tasks', 'delete');

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
        $this->authorize('tasks', 'delete');
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
        $this->authorize('tasks', 'delete');

        Database::restore('tasks', 'id = ?', [$id]);
        Database::update('tasks', ['cancelled_at' => null, 'status' => 'todo'], 'id = ?', [$id]);

        $this->setFlash('success', 'Đã khôi phục công việc.');
        return $this->redirect('tasks/trash');
    }

    // ---- Xóa công việc (soft delete) ----
    public function delete($id)
    {
        if (!$this->isPost()) return $this->redirect('tasks');
        $this->authorize('tasks', 'delete');

        $task = $this->findSecure('tasks', (int)$id);
        if (!$task) {
            $this->setFlash('error', 'Công việc không tồn tại.');
            return $this->redirect('tasks');
        }

        // Ownership check: staff can only delete own tasks
        if (!$this->isAdminOrManager() && ($task['assigned_to'] ?? null) != $this->userId()) {
            $this->setFlash('error', 'Bạn không có quyền truy cập.');
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

    public function quickUpdate($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('tasks', 'edit');

        $task = Database::fetch("SELECT * FROM tasks WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$task) {
            return $this->json(['error' => 'Công việc không tồn tại'], 404);
        }

        $field = $this->input('field');
        $value = $this->input('value');
        $allowed = ['status', 'assigned_to', 'priority', 'title', 'due_date'];

        if (!in_array($field, $allowed)) {
            return $this->json(['error' => 'Trường không được phép cập nhật'], 422);
        }

        $updateData = [$field => $value ?: null];
        if ($field === 'status' && $value === 'done' && $task['status'] !== 'done') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        } elseif ($field === 'status' && $value !== 'done') {
            $updateData['completed_at'] = null;
        }

        Database::update('tasks', $updateData, 'id = ?', [$id]);

        $display = $value;
        if ($field === 'status') {
            $labels = ['todo' => 'Cần làm', 'in_progress' => 'Đang làm', 'review' => 'Xem xét', 'done' => 'Hoàn thành'];
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

    public function updateStatus($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }
        $this->authorize('tasks', 'edit');

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

    // ---- Followers ----
    public function followers($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $task = Database::fetch("SELECT id FROM tasks WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$task) return $this->json(['error' => 'Task không tồn tại'], 404);

        $action = $this->input('action'); // add or remove
        $userId = (int)$this->input('user_id');

        if ($action === 'add' && $userId) {
            try {
                Database::query("INSERT INTO task_followers (task_id, user_id) VALUES (?, ?)", [$id, $userId]);
            } catch (\Exception $e) {} // duplicate ignore
            $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$userId]);
            return $this->json(['success' => true, 'user_name' => $user['name'] ?? '']);
        } elseif ($action === 'remove' && $userId) {
            Database::query("DELETE FROM task_followers WHERE task_id = ? AND user_id = ?", [$id, $userId]);
            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Invalid action'], 422);
    }

    // ---- Subtasks ----
    public function addSubtask($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $this->authorize('tasks', 'create');

        $parent = Database::fetch("SELECT * FROM tasks WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$parent) return $this->json(['error' => 'Task không tồn tại'], 404);

        $title = trim($this->input('title') ?? '');
        if (empty($title)) return $this->json(['error' => 'Tiêu đề không được để trống'], 422);

        $subId = Database::insert('tasks', [
            'title' => $title,
            'parent_id' => (int)$id,
            'status' => 'todo',
            'priority' => $parent['priority'],
            'assigned_to' => $parent['assigned_to'],
            'due_date' => $parent['due_date'],
            'contact_id' => $parent['contact_id'],
            'deal_id' => $parent['deal_id'],
            'created_by' => $this->userId(),
        ]);

        return $this->json(['success' => true, 'id' => $subId, 'title' => $title]);
    }

    public function toggleSubtask($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        $task = Database::fetch("SELECT * FROM tasks WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$task) return $this->json(['error' => 'Not found'], 404);

        $newStatus = $task['status'] === 'done' ? 'todo' : 'done';
        $update = ['status' => $newStatus];
        if ($newStatus === 'done') $update['completed_at'] = date('Y-m-d H:i:s');
        else $update['completed_at'] = null;

        Database::update('tasks', $update, 'id = ?', [$id]);
        return $this->json(['success' => true, 'status' => $newStatus]);
    }

    // ---- Comments ----
    public function addComment($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $task = Database::fetch("SELECT id FROM tasks WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$task) return $this->json(['error' => 'Task không tồn tại'], 404);

        $content = trim($this->input('content') ?? '');
        if (empty($content)) return $this->json(['error' => 'Nội dung không được để trống'], 422);

        $commentId = Database::insert('task_comments', [
            'task_id' => (int)$id,
            'user_id' => $this->userId(),
            'content' => $content,
        ]);

        $user = Database::fetch("SELECT name FROM users WHERE id = ?", [$this->userId()]);

        return $this->json([
            'success' => true,
            'comment' => [
                'id' => $commentId,
                'content' => $content,
                'user_name' => $user['name'] ?? '',
                'created_at' => date('d/m/Y H:i'),
            ],
        ]);
    }

    public function deleteComment($id, $commentId)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $comment = Database::fetch("SELECT * FROM task_comments WHERE id = ? AND task_id = ?", [$commentId, $id]);
        if (!$comment) return $this->json(['error' => 'Không tồn tại'], 404);

        if ($comment['user_id'] != $this->userId() && !$this->isAdminOrManager()) {
            return $this->json(['error' => 'Không có quyền'], 403);
        }

        Database::query("DELETE FROM task_comments WHERE id = ?", [$commentId]);
        return $this->json(['success' => true]);
    }

    // ---- Time Tracking ----
    public function startTimer($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $task = Database::fetch("SELECT id FROM tasks WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$task) return $this->json(['error' => 'Task không tồn tại'], 404);

        // Check if already running
        $running = Database::fetch(
            "SELECT id FROM task_time_logs WHERE task_id = ? AND user_id = ? AND ended_at IS NULL",
            [$id, $this->userId()]
        );
        if ($running) return $this->json(['error' => 'Timer đang chạy'], 422);

        $logId = Database::insert('task_time_logs', [
            'task_id' => (int)$id,
            'user_id' => $this->userId(),
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->json(['success' => true, 'log_id' => $logId, 'started_at' => date('Y-m-d H:i:s')]);
    }

    public function stopTimer($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $log = Database::fetch(
            "SELECT * FROM task_time_logs WHERE task_id = ? AND user_id = ? AND ended_at IS NULL ORDER BY id DESC LIMIT 1",
            [$id, $this->userId()]
        );
        if (!$log) return $this->json(['error' => 'Không có timer đang chạy'], 422);

        $now = date('Y-m-d H:i:s');
        $duration = strtotime($now) - strtotime($log['started_at']);
        $note = trim($this->input('note') ?? '');

        Database::update('task_time_logs', [
            'ended_at' => $now,
            'duration' => $duration,
            'note' => $note ?: null,
        ], 'id = ?', [$log['id']]);

        return $this->json(['success' => true, 'duration' => $duration]);
    }

    public function addTimeLog($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $hours = (float)($this->input('hours') ?? 0);
        $minutes = (int)($this->input('minutes') ?? 0);
        $note = trim($this->input('note') ?? '');
        $duration = ($hours * 3600) + ($minutes * 60);

        if ($duration <= 0) return $this->json(['error' => 'Thời gian không hợp lệ'], 422);

        $now = date('Y-m-d H:i:s');
        Database::insert('task_time_logs', [
            'task_id' => (int)$id,
            'user_id' => $this->userId(),
            'started_at' => date('Y-m-d H:i:s', strtotime($now) - $duration),
            'ended_at' => $now,
            'duration' => (int)$duration,
            'note' => $note ?: null,
        ]);

        return $this->json(['success' => true]);
    }

    // ---- File Attachments ----
    public function uploadAttachment($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $task = Database::fetch("SELECT id FROM tasks WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        if (!$task) return $this->json(['error' => 'Task không tồn tại'], 404);

        if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'Không có file hoặc lỗi upload'], 422);
        }

        $file = $_FILES['file'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) return $this->json(['error' => 'File quá lớn (tối đa 10MB)'], 422);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'task_' . $id . '_' . uniqid() . '.' . $ext;
        $uploadDir = BASE_PATH . '/public/uploads/tasks/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        move_uploaded_file($file['tmp_name'], $uploadDir . $filename);

        $attId = Database::insert('task_attachments', [
            'task_id' => (int)$id,
            'user_id' => $this->userId(),
            'filename' => $filename,
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
        ]);

        return $this->json([
            'success' => true,
            'attachment' => [
                'id' => $attId,
                'filename' => $filename,
                'original_name' => $file['name'],
                'file_size' => $file['size'],
                'url' => '/uploads/tasks/' . $filename,
            ],
        ]);
    }

    public function deleteAttachment($id, $attId)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $att = Database::fetch("SELECT * FROM task_attachments WHERE id = ? AND task_id = ?", [$attId, $id]);
        if (!$att) return $this->json(['error' => 'Không tồn tại'], 404);

        $filePath = BASE_PATH . '/public/uploads/tasks/' . $att['filename'];
        if (file_exists($filePath)) unlink($filePath);

        Database::query("DELETE FROM task_attachments WHERE id = ?", [$attId]);
        return $this->json(['success' => true]);
    }

    // ---- Bulk Actions ----
    public function bulk()
    {
        if (!$this->isPost()) return $this->redirect('tasks');
        $this->authorize('tasks', 'edit');

        $ids = $this->input('ids') ?? [];
        $action = $this->input('action');

        if (empty($ids) || !is_array($ids)) {
            $this->setFlash('error', 'Chưa chọn công việc nào.');
            return $this->back();
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $tid = Database::tenantId();

        switch ($action) {
            case 'delete':
                $this->authorize('tasks', 'delete');
                Database::query("UPDATE tasks SET is_deleted = 1, deleted_at = NOW() WHERE id IN ({$placeholders}) AND tenant_id = ?", array_merge($ids, [$tid]));
                $this->setFlash('success', 'Đã xóa ' . count($ids) . ' công việc.');
                break;
            case 'done':
                Database::query("UPDATE tasks SET status = 'done', completed_at = NOW() WHERE id IN ({$placeholders}) AND tenant_id = ?", array_merge($ids, [$tid]));
                $this->setFlash('success', 'Đã hoàn thành ' . count($ids) . ' công việc.');
                break;
            case 'assign':
                $assignTo = $this->input('bulk_assign_to');
                if ($assignTo) {
                    Database::query("UPDATE tasks SET assigned_to = ? WHERE id IN ({$placeholders}) AND tenant_id = ?", array_merge([$assignTo], $ids, [$tid]));
                    $this->setFlash('success', 'Đã gán ' . count($ids) . ' công việc.');
                }
                break;
            case 'priority':
                $priority = $this->input('bulk_priority');
                if ($priority) {
                    Database::query("UPDATE tasks SET priority = ? WHERE id IN ({$placeholders}) AND tenant_id = ?", array_merge([$priority], $ids, [$tid]));
                    $this->setFlash('success', 'Đã cập nhật ưu tiên.');
                }
                break;
            default:
                $this->setFlash('error', 'Hành động không hợp lệ.');
        }

        return $this->back();
    }

    // ---- Calendar View ----
    public function calendar()
    {
        $this->authorize('tasks', 'view');
        return $this->view('tasks.calendar');
    }

    public function calendarEvents()
    {
        $start = $this->input('start');
        $end = $this->input('end');

        $where = ["t.is_deleted = 0", "t.tenant_id = ?"];
        $params = [Database::tenantId()];

        $ownerScope = $this->ownerScope('t', 'assigned_to');
        if ($ownerScope['where']) {
            $where[] = $ownerScope['where'];
            $params = array_merge($params, $ownerScope['params']);
        }

        if ($start) { $where[] = "t.due_date >= ?"; $params[] = $start; }
        if ($end) { $where[] = "t.due_date <= ?"; $params[] = $end; }

        $whereClause = implode(' AND ', $where);
        $tasks = Database::fetchAll(
            "SELECT t.id, t.title, t.due_date, t.start_date, t.status, t.priority, u.name as assigned_name
             FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id
             WHERE {$whereClause} AND t.due_date IS NOT NULL",
            $params
        );

        $pc = ['low'=>'#299cdb','medium'=>'#f7b84b','high'=>'#f06548','urgent'=>'#cc563d'];
        $events = [];
        foreach ($tasks as $t) {
            $events[] = [
                'id' => $t['id'],
                'title' => $t['title'],
                'start' => $t['start_date'] ?: $t['due_date'],
                'end' => $t['due_date'],
                'url' => url('tasks/' . $t['id']),
                'backgroundColor' => $t['status'] === 'done' ? '#45cb85' : ($pc[$t['priority']] ?? '#405189'),
                'borderColor' => 'transparent',
                'extendedProps' => ['status' => $t['status'], 'assigned' => $t['assigned_name'] ?? ''],
            ];
        }

        return $this->json($events);
    }

    // ---- Export ----
    public function export()
    {
        $this->authorize('tasks', 'view');

        $where = ["t.is_deleted = 0", "t.tenant_id = ?"];
        $params = [Database::tenantId()];
        $ownerScope = $this->ownerScope('t', 'assigned_to');
        if ($ownerScope['where']) { $where[] = $ownerScope['where']; $params = array_merge($params, $ownerScope['params']); }

        $tasks = Database::fetchAll(
            "SELECT t.title, t.status, t.priority, t.due_date, t.created_at, t.completed_at,
                    u.name as assigned_name, c.first_name as contact_name, d.title as deal_title
             FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             LEFT JOIN contacts c ON t.contact_id = c.id
             LEFT JOIN deals d ON t.deal_id = d.id
             WHERE " . implode(' AND ', $where) . " ORDER BY t.created_at DESC",
            $params
        );

        $format = $this->input('format') ?: 'csv';

        if ($format === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="tasks_' . date('Y-m-d') . '.csv"');
            echo "\xEF\xBB\xBF"; // BOM for Excel
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tiêu đề', 'Trạng thái', 'Ưu tiên', 'Phụ trách', 'Hạn', 'Ngày tạo', 'Hoàn thành', 'Khách hàng', 'Deal']);
            $sl = ['todo'=>'Cần làm','in_progress'=>'Đang làm','review'=>'Review','done'=>'Hoàn thành'];
            $pl = ['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn'];
            foreach ($tasks as $t) {
                fputcsv($out, [
                    $t['title'], $sl[$t['status']] ?? $t['status'], $pl[$t['priority']] ?? $t['priority'],
                    $t['assigned_name'] ?? '', $t['due_date'] ?? '', $t['created_at'] ?? '',
                    $t['completed_at'] ?? '', $t['contact_name'] ?? '', $t['deal_title'] ?? '',
                ]);
            }
            fclose($out);
            exit;
        }
    }

    // ---- Dependencies ----
    public function addDependency($id)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);

        $dependsOn = (int)$this->input('depends_on_id');
        if ($dependsOn == $id) return $this->json(['error' => 'Không thể phụ thuộc chính nó'], 422);

        $depTask = Database::fetch("SELECT id, title FROM tasks WHERE id = ? AND tenant_id = ?", [$dependsOn, Database::tenantId()]);
        if (!$depTask) return $this->json(['error' => 'Task phụ thuộc không tồn tại'], 404);

        // Check circular
        $existing = Database::fetch("SELECT id FROM task_dependencies WHERE task_id = ? AND depends_on_id = ?", [$id, $dependsOn]);
        if ($existing) return $this->json(['error' => 'Đã tồn tại'], 422);

        Database::query(
            "INSERT INTO task_dependencies (task_id, depends_on_id, type) VALUES (?, ?, ?)",
            [$id, $dependsOn, $this->input('type') ?: 'finish_to_start']
        );

        return $this->json(['success' => true, 'depends_on' => $depTask]);
    }

    public function removeDependency($id, $depId)
    {
        if (!$this->isPost()) return $this->json(['error' => 'Method not allowed'], 405);
        Database::query("DELETE FROM task_dependencies WHERE id = ? AND task_id = ?", [$depId, $id]);
        return $this->json(['success' => true]);
    }

    // ---- Templates ----
    public function templates()
    {
        $this->authorize('tasks', 'view');
        $templates = Database::fetchAll(
            "SELECT * FROM task_templates WHERE tenant_id = ? ORDER BY name",
            [Database::tenantId()]
        );
        return $this->view('tasks.templates', ['templates' => $templates]);
    }

    public function createFromTemplate($templateId)
    {
        $this->authorize('tasks', 'create');
        $tpl = Database::fetch("SELECT * FROM task_templates WHERE id = ? AND tenant_id = ?", [$templateId, Database::tenantId()]);
        if (!$tpl) { $this->setFlash('error', 'Template không tồn tại.'); return $this->redirect('tasks/templates'); }

        $dueDate = $tpl['due_days'] ? date('Y-m-d H:i:s', strtotime("+{$tpl['due_days']} days")) : null;

        $taskId = Database::insert('tasks', [
            'title' => $tpl['name'],
            'description' => $tpl['description'],
            'status' => $tpl['default_status'] ?: 'todo',
            'priority' => $tpl['default_priority'] ?: 'medium',
            'due_date' => $dueDate,
            'assigned_to' => $this->userId(),
            'created_by' => $this->userId(),
        ]);

        // Create subtasks from checklist
        $checklist = json_decode($tpl['checklist'] ?? '[]', true);
        foreach ($checklist as $item) {
            if (is_string($item)) {
                Database::insert('tasks', [
                    'title' => $item, 'parent_id' => $taskId, 'status' => 'todo',
                    'priority' => $tpl['default_priority'] ?: 'medium', 'created_by' => $this->userId(),
                ]);
            } elseif (is_array($item) && isset($item['title'])) {
                Database::insert('tasks', [
                    'title' => $item['title'], 'parent_id' => $taskId, 'status' => 'todo',
                    'priority' => $tpl['default_priority'] ?: 'medium', 'created_by' => $this->userId(),
                ]);
            }
        }

        $this->setFlash('success', 'Đã tạo task từ template.');
        return $this->redirect('tasks/' . $taskId);
    }

    public function storeTemplate()
    {
        if (!$this->isPost()) return $this->redirect('tasks/templates');
        $this->authorize('tasks', 'create');

        $name = trim($this->input('name') ?? '');
        if (empty($name)) { $this->setFlash('error', 'Tên template không được trống.'); return $this->back(); }

        $checklist = array_filter(array_map('trim', explode("\n", $this->input('checklist') ?? '')));

        Database::insert('task_templates', [
            'name' => $name,
            'description' => trim($this->input('description') ?? ''),
            'checklist' => json_encode(array_values($checklist)),
            'default_priority' => $this->input('default_priority') ?: 'medium',
            'default_status' => 'todo',
            'due_days' => (int)($this->input('due_days') ?: 0) ?: null,
            'created_by' => $this->userId(),
        ]);

        $this->setFlash('success', 'Đã tạo template.');
        return $this->redirect('tasks/templates');
    }

    public function deleteTemplate($id)
    {
        if (!$this->isPost()) return $this->redirect('tasks/templates');
        Database::query("DELETE FROM task_templates WHERE id = ? AND tenant_id = ?", [$id, Database::tenantId()]);
        $this->setFlash('success', 'Đã xóa template.');
        return $this->redirect('tasks/templates');
    }

    // ---- Gantt Chart ----
    public function gantt()
    {
        $this->authorize('tasks', 'view');
        return $this->view('tasks.gantt');
    }

    public function ganttData()
    {
        $where = ["t.is_deleted = 0", "t.tenant_id = ?"];
        $params = [Database::tenantId()];
        $ownerScope = $this->ownerScope('t', 'assigned_to');
        if ($ownerScope['where']) { $where[] = $ownerScope['where']; $params = array_merge($params, $ownerScope['params']); }

        $tasks = Database::fetchAll(
            "SELECT t.id, t.title, t.start_date, t.due_date, t.created_at, t.status, t.priority, t.progress, t.parent_id,
                    u.name as assigned_name
             FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id
             WHERE " . implode(' AND ', $where) . " ORDER BY t.created_at ASC",
            $params
        );

        $deps = Database::fetchAll("SELECT task_id, depends_on_id FROM task_dependencies");
        $depMap = [];
        foreach ($deps as $d) $depMap[$d['task_id']][] = $d['depends_on_id'];

        $data = [];
        foreach ($tasks as $t) {
            $data[] = [
                'id' => $t['id'],
                'name' => $t['title'],
                'start' => $t['start_date'] ?: $t['created_at'],
                'end' => $t['due_date'] ?: date('Y-m-d', strtotime($t['created_at'] . ' +3 days')),
                'progress' => (int)($t['progress'] ?? 0),
                'dependencies' => isset($depMap[$t['id']]) ? implode(',', $depMap[$t['id']]) : '',
                'status' => $t['status'],
                'assigned' => $t['assigned_name'] ?? '',
            ];
        }

        return $this->json($data);
    }
}
