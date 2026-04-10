<?php
$currentUrl = trim($_GET['url'] ?? '', '/');
$user = $_SESSION['user'] ?? null;
$_branding = \App\Services\BrandingService::get();
$_brandName = $_branding['name'] ?? 'ToryCRM';
$_brandLogo = $_branding['logo_url'] ?? '';
$_role = $user['role'] ?? 'staff';

function isActive(string $path, string $cur): string {
    return ($cur === trim($path, '/') || str_starts_with($cur, trim($path, '/') . '/')) ? 'active' : '';
}
function isOpen(array $paths, string $cur): bool {
    foreach ($paths as $p) { if ($cur === $p || str_starts_with($cur, $p . '/')) return true; }
    return false;
}
function canSee(string $module, string $action = 'view'): bool {
    return \App\Services\PermissionService::can($module, $action);
}

$convUnread = 0;
try { $convUnread = (int) (\Core\Database::fetch("SELECT COUNT(*) as cnt FROM conversations WHERE tenant_id = ? AND unread_count > 0", [$_SESSION['tenant_id'] ?? 1])['cnt'] ?? 0); } catch (\Throwable $e) {}
?>

<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="<?= url('dashboard') ?>" class="logo logo-dark">
            <?php if ($_brandLogo): ?>
                <span class="logo-sm"><img src="<?= e($_brandLogo) ?>" alt="" height="22"></span>
                <span class="logo-lg"><img src="<?= e($_brandLogo) ?>" alt="" height="28"></span>
            <?php else: ?>
                <span class="logo-sm"><i class="ri-customer-service-2-fill" style="font-size:22px;color:var(--vz-vertical-menu-item-active-color)"></i></span>
                <span class="logo-lg"><span style="font-size:17px;font-weight:700;color:var(--vz-vertical-menu-item-active-color)"><?= e($_brandName) ?></span></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('dashboard') ?>" class="logo logo-light">
            <?php if ($_brandLogo): ?>
                <span class="logo-sm"><img src="<?= e($_brandLogo) ?>" alt="" height="22"></span>
                <span class="logo-lg"><img src="<?= e($_brandLogo) ?>" alt="" height="28"></span>
            <?php else: ?>
                <span class="logo-sm"><i class="ri-customer-service-2-fill" style="font-size:22px;color:#fff"></i></span>
                <span class="logo-lg"><span style="font-size:17px;font-weight:700;color:#fff"><?= e($_brandName) ?></span></span>
            <?php endif; ?>
        </a>
        <button type="button" class="btn btn-link p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover"><i class="ri-record-circle-line"></i></button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">

                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('dashboard', $currentUrl) ?>" href="<?= url('dashboard') ?>">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('ai-chat', $currentUrl) ?>" href="<?= url('ai-chat') ?>">
                        <i class="ri-robot-line"></i> <span>AI Trợ lý</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('conversations', $currentUrl) ?>" href="<?= url('conversations') ?>">
                        <i class="ri-chat-1-line"></i> <span>Hộp thư</span>
                        <?php if ($convUnread > 0): ?><span class="badge badge-pill bg-danger" data-key="t-new"><?= $convUnread ?></span><?php endif; ?>
                    </a>
                </li>

                <?php if (canSee('contacts')): ?>
                <li class="menu-title"><span>CRM</span></li>
                <?php $crmOpen = isOpen(['contacts','companies','deals','checkins','bookings'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $crmOpen ? '' : 'collapsed' ?>" href="#sidebarCrm" data-bs-toggle="collapse" role="button" aria-expanded="<?= $crmOpen ? 'true' : 'false' ?>">
                        <i class="ri-contacts-line"></i> <span>Khách hàng</span>
                    </a>
                    <div class="collapse <?= $crmOpen ? 'show' : '' ?>" id="sidebarCrm">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('contacts') ?>" class="nav-link <?= isActive('contacts', $currentUrl) ?>">Danh sách KH</a></li>
                            <?php if (canSee('companies')): ?><li class="nav-item"><a href="<?= url('companies') ?>" class="nav-link <?= isActive('companies', $currentUrl) ?>">Doanh nghiệp</a></li><?php endif; ?>
                            <li class="nav-item"><a href="<?= url('checkins') ?>" class="nav-link <?= isActive('checkins', $currentUrl) ?>">Check-in</a></li>
                            <li class="nav-item"><a href="<?= url('bookings') ?>" class="nav-link <?= isActive('bookings', $currentUrl) ?>">Đặt lịch hẹn</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (canSee('deals')): ?>
                <?php $dealOpen = isOpen(['deals','deals/pipeline','deals/forecast'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $dealOpen ? '' : 'collapsed' ?>" href="#sidebarDeals" data-bs-toggle="collapse" role="button" aria-expanded="<?= $dealOpen ? 'true' : 'false' ?>">
                        <i class="ri-hand-coin-line"></i> <span>Cơ hội</span>
                    </a>
                    <div class="collapse <?= $dealOpen ? 'show' : '' ?>" id="sidebarDeals">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('deals') ?>" class="nav-link <?= isActive('deals', $currentUrl) && !str_contains($currentUrl, 'pipeline') && !str_contains($currentUrl, 'forecast') ? 'active' : '' ?>">Danh sách</a></li>
                            <li class="nav-item"><a href="<?= url('deals/pipeline') ?>" class="nav-link <?= isActive('deals/pipeline', $currentUrl) ?>">Pipeline</a></li>
                            <li class="nav-item"><a href="<?= url('deals/forecast') ?>" class="nav-link <?= isActive('deals/forecast', $currentUrl) ?>">Dự báo</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (canSee('products') || canSee('orders')): ?>
                <li class="menu-title"><span>Bán hàng</span></li>
                <?php if (canSee('products')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('products', $currentUrl) ?>" href="<?= url('products') ?>">
                        <i class="ri-shopping-bag-line"></i> <span>Sản phẩm</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if (canSee('orders')): ?>
                <?php $orderOpen = isOpen(['orders','purchase-orders','quotations'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $orderOpen ? '' : 'collapsed' ?>" href="#sidebarOrders" data-bs-toggle="collapse" role="button" aria-expanded="<?= $orderOpen ? 'true' : 'false' ?>">
                        <i class="ri-file-list-3-line"></i> <span>Đơn hàng</span>
                    </a>
                    <div class="collapse <?= $orderOpen ? 'show' : '' ?>" id="sidebarOrders">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('orders') ?>" class="nav-link <?= isActive('orders', $currentUrl) ?>">Đơn hàng bán</a></li>
                            <li class="nav-item"><a href="<?= url('purchase-orders') ?>" class="nav-link <?= isActive('purchase-orders', $currentUrl) ?>">Đơn hàng mua</a></li>
                            <li class="nav-item"><a href="<?= url('quotations') ?>" class="nav-link <?= isActive('quotations', $currentUrl) ?>">Báo giá</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
                <?php endif; ?>

                <?php if (canSee('campaigns')): ?>
                <li class="menu-title"><span>Marketing</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('campaigns', $currentUrl) ?>" href="<?= url('campaigns') ?>">
                        <i class="ri-megaphone-line"></i> <span>Chiến dịch</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('email-templates', $currentUrl) ?>" href="<?= url('email-templates') ?>">
                        <i class="ri-mail-settings-line"></i> <span>Email Templates</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="menu-title"><span>Quản lý</span></li>

                <?php if (canSee('tasks')): ?>
                <?php $taskOpen = isOpen(['tasks','calendar','activities'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $taskOpen ? '' : 'collapsed' ?>" href="#sidebarTasks" data-bs-toggle="collapse" role="button" aria-expanded="<?= $taskOpen ? 'true' : 'false' ?>">
                        <i class="ri-task-line"></i> <span>Công việc</span>
                    </a>
                    <div class="collapse <?= $taskOpen ? 'show' : '' ?>" id="sidebarTasks">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('tasks') ?>" class="nav-link <?= isActive('tasks', $currentUrl) && !str_contains($currentUrl, 'kanban') ? 'active' : '' ?>">Danh sách</a></li>
                            <li class="nav-item"><a href="<?= url('tasks/kanban') ?>" class="nav-link <?= isActive('tasks/kanban', $currentUrl) ?>">Kanban</a></li>
                            <li class="nav-item"><a href="<?= url('calendar') ?>" class="nav-link <?= isActive('calendar', $currentUrl) ?>">Lịch hẹn</a></li>
                            <li class="nav-item"><a href="<?= url('activities') ?>" class="nav-link <?= isActive('activities', $currentUrl) ?>">Hoạt động</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (canSee('tickets')): ?>
                <?php $supportOpen = isOpen(['tickets','sla'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $supportOpen ? '' : 'collapsed' ?>" href="#sidebarSupport" data-bs-toggle="collapse" role="button" aria-expanded="<?= $supportOpen ? 'true' : 'false' ?>">
                        <i class="ri-customer-service-line"></i> <span>Hỗ trợ</span>
                    </a>
                    <div class="collapse <?= $supportOpen ? 'show' : '' ?>" id="sidebarSupport">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('tickets') ?>" class="nav-link <?= isActive('tickets', $currentUrl) ?>">Ticket</a></li>
                            <li class="nav-item"><a href="<?= url('sla') ?>" class="nav-link <?= isActive('sla', $currentUrl) ?>">Chính sách SLA</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (canSee('automation')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('workflows', $currentUrl) ?>" href="<?= url('workflows') ?>">
                        <i class="ri-flow-chart"></i> <span>Workflow</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('approvals', $currentUrl) ?>" href="<?= url('approvals/pending') ?>">
                        <i class="ri-checkbox-circle-line"></i> <span>Phê duyệt</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('leaderboard', $currentUrl) || isActive('achievements', $currentUrl) ? 'active' : '' ?>" href="<?= url('leaderboard') ?>">
                        <i class="ri-trophy-line"></i> <span>Bảng xếp hạng</span>
                    </a>
                </li>

                <?php if (canSee('fund')): ?>
                <li class="menu-title"><span>Tài chính</span></li>
                <?php $financeOpen = isOpen(['fund','debts','contracts','budgets','commissions','finance-reports'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $financeOpen ? '' : 'collapsed' ?>" href="#sidebarFinance" data-bs-toggle="collapse" role="button" aria-expanded="<?= $financeOpen ? 'true' : 'false' ?>">
                        <i class="ri-money-dollar-circle-line"></i> <span>Tài chính</span>
                    </a>
                    <div class="collapse <?= $financeOpen ? 'show' : '' ?>" id="sidebarFinance">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('fund') ?>" class="nav-link <?= isActive('fund', $currentUrl) ?>">Quỹ thu/chi</a></li>
                            <li class="nav-item"><a href="<?= url('debts') ?>" class="nav-link <?= isActive('debts', $currentUrl) ?>">Công nợ</a></li>
                            <li class="nav-item"><a href="<?= url('contracts') ?>" class="nav-link <?= isActive('contracts', $currentUrl) ?>">Hợp đồng</a></li>
                            <li class="nav-item"><a href="<?= url('budgets') ?>" class="nav-link <?= isActive('budgets', $currentUrl) ?>">Ngân sách</a></li>
                            <li class="nav-item"><a href="<?= url('commissions') ?>" class="nav-link <?= isActive('commissions', $currentUrl) ?>">Hoa hồng</a></li>
                            <li class="nav-item"><a href="<?= url('finance-reports') ?>" class="nav-link <?= isActive('finance-reports', $currentUrl) ?>">Báo cáo tài chính</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if ($_role !== 'staff'): ?>
                <li class="menu-title"><span>Hệ thống</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('departments', $currentUrl) ?>" href="<?= url('departments') ?>">
                        <i class="ri-organization-chart"></i> <span>Phòng ban</span>
                    </a>
                </li>

                <?php if (canSee('reports') || canSee('users') || canSee('automation') || canSee('webhooks')): ?>
                <?php $sysOpen = isOpen(['reports','import-export','automation','users','call-logs','integrations','webhooks','plugins','duplicates','billing'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $sysOpen ? '' : 'collapsed' ?>" href="#sidebarSystem" data-bs-toggle="collapse" role="button" aria-expanded="<?= $sysOpen ? 'true' : 'false' ?>">
                        <i class="ri-settings-3-line"></i> <span>Hệ thống</span>
                    </a>
                    <div class="collapse <?= $sysOpen ? 'show' : '' ?>" id="sidebarSystem">
                        <ul class="nav nav-sm flex-column">
                            <?php if (canSee('reports')): ?><li class="nav-item"><a href="<?= url('reports') ?>" class="nav-link <?= isActive('reports', $currentUrl) ?>">Báo cáo</a></li><?php endif; ?>
                            <?php if (canSee('users')): ?><li class="nav-item"><a href="<?= url('users') ?>" class="nav-link <?= isActive('users', $currentUrl) ?>">Người dùng</a></li><?php endif; ?>
                            <?php if (canSee('import_export', 'use')): ?><li class="nav-item"><a href="<?= url('import-export') ?>" class="nav-link <?= isActive('import-export', $currentUrl) ?>">Import / Export</a></li><?php endif; ?>
                            <?php if (canSee('automation')): ?><li class="nav-item"><a href="<?= url('automation') ?>" class="nav-link <?= isActive('automation', $currentUrl) ?>">Automation</a></li><?php endif; ?>
                            <?php if (canSee('webhooks')): ?><li class="nav-item"><a href="<?= url('integrations') ?>" class="nav-link <?= isActive('integrations', $currentUrl) ?>">Tích hợp</a></li><?php endif; ?>
                            <?php if (canSee('webhooks')): ?><li class="nav-item"><a href="<?= url('webhooks') ?>" class="nav-link <?= isActive('webhooks', $currentUrl) ?>">Webhook</a></li><?php endif; ?>
                            <li class="nav-item"><a href="<?= url('call-logs') ?>" class="nav-link <?= isActive('call-logs', $currentUrl) ?>">Tổng đài</a></li>
                            <li class="nav-item"><a href="<?= url('duplicates') ?>" class="nav-link <?= isActive('duplicates', $currentUrl) ?>">Trùng lặp</a></li>
                            <?php if (canSee('webhooks', 'manage')): ?><li class="nav-item"><a href="<?= url('plugins/marketplace') ?>" class="nav-link <?= isActive('plugins', $currentUrl) ?>">Marketplace</a></li><?php endif; ?>
                            <li class="nav-item"><a href="<?= url('billing') ?>" class="nav-link <?= isActive('billing', $currentUrl) ?>">Gói dịch vụ</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
                <?php endif; /* end $_role !== 'staff' */ ?>

                <?php if (canSee('settings', 'manage') || $_role === 'admin'): ?>
                <li class="menu-title"><span>Cài đặt</span></li>
                <?php $settingsOpen = isOpen(['settings','custom-fields','tags','help'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $settingsOpen ? '' : 'collapsed' ?>" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="<?= $settingsOpen ? 'true' : 'false' ?>">
                        <i class="ri-tools-line"></i> <span>Cài đặt</span>
                    </a>
                    <div class="collapse <?= $settingsOpen ? 'show' : '' ?>" id="sidebarSettings">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('settings') ?>" class="nav-link <?= $currentUrl === 'settings' ? 'active' : '' ?>">Cài đặt chung</a></li>
                            <li class="nav-item"><a href="<?= url('settings/contact-statuses') ?>" class="nav-link <?= isActive('settings/contact-statuses', $currentUrl) ?>">Trạng thái KH</a></li>
                            <li class="nav-item"><a href="<?= url('custom-fields') ?>" class="nav-link <?= isActive('custom-fields', $currentUrl) ?>">Trường tùy chỉnh</a></li>
                            <li class="nav-item"><a href="<?= url('tags') ?>" class="nav-link <?= isActive('tags', $currentUrl) ?>">Nhãn</a></li>
                            <li class="nav-item"><a href="<?= url('settings/api') ?>" class="nav-link <?= isActive('settings/api', $currentUrl) ?>">Cấu hình API</a></li>
                            <?php if ($_role === 'admin'): ?><li class="nav-item"><a href="<?= url('settings/white-label') ?>" class="nav-link <?= isActive('settings/white-label', $currentUrl) ?>">White-label</a></li><?php endif; ?>
                            <li class="nav-item"><a href="<?= url('help') ?>" class="nav-link <?= isActive('help', $currentUrl) ?>">Trợ giúp</a></li>
                        </ul>
                    </div>
                </li>
                <?php else: ?>
                <!-- Staff chỉ thấy Cài đặt cá nhân + Trợ giúp -->
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('settings', $currentUrl) ?>" href="<?= url('settings') ?>">
                        <i class="ri-tools-line"></i> <span>Cài đặt</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('help', $currentUrl) ?>" href="<?= url('help') ?>">
                        <i class="ri-question-line"></i> <span>Trợ giúp</span>
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
