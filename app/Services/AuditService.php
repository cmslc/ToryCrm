<?php

namespace App\Services;

use Core\Database;

class AuditService
{
    /**
     * Log an audit entry.
     */
    public static function log(
        string $action,
        string $module,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $userId = $_SESSION['user']['id'] ?? null;

        Database::insert('audit_logs', [
            'user_id'    => $userId,
            'action'     => $action,
            'module'     => $module,
            'entity_id'  => $entityId,
            'old_values' => $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
            'new_values' => $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get audit logs for a specific entity with user names.
     */
    public static function getForEntity(string $module, int $entityId, int $limit = 20): array
    {
        $sql = "SELECT al.*, u.name as user_name
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.module = ? AND al.entity_id = ?
                ORDER BY al.created_at DESC
                LIMIT ?";

        return Database::fetchAll($sql, [$module, $entityId, $limit]);
    }

    /**
     * Compare old and new arrays, return only changed fields.
     * Only includes fields listed in $trackFields.
     */
    public static function diff(array $old, array $new, array $trackFields): array
    {
        $changes = [];

        foreach ($trackFields as $field) {
            $oldVal = $old[$field] ?? null;
            $newVal = $new[$field] ?? null;

            if ($oldVal != $newVal) {
                $changes[$field] = [
                    'old' => $oldVal,
                    'new' => $newVal
                ];
            }
        }

        return $changes;
    }
}
