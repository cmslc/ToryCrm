<?php

namespace App\Services;

use Core\Database;

/**
 * Append-only audit log for sensitive actions.
 * Write into the existing audit_logs table (tenant_id, user_id, action,
 * module, entity_id, description, old_values, new_values, ip, ua, created_at).
 */
class AuditLog
{
    public static function log(
        string $action,
        string $module = 'system',
        ?int $entityId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        try {
            Database::insert('audit_logs', [
                'tenant_id'   => $_SESSION['tenant_id'] ?? null,
                'user_id'     => $_SESSION['user']['id'] ?? null,
                'action'      => $action,
                'module'      => $module,
                'entity_id'   => $entityId,
                'description' => $description,
                'old_values'  => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'new_values'  => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'ip_address'  => function_exists('client_ip') ? client_ip() : ($_SERVER['REMOTE_ADDR'] ?? null),
                'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]);
        } catch (\Exception $e) {
            // Audit failures must not break the app; swallow silently
        }
    }

    public static function loginSuccess(int $userId, string $email): void
    {
        self::log('login', 'auth', $userId, "Đăng nhập thành công: {$email}");
    }

    public static function loginFailed(string $email): void
    {
        self::log('login_failed', 'auth', null, "Thất bại: {$email}");
    }

    public static function logout(int $userId): void
    {
        self::log('logout', 'auth', $userId);
    }

    public static function permissionChanged(int $groupId, string $groupName, array $before, array $after): void
    {
        self::log('update', 'permission_group', $groupId, "Đổi quyền: {$groupName}", ['perms' => $before], ['perms' => $after]);
    }

    public static function roleChanged(int $userId, string $before, string $after): void
    {
        self::log('role_change', 'user', $userId, "Đổi vai trò: {$before} → {$after}", ['role' => $before], ['role' => $after]);
    }

    public static function deleted(string $module, int $id, ?string $name = null): void
    {
        self::log('delete', $module, $id, $name ? "Xoá: {$name}" : null);
    }

    public static function exported(string $module, int $rowCount): void
    {
        self::log('export', $module, null, "Export {$rowCount} records");
    }
}
