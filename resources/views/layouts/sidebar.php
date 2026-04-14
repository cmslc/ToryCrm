<?php
$currentUrl = trim($_GET['url'] ?? '', '/');
$user = $_SESSION['user'] ?? null;
$_branding = \App\Services\BrandingService::get();
$_brandName = $_branding['name'] ?? 'ToryCRM';
$_brandLogo = $_branding['logo_url'] ?? '';
$_role = $user['role'] ?? 'staff';

function isActive(string|array $path, string $cur): string {
    $paths = is_array($path) ? $path : [$path];
    foreach ($paths as $p) {
        if ($cur === trim($p, '/') || str_starts_with($cur, trim($p, '/') . '/')) return 'active';
    }
    return '';
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
                <span class="logo-lg"><i class="ri-customer-service-2-fill" style="font-size:22px;color:var(--vz-vertical-menu-item-active-color);margin-right:6px"></i><span style="font-size:17px;font-weight:700;color:var(--vz-vertical-menu-item-active-color)"><?= e($_brandName) ?></span></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('dashboard') ?>" class="logo logo-light">
            <?php if ($_brandLogo): ?>
                <span class="logo-sm"><img src="<?= e($_brandLogo) ?>" alt="" height="22"></span>
                <span class="logo-lg"><img src="<?= e($_brandLogo) ?>" alt="" height="28"></span>
            <?php else: ?>
                <span class="logo-sm"><i class="ri-customer-service-2-fill" style="font-size:22px;color:#fff"></i></span>
                <span class="logo-lg"><i class="ri-customer-service-2-fill" style="font-size:22px;color:#fff;margin-right:6px"></i><span style="font-size:17px;font-weight:700;color:#fff"><?= e($_brandName) ?></span></span>
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
                <!-- Hộp thư moved to topbar -->

                <?php if (canSee('contacts')): ?>
                <?php $crmOpen = isOpen(['contacts','companies','checkins','bookings'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $crmOpen ? '' : 'collapsed' ?>" href="#sidebarCrm" data-bs-toggle="collapse" role="button" aria-expanded="<?= $crmOpen ? 'true' : 'false' ?>">
                        <i class="ri-contacts-line"></i> <span>Khách hàng</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $crmOpen ? 'show' : '' ?>" id="sidebarCrm">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('contacts') ?>" class="nav-link <?= isActive('contacts', $currentUrl) ?>">Danh sách KH</a></li>
                            <?php if (canSee('companies')): ?><li class="nav-item"><a href="<?= url('companies') ?>" class="nav-link <?= isActive('companies', $currentUrl) ?>">Doanh nghiệp</a></li><?php endif; ?>
                            <?php if (plugin_active('checkin')): ?><li class="nav-item"><a href="<?= url('checkins') ?>" class="nav-link <?= isActive('checkins', $currentUrl) ?>">Check-in</a></li><?php endif; ?>
                            <?php if (plugin_active('booking')): ?><li class="nav-item"><a href="<?= url('bookings') ?>" class="nav-link <?= isActive('bookings', $currentUrl) ?>">Đặt lịch hẹn</a></li><?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (canSee('deals')): ?>
                <?php $dealOpen = isOpen(['deals','deals/pipeline','deals/forecast','lead-forms','campaigns','email-templates'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $dealOpen ? '' : 'collapsed' ?>" href="#sidebarDeals" data-bs-toggle="collapse" role="button" aria-expanded="<?= $dealOpen ? 'true' : 'false' ?>">
                        <i class="ri-hand-coin-line"></i> <span>Marketing</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $dealOpen ? 'show' : '' ?>" id="sidebarDeals">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('deals') ?>" class="nav-link <?= isActive('deals', $currentUrl) && !str_contains($currentUrl, 'pipeline') && !str_contains($currentUrl, 'forecast') ? 'active' : '' ?>">Danh sách cơ hội</a></li>
                            <li class="nav-item"><a href="<?= url('deals/pipeline') ?>" class="nav-link <?= isActive('deals/pipeline', $currentUrl) ?>">Pipeline</a></li>
                            <li class="nav-item"><a href="<?= url('deals/forecast') ?>" class="nav-link <?= isActive('deals/forecast', $currentUrl) ?>">Dự báo</a></li>
                            <?php if (plugin_active('lead-forms')): ?><li class="nav-item"><a href="<?= url('lead-forms') ?>" class="nav-link <?= isActive('lead-forms', $currentUrl) ?>">Lead Forms</a></li><?php endif; ?>
                            <?php if (canSee('campaigns')): ?>
                            <li class="nav-item"><a href="<?= url('campaigns') ?>" class="nav-link <?= isActive('campaigns', $currentUrl) ?>">Chiến dịch</a></li>
                            <li class="nav-item"><a href="<?= url('email-templates') ?>" class="nav-link <?= isActive('email-templates', $currentUrl) ?>">Mẫu email</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (canSee('products') || canSee('orders')): ?>
                <?php $prodOpen = isOpen(['products','orders','purchase-orders','quotations','warehouses'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $prodOpen ? '' : 'collapsed' ?>" href="#sidebarProducts" data-bs-toggle="collapse" role="button" aria-expanded="<?= $prodOpen ? 'true' : 'false' ?>">
                        <i class="ri-shopping-bag-line"></i> <span>Bán hàng</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $prodOpen ? 'show' : '' ?>" id="sidebarProducts">
                        <ul class="nav nav-sm flex-column">
                            <?php if (canSee('products')): ?>
                            <li class="nav-item"><a href="<?= url('products') ?>" class="nav-link <?= isActive('products', $currentUrl) ?>">Sản phẩm</a></li>
                            <?php endif; ?>
                            <?php if (canSee('orders')): ?>
                            <li class="nav-item"><a href="<?= url('orders') ?>" class="nav-link <?= isActive('orders', $currentUrl) ?>">Đơn hàng bán</a></li>
                            <li class="nav-item"><a href="<?= url('purchase-orders') ?>" class="nav-link <?= isActive('purchase-orders', $currentUrl) ?>">Đơn hàng mua</a></li>
                            <li class="nav-item"><a href="<?= url('quotations') ?>" class="nav-link <?= isActive('quotations', $currentUrl) ?>">Báo giá</a></li>
                            <?php endif; ?>
                            <?php if (plugin_active('warehouse')): ?><li class="nav-item"><a href="<?= url('warehouses') ?>" class="nav-link <?= isActive('warehouses', $currentUrl) ?>">Kho</a></li><?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (plugin_active('kho-logistics')):
                    $logOpen = isOpen(['logistics'], $currentUrl);
                ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $logOpen ? '' : 'collapsed' ?>" href="#sidebarLogistics" data-bs-toggle="collapse" role="button" aria-expanded="<?= $logOpen ? 'true' : 'false' ?>">
                        <i class="ri-truck-line"></i> <span>Kho Logistics</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $logOpen ? 'show' : '' ?>" id="sidebarLogistics">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('logistics') ?>" class="nav-link <?= $currentUrl === 'logistics' ? 'active' : '' ?>">Dashboard</a></li>
                            <li class="nav-item"><a href="<?= url('logistics/receive') ?>" class="nav-link <?= isActive('logistics/receive', $currentUrl) ?>">Nhập kho (Quét)</a></li>
                            <li class="nav-item"><a href="<?= url('logistics/packages') ?>" class="nav-link <?= isActive('logistics/packages', $currentUrl) ?>">Kiện hàng</a></li>
                            <li class="nav-item"><a href="<?= url('logistics/orders') ?>" class="nav-link <?= isActive('logistics/orders', $currentUrl) ?>">Đơn hàng</a></li>
                            <li class="nav-item"><a href="<?= url('logistics/bags') ?>" class="nav-link <?= isActive('logistics/bags', $currentUrl) ?>">Bao hàng</a></li>
                            <li class="nav-item"><a href="<?= url('logistics/shipments') ?>" class="nav-link <?= isActive('logistics/shipments', $currentUrl) ?>">Lô hàng</a></li>
                            <li class="nav-item"><a href="<?= url('logistics/deliveries') ?>" class="nav-link <?= isActive('logistics/deliveries', $currentUrl) ?>">Giao hàng</a></li>
                            <li class="nav-item"><a href="<?= url('logistics/calculator') ?>" class="nav-link <?= isActive('logistics/calculator', $currentUrl) ?>">Tính phí</a></li>
                            <li class="nav-item"><a href="<?= url('logistics/reports') ?>" class="nav-link <?= isActive('logistics/reports', $currentUrl) ?>">Báo cáo</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>



                <?php if (canSee('tasks')): ?>
                <?php $taskOpen = isOpen(['tasks','calendar','activities'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $taskOpen ? '' : 'collapsed' ?>" href="#sidebarTasks" data-bs-toggle="collapse" role="button" aria-expanded="<?= $taskOpen ? 'true' : 'false' ?>">
                        <i class="ri-task-line"></i> <span>Công việc</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $taskOpen ? 'show' : '' ?>" id="sidebarTasks">
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
                    <div class="collapse menu-dropdown <?= $supportOpen ? 'show' : '' ?>" id="sidebarSupport">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('tickets') ?>" class="nav-link <?= isActive('tickets', $currentUrl) ?>">Ticket</a></li>
                            <?php if (plugin_active('sla')): ?><li class="nav-item"><a href="<?= url('sla') ?>" class="nav-link <?= isActive('sla', $currentUrl) ?>">Chính sách SLA</a></li><?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (canSee('automation')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive(['workflows','automation'], $currentUrl) ?>" href="<?= url('workflows') ?>">
                        <i class="ri-flow-chart"></i> <span>Tự động hóa</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('approvals', $currentUrl) ?>" href="<?= url('approvals/pending') ?>">
                        <i class="ri-checkbox-circle-line"></i> <span>Phê duyệt</span>
                    </a>
                </li>

                <?php if (plugin_active('documents')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('documents', $currentUrl) ?>" href="<?= url('documents') ?>">
                        <i class="ri-folder-line"></i> <span>Tài liệu</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (plugin_active('email')): ?>
                <?php
                $__unread = 0;
                try { $__unread = (int)(\Core\Database::fetch("SELECT COUNT(*) as c FROM email_messages WHERE tenant_id = ? AND folder = 'inbox' AND is_read = 0", [$_SESSION['tenant_id'] ?? 1])['c'] ?? 0); } catch (\Exception $e) {}
                $emailOpen = isOpen(['email'], $currentUrl);
                ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $emailOpen ? '' : 'collapsed' ?>" href="#sidebarEmail" data-bs-toggle="collapse" role="button" aria-expanded="<?= $emailOpen ? 'true' : 'false' ?>">
                        <i class="ri-mail-line"></i> <span>Email</span>
                        <?php if ($__unread > 0): ?><span class="badge bg-danger ms-auto"><?= $__unread ?></span><?php endif; ?>
                    </a>
                    <div class="collapse menu-dropdown <?= $emailOpen ? 'show' : '' ?>" id="sidebarEmail">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('email') ?>" class="nav-link <?= $currentUrl === 'email' || str_starts_with($currentUrl, 'email?') ? 'active' : '' ?>">Hộp thư<?php if ($__unread > 0): ?> <span class="badge bg-danger ms-1"><?= $__unread ?></span><?php endif; ?></a></li>
                            <li class="nav-item"><a href="<?= url('email/templates') ?>" class="nav-link <?= isActive('email/templates', $currentUrl) ?>">Mẫu email</a></li>
                            <?php if ($_role !== 'staff'): ?><li class="nav-item"><a href="<?= url('email/settings') ?>" class="nav-link <?= isActive('email/settings', $currentUrl) ?>">Cài đặt</a></li><?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (plugin_active('attendance-payroll')): ?>
                <li class="nav-item">
                    <?php $attOpen = isOpen(['attendance','users','departments'], $currentUrl); ?>
                    <a class="nav-link menu-link <?= $attOpen ? '' : 'collapsed' ?>" href="#sidebarAttendance" data-bs-toggle="collapse" role="button" aria-expanded="<?= $attOpen ? 'true' : 'false' ?>">
                        <i class="ri-team-line"></i> <span>Nhân sự</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $attOpen ? 'show' : '' ?>" id="sidebarAttendance">
                        <ul class="nav nav-sm flex-column">
                            <?php if (canSee('users')): ?><li class="nav-item"><a href="<?= url('users') ?>" class="nav-link <?= isActive('users', $currentUrl) ?>">Người dùng</a></li><?php endif; ?>
                            <li class="nav-item"><a href="<?= url('departments') ?>" class="nav-link <?= isActive('departments', $currentUrl) ?>">Phòng ban</a></li>
                            <li class="nav-item"><a href="<?= url('attendance') ?>" class="nav-link <?= isActive('attendance', $currentUrl) && !str_contains($currentUrl, 'leaves') && !str_contains($currentUrl, 'payroll') && !str_contains($currentUrl, 'advances') ? 'active' : '' ?>">Chấm công</a></li>
                            <li class="nav-item"><a href="<?= url('attendance/leaves') ?>" class="nav-link <?= isActive('attendance/leaves', $currentUrl) ?>">Nghỉ phép</a></li>
                            <li class="nav-item"><a href="<?= url('attendance/payroll') ?>" class="nav-link <?= isActive('attendance/payroll', $currentUrl) ?>">Bảng lương</a></li>
                            <li class="nav-item"><a href="<?= url('attendance/advances') ?>" class="nav-link <?= isActive('attendance/advances', $currentUrl) ?>">Tạm ứng</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if (plugin_active('gamification')): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('leaderboard', $currentUrl) || isActive('achievements', $currentUrl) ? 'active' : '' ?>" href="<?= url('leaderboard') ?>">
                        <i class="ri-trophy-line"></i> <span>Bảng xếp hạng</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php if (canSee('fund')): ?>
                <?php $financeOpen = isOpen(['fund','debts','contracts','budgets','commissions','finance-reports'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $financeOpen ? '' : 'collapsed' ?>" href="#sidebarFinance" data-bs-toggle="collapse" role="button" aria-expanded="<?= $financeOpen ? 'true' : 'false' ?>">
                        <i class="ri-money-dollar-circle-line"></i> <span>Tài chính</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $financeOpen ? 'show' : '' ?>" id="sidebarFinance">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('fund') ?>" class="nav-link <?= isActive('fund', $currentUrl) ?>">Quỹ thu/chi</a></li>
                            <li class="nav-item"><a href="<?= url('debts') ?>" class="nav-link <?= isActive('debts', $currentUrl) ?>">Công nợ</a></li>
                            <li class="nav-item"><a href="<?= url('contracts') ?>" class="nav-link <?= isActive('contracts', $currentUrl) ?>">Hợp đồng</a></li>
                            <li class="nav-item"><a href="<?= url('budgets') ?>" class="nav-link <?= isActive('budgets', $currentUrl) ?>">Ngân sách</a></li>
                            <li class="nav-item"><a href="<?= url('commissions') ?>" class="nav-link <?= isActive('commissions', $currentUrl) ?>">Hoa hồng</a></li>
                            <?php if ($_role !== 'staff'): ?><li class="nav-item"><a href="<?= url('finance-reports') ?>" class="nav-link <?= isActive('finance-reports', $currentUrl) ?>">Báo cáo tài chính</a></li><?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <?php if ($_role !== 'staff'): ?>

                <?php if (canSee('reports') || canSee('users') || canSee('automation') || canSee('webhooks')): ?>
                <?php $sysOpen = isOpen(['reports','plugins','integrations','duplicates','billing'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $sysOpen ? '' : 'collapsed' ?>" href="#sidebarSystem" data-bs-toggle="collapse" role="button" aria-expanded="<?= $sysOpen ? 'true' : 'false' ?>">
                        <i class="ri-settings-3-line"></i> <span>Hệ thống</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $sysOpen ? 'show' : '' ?>" id="sidebarSystem">
                        <ul class="nav nav-sm flex-column">
                            <?php if (canSee('reports')): ?><li class="nav-item"><a href="<?= url('reports') ?>" class="nav-link <?= isActive('reports', $currentUrl) ?>">Báo cáo</a></li><?php endif; ?>
                            <li class="nav-item"><a href="<?= url('duplicates') ?>" class="nav-link <?= isActive('duplicates', $currentUrl) ?>">Trùng lặp</a></li>
                            <?php if (canSee('webhooks', 'manage')): ?><li class="nav-item"><a href="<?= url('plugins') ?>" class="nav-link <?= isActive(['plugins','integrations'], $currentUrl) ?>">Plugin</a></li><?php endif; ?>
                            <li class="nav-item"><a href="<?= url('billing') ?>" class="nav-link <?= isActive('billing', $currentUrl) ?>">Gói dịch vụ</a></li>
                            <li class="nav-item"><a href="<?= url('system-info') ?>" class="nav-link <?= isActive('system-info', $currentUrl) ?>">Thông tin hệ thống</a></li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>
                <?php endif; /* end $_role !== 'staff' */ ?>

                <?php $settingsOpen = isOpen(['settings','custom-fields','tags','help'], $currentUrl); ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= $settingsOpen ? '' : 'collapsed' ?>" href="#sidebarSettings" data-bs-toggle="collapse" role="button" aria-expanded="<?= $settingsOpen ? 'true' : 'false' ?>">
                        <i class="ri-tools-line"></i> <span>Cài đặt</span>
                    </a>
                    <div class="collapse menu-dropdown <?= $settingsOpen ? 'show' : '' ?>" id="sidebarSettings">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="<?= url('settings') ?>" class="nav-link <?= $currentUrl === 'settings' || str_starts_with($currentUrl, 'settings?') ? 'active' : '' ?>">Tài khoản</a></li>
                            <?php if ($_role !== 'staff'): ?>
                            <li class="nav-item"><a href="<?= url('settings/widgets') ?>" class="nav-link <?= isActive('settings/widgets', $currentUrl) ?>">Dashboard</a></li>
                            <?php endif; ?>
                            <?php if ($_role === 'admin'): ?>
                            <li class="nav-item"><a href="<?= url('settings/permissions') ?>" class="nav-link <?= isActive('settings/permissions', $currentUrl) ?>">Phân quyền</a></li>
                            <li class="nav-item"><a href="<?= url('settings/white-label') ?>" class="nav-link <?= isActive('settings/white-label', $currentUrl) ?>">Thương hiệu</a></li>
                            <li class="nav-item"><a href="<?= url('settings/api') ?>" class="nav-link <?= isActive('settings/api', $currentUrl) ?>">Cấu hình API</a></li>
                            <li class="nav-item"><a href="<?= url('settings/contact-statuses') ?>" class="nav-link <?= isActive(['settings/contact-statuses','tags'], $currentUrl) ?>">Nhãn & Trạng thái</a></li>
                            <li class="nav-item"><a href="<?= url('custom-fields') ?>" class="nav-link <?= isActive('custom-fields', $currentUrl) ?>">Trường tùy chỉnh</a></li>
                            <li class="nav-item"><a href="<?= url('settings/api-keys') ?>" class="nav-link <?= isActive('settings/api-keys', $currentUrl) ?>">API Keys</a></li>
                            <li class="nav-item"><a href="<?= url('settings/audit-log') ?>" class="nav-link <?= isActive('settings/audit-log', $currentUrl) ?>">Audit Log</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isActive('help', $currentUrl) ?>" href="<?= url('help') ?>">
                        <i class="ri-question-line"></i> <span>Trợ giúp</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
