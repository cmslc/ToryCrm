<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Notification;

class ChatController extends Controller
{
    private array $allowedEntities = ['deal', 'ticket', 'contact', 'order'];

    public function getMessages($entityType, $entityId)
    {
        if (!in_array($entityType, $this->allowedEntities)) {
            return $this->json(['error' => 'Invalid entity type'], 400);
        }

        $messages = Database::fetchAll(
            "SELECT ic.*, u.name as user_name
             FROM internal_chats ic
             LEFT JOIN users u ON ic.user_id = u.id
             WHERE ic.entity_type = ? AND ic.entity_id = ? AND ic.tenant_id = ?
             ORDER BY ic.created_at ASC",
            [$entityType, $entityId, Database::tenantId()]
        );

        // Add avatar initial and time_ago
        foreach ($messages as &$msg) {
            $msg['avatar_initial'] = mb_strtoupper(mb_substr($msg['user_name'] ?? '?', 0, 1));
            $msg['time_ago'] = time_ago($msg['created_at']);
            // Highlight @mentions in content
            $msg['content_html'] = preg_replace(
                '/@(\w+)/',
                '<span class="text-primary fw-medium">@$1</span>',
                e($msg['content'])
            );
        }

        return $this->json(['messages' => $messages]);
    }

    public function postMessage($entityType, $entityId)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        if (!in_array($entityType, $this->allowedEntities)) {
            return $this->json(['error' => 'Invalid entity type'], 400);
        }

        $content = trim($this->input('content', ''));
        if (empty($content)) {
            return $this->json(['error' => 'Nội dung không được để trống'], 422);
        }

        $chatId = Database::insert('internal_chats', [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'user_id' => $this->userId(),
            'content' => $content,
        ]);

        // Parse @mentions
        preg_match_all('/@(\w+)/', $content, $matches);
        if (!empty($matches[1])) {
            $mentionedNames = array_unique($matches[1]);
            foreach ($mentionedNames as $name) {
                $user = Database::fetch(
                    "SELECT id, name FROM users WHERE REPLACE(name, ' ', '') LIKE ? AND tenant_id = ? AND is_active = 1 LIMIT 1",
                    ['%' . $name . '%', Database::tenantId()]
                );
                if ($user && $user['id'] !== $this->userId()) {
                    Database::insert('mentions', [
                        'chat_id' => $chatId,
                        'user_id' => $user['id'],
                    ]);

                    // Create notification
                    $currentUser = $this->user();
                    $entityLabel = $this->getEntityLabel($entityType);
                    Notification::send(
                        $user['id'],
                        'mention',
                        ($currentUser['name'] ?? 'Ai đó') . " đã nhắc bạn trong {$entityLabel}",
                        mb_substr($content, 0, 100),
                        "{$entityType}s/{$entityId}",
                        'ri-at-line'
                    );
                }
            }
        }

        // Return the created message
        $message = Database::fetch(
            "SELECT ic.*, u.name as user_name
             FROM internal_chats ic
             LEFT JOIN users u ON ic.user_id = u.id
             WHERE ic.id = ?",
            [$chatId]
        );
        $message['avatar_initial'] = mb_strtoupper(mb_substr($message['user_name'] ?? '?', 0, 1));
        $message['time_ago'] = time_ago($message['created_at']);
        $message['content_html'] = preg_replace(
            '/@(\w+)/',
            '<span class="text-primary fw-medium">@$1</span>',
            e($message['content'])
        );

        return $this->json(['success' => true, 'message' => $message]);
    }

    public function pinMessage($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $chat = Database::fetch(
            "SELECT * FROM internal_chats WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$chat) {
            return $this->json(['error' => 'Tin nhắn không tồn tại'], 404);
        }

        $newPinned = $chat['is_pinned'] ? 0 : 1;
        Database::update('internal_chats', ['is_pinned' => $newPinned], 'id = ?', [$id]);

        return $this->json(['success' => true, 'is_pinned' => $newPinned]);
    }

    public function deleteMessage($id)
    {
        if (!$this->isPost()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        $chat = Database::fetch(
            "SELECT * FROM internal_chats WHERE id = ? AND tenant_id = ?",
            [$id, Database::tenantId()]
        );

        if (!$chat) {
            return $this->json(['error' => 'Tin nhắn không tồn tại'], 404);
        }

        // Only owner or admin can delete
        $user = $this->user();
        if ($chat['user_id'] !== $this->userId() && ($user['role'] ?? '') !== 'admin') {
            return $this->json(['error' => 'Bạn không có quyền xóa tin nhắn này'], 403);
        }

        Database::delete('mentions', 'chat_id = ?', [$id]);
        Database::delete('internal_chats', 'id = ?', [$id]);

        return $this->json(['success' => true]);
    }

    public function searchUsers()
    {
        $q = trim($this->input('q', ''));

        $where = "tenant_id = ? AND is_active = 1";
        $params = [Database::tenantId()];

        if ($q !== '') {
            $where .= " AND name LIKE ?";
            $params[] = '%' . $q . '%';
        }

        $users = Database::fetchAll(
            "SELECT id, name FROM users WHERE {$where} ORDER BY name LIMIT 20",
            $params
        );

        return $this->json($users);
    }

    private function getEntityLabel(string $entityType): string
    {
        return match ($entityType) {
            'deal' => 'cơ hội',
            'ticket' => 'ticket',
            'contact' => 'khách hàng',
            'order' => 'đơn hàng',
            default => $entityType,
        };
    }
}
