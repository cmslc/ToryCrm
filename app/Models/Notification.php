<?php

namespace App\Models;

use Core\Model;
use Core\Database;

class Notification extends Model
{
    protected string $table = 'notifications';

    public function getForUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        $total = $this->count('user_id = ?', [$userId]);
        $offset = ($page - 1) * $perPage;

        $items = Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            [$userId]
        );

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    public function getUnread(int $userId, int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT {$limit}",
            [$userId]
        );
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->count('user_id = ? AND is_read = 0', [$userId]);
    }

    public function markAsRead(int $id): void
    {
        Database::query(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    public function markAllRead(int $userId): void
    {
        Database::query(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }

    public static function send(int $userId, string $type, string $title, ?string $message = null, ?string $link = null, string $icon = 'ri-notification-3-line'): int
    {
        return Database::insert('notifications', [
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'icon' => $icon,
        ]);
    }

    public function deleteOld(int $days = 30): int
    {
        return Database::delete(
            'notifications',
            'is_read = 1 AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
            [$days]
        );
    }
}
