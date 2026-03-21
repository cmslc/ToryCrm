<?php

namespace App\Middleware;

use Core\Database;

class TenantMiddleware
{
    /**
     * Resolve current tenant from:
     * 1. Session (already resolved)
     * 2. Custom domain (khachhang.com)
     * 3. Subdomain (khachhang.torycrm.com)
     * 4. Default tenant_id = 1 (single-tenant / localhost)
     */
    public function handle(): bool
    {
        // Already resolved
        if (!empty($_SESSION['tenant_id']) && !empty($_SESSION['tenant'])) {
            return true;
        }

        $tenant = null;
        $host = strtok($_SERVER['HTTP_HOST'] ?? '', ':');

        try {
            // 1. Try custom domain match (khachhang.com)
            if ($host && $host !== 'localhost') {
                $tenant = Database::fetch(
                    "SELECT * FROM tenants WHERE domain = ? AND is_active = 1 LIMIT 1",
                    [$host]
                );
            }

            // 2. Try subdomain match (khachhang.torycrm.com)
            if (!$tenant && $host) {
                $parts = explode('.', $host);
                if (count($parts) >= 3) {
                    $slug = $parts[0];
                    if (!in_array($slug, ['www', 'api', 'mail', 'admin', 'app'], true)) {
                        $tenant = Database::fetch(
                            "SELECT * FROM tenants WHERE slug = ? AND is_active = 1 LIMIT 1",
                            [$slug]
                        );
                    }
                }
            }

            // 3. Default: tenant_id = 1 (localhost, single-tenant)
            if (!$tenant) {
                $tenant = Database::fetch("SELECT * FROM tenants WHERE id = 1 LIMIT 1");
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet (pre-migration) - default to tenant 1
            $_SESSION['tenant_id'] = 1;
            $_SESSION['tenant'] = ['id' => 1, 'name' => 'Default', 'slug' => 'default'];
            return true;
        }

        if ($tenant) {
            $_SESSION['tenant_id'] = (int) $tenant['id'];
            $_SESSION['tenant'] = $tenant;

            // Check subscription active
            if ($tenant['plan_id']) {
                $sub = Database::fetch(
                    "SELECT * FROM subscriptions WHERE tenant_id = ? AND status IN ('active', 'trial') ORDER BY id DESC LIMIT 1",
                    [$tenant['id']]
                );
                $_SESSION['subscription'] = $sub;
            }

            return true;
        }

        // No tenant found - show error
        http_response_code(404);
        echo '<h1>Tổ chức không tồn tại</h1><p>Vui lòng kiểm tra lại URL.</p>';
        return false;
    }

    /**
     * Get current tenant_id (for use in queries)
     */
    public static function id(): int
    {
        return (int) ($_SESSION['tenant_id'] ?? 1);
    }

    /**
     * Get current tenant data
     */
    public static function tenant(): ?array
    {
        return $_SESSION['tenant'] ?? null;
    }

    /**
     * Check if current tenant has a specific feature
     * Based on plan features JSON
     */
    public static function hasFeature(string $feature): bool
    {
        $tenant = self::tenant();
        if (!$tenant || !$tenant['plan_id']) return true; // No plan = unlimited (trial/demo)

        $sub = $_SESSION['subscription'] ?? null;
        if (!$sub) return false;

        $plan = Database::fetch("SELECT features FROM plans WHERE id = ?", [$sub['plan_id']]);
        if (!$plan) return false;

        $features = json_decode($plan['features'] ?? '[]', true);
        return in_array($feature, $features);
    }

    /**
     * Check usage limits for current tenant
     */
    public static function checkLimit(string $resource): bool
    {
        $sub = $_SESSION['subscription'] ?? null;
        if (!$sub) return true; // No subscription = no limit (trial)

        $plan = Database::fetch("SELECT * FROM plans WHERE id = ?", [$sub['plan_id']]);
        if (!$plan) return true;

        $tenantId = self::id();

        switch ($resource) {
            case 'users':
                if ($plan['max_users'] < 0) return true; // -1 = unlimited
                $count = Database::fetch("SELECT COUNT(*) as c FROM users WHERE tenant_id = ? AND is_active = 1", [$tenantId])['c'];
                return $count < $plan['max_users'];

            case 'contacts':
                if ($plan['max_contacts'] < 0) return true;
                $count = Database::fetch("SELECT COUNT(*) as c FROM contacts WHERE tenant_id = ?", [$tenantId])['c'];
                return $count < $plan['max_contacts'];

            case 'deals':
                if ($plan['max_deals'] < 0) return true;
                $count = Database::fetch("SELECT COUNT(*) as c FROM deals WHERE tenant_id = ?", [$tenantId])['c'];
                return $count < $plan['max_deals'];

            default:
                return true;
        }
    }
}
