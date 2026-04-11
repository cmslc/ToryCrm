<?php $user = $_SESSION['user'] ?? null; ?>

<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- Hamburger -->
                <button type="button" class="btn btn px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span><span></span><span></span>
                    </span>
                </button>

                <!-- Search -->
                <form class="app-search d-none d-md-block" action="<?= url('search') ?>" method="GET">
                    <div class="position-relative">
                        <input type="text" class="form-control" name="q" placeholder="Tìm kiếm KH, deal, ticket..." autocomplete="off" value="<?= e($_GET['q'] ?? '') ?>">
                        <span class="mdi mdi-magnify search-widget-icon"></span>
                        <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none" id="search-close-options"></span>
                    </div>
                </form>
            </div>

            <div class="d-flex align-items-center">
                <!-- Conversations (Hộp thư) -->
                <div class="ms-1 header-item">
                    <a href="<?= url('conversations') ?>" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle position-relative" title="Hộp thư">
                        <i class="ri-chat-1-line fs-22"></i>
                        <?php if ($convUnread ?? 0): ?><span class="position-absolute topbar-badge badge rounded-pill bg-danger"><?= $convUnread ?></span><?php endif; ?>
                    </a>
                </div>

                <!-- Activity Feed -->
                <div class="ms-1 header-item">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle position-relative" id="af-btn" title="Hoạt động">
                        <i class="ri-pulse-line fs-22"></i>
                        <span id="af-badge" class="position-absolute topbar-badge badge rounded-pill bg-danger" style="display:none">0</span>
                    </button>
                </div>

                <!-- Fullscreen -->
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class="bx bx-fullscreen fs-22"></i>
                    </button>
                </div>

                <!-- Theme Customizer -->
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" title="Theme Customizer">
                        <i class="ri-settings-5-line fs-22"></i>
                    </button>
                </div>

                <!-- Dark/Light Mode -->
                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="theme-toggle" title="Chế độ tối/sáng">
                        <i class="<?= ($userTheme ?? 'light') === 'dark' ? 'ri-sun-line' : 'ri-moon-line' ?> fs-22"></i>
                    </button>
                </div>
                <script>
                document.getElementById('theme-toggle')?.addEventListener('click', function() {
                    var btn = this;
                    var icon = btn.querySelector('i');
                    fetch('/theme/toggle', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'_token=<?= $_SESSION['csrf_token'] ?? '' ?>'})
                    .then(function(r){return r.json()})
                    .then(function(d) {
                        if (d.theme) {
                            document.documentElement.setAttribute('data-bs-theme', d.theme);
                            document.documentElement.setAttribute('data-topbar', d.theme);
                            icon.className = (d.theme === 'dark' ? 'ri-sun-line' : 'ri-moon-line') + ' fs-22';
                        }
                    });
                });
                </script>

                <!-- Notifications -->
                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <i class="bx bx-bell fs-22"></i>
                        <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger" id="notif-badge" style="display:none">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0">
                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white">Thông báo <span class="badge bg-danger-subtle text-danger fs-13 ms-1" id="notif-count-header"></span></h6>
                                    </div>
                                    <div class="col-auto">
                                        <form method="POST" action="<?= url('notifications/mark-all-read') ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-link text-white p-0 fs-12">Đọc tất cả</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="notif-list" class="pe-2" style="max-height:300px;overflow-y:auto" data-simplebar>
                            <div class="text-center py-4 text-muted">
                                <i class="ri-notification-off-line fs-24 d-block mb-2"></i>
                                Không có thông báo mới
                            </div>
                        </div>
                        <div class="p-2 border-top text-center">
                            <a href="<?= url('notifications') ?>" class="btn btn btn-link text-primary">Xem tất cả <i class="ri-arrow-right-s-line"></i></a>
                        </div>
                    </div>
                </div>

                <!-- User -->
                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <span class="rounded-circle header-profile-user bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:14px;font-weight:600">
                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                            </span>
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"><?= e($user['name'] ?? '') ?></span>
                                <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text"><?= ucfirst($user['role'] ?? '') ?></span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Xin chào, <?= e($user['name'] ?? '') ?>!</h6>
                        <a class="dropdown-item" href="<?= url('settings') ?>"><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> Cài đặt</a>
                        <a class="dropdown-item" href="<?= url('billing') ?>"><i class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> Gói dịch vụ</a>
                        <a class="dropdown-item" href="<?= url('help') ?>"><i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> Trợ giúp</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= url('logout') ?>"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> Đăng xuất</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
