<?php
$currentUrl = trim($_GET['url'] ?? '', '/');
$user = $_SESSION['user'] ?? null;
$_branding = \App\Services\BrandingService::get();
$_brandName = $_branding['name'] ?? 'ToryCRM';
$_brandLogo = $_branding['logo_url'] ?? '';

function isMenuActive(string $path, string $currentUrl): bool {
    return $currentUrl === trim($path, '/') || str_starts_with($currentUrl, trim($path, '/') . '/');
}
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

                <li class="menu-title"><span>Menu</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('dashboard', $currentUrl) ? 'active' : '' ?>" href="<?= url('dashboard') ?>">
                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
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

                <li class="menu-title"><span>CRM</span></li>

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
                    <a class="nav-link menu-link <?= isMenuActive('deals', $currentUrl) ? 'active' : '' ?>" href="<?= url('deals') ?>">
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

                <li class="menu-title"><span>Bán hàng</span></li>

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

                <li class="menu-title"><span>Marketing</span></li>

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

                <li class="menu-title"><span>Quản lý</span></li>

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
                    <a class="nav-link menu-link <?= isMenuActive('activities', $currentUrl) ? 'active' : '' ?>" href="<?= url('activities') ?>">
                        <i class="ri-history-line"></i> <span>Hoạt động</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('workflows', $currentUrl) ? 'active' : '' ?>" href="<?= url('workflows') ?>">
                        <i class="ri-flow-chart"></i> <span>Workflow</span>
                    </a>
                </li>

                <li class="menu-title"><span>Tài chính</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('fund', $currentUrl) ? 'active' : '' ?>" href="<?= url('fund') ?>">
                        <i class="ri-wallet-3-line"></i> <span>Quỹ</span>
                    </a>
                </li>

                <li class="menu-title"><span>Hệ thống</span></li>

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
                    <a class="nav-link menu-link <?= isMenuActive('automation', $currentUrl) ? 'active' : '' ?>" href="<?= url('automation') ?>">
                        <i class="ri-robot-line"></i> <span>Automation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('users', $currentUrl) ? 'active' : '' ?>" href="<?= url('users') ?>">
                        <i class="ri-group-line"></i> <span>Người dùng</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('call-logs', $currentUrl) ? 'active' : '' ?>" href="<?= url('call-logs') ?>">
                        <i class="ri-phone-line"></i> <span>Tổng đài</span>
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
                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('billing', $currentUrl) ? 'active' : '' ?>" href="<?= url('billing') ?>">
                        <i class="ri-bank-card-line"></i> <span>Gói dịch vụ</span>
                    </a>
                </li>

                <li class="menu-title"><span>Cài đặt</span></li>

                <li class="nav-item">
                    <a class="nav-link menu-link <?= isMenuActive('settings', $currentUrl) ? 'active' : '' ?>" href="<?= url('settings') ?>">
                        <i class="ri-settings-3-line"></i> <span>Cài đặt</span>
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

            </ul>
        </div>
    </div>

    <?php if ($user): ?>
    <div class="sidebar-background"></div>
    <?php endif; ?>
</div>
<!-- Left Sidebar End -->
<div class="vertical-overlay"></div>
