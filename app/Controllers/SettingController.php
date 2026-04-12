<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Helpers\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$this->userId()]);

        return $this->view('settings.index', [
            'user' => $user,
        ]);
    }

    public function updateProfile()
    {
        if (!$this->isPost()) {
            return $this->redirect('settings');
        }

        $data = $this->allInput();

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');

        if (empty($name) || empty($email)) {
            $this->setFlash('error', 'Name and email are required.');
            return $this->back();
        }

        // Check if email is taken by another user
        $existing = Database::fetch(
            "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1",
            [$email, $this->userId()]
        );

        if ($existing) {
            $this->setFlash('error', 'This email is already in use by another account.');
            return $this->back();
        }

        Database::update('users', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ], 'id = ?', [$this->userId()]);

        $this->setFlash('success', 'Profile updated successfully.');
        return $this->redirect('settings');
    }

    public function updateAvatar()
    {
        if (!$this->isPost()) return $this->redirect('settings');

        $user = Database::fetch("SELECT avatar FROM users WHERE id = ?", [$this->userId()]);
        $filename = upload_avatar('avatar', 'avatars', $user['avatar'] ?? null);

        if ($filename) {
            Database::update('users', ['avatar' => $filename], 'id = ?', [$this->userId()]);
            $_SESSION['user']['avatar'] = $filename;
            $this->setFlash('success', 'Đã cập nhật ảnh đại diện.');
        } else {
            $this->setFlash('error', 'Không thể tải ảnh. Chỉ chấp nhận JPG, PNG, GIF, WebP (tối đa 5MB).');
        }

        return $this->redirect('settings');
    }

    public function updatePassword()
    {
        if (!$this->isPost()) {
            return $this->redirect('settings');
        }

        $currentPassword = $this->input('current_password');
        $newPassword = $this->input('new_password');
        $confirmPassword = $this->input('confirm_password');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->setFlash('error', 'All password fields are required.');
            return $this->back();
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'New passwords do not match.');
            return $this->back();
        }

        if (strlen($newPassword) < 8) {
            $this->setFlash('error', 'New password must be at least 8 characters.');
            return $this->back();
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$this->userId()]);

        if (!Auth::verifyPassword($currentPassword, $user['password'])) {
            $this->setFlash('error', 'Current password is incorrect.');
            return $this->back();
        }

        $hashedPassword = Auth::hashPassword($newPassword);

        Database::update('users', [
            'password' => $hashedPassword,
        ], 'id = ?', [$this->userId()]);

        $this->setFlash('success', 'Password updated successfully.');
        return $this->redirect('settings');
    }

    // ---- Dashboard Widgets ----
    public function widgets()
    {
        $widgets = [
            'stats_cards' => 'Thẻ thống kê tổng quan',
            'revenue_chart' => 'Biểu đồ doanh thu',
            'pipeline_summary' => 'Tổng quan Pipeline',
            'recent_contacts' => 'Khách hàng mới',
            'recent_activities' => 'Hoạt động gần đây',
            'overdue_tasks' => 'Công việc quá hạn',
            'task_chart' => 'Biểu đồ công việc',
            'today_events' => 'Lịch hẹn hôm nay',
            'orders_stats' => 'Thống kê đơn hàng',
        ];

        $userSettings = Database::fetchAll(
            "SELECT * FROM user_widget_settings WHERE user_id = ? ORDER BY sort_order",
            [$this->userId()]
        );

        $settingsMap = [];
        foreach ($userSettings as $s) {
            $settingsMap[$s['widget_key']] = $s;
        }

        return $this->view('settings.widgets', [
            'widgets' => $widgets,
            'settingsMap' => $settingsMap,
        ]);
    }

    public function saveWidgets()
    {
        if (!$this->isPost()) return $this->redirect('settings/widgets');

        $data = $this->allInput();
        $widgetKeys = $data['widgets'] ?? [];
        $orders = $data['sort_order'] ?? [];

        Database::delete('user_widget_settings', 'user_id = ?', [$this->userId()]);

        $allWidgets = ['stats_cards','revenue_chart','pipeline_summary','recent_contacts','recent_activities','overdue_tasks','task_chart','today_events','orders_stats'];
        foreach ($allWidgets as $i => $key) {
            Database::insert('user_widget_settings', [
                'user_id' => $this->userId(),
                'widget_key' => $key,
                'is_visible' => in_array($key, $widgetKeys) ? 1 : 0,
                'sort_order' => (int)($orders[$key] ?? $i),
            ]);
        }

        $this->setFlash('success', 'Đã lưu cài đặt Dashboard.');
        return $this->redirect('settings/widgets');
    }

    // ---- API Keys ----
    public function apiKeys()
    {
        $keys = Database::fetchAll(
            "SELECT * FROM api_keys WHERE user_id = ? ORDER BY created_at DESC",
            [$this->userId()]
        );

        return $this->view('settings.api-keys', ['keys' => $keys]);
    }

    public function createApiKey()
    {
        if (!$this->isPost()) return $this->redirect('settings/api-keys');

        $name = trim($this->input('name') ?? '');
        if (empty($name)) {
            $this->setFlash('error', 'Tên API key không được để trống.');
            return $this->back();
        }

        $apiKey = bin2hex(random_bytes(30));

        Database::insert('api_keys', [
            'name' => $name,
            'api_key' => $apiKey,
            'user_id' => $this->userId(),
            'rate_limit' => (int)($this->input('rate_limit') ?: 100),
        ]);

        $this->setFlash('success', "API Key đã tạo: {$apiKey}");
        return $this->redirect('settings/api-keys');
    }

    public function deleteApiKey($id)
    {
        Database::delete('api_keys', 'id = ? AND user_id = ?', [$id, $this->userId()]);
        $this->setFlash('success', 'Đã xóa API Key.');
        return $this->redirect('settings/api-keys');
    }

    // ---- Permissions ----
    public function permissions()
    {
        $permissions = Database::fetchAll("SELECT * FROM permissions ORDER BY module, action");
        $roles = ['admin', 'manager', 'staff'];
        $rolePerms = [];
        foreach ($roles as $role) {
            $perms = Database::fetchAll(
                "SELECT permission_id FROM role_permissions WHERE role = ?", [$role]
            );
            $rolePerms[$role] = array_column($perms, 'permission_id');
        }

        return $this->view('settings.permissions', [
            'permissions' => $permissions,
            'roles' => $roles,
            'rolePerms' => $rolePerms,
        ]);
    }

    public function savePermissions()
    {
        if (!$this->isPost()) return $this->redirect('settings/permissions');

        $data = $this->allInput();
        $roles = ['manager', 'staff']; // admin always has all

        foreach ($roles as $role) {
            Database::delete('role_permissions', 'role = ?', [$role]);
            $permIds = $data['perms'][$role] ?? [];
            foreach ($permIds as $permId) {
                Database::insert('role_permissions', [
                    'role' => $role,
                    'permission_id' => (int)$permId,
                ]);
            }
        }

        // Ensure admin has all
        Database::delete('role_permissions', 'role = ?', ['admin']);
        $allPerms = Database::fetchAll("SELECT id FROM permissions");
        foreach ($allPerms as $p) {
            Database::insert('role_permissions', ['role' => 'admin', 'permission_id' => $p['id']]);
        }

        $this->setFlash('success', 'Đã cập nhật phân quyền.');
        return $this->redirect('settings/permissions');
    }

    // ---- Audit Log ----
    public function auditLog()
    {
        $page = max(1, (int) $this->input('page') ?: 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $module = $this->input('module');
        $where = '1=1';
        $params = [];
        if ($module) {
            $where .= " AND al.module = ?";
            $params[] = $module;
        }

        $total = Database::fetch("SELECT COUNT(*) as c FROM audit_logs al WHERE {$where}", $params)['c'];
        $logs = Database::fetchAll(
            "SELECT al.*, u.name as user_name
             FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id
             WHERE {$where} ORDER BY al.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return $this->view('settings.audit-log', [
            'logs' => ['items' => $logs, 'total' => $total, 'page' => $page, 'total_pages' => ceil($total / $perPage)],
            'filters' => ['module' => $module],
        ]);
    }

    public function ai()
    {
        $tenant = Database::fetch("SELECT settings FROM tenants WHERE id = ?", [$this->tenantId()]);
        $settings = json_decode($tenant['settings'] ?? '{}', true);
        $aiConfig = $settings['ai'] ?? [];

        $activeProvider = $aiConfig['provider'] ?? 'groq';

        return $this->view('settings.api', [
            'aiConfig' => $aiConfig,
            'activeProvider' => $activeProvider,
        ]);
    }

    public function saveAi()
    {
        if (!$this->isPost()) return $this->redirect('settings/api');

        $deepseekKey = trim($this->input('deepseek_api_key') ?? '');
        $openrouterKey = trim($this->input('openrouter_api_key') ?? '');
        $groqKey = trim($this->input('groq_api_key') ?? '');
        $geminiKey = trim($this->input('gemini_api_key') ?? '');
        $gmapsKey = trim($this->input('google_maps_api_key') ?? '');

        // Save all keys to .env file
        $envPath = BASE_PATH . '/.env';
        $envContent = file_get_contents($envPath);

        foreach (['DEEPSEEK_API_KEY' => $deepseekKey, 'OPENROUTER_API_KEY' => $openrouterKey, 'GROQ_API_KEY' => $groqKey, 'GEMINI_API_KEY' => $geminiKey, 'GOOGLE_MAPS_API_KEY' => $gmapsKey] as $envKey => $envVal) {
            if (str_contains($envContent, $envKey . '=')) {
                $envContent = preg_replace('/' . $envKey . '=.*/', $envKey . '=' . $envVal, $envContent);
            } else {
                $envContent .= "\n" . $envKey . "=" . $envVal;
            }
        }
        file_put_contents($envPath, $envContent);

        // Update $_ENV for current request
        $_ENV['DEEPSEEK_API_KEY'] = $deepseekKey;
        $_ENV['OPENROUTER_API_KEY'] = $openrouterKey;
        $_ENV['GROQ_API_KEY'] = $groqKey;
        $_ENV['GEMINI_API_KEY'] = $geminiKey;
        $_ENV['GOOGLE_MAPS_API_KEY'] = $gmapsKey;

        // Save toggle states to tenant settings
        $enabledInput = $this->input('api_enabled') ?? [];
        $apiEnabled = [
            'deepseek' => isset($enabledInput['ds']),
            'openrouter' => isset($enabledInput['or']),
            'groq' => isset($enabledInput['gq']),
            'gemini' => isset($enabledInput['gm']),
            'google_maps' => isset($enabledInput['mp']),
        ];
        $tenant = Database::fetch("SELECT settings FROM tenants WHERE id = ?", [$this->tenantId()]);
        $settings = json_decode($tenant['settings'] ?? '{}', true);
        $settings['ai']['api_enabled'] = $apiEnabled;
        Database::update('tenants', ['settings' => json_encode($settings)], 'id = ?', [$this->tenantId()]);

        $this->setFlash('success', 'Đã lưu cấu hình AI.');
        return $this->redirect('settings/api');
    }

    public function saveAiBehavior()
    {
        if (!$this->isPost()) return $this->redirect('settings/api');

        $tenant = Database::fetch("SELECT settings FROM tenants WHERE id = ?", [$this->tenantId()]);
        $settings = json_decode($tenant['settings'] ?? '{}', true);

        $settings['ai']['system_prompt'] = trim($this->input('system_prompt') ?? '');
        $settings['ai']['temperature'] = (int) ($this->input('temperature') ?? 7);
        $settings['ai']['max_tokens'] = (int) ($this->input('max_tokens') ?? 500);
        $settings['ai']['include_crm_context'] = $this->input('include_crm_context') ? true : false;
        $settings['ai']['show_widget'] = $this->input('show_widget') ? true : false;

        Database::update('tenants', ['settings' => json_encode($settings)], 'id = ?', [$this->tenantId()]);

        $this->setFlash('success', 'Đã lưu cấu hình hành vi AI.');
        return $this->redirect('settings/api');
    }
}
