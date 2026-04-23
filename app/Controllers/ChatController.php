<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ChatController extends Controller
{
    public function index()
    {
        $tenantId = $this->tenantId();
        $page = max(1, (int) $this->input('page', 1));
        $perPage = in_array((int)$this->input('per_page'), [10,20,50,100]) ? (int)$this->input('per_page') : 20;
        $offset = ($page - 1) * $perPage;

        $status = $this->input('status');
        $channel = $this->input('channel');
        $assignedTo = $this->input('assigned_to');
        $search = $this->input('search');
        $filter = $this->input('filter'); // unread, mine, starred

        $conditions = ['cv.tenant_id = ?'];
        $params = [$tenantId];

        if ($status) {
            $conditions[] = 'cv.status = ?';
            $params[] = $status;
        }
        if ($channel) {
            $conditions[] = 'cv.channel = ?';
            $params[] = $channel;
        }
        if ($assignedTo) {
            $conditions[] = 'cv.assigned_to = ?';
            $params[] = (int) $assignedTo;
        }
        if ($search) {
            $conditions[] = '(c.first_name LIKE ? OR c.last_name LIKE ? OR cv.subject LIKE ? OR cv.last_message_preview LIKE ?)';
            $s = '%' . $search . '%';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }
        if ($filter === 'unread') {
            $conditions[] = 'cv.unread_count > 0';
        } elseif ($filter === 'mine') {
            $conditions[] = 'cv.assigned_to = ?';
            $params[] = $this->userId();
        } elseif ($filter === 'starred') {
            $conditions[] = 'cv.is_starred = 1';
        }

        $where = implode(' AND ', $conditions);

        $total = (int) Database::fetch(
            "SELECT COUNT(*) as cnt FROM conversations cv
             LEFT JOIN contacts c ON cv.contact_id = c.id
             WHERE {$where}",
            $params
        )['cnt'];

        $totalPages = max(1, (int) ceil($total / $perPage));

        $conversations = Database::fetchAll(
            "SELECT cv.*,
                    CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,'')) as contact_name,
                    c.email as contact_email,
                    c.phone as contact_phone,
                    u.name as assigned_name
             FROM conversations cv
             LEFT JOIN contacts c ON cv.contact_id = c.id
             LEFT JOIN users u ON cv.assigned_to = u.id
             WHERE {$where}
             ORDER BY cv.last_message_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // Get unread total for badge
        $unreadTotal = (int) Database::fetch(
            "SELECT COUNT(*) as cnt FROM conversations WHERE tenant_id = ? AND unread_count > 0",
            [$tenantId]
        )['cnt'];

        // Active conversation (first one or selected)
        $activeId = (int) $this->input('active');
        $activeConversation = null;
        $messages = [];
        $cannedResponses = [];

        if ($activeId) {
            $activeConversation = Database::fetch(
                "SELECT cv.*,
                        CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,'')) as contact_name,
                        c.email as contact_email, c.phone as contact_phone,
                        u.name as assigned_name
                 FROM conversations cv
                 LEFT JOIN contacts c ON cv.contact_id = c.id
                 LEFT JOIN users u ON cv.assigned_to = u.id
                 WHERE cv.id = ? AND cv.tenant_id = ?",
                [$activeId, $tenantId]
            );

            if ($activeConversation) {
                $messages = Database::fetchAll(
                    "SELECT cm.*, u.name as sender_name
                     FROM messages cm
                     LEFT JOIN users u ON cm.sender_id = u.id
                     WHERE cm.conversation_id = ?
                     ORDER BY cm.created_at ASC",
                    [$activeId]
                );

                // Mark unread messages as read
                Database::query(
                    "UPDATE messages SET is_read = 1
                     WHERE conversation_id = ? AND is_read = 0 AND direction = 'inbound'",
                    [$activeId]
                );
                Database::update('conversations', ['unread_count' => 0], 'id = ?', [$activeId]);
            }
        }

        $users = Database::fetchAll(
            "SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name",
            [$tenantId]
        );

        $cannedResponses = Database::fetchAll(
            "SELECT * FROM canned_responses WHERE tenant_id = ? ORDER BY title ASC",
            [$tenantId]
        );

        return $this->view('chat.index', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
            'users' => $users,
            'cannedResponses' => $cannedResponses,
            'unreadTotal' => $unreadTotal,
            'filters' => [
                'status' => $status,
                'channel' => $channel,
                'assigned_to' => $assignedTo,
                'search' => $search,
                'filter' => $filter,
            ],
            'pagination' => [
                'page' => $page,
                'total' => $total,
                'total_pages' => $totalPages,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function show($id)
    {
        $tenantId = $this->tenantId();

        $conversation = Database::fetch(
            "SELECT cv.*,
                    CONCAT(COALESCE(c.first_name,''), ' ', COALESCE(c.last_name,'')) as contact_name,
                    c.email as contact_email, c.phone as contact_phone,
                    c.id as cid,
                    u.name as assigned_name
             FROM conversations cv
             LEFT JOIN contacts c ON cv.contact_id = c.id
             LEFT JOIN users u ON cv.assigned_to = u.id
             WHERE cv.id = ? AND cv.tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$conversation) {
            $this->setFlash('error', 'Cuộc hội thoại không tồn tại.');
            return $this->redirect('conversations');
        }

        $messages = Database::fetchAll(
            "SELECT cm.*, u.name as sender_name
             FROM messages cm
             LEFT JOIN users u ON cm.sender_id = u.id
             WHERE cm.conversation_id = ?
             ORDER BY cm.created_at ASC",
            [$id]
        );

        // Mark unread messages as read
        Database::query(
            "UPDATE messages SET is_read = 1
             WHERE conversation_id = ? AND is_read = 0 AND direction = 'inbound'",
            [$id]
        );
        Database::update('conversations', ['unread_count' => 0], 'id = ?', [$id]);

        $users = Database::fetchAll(
            "SELECT u.id, u.name, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name",
            [$tenantId]
        );

        $cannedResponses = Database::fetchAll(
            "SELECT * FROM canned_responses WHERE tenant_id = ? ORDER BY title ASC",
            [$tenantId]
        );

        return $this->view('chat.show', [
            'conversation' => $conversation,
            'messages' => $messages,
            'users' => $users,
            'cannedResponses' => $cannedResponses,
        ]);
    }

    public function create()
    {
        $tenantId = $this->tenantId();

        $contacts = Database::fetchAll(
            "SELECT id, first_name, last_name, email FROM contacts WHERE tenant_id = ? AND is_deleted = 0 ORDER BY first_name",
            [$tenantId]
        );

        return $this->view('chat.create', [
            'contacts' => $contacts,
            'selectedContactId' => (int) $this->input('contact_id'),
        ]);
    }

    public function store()
    {
        if (!$this->isPost()) return $this->redirect('conversations');

        $data = $this->allInput();
        $contactId = !empty($data['contact_id']) ? (int) $data['contact_id'] : null;
        $channel = $data['channel'] ?? 'email';
        $subject = trim($data['subject'] ?? '');
        $content = trim($data['content'] ?? '');

        if (empty($content)) {
            $this->setFlash('error', 'Nội dung tin nhắn không được để trống.');
            return $this->back();
        }

        $now = date('Y-m-d H:i:s');
        $preview = mb_substr(strip_tags($content), 0, 100);

        $conversationId = Database::insert('conversations', [
            'tenant_id' => $this->tenantId(),
            'contact_id' => $contactId,
            'channel' => $channel,
            'subject' => $subject,
            'status' => 'open',
            'assigned_to' => $this->userId(),
            'last_message_at' => $now,
            'last_message_preview' => $preview,
            'unread_count' => 0,
            'is_starred' => 0,
        ]);

        Database::insert('messages', [
            'conversation_id' => $conversationId,
            'direction' => 'outbound',
            'content' => $content,
            'sender_id' => $this->userId(),
            'is_read' => 1,
        ]);

        $this->setFlash('success', 'Cuộc hội thoại đã được tạo.');
        return $this->redirect('conversations/' . $conversationId);
    }

    public function reply($id)
    {
        if (!$this->isPost()) return $this->redirect('conversations?active=' . $id);

        $tenantId = $this->tenantId();
        $conversation = Database::fetch(
            "SELECT * FROM conversations WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$conversation) {
            $this->setFlash('error', 'Cuộc hội thoại không tồn tại.');
            return $this->redirect('conversations');
        }

        $content = trim($this->input('content', ''));
        if (empty($content)) {
            $this->setFlash('error', 'Nội dung tin nhắn không được để trống.');
            return $this->back();
        }

        $now = date('Y-m-d H:i:s');
        $preview = mb_substr(strip_tags($content), 0, 100);

        Database::insert('messages', [
            'conversation_id' => $id,
            'direction' => 'outbound',
            'content' => $content,
            'sender_id' => $this->userId(),
            'is_read' => 1,
        ]);

        Database::update('conversations', [
            'last_message_at' => $now,
            'last_message_preview' => $preview,
        ], 'id = ?', [$id]);

        // If conversation was closed/resolved, reopen
        if (in_array($conversation['status'], ['closed', 'resolved'])) {
            Database::update('conversations', ['status' => 'open'], 'id = ?', [$id]);
        }

        $this->setFlash('success', 'Đã gửi tin nhắn.');
        return $this->redirect('conversations?active=' . $id);
    }

    public function assign($id)
    {
        if (!$this->isPost()) return $this->redirect('conversations?active=' . $id);

        $tenantId = $this->tenantId();
        $conversation = Database::fetch(
            "SELECT * FROM conversations WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$conversation) {
            $this->setFlash('error', 'Cuộc hội thoại không tồn tại.');
            return $this->redirect('conversations');
        }

        $assignedTo = !empty($this->input('assigned_to')) ? (int) $this->input('assigned_to') : null;
        Database::update('conversations', ['assigned_to' => $assignedTo], 'id = ?', [$id]);

        $this->setFlash('success', 'Đã cập nhật phụ trách.');
        return $this->redirect('conversations?active=' . $id);
    }

    public function updateStatus($id)
    {
        if (!$this->isPost()) return $this->redirect('conversations?active=' . $id);

        $tenantId = $this->tenantId();
        $conversation = Database::fetch(
            "SELECT * FROM conversations WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$conversation) {
            $this->setFlash('error', 'Cuộc hội thoại không tồn tại.');
            return $this->redirect('conversations');
        }

        $status = $this->input('status', 'open');
        $allowed = ['open', 'pending', 'resolved', 'closed'];
        if (!in_array($status, $allowed)) $status = 'open';

        Database::update('conversations', ['status' => $status], 'id = ?', [$id]);

        $labels = ['open' => 'Mở', 'pending' => 'Chờ', 'resolved' => 'Đã xử lý', 'closed' => 'Đóng'];
        $this->setFlash('success', 'Trạng thái đã chuyển sang: ' . ($labels[$status] ?? $status));
        return $this->redirect('conversations?active=' . $id);
    }

    public function star($id)
    {
        if (!$this->isPost()) return $this->redirect('conversations?active=' . $id);

        $tenantId = $this->tenantId();
        $conversation = Database::fetch(
            "SELECT * FROM conversations WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$conversation) {
            $this->setFlash('error', 'Cuộc hội thoại không tồn tại.');
            return $this->redirect('conversations');
        }

        $newVal = $conversation['is_starred'] ? 0 : 1;
        Database::update('conversations', ['is_starred' => $newVal], 'id = ?', [$id]);

        $this->setFlash('success', $newVal ? 'Đã đánh dấu.' : 'Đã bỏ đánh dấu.');
        return $this->redirect('conversations?active=' . $id);
    }

    public function cannedResponses()
    {
        $tenantId = $this->tenantId();
        $responses = Database::fetchAll(
            "SELECT id, title, content FROM canned_responses WHERE tenant_id = ? ORDER BY title ASC",
            [$tenantId]
        );

        return $this->json($responses);
    }
}
