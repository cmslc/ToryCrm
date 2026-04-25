<?php

namespace App\Services;

use Core\Database;

/**
 * Activity Exchange Service
 * Plugin: activity-exchange
 */
class ActivityExchangeService
{
    /**
     * Load activities for any entity type (contact, quotation, order, contract, deal).
     */
    public static function getActivities(string $entityType, int $entityId, int $userId, int $limit = 50): array
    {
        $column = $entityType . '_id';

        $activities = Database::fetchAll(
            "SELECT a.*, u.name as user_name, u.avatar as user_avatar,
                    (SELECT COUNT(*) FROM activity_reactions WHERE activity_id = a.id AND type = 'like') as likes,
                    (SELECT COUNT(*) FROM activity_reactions WHERE activity_id = a.id AND type = 'dislike') as dislikes,
                    (SELECT type FROM activity_reactions WHERE activity_id = a.id AND user_id = ? LIMIT 1) as my_reaction
             FROM activities a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.{$column} = ? AND a.parent_id IS NULL
             ORDER BY a.created_at DESC
             LIMIT ?",
            [$userId, $entityId, $limit]
        );

        foreach ($activities as &$act) {
            $act['replies'] = Database::fetchAll(
                "SELECT a.*, u.name as user_name, u.avatar as user_avatar,
                        (SELECT COUNT(*) FROM activity_reactions WHERE activity_id = a.id AND type = 'like') as likes,
                        (SELECT COUNT(*) FROM activity_reactions WHERE activity_id = a.id AND type = 'dislike') as dislikes,
                        (SELECT type FROM activity_reactions WHERE activity_id = a.id AND user_id = ? LIMIT 1) as my_reaction
                 FROM activities a LEFT JOIN users u ON a.user_id = u.id
                 WHERE a.parent_id = ? ORDER BY a.created_at ASC",
                [$userId, $act['id']]
            );
        }
        unset($act);

        return $activities;
    }

    /**
     * Get all active users for @mention.
     */
    public static function getAllUsers(): array
    {
        return Database::fetchAll("SELECT id, name, avatar, department_id FROM users WHERE is_active = 1 ORDER BY name");
    }
}
