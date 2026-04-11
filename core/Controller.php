<?php

namespace Core;

class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);

        $viewPath = BASE_PATH . '/resources/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            die("View not found: {$view}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = BASE_PATH . '/resources/views/layouts/app.php';
        if (file_exists($layoutPath) && !isset($noLayout)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function redirect(string $url): void
    {
        $url = '/' . ltrim($url, '/');
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: {$referer}");
        exit;
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function allInput(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function userId(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    protected function tenantId(): int
    {
        return (int) ($_SESSION['tenant_id'] ?? 1);
    }

    /**
     * Check if current user is admin or manager (can see all data).
     */
    protected function isAdminOrManager(): bool
    {
        $role = $_SESSION['user']['role'] ?? 'staff';
        return in_array($role, ['admin', 'manager']);
    }

    /**
     * Check if current user is department head (trưởng/phó phòng).
     * Returns array of user IDs in their department, or null if not a dept head.
     */
    protected function getDeptMemberIds(): ?array
    {
        static $cache = null;
        if ($cache !== null) return $cache ?: null;

        $uid = $this->userId();
        $deptId = $_SESSION['user']['department_id'] ?? null;
        if (!$deptId) {
            try {
                $u = Database::fetch("SELECT department_id FROM users WHERE id = ?", [$uid]);
                $deptId = $u['department_id'] ?? null;
                $_SESSION['user']['department_id'] = $deptId;
            } catch (\Exception $e) {}
        }

        if (!$deptId) { $cache = false; return null; }

        // Check if user is manager or vice_manager of their department
        try {
            $dept = Database::fetch(
                "SELECT manager_id, vice_manager_id FROM departments WHERE id = ?",
                [$deptId]
            );
            if (!$dept) { $cache = false; return null; }

            if ($dept['manager_id'] == $uid || $dept['vice_manager_id'] == $uid) {
                $members = Database::fetchAll(
                    "SELECT id FROM users WHERE department_id = ? AND is_active = 1",
                    [$deptId]
                );
                $cache = array_column($members, 'id');
                return $cache;
            }
        } catch (\Exception $e) {}

        $cache = false;
        return null;
    }

    /**
     * Get owner scope SQL for list queries.
     * Admin/Manager role: no filter (see all).
     * Dept head (trưởng/phó phòng): see department members' data.
     * Staff: only own records.
     * Returns ['where' => string, 'params' => array]
     */
    protected function ownerScope(string $alias = '', string $ownerField = 'owner_id'): array
    {
        if ($this->isAdminOrManager()) {
            return ['where' => '', 'params' => []];
        }

        $col = $alias ? "{$alias}.{$ownerField}" : $ownerField;

        // Department head: see all members' data
        $deptMembers = $this->getDeptMemberIds();
        if ($deptMembers && count($deptMembers) > 0) {
            $placeholders = implode(',', array_fill(0, count($deptMembers), '?'));
            return [
                'where' => "{$col} IN ({$placeholders})",
                'params' => $deptMembers,
            ];
        }

        // Regular staff: own data only
        return [
            'where' => "{$col} = ?",
            'params' => [$this->userId()],
        ];
    }

    /**
     * Check if current user can access a record owned by $ownerId.
     * Admin/Manager: always. Dept head: if owner is in same dept. Staff: only own.
     */
    protected function canAccessOwner(?int $ownerId): bool
    {
        if ($this->isAdminOrManager()) return true;
        if ($ownerId == $this->userId()) return true;
        $deptMembers = $this->getDeptMemberIds();
        if ($deptMembers && in_array($ownerId, $deptMembers)) return true;
        return false;
    }

    /**
     * Check permission. Redirects with error if denied.
     */
    protected function authorize(string $module, string $action): void
    {
        \App\Services\PermissionService::canOrFail($module, $action);
    }

    /**
     * Check permission. Returns bool without redirect.
     */
    protected function can(string $module, string $action): bool
    {
        return \App\Services\PermissionService::can($module, $action);
    }

    /**
     * Find a record by ID with automatic tenant scope.
     * Returns null if not found or belongs to different tenant.
     */
    protected function findSecure(string $table, int $id, string $alias = ''): ?array
    {
        $prefix = $alias ? "{$alias}." : '';
        $conditions = ["{$prefix}id = ?"];
        $params = [$id];

        // Add tenant scope for tenant-scoped tables
        if (Database::isTenantScoped($table)) {
            $conditions[] = "{$prefix}tenant_id = ?";
            $params[] = $this->tenantId();
        }

        // Add soft delete filter
        if (Database::hasSoftDelete($table)) {
            $conditions[] = "{$prefix}is_deleted = 0";
        }

        $where = implode(' AND ', $conditions);
        return Database::fetch("SELECT * FROM `{$table}` WHERE {$where}", $params);
    }

    /**
     * Sanitize orderBy to prevent SQL injection.
     * Only allows column names from whitelist.
     */
    protected function sanitizeOrderBy(string $orderBy, array $allowedColumns = ['id', 'created_at', 'updated_at', 'name', 'title']): string
    {
        $parts = explode(' ', trim($orderBy));
        $column = preg_replace('/[^a-zA-Z0-9_.]/', '', $parts[0] ?? 'id');
        $direction = strtoupper($parts[1] ?? 'DESC');

        if (!in_array($direction, ['ASC', 'DESC'])) $direction = 'DESC';

        // Extract base column name (remove alias prefix like "c.")
        $baseColumn = str_contains($column, '.') ? explode('.', $column)[1] : $column;

        if (!in_array($baseColumn, $allowedColumns)) {
            return 'id DESC';
        }

        return "{$column} {$direction}";
    }
}
