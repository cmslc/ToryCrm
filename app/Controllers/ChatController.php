<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;

class ChatController extends Controller
{
    /** Old /conversations URL → permanent redirect to /chat. */
    public function redirectLegacyIndex()
    {
        return $this->redirect('chat');
    }
    public function redirectLegacyShow($id)
    {
        return $this->redirect('chat/' . (int)$id);
    }

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

        $conditions = ['cv.tenant_id = ?', "cv.channel != 'internal'"];
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
            return $this->redirect('chat');
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
        if (!$this->isPost()) return $this->redirect('chat');

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
        return $this->redirect('chat/' . $conversationId);
    }

    public function reply($id)
    {
        if (!$this->isPost()) return $this->redirect('chat?active=' . $id);

        $tenantId = $this->tenantId();
        $conversation = Database::fetch(
            "SELECT * FROM conversations WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$conversation) {
            $this->setFlash('error', 'Cuộc hội thoại không tồn tại.');
            return $this->redirect('chat');
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
        return $this->redirect('chat?active=' . $id);
    }

    public function assign($id)
    {
        if (!$this->isPost()) return $this->redirect('chat?active=' . $id);

        $tenantId = $this->tenantId();
        $conversation = Database::fetch(
            "SELECT * FROM conversations WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$conversation) {
            $this->setFlash('error', 'Cuộc hội thoại không tồn tại.');
            return $this->redirect('chat');
        }

        $assignedTo = !empty($this->input('assigned_to')) ? (int) $this->input('assigned_to') : null;
        Database::update('conversations', ['assigned_to' => $assignedTo], 'id = ?', [$id]);

        $this->setFlash('success', 'Đã cập nhật phụ trách.');
        return $this->redirect('chat?active=' . $id);
    }

    public function updateStatus($id)
    {
        if (!$this->isPost()) return $this->redirect('chat?active=' . $id);

        $tenantId = $this->tenantId();
        $conversation = Database::fetch(
            "SELECT * FROM conversations WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$conversation) {
            $this->setFlash('error', 'Cuộc hội thoại không tồn tại.');
            return $this->redirect('chat');
        }

        $status = $this->input('status', 'open');
        $allowed = ['open', 'pending', 'resolved', 'closed'];
        if (!in_array($status, $allowed)) $status = 'open';

        Database::update('conversations', ['status' => $status], 'id = ?', [$id]);

        $labels = ['open' => 'Mở', 'pending' => 'Chờ', 'resolved' => 'Đã xử lý', 'closed' => 'Đóng'];
        $this->setFlash('success', 'Trạng thái đã chuyển sang: ' . ($labels[$status] ?? $status));
        return $this->redirect('chat?active=' . $id);
    }

    public function star($id)
    {
        if (!$this->isPost()) return $this->redirect('chat?active=' . $id);

        $tenantId = $this->tenantId();
        $conversation = Database::fetch(
            "SELECT * FROM conversations WHERE id = ? AND tenant_id = ?",
            [$id, $tenantId]
        );

        if (!$conversation) {
            $this->setFlash('error', 'Cuộc hội thoại không tồn tại.');
            return $this->redirect('chat');
        }

        $newVal = $conversation['is_starred'] ? 0 : 1;
        Database::update('conversations', ['is_starred' => $newVal], 'id = ?', [$id]);

        $this->setFlash('success', $newVal ? 'Đã đánh dấu.' : 'Đã bỏ đánh dấu.');
        return $this->redirect('chat?active=' . $id);
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

    // ==========================================================
    // Internal DM — nhân viên nội bộ
    // ==========================================================

    /** List of DM threads the current user is part of. */
    public function internalIndex()
    {
        $tid = $this->tenantId();
        $uid = $this->userId();

        // All DMs where current user is A or B, with peer info and unread count
        $dms = Database::fetchAll(
            "SELECT cv.id, cv.user_a_id, cv.user_b_id, cv.unread_a, cv.unread_b,
                    cv.last_message_at, cv.last_message_preview,
                    IF(cv.user_a_id = ?, cv.user_b_id, cv.user_a_id) as peer_id,
                    IF(cv.user_a_id = ?, cv.unread_a, cv.unread_b) as my_unread,
                    u.name as peer_name, u.avatar as peer_avatar, u.email as peer_email
             FROM conversations cv
             LEFT JOIN users u ON u.id = IF(cv.user_a_id = ?, cv.user_b_id, cv.user_a_id)
             WHERE cv.tenant_id = ? AND cv.channel = 'internal'
             AND (cv.user_a_id = ? OR cv.user_b_id = ?)
             ORDER BY cv.last_message_at DESC",
            [$uid, $uid, $uid, $tid, $uid, $uid]
        );

        $users = Database::fetchAll(
            "SELECT u.id, u.name, u.avatar, u.email, p.name as position_name
             FROM users u LEFT JOIN positions p ON p.id = u.position_id
             WHERE u.tenant_id = ? AND u.is_active = 1 AND u.id != ?
             ORDER BY u.name",
            [$tid, $uid]
        );

        $activeId = (int) $this->input('active');
        $active = null;
        $messages = [];
        if ($activeId) {
            $active = $this->loadDm($activeId, $uid, $tid);
            if ($active) {
                $messages = Database::fetchAll(
                    "SELECT m.*, u.name as sender_name, u.avatar as sender_avatar
                     FROM messages m LEFT JOIN users u ON m.sender_id = u.id
                     WHERE m.conversation_id = ? ORDER BY m.created_at ASC",
                    [$activeId]
                );
                $messages = $this->enrichMessages($messages, $uid);
                $this->markDmRead($activeId, $uid);
            }
        }

        return $this->view('chat.internal', [
            'dms' => $dms,
            'users' => $users,
            'active' => $active,
            'messages' => $messages,
        ]);
    }

    /** Open (or create) a 1-1 DM with another user. Redirects to /chat/internal?active=X */
    public function internalStart($peerId)
    {
        $tid = $this->tenantId();
        $uid = $this->userId();
        $peer = (int) $peerId;
        if ($peer === $uid || $peer <= 0) {
            $this->setFlash('error', 'Không thể chat với chính mình.');
            return $this->redirect('chat/internal');
        }

        $peerRow = Database::fetch(
            "SELECT id FROM users WHERE id = ? AND tenant_id = ? AND is_active = 1",
            [$peer, $tid]
        );
        if (!$peerRow) {
            $this->setFlash('error', 'Người dùng không tồn tại.');
            return $this->redirect('chat/internal');
        }

        // Canonical pair: smaller id is user_a
        [$a, $b] = $uid < $peer ? [$uid, $peer] : [$peer, $uid];

        $dm = Database::fetch(
            "SELECT id FROM conversations
             WHERE tenant_id = ? AND channel = 'internal' AND user_a_id = ? AND user_b_id = ?",
            [$tid, $a, $b]
        );

        if (!$dm) {
            $newId = Database::insert('conversations', [
                'tenant_id' => $tid,
                'channel' => 'internal',
                'status' => 'open',
                'user_a_id' => $a,
                'user_b_id' => $b,
                'created_by' => $uid,
                'last_message_at' => date('Y-m-d H:i:s'),
            ]);
            return $this->redirect('chat/internal?active=' . $newId);
        }
        return $this->redirect('chat/internal?active=' . $dm['id']);
    }

    /** Send a message in a DM thread. */
    public function internalReply($id)
    {
        if (!$this->isPost()) return $this->redirect('chat/internal?active=' . (int)$id);
        $tid = $this->tenantId();
        $uid = $this->userId();
        $content = trim((string) $this->input('content', ''));

        $attachments = [];
        if (!empty($_FILES['attachment']['tmp_name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $att = $this->saveChatAttachment($_FILES['attachment'], (int)$id);
            if ($att) $attachments[] = $att;
        }
        if ($content === '' && empty($attachments)) return $this->redirect('chat/internal?active=' . (int)$id);

        $dm = $this->loadDm((int)$id, $uid, $tid);
        if (!$dm) {
            $this->setFlash('error', 'Không có quyền.');
            return $this->redirect('chat/internal');
        }

        $peerIsA = ($dm['user_a_id'] != $uid); // if I'm B, peer is A, so increment unread_a
        $contentType = !empty($attachments) && !empty($attachments[0]['is_image']) ? 'image' : (!empty($attachments) ? 'file' : 'text');
        $replyTo = (int) $this->input('reply_to_id', 0);
        if ($replyTo) {
            $ok = Database::fetch("SELECT 1 FROM messages WHERE id = ? AND conversation_id = ?", [$replyTo, $id]);
            if (!$ok) $replyTo = 0;
        }
        Database::insert('messages', [
            'conversation_id' => $id,
            'reply_to_id' => $replyTo ?: null,
            'direction' => 'outbound',
            'sender_type' => 'user',
            'sender_id' => $uid,
            'content' => $content ?: ($attachments[0]['name'] ?? ''),
            'content_type' => $contentType,
            'attachments' => $attachments ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null,
        ]);

        $updates = [
            'last_message_at' => date('Y-m-d H:i:s'),
            'last_message_preview' => mb_substr($content, 0, 255),
        ];
        // bump peer's unread counter
        Database::query(
            "UPDATE conversations SET last_message_at = NOW(), last_message_preview = ?, "
            . ($peerIsA ? 'unread_a = unread_a + 1' : 'unread_b = unread_b + 1')
            . " WHERE id = ?",
            [mb_substr($content, 0, 255), $id]
        );

        if ($this->isAjax()) {
            return $this->json(['success' => true]);
        }
        return $this->redirect('chat/internal?active=' . $id);
    }

    /** Poll endpoint: returns messages since the given message id. */
    public function internalPoll($id)
    {
        $tid = $this->tenantId();
        $uid = $this->userId();
        $after = (int) $this->input('after', 0);
        $dm = $this->loadDm((int)$id, $uid, $tid);
        if (!$dm) return $this->json(['error' => 'forbidden'], 403);

        $messages = Database::fetchAll(
            "SELECT m.*, u.name as sender_name, u.avatar as sender_avatar
             FROM messages m LEFT JOIN users u ON m.sender_id = u.id
             WHERE m.conversation_id = ? AND m.id > ?
             ORDER BY m.created_at ASC",
            [$id, $after]
        );
        $messages = $this->enrichMessages($messages, $uid);
        if ($messages) $this->markDmRead((int)$id, $uid);
        $peerLastRead = ($dm['user_a_id'] == $uid) ? ($dm['last_read_b_at'] ?? null) : ($dm['last_read_a_at'] ?? null);
        // Re-fetch to get fresh peer read timestamp (peer may have read while we were away)
        $fresh = Database::fetch("SELECT last_read_a_at, last_read_b_at FROM conversations WHERE id = ?", [$id]);
        if ($fresh) $peerLastRead = ($dm['user_a_id'] == $uid) ? $fresh['last_read_b_at'] : $fresh['last_read_a_at'];
        return $this->json(['messages' => $messages, 'my_id' => $uid, 'peer_last_read_at' => $peerLastRead]);
    }

    /** Private helpers */
    private function loadDm(int $id, int $uid, int $tid): ?array
    {
        return Database::fetch(
            "SELECT * FROM conversations
             WHERE id = ? AND tenant_id = ? AND channel = 'internal'
             AND (user_a_id = ? OR user_b_id = ?)",
            [$id, $tid, $uid, $uid]
        );
    }

    private function markDmRead(int $id, int $uid): void
    {
        $col = Database::fetch("SELECT user_a_id FROM conversations WHERE id = ?", [$id]);
        if (!$col) return;
        if ($col['user_a_id'] == $uid) {
            Database::query("UPDATE conversations SET unread_a = 0, last_read_a_at = NOW() WHERE id = ?", [$id]);
        } else {
            Database::query("UPDATE conversations SET unread_b = 0, last_read_b_at = NOW() WHERE id = ?", [$id]);
        }
    }

    private function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    /** Global unread count across DMs + groups — used by topbar badge. */
    public function unreadTotal()
    {
        $tid = $this->tenantId();
        $uid = $this->userId();
        $dm = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM conversations
             WHERE tenant_id = ? AND channel = 'internal'
               AND ((user_a_id = ? AND unread_a > 0) OR (user_b_id = ? AND unread_b > 0))",
            [$tid, $uid, $uid]
        )['c'] ?? 0);
        $gr = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM conversation_members WHERE user_id = ? AND unread_count > 0",
            [$uid]
        )['c'] ?? 0);
        return $this->json(['total' => $dm + $gr]);
    }

    // ==========================================================
    // Phase 2: Groups, attachments, pin, search
    // ==========================================================

    /** Create a group chat with a name + picked members. */
    public function internalCreateGroup()
    {
        if (!$this->isPost()) return $this->redirect('chat/internal');
        $tid = $this->tenantId();
        $uid = $this->userId();

        $name = trim((string) $this->input('name', ''));
        $members = (array) ($this->input('members') ?? []);
        $members = array_values(array_unique(array_filter(array_map('intval', $members), fn($i) => $i > 0 && $i !== $uid)));

        if ($name === '' || count($members) < 2) {
            $this->setFlash('error', 'Cần tên nhóm và ít nhất 2 thành viên khác.');
            return $this->redirect('chat/internal');
        }

        // Validate members belong to tenant
        $ph = implode(',', array_fill(0, count($members), '?'));
        $valid = Database::fetchAll(
            "SELECT id FROM users WHERE id IN ({$ph}) AND tenant_id = ? AND is_active = 1",
            array_merge($members, [$tid])
        );
        $validIds = array_column($valid, 'id');
        if (count($validIds) !== count($members)) {
            $this->setFlash('error', 'Một số user không hợp lệ.');
            return $this->redirect('chat/internal');
        }

        $gid = Database::insert('conversations', [
            'tenant_id' => $tid,
            'channel' => 'group',
            'status' => 'open',
            'name' => mb_substr($name, 0, 100),
            'created_by' => $uid,
            'last_message_at' => date('Y-m-d H:i:s'),
        ]);

        // Creator is admin, others are members
        Database::query(
            "INSERT INTO conversation_members (conversation_id, user_id, role) VALUES (?, ?, 'admin')",
            [$gid, $uid]
        );
        foreach ($validIds as $mid) {
            Database::query(
                "INSERT IGNORE INTO conversation_members (conversation_id, user_id) VALUES (?, ?)",
                [$gid, $mid]
            );
        }

        return $this->redirect('chat/internal?active=' . $gid);
    }

    /** Send a message in a group thread. */
    public function internalGroupReply($id)
    {
        if (!$this->isPost()) return $this->redirect('chat/internal?active=' . (int)$id);
        $tid = $this->tenantId();
        $uid = $this->userId();
        $content = trim((string) $this->input('content', ''));

        // Handle optional file upload
        $attachments = [];
        if (!empty($_FILES['attachment']['tmp_name']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $att = $this->saveChatAttachment($_FILES['attachment'], (int)$id);
            if ($att) $attachments[] = $att;
        }

        if ($content === '' && empty($attachments)) {
            return $this->isAjax() ? $this->json(['error' => 'Empty']) : $this->redirect('chat/internal?active=' . (int)$id);
        }

        $group = $this->loadGroup((int)$id, $uid, $tid);
        if (!$group) {
            return $this->isAjax() ? $this->json(['error' => 'forbidden'], 403) : $this->redirect('chat/internal');
        }

        $contentType = !empty($attachments) && !empty($attachments[0]['is_image']) ? 'image' : (!empty($attachments) ? 'file' : 'text');
        $replyTo = (int) $this->input('reply_to_id', 0);
        if ($replyTo) {
            $ok = Database::fetch("SELECT 1 FROM messages WHERE id = ? AND conversation_id = ?", [$replyTo, $id]);
            if (!$ok) $replyTo = 0;
        }
        $newMsgId = Database::insert('messages', [
            'conversation_id' => $id,
            'reply_to_id' => $replyTo ?: null,
            'direction' => 'outbound',
            'sender_type' => 'user',
            'sender_id' => $uid,
            'content' => $content ?: ($attachments[0]['name'] ?? ''),
            'content_type' => $contentType,
            'attachments' => $attachments ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null,
        ]);

        // Save @mentions: trust only user IDs that belong to this group
        $mentionIds = array_filter(array_map('intval', (array) $this->input('mentions', [])));
        if ($mentionIds && $newMsgId) {
            $validMembers = Database::fetchAll(
                "SELECT user_id FROM conversation_members WHERE conversation_id = ? AND user_id IN (" . implode(',', array_fill(0, count($mentionIds), '?')) . ")",
                array_merge([(int)$id], $mentionIds)
            );
            foreach ($validMembers as $mm) {
                if ((int)$mm['user_id'] === $uid) continue;
                try {
                    Database::query(
                        "INSERT IGNORE INTO message_mentions (message_id, user_id) VALUES (?, ?)",
                        [$newMsgId, (int)$mm['user_id']]
                    );
                } catch (\Throwable $e) {}
            }
        }

        // Bump unread for every other member
        $preview = $content !== '' ? mb_substr($content, 0, 255) : ($attachments[0]['name'] ?? '📎');
        Database::query(
            "UPDATE conversations SET last_message_at = NOW(), last_message_preview = ? WHERE id = ?",
            [$preview, $id]
        );
        Database::query(
            "UPDATE conversation_members SET unread_count = unread_count + 1 WHERE conversation_id = ? AND user_id != ?",
            [$id, $uid]
        );

        return $this->isAjax() ? $this->json(['success' => true]) : $this->redirect('chat/internal?active=' . $id);
    }

    /** Upload-only endpoint for DM (adds attachment to a new message). */
    public function internalReplyWithAttachment($id)
    {
        return $this->internalReply($id); // DM path — internalReply already handles content
    }

    /** Pin / unpin a message. */
    public function togglePin($msgId)
    {
        if (!$this->isPost()) return $this->json(['error' => 'method'], 405);
        $tid = $this->tenantId();
        $uid = $this->userId();
        $msg = Database::fetch(
            "SELECT m.id, m.is_pinned, m.conversation_id, cv.channel, cv.user_a_id, cv.user_b_id
             FROM messages m JOIN conversations cv ON m.conversation_id = cv.id
             WHERE m.id = ? AND cv.tenant_id = ?",
            [$msgId, $tid]
        );
        if (!$msg) return $this->json(['error' => 'not_found'], 404);

        // Must be a member
        $ok = false;
        if ($msg['channel'] === 'internal') {
            $ok = ($msg['user_a_id'] == $uid || $msg['user_b_id'] == $uid);
        } elseif ($msg['channel'] === 'group') {
            $mem = Database::fetch("SELECT 1 FROM conversation_members WHERE conversation_id = ? AND user_id = ?", [$msg['conversation_id'], $uid]);
            $ok = (bool) $mem;
        }
        if (!$ok) return $this->json(['error' => 'forbidden'], 403);

        $newVal = $msg['is_pinned'] ? 0 : 1;
        Database::update('messages', ['is_pinned' => $newVal], 'id = ?', [$msgId]);
        return $this->json(['success' => true, 'is_pinned' => $newVal]);
    }

    /** Toggle a reaction (emoji) on a message. Membership enforced. */
    public function reactMessage($msgId)
    {
        if (!$this->isPost()) return $this->json(['error' => 'method'], 405);
        $tid = $this->tenantId();
        $uid = $this->userId();
        $emoji = trim((string) $this->input('emoji', ''));
        if ($emoji === '' || mb_strlen($emoji) > 16) return $this->json(['error' => 'invalid'], 400);
        if (!$this->canAccessMessage((int)$msgId, $uid, $tid)) return $this->json(['error' => 'forbidden'], 403);

        $existing = Database::fetch("SELECT id FROM message_reactions WHERE message_id = ? AND user_id = ? AND emoji = ?", [$msgId, $uid, $emoji]);
        if ($existing) {
            Database::query("DELETE FROM message_reactions WHERE id = ?", [$existing['id']]);
            $action = 'removed';
        } else {
            Database::query("INSERT INTO message_reactions (message_id, user_id, emoji) VALUES (?, ?, ?)", [$msgId, $uid, $emoji]);
            $action = 'added';
        }
        return $this->json(['success' => true, 'action' => $action, 'reactions' => $this->reactionsFor((int)$msgId, $uid)]);
    }

    /** Edit a message (only own, only within 5 minutes). */
    public function editMessage($msgId)
    {
        if (!$this->isPost()) return $this->json(['error' => 'method'], 405);
        $tid = $this->tenantId();
        $uid = $this->userId();
        $content = trim((string) $this->input('content', ''));
        if ($content === '') return $this->json(['error' => 'empty'], 400);

        $msg = Database::fetch(
            "SELECT m.id, m.sender_id, m.created_at, m.deleted_at, cv.tenant_id
             FROM messages m JOIN conversations cv ON m.conversation_id = cv.id
             WHERE m.id = ?",
            [$msgId]
        );
        if (!$msg || (int)$msg['tenant_id'] !== $tid) return $this->json(['error' => 'not_found'], 404);
        if ((int)$msg['sender_id'] !== $uid) return $this->json(['error' => 'forbidden'], 403);
        if ($msg['deleted_at']) return $this->json(['error' => 'deleted'], 400);
        if (strtotime($msg['created_at']) < time() - 300) return $this->json(['error' => 'expired', 'message' => 'Chỉ có thể sửa tin nhắn trong 5 phút đầu.'], 400);

        Database::update('messages', ['content' => $content, 'edited_at' => date('Y-m-d H:i:s')], 'id = ?', [$msgId]);
        return $this->json(['success' => true, 'content' => $content, 'edited_at' => date('Y-m-d H:i:s')]);
    }

    /** Soft-delete a message (only own). */
    public function deleteMessage($msgId)
    {
        if (!$this->isPost()) return $this->json(['error' => 'method'], 405);
        $tid = $this->tenantId();
        $uid = $this->userId();
        $msg = Database::fetch(
            "SELECT m.id, m.sender_id, cv.tenant_id
             FROM messages m JOIN conversations cv ON m.conversation_id = cv.id
             WHERE m.id = ?",
            [$msgId]
        );
        if (!$msg || (int)$msg['tenant_id'] !== $tid) return $this->json(['error' => 'not_found'], 404);
        if ((int)$msg['sender_id'] !== $uid) return $this->json(['error' => 'forbidden'], 403);

        Database::update('messages', ['deleted_at' => date('Y-m-d H:i:s'), 'content' => '', 'attachments' => null], 'id = ?', [$msgId]);
        return $this->json(['success' => true]);
    }

    /** Permission check: user can read/act on message if DM peer or group member. */
    private function canAccessMessage(int $msgId, int $uid, int $tid): bool
    {
        $r = Database::fetch(
            "SELECT cv.id, cv.channel, cv.user_a_id, cv.user_b_id, cv.tenant_id
             FROM messages m JOIN conversations cv ON m.conversation_id = cv.id
             WHERE m.id = ?",
            [$msgId]
        );
        if (!$r || (int)$r['tenant_id'] !== $tid) return false;
        if ($r['channel'] === 'internal') return ((int)$r['user_a_id'] === $uid || (int)$r['user_b_id'] === $uid);
        if ($r['channel'] === 'group') {
            return (bool) Database::fetch("SELECT 1 FROM conversation_members WHERE conversation_id = ? AND user_id = ?", [$r['id'], $uid]);
        }
        return false;
    }

    /** Fetch reactions for a message grouped by emoji with a my-reacted flag. */
    private function reactionsFor(int $msgId, int $uid): array
    {
        $rows = Database::fetchAll(
            "SELECT emoji, COUNT(*) as cnt, SUM(CASE WHEN user_id = ? THEN 1 ELSE 0 END) as mine
             FROM message_reactions WHERE message_id = ? GROUP BY emoji ORDER BY cnt DESC, emoji",
            [$uid, $msgId]
        );
        return array_map(fn($r) => ['emoji' => $r['emoji'], 'count' => (int)$r['cnt'], 'mine' => (int)$r['mine'] > 0], $rows);
    }

    /** Enrich a list of messages with reactions, mentions, reply snapshot. */
    public function enrichMessages(array $messages, int $uid): array
    {
        if (empty($messages)) return $messages;
        $ids = array_column($messages, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Reactions grouped per message
        $reactionRows = Database::fetchAll(
            "SELECT message_id, emoji, COUNT(*) as cnt, SUM(CASE WHEN user_id = ? THEN 1 ELSE 0 END) as mine
             FROM message_reactions WHERE message_id IN ($placeholders) GROUP BY message_id, emoji",
            array_merge([$uid], $ids)
        );
        $rxByMsg = [];
        foreach ($reactionRows as $r) {
            $rxByMsg[$r['message_id']][] = ['emoji' => $r['emoji'], 'count' => (int)$r['cnt'], 'mine' => (int)$r['mine'] > 0];
        }

        // Reply snapshots
        $replyIds = array_filter(array_column($messages, 'reply_to_id'));
        $replyMap = [];
        if ($replyIds) {
            $rph = implode(',', array_fill(0, count($replyIds), '?'));
            $replies = Database::fetchAll(
                "SELECT m.id, m.content, m.sender_id, m.deleted_at, u.name as sender_name
                 FROM messages m LEFT JOIN users u ON m.sender_id = u.id
                 WHERE m.id IN ($rph)",
                array_values($replyIds)
            );
            foreach ($replies as $r) $replyMap[$r['id']] = $r;
        }

        // Mentions: which user ids are tagged per message
        $mentionRows = Database::fetchAll(
            "SELECT message_id, user_id FROM message_mentions WHERE message_id IN ($placeholders)",
            $ids
        );
        $mentionMap = [];
        foreach ($mentionRows as $r) $mentionMap[$r['message_id']][] = (int)$r['user_id'];

        foreach ($messages as &$m) {
            $m['reactions'] = $rxByMsg[$m['id']] ?? [];
            $m['reply_to'] = null;
            if (!empty($m['reply_to_id']) && isset($replyMap[$m['reply_to_id']])) {
                $rp = $replyMap[$m['reply_to_id']];
                $m['reply_to'] = [
                    'id' => (int)$rp['id'],
                    'sender_name' => $rp['sender_name'] ?? '',
                    'preview' => $rp['deleted_at'] ? '[Đã thu hồi]' : mb_substr((string)$rp['content'], 0, 120),
                ];
            }
            $m['mentions'] = $mentionMap[$m['id']] ?? [];
            $m['is_mentioned'] = in_array($uid, $m['mentions'], true);
        }
        return $messages;
    }

    /** Full-text search across this user's conversations. */
    public function searchMessages()
    {
        $tid = $this->tenantId();
        $uid = $this->userId();
        $q = trim((string) $this->input('q', ''));
        if (mb_strlen($q) < 2) return $this->json(['results' => []]);

        $like = '%' . $q . '%';
        $results = Database::fetchAll(
            "SELECT m.id, m.conversation_id, m.content, m.created_at,
                    cv.channel, cv.name as group_name,
                    cv.user_a_id, cv.user_b_id,
                    u.name as sender_name
             FROM messages m
             JOIN conversations cv ON m.conversation_id = cv.id
             LEFT JOIN users u ON m.sender_id = u.id
             LEFT JOIN conversation_members cm ON cm.conversation_id = cv.id AND cm.user_id = ?
             WHERE cv.tenant_id = ?
             AND (
                  (cv.channel = 'internal' AND (cv.user_a_id = ? OR cv.user_b_id = ?))
               OR (cv.channel = 'group' AND cm.user_id = ?)
             )
             AND m.content LIKE ?
             ORDER BY m.created_at DESC LIMIT 30",
            [$uid, $tid, $uid, $uid, $uid, $like]
        );
        return $this->json(['q' => $q, 'results' => $results]);
    }

    /** Update poll to also cover groups. */
    public function internalGroupPoll($id)
    {
        $tid = $this->tenantId();
        $uid = $this->userId();
        $after = (int) $this->input('after', 0);
        $group = $this->loadGroup((int)$id, $uid, $tid);
        if (!$group) return $this->json(['error' => 'forbidden'], 403);

        $messages = Database::fetchAll(
            "SELECT m.*, u.name as sender_name, u.avatar as sender_avatar
             FROM messages m LEFT JOIN users u ON m.sender_id = u.id
             WHERE m.conversation_id = ? AND m.id > ?
             ORDER BY m.created_at ASC",
            [$id, $after]
        );
        $messages = $this->enrichMessages($messages, $uid);
        if ($messages) {
            Database::query(
                "UPDATE conversation_members SET unread_count = 0, last_read_at = NOW() WHERE conversation_id = ? AND user_id = ?",
                [$id, $uid]
            );
            // Mark any mentions as read for this user
            $ids = array_column($messages, 'id');
            if ($ids) {
                $ph = implode(',', array_fill(0, count($ids), '?'));
                Database::query("UPDATE message_mentions SET is_read = 1 WHERE user_id = ? AND message_id IN ($ph)", array_merge([$uid], $ids));
            }
        }
        return $this->json(['messages' => $messages, 'my_id' => $uid]);
    }

    private function loadGroup(int $id, int $uid, int $tid): ?array
    {
        return Database::fetch(
            "SELECT cv.* FROM conversations cv
             JOIN conversation_members cm ON cm.conversation_id = cv.id AND cm.user_id = ?
             WHERE cv.id = ? AND cv.tenant_id = ? AND cv.channel = 'group'",
            [$uid, $id, $tid]
        );
    }

    /** Save uploaded attachment for chat — returns array {name, url, size, mime, is_image}. */
    private function saveChatAttachment(array $file, int $convId): ?array
    {
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) return null;
        $dir = BASE_PATH . '/public/uploads/chat/';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip','rar'];
        if (!in_array($ext, $allowed, true)) return null;
        $filename = 'c' . $convId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) return null;
        return [
            'name' => mb_substr($file['name'], 0, 200),
            'url' => '/uploads/chat/' . $filename,
            'size' => (int)$file['size'],
            'mime' => $file['type'] ?: '',
            'is_image' => in_array($ext, ['jpg','jpeg','png','gif','webp'], true),
        ];
    }
}
