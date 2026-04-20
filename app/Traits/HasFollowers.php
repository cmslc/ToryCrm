<?php

namespace App\Traits;

use Core\Database;

trait HasFollowers
{
    protected function handleFollowers(string $entityType, int $entityId): array
    {
        $userId = (int)($this->input('user_id') ?? 0);
        $action = $this->input('action');
        $table = $entityType . '_followers';
        $column = $entityType . '_id';

        if (!$userId) return ['error' => 'User ID required'];

        if ($action === 'add') {
            Database::query(
                "INSERT IGNORE INTO {$table} ({$column}, user_id) VALUES (?, ?)",
                [$entityId, $userId]
            );
            return ['success' => true];
        } elseif ($action === 'remove') {
            Database::query(
                "DELETE FROM {$table} WHERE {$column} = ? AND user_id = ?",
                [$entityId, $userId]
            );
            return ['success' => true];
        }

        return ['error' => 'Invalid action'];
    }
}
