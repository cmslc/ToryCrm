<?php
$currentUrl = trim($_GET['url'] ?? '', '/');
$user = $_SESSION['user'] ?? null;
$_branding = \App\Services\BrandingService::get();
$_brandName = $_branding['name'] ?? 'ToryCRM';
$_brandLogo = $_branding['logo_url'] ?? '';

function isMenuActive(string $path, string $currentUrl): bool {
    return $currentUrl === trim($path, '/') || str_starts_with($currentUrl, trim($path, '/') . '/');
}

// Determine which group is active based on current URL
function getActiveGroup(string $url): string {
    $map = [
        'crm' => ['contacts', 'companies', 'deals', 'checkins', 'bookings'],
        'sales' => ['products', 'orders', 'purchase-orders', 'quotations'],
        'marketing' => ['campaigns', 'email-templates'],
        'management' => ['tasks', 'calendar', 'tickets', 'sla', 'activities', 'workflows', 'leaderboard', 'achievements', 'approvals'],
        'finance' => ['fund', 'budgets', 'debts', 'contracts', 'commissions', 'finance-reports'],
        'system' => ['reports', 'import-export', 'automation', 'users', 'call-logs', 'integrations', 'webhooks', 'plugins', 'billing'],
        'settings' => ['settings', 'help', 'custom-fields'],
    ];
    foreach ($map as $group => $paths) {
        foreach ($paths as $p) {
            if ($url === $p || str_starts_with($url, $p . '/')) {
                return $group;
            }
        }
    }
    return '';
}

$activeGroup = getActiveGroup($currentUrl);
?>

<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="<?= url('dashboard') ?>" class="logo logo-dark">
            <?php if ($_brandLogo): ?>
                <span class="logo-sm"><img src="<?= e($_brandLogo) ?>" alt="<?= e($_brandName) ?>" height="22"></span>
                <span class="logo-lg"><img src="<?= e($_brandLogo) ?>" alt="<?= e($_brandName) ?>" height="28"></span>
            <?php else: ?>
                <span class="logo-sm"><i class="ri-customer-service-2-fill" style="font-size:22px;color:var(--vz-vertical-menu-item-active-color)"></i></span>
                <span class="logo-lg"><span style="font-size:17px;font-weight:700;color:var(--vz-vertical-menu-item-active-color)"><?= e($_brandName) ?></span></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('dashboard') ?>" class="logo logo-light">
            <?php if ($_brandLogo): ?>
                <span class="logo-sm"><img src="<?= e($_brandLogo) ?>" alt="<?= e($_brandName) ?>" height="22"></span>
                <span class="logo-lg"><img src="<?= e($_brandLogo) ?>" alt="<?= e($_brandName) ?>" height="28"></span>
            <?php else: ?>
                <span class="logo-sm"><i class="ri-customer-service-2-fill" style="font-size:22px;color:#fff"></i></span>
                <span class="logo-lg"><span style="font-size:17px;font-weight:700;color:#fff"><?= e($_brandName) ?></span></span>
            <?php endif; ?>
        </a>
        <button type="button" class="btn btn p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover"><i class="ri-record-circle-line"></i></button>
    </div>

    <div id="scrollbar" data-simplebar class="h-100">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">

                <!-- MENU (non-collapsible) -->
                <li class="menu-title"><span>Menu</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('dashboard', $currentUrl) ? 'active' : '' ?>" href="<?= url('dashboard') ?>">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('ai-chat', $currentUrl) ? 'active' : '' ?>" href="<?= url('ai-chat') ?>">
                        <i class="ri-robot-line"></i> <span>AI Trợ lý</span>
                    </a>
                </li>
                <?php
                    $convUnread = 0;
                    try { $convUnread = (int) (\Core\Database::fetch("SELECT COUNT(*) as cnt FROM conversations WHERE tenant_id = ? AND unread_count > 0", [$_SESSION['tenant_id'] ?? 1])['cnt'] ?? 0); } catch (\Throwable $e) {}
                ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('conversations', $currentUrl) ? 'active' : '' ?>" href="<?= url('conversations') ?>">
                        <i class="ri-chat-1-line"></i> <span>Hộp thư</span>
                        <?php if ($convUnread > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-auto"><?= $convUnread ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- CRM (collapsible) -->
                <li class="menu-title sidebar-group-toggle" data-bs-toggle="collapse" data-bs-target="#menu-crm" role="button" aria-expanded="<?= $activeGroup === 'crm' ? 'true' : 'false' ?>">
                    <span>CRM</span>
                    <i class="ri-arrow-down-s-line sidebar-group-arrow"></i>
                </li>
                <div class="collapse <?= $activeGroup === 'crm' ? 'show' : '' ?>" id="menu-crm">
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('contacts', $currentUrl) ? 'active' : '' ?>" href="<?= url('contacts') ?>">
                            <i class="ri-contacts-line"></i> <span>Khách hàng</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('companies', $currentUrl) ? 'active' : '' ?>" href="<?= url('companies') ?>">
                            <i class="ri-building-line"></i> <span>Doanh nghiệp</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('deals', $currentUrl) && !isMenuActive('deals/pipeline', $currentUrl) ? 'active' : '' ?>" href="<?= url('deals') ?>">
                            <i class="ri-hand-coin-line"></i> <span>Cơ hội</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('deals/pipeline', $currentUrl) ? 'active' : '' ?>" href="<?= url('deals/pipeline') ?>">
                            <i class="ri-git-branch-line"></i> <span>Pipeline</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('checkins', $currentUrl) ? 'active' : '' ?>" href="<?= url('checkins') ?>">
                            <i class="ri-map-pin-user-line"></i> <span>Check-in</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('bookings', $currentUrl) ? 'active' : '' ?>" href="<?= url('bookings') ?>">
                            <i class="ri-calendar-check-line"></i> <span>Đặt lịch hẹn</span>
                        </a>
                    </li>
                </div>

                <!-- BAN HANG (collapsible) -->
                <li class="menu-title sidebar-group-toggle" data-bs-toggle="collapse" data-bs-target="#menu-sales" role="button" aria-expanded="<?= $activeGroup === 'sales' ? 'true' : 'false' ?>">
                    <span>Bán hàng</span>
                    <i class="ri-arrow-down-s-line sidebar-group-arrow"></i>
                </li>
                <div class="collapse <?= $activeGroup === 'sales' ? 'show' : '' ?>" id="menu-sales">
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('products', $currentUrl) ? 'active' : '' ?>" href="<?= url('products') ?>">
                            <i class="ri-shopping-bag-line"></i> <span>Sản phẩm</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('orders', $currentUrl) ? 'active' : '' ?>" href="<?= url('orders') ?>">
                            <i class="ri-file-list-3-line"></i> <span>Đơn hàng bán</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('purchase-orders', $currentUrl) ? 'active' : '' ?>" href="<?= url('purchase-orders') ?>">
                            <i class="ri-shopping-cart-line"></i> <span>Đơn hàng mua</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('quotations', $currentUrl) ? 'active' : '' ?>" href="<?= url('quotations') ?>">
                            <i class="ri-file-text-line"></i> <span>Báo giá</span>
                        </a>
                    </li>
                </div>

                <!-- MARKETING (collapsible) -->
                <li class="menu-title sidebar-group-toggle" data-bs-toggle="collapse" data-bs-target="#menu-marketing" role="button" aria-expanded="<?= $activeGroup === 'marketing' ? 'true' : 'false' ?>">
                    <span>Marketing</span>
                    <i class="ri-arrow-down-s-line sidebar-group-arrow"></i>
                </li>
                <div class="collapse <?= $activeGroup === 'marketing' ? 'show' : '' ?>" id="menu-marketing">
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('campaigns', $currentUrl) ? 'active' : '' ?>" href="<?= url('campaigns') ?>">
                            <i class="ri-megaphone-line"></i> <span>Chiến dịch</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('email-templates', $currentUrl) ? 'active' : '' ?>" href="<?= url('email-templates') ?>">
                            <i class="ri-mail-settings-line"></i> <span>Email Templates</span>
                        </a>
                    </li>
                </div>

                <!-- QUAN LY (collapsible) -->
                <li class="menu-title sidebar-group-toggle" data-bs-toggle="collapse" data-bs-target="#menu-management" role="button" aria-expanded="<?= $activeGroup === 'management' ? 'true' : 'false' ?>">
                    <span>Quản lý</span>
                    <i class="ri-arrow-down-s-line sidebar-group-arrow"></i>
                </li>
                <div class="collapse <?= $activeGroup === 'management' ? 'show' : '' ?>" id="menu-management">
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('tasks', $currentUrl) ? 'active' : '' ?>" href="<?= url('tasks') ?>">
                            <i class="ri-task-line"></i> <span>Công việc</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('calendar', $currentUrl) ? 'active' : '' ?>" href="<?= url('calendar') ?>">
                            <i class="ri-calendar-2-line"></i> <span>Lịch hẹn</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('tickets', $currentUrl) ? 'active' : '' ?>" href="<?= url('tickets') ?>">
                            <i class="ri-customer-service-line"></i> <span>Ticket</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('sla', $currentUrl) ? 'active' : '' ?>" href="<?= url('sla') ?>">
                            <i class="ri-timer-line"></i> <span>Chính sách SLA</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('approvals', $currentUrl) ? 'active' : '' ?>" href="<?= url('approvals') ?>">
                            <i class="ri-checkbox-circle-line"></i> <span>Phê duyệt</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('activities', $currentUrl) ? 'active' : '' ?>" href="<?= url('activities') ?>">
                            <i class="ri-history-line"></i> <span>Hoạt động</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('workflows', $currentUrl) ? 'active' : '' ?>" href="<?= url('workflows') ?>">
                            <i class="ri-flow-chart"></i> <span>Workflow</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('leaderboard', $currentUrl) || isMenuActive('achievements', $currentUrl) ? 'active' : '' ?>" href="<?= url('leaderboard') ?>">
                            <i class="ri-trophy-line"></i> <span>Bảng xếp hạng</span>
                        </a>
                    </li>
                </div>

                <!-- TAI CHINH (collapsible) -->
                <li class="menu-title sidebar-group-toggle" data-bs-toggle="collapse" data-bs-target="#menu-finance" role="button" aria-expanded="<?= $activeGroup === 'finance' ? 'true' : 'false' ?>">
                    <span>Tài chính</span>
                    <i class="ri-arrow-down-s-line sidebar-group-arrow"></i>
                </li>
                <div class="collapse <?= $activeGroup === 'finance' ? 'show' : '' ?>" id="menu-finance">
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('fund', $currentUrl) ? 'active' : '' ?>" href="<?= url('fund') ?>">
                            <i class="ri-wallet-3-line"></i> <span>Quỹ</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('budgets', $currentUrl) ? 'active' : '' ?>" href="<?= url('budgets') ?>">
                            <i class="ri-wallet-line"></i> <span>Ngân sách</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('debts', $currentUrl) ? 'active' : '' ?>" href="<?= url('debts') ?>">
                            <i class="ri-money-dollar-circle-line"></i> <span>Công nợ</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('contracts', $currentUrl) ? 'active' : '' ?>" href="<?= url('contracts') ?>">
                            <i class="ri-file-shield-2-line"></i> <span>Hợp đồng</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('commissions', $currentUrl) ? 'active' : '' ?>" href="<?= url('commissions') ?>">
                            <i class="ri-percent-line"></i> <span>Hoa hồng</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('finance-reports', $currentUrl) ? 'active' : '' ?>" href="<?= url('finance-reports') ?>">
                            <i class="ri-line-chart-line"></i> <span>Báo cáo tài chính</span>
                        </a>
                    </li>
                </div>

                <!-- HE THONG (collapsible) -->
                <li class="menu-title sidebar-group-toggle" data-bs-toggle="collapse" data-bs-target="#menu-system" role="button" aria-expanded="<?= $activeGroup === 'system' ? 'true' : 'false' ?>">
                    <span>Hệ thống</span>
                    <i class="ri-arrow-down-s-line sidebar-group-arrow"></i>
                </li>
                <div class="collapse <?= $activeGroup === 'system' ? 'show' : '' ?>" id="menu-system">
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('reports', $currentUrl) ? 'active' : '' ?>" href="<?= url('reports') ?>">
                            <i class="ri-bar-chart-box-line"></i> <span>Báo cáo</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('import-export', $currentUrl) ? 'active' : '' ?>" href="<?= url('import-export') ?>">
                            <i class="ri-upload-cloud-line"></i> <span>Import / Export</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('users', $currentUrl) ? 'active' : '' ?>" href="<?= url('users') ?>">
                            <i class="ri-group-line"></i> <span>Người dùng</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('integrations', $currentUrl) ? 'active' : '' ?>" href="<?= url('integrations') ?>">
                            <i class="ri-links-line"></i> <span>Tích hợp</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('webhooks', $currentUrl) ? 'active' : '' ?>" href="<?= url('webhooks') ?>">
                            <i class="ri-webhook-line"></i> <span>Webhook</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('plugins', $currentUrl) ? 'active' : '' ?>" href="<?= url('plugins/marketplace') ?>">
                            <i class="ri-store-2-line"></i> <span>Marketplace</span>
                        </a>
                    </li>
                </div>

                <!-- CAI DAT (collapsible) -->
                <li class="menu-title sidebar-group-toggle" data-bs-toggle="collapse" data-bs-target="#menu-settings" role="button" aria-expanded="<?= $activeGroup === 'settings' ? 'true' : 'false' ?>">
                    <span>Cài đặt</span>
                    <i class="ri-arrow-down-s-line sidebar-group-arrow"></i>
                </li>
                <div class="collapse <?= $activeGroup === 'settings' ? 'show' : '' ?>" id="menu-settings">
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('settings', $currentUrl) && !isMenuActive('settings/white-label', $currentUrl) ? 'active' : '' ?>" href="<?= url('settings') ?>">
                            <i class="ri-settings-3-line"></i> <span>Cài đặt</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('custom-fields', $currentUrl) ? 'active' : '' ?>" href="<?= url('custom-fields') ?>">
                            <i class="ri-input-method-line"></i> <span>Trường tùy chỉnh</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('settings/white-label', $currentUrl) ? 'active' : '' ?>" href="<?= url('settings/white-label') ?>">
                            <i class="ri-palette-line"></i> <span>White-label</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?= isMenuActive('help', $currentUrl) ? 'active' : '' ?>" href="<?= url('help') ?>">
                            <i class="ri-question-line"></i> <span>Trợ giúp</span>
                        </a>
                    </li>
                </div>

            </ul>
        </div>
    </div>

    <?php if ($user): ?>
    <div class="sidebar-background"></div>
    <?php endif; ?>
</div>
<!-- Left Sidebar End -->
<div class="vertical-overlay"></div>

<!-- Sidebar collapse state management -->
<style>
.sidebar-group-toggle {
    cursor: pointer;
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    user-select: none;
}
.sidebar-group-toggle:hover {
    opacity: .85;
}
.sidebar-group-arrow {
    font-size: 14px;
    transition: transform .25s ease;
    margin-right: 4px;
}
.sidebar-group-toggle[aria-expanded="true"] .sidebar-group-arrow {
    transform: rotate(0deg);
}
.sidebar-group-toggle[aria-expanded="false"] .sidebar-group-arrow {
    transform: rotate(-90deg);
}
</style>
<script>
(function() {
    var STORAGE_KEY = 'torycrm_sidebar_collapsed';

    function getSavedState() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}; } catch(e) { return {}; }
    }

    function saveState(id, collapsed) {
        var state = getSavedState();
        state[id] = collapsed;
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    }

    // On load, restore saved state (but always expand active group)
    document.addEventListener('DOMContentLoaded', function() {
        var saved = getSavedState();
        var toggles = document.querySelectorAll('.sidebar-group-toggle[data-bs-target]');

        toggles.forEach(function(toggle) {
            var targetId = toggle.getAttribute('data-bs-target').replace('#', '');
            var targetEl = document.getElementById(targetId);
            if (!targetEl) return;

            // If this group has an active item, always show it
            var hasActive = targetEl.querySelector('.nav-link.active');
            if (hasActive) {
                targetEl.classList.add('show');
                toggle.setAttribute('aria-expanded', 'true');
                return;
            }

            // Otherwise, use saved state
            if (saved[targetId] === true) {
                targetEl.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Listen for collapse events to save state
        document.querySelectorAll('.collapse').forEach(function(el) {
            el.addEventListener('hidden.bs.collapse', function() {
                saveState(el.id, true);
            });
            el.addEventListener('shown.bs.collapse', function() {
                saveState(el.id, false);
            });
        });
    });
})();
</script>
