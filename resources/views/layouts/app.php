<?php
$branding = \App\Services\BrandingService::get();
$pageTitle = $pageTitle ?? 'ToryCRM';
$brandName = $branding['name'] ?? 'ToryCRM';
$userTheme = $_SESSION['user']['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="vi" data-layout="vertical" data-topbar="<?= $userTheme === 'dark' ? 'dark' : 'light' ?>" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-bs-theme="<?= e($userTheme) ?>" data-layout-width="fluid" data-layout-position="fixed" data-layout-style="default">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e($brandName) ?></title>
    <?php if (!empty($branding['favicon_url'])): ?>
    <link rel="icon" href="<?= e($branding['favicon_url']) ?>" type="image/x-icon">
    <?php endif; ?>

    <!-- Velzon CSS -->
    <link href="<?= asset('css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('libs/simplebar/simplebar.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('libs/node-waves/waves.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/icons.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/app.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/custom.css') ?>" rel="stylesheet">

    <!-- White-label Branding -->
    <?php $brandingCss = \App\Services\BrandingService::getCssVariables(); ?>
    <?php if (!empty($brandingCss)): ?>
    <style><?= $brandingCss ?></style>
    <?php endif; ?>

    <!-- PWA -->
    <link rel="manifest" href="<?= asset('manifest.json') ?>">
    <meta name="theme-color" content="#405189">

    <!-- Prefetch on hover -->
    <script>
    document.addEventListener('mouseover', function(e) {
        var a = e.target.closest('a[href]');
        if (!a || a.dataset.prefetched || a.getAttribute('href').charAt(0) === '#') return;
        var href = a.href;
        if (href.indexOf(location.origin) !== 0) return;
        var link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = href;
        document.head.appendChild(link);
        a.dataset.prefetched = '1';
    });
    </script>
</head>
<body>

    <!-- Top loading bar -->
    <div id="tory-loader" style="position:fixed;top:0;left:0;height:3px;background:var(--vz-primary,#405189);z-index:99999;width:0;transition:width .4s ease;pointer-events:none"></div>
    <script>
    (function(){
        var bar = document.getElementById('tory-loader');
        document.addEventListener('click', function(e) {
            var a = e.target.closest('a[href]');
            if (!a) return;
            var h = a.getAttribute('href');
            if (!h || h.charAt(0) === '#' || h.indexOf('javascript') === 0 || a.getAttribute('target') === '_blank' || a.getAttribute('download')) return;
            bar.style.transition = 'width .4s ease';
            bar.style.opacity = '1';
            bar.style.width = '30%';
            setTimeout(function(){ bar.style.transition = 'width 8s cubic-bezier(.1,.05,.1,1)'; bar.style.width = '85%'; }, 50);
        });
        window.addEventListener('load', function(){ bar.style.transition = 'width .2s ease'; bar.style.width = '100%'; setTimeout(function(){ bar.style.opacity = '0'; bar.style.width = '0'; }, 300); });
    })();
    </script>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include __DIR__ . '/sidebar.php'; ?>

        <div class="main-content">

            <?php include __DIR__ . '/header.php'; ?>

            <div class="page-content">
                <div class="container-fluid">

                    <?php $flashMsg = flash(); if ($flashMsg):
                        $alertType = match($flashMsg['type']) {
                            'success' => 'success', 'error' => 'danger',
                            'warning' => 'warning', 'info' => 'info', default => 'primary',
                        };
                        $alertIcons = ['success'=>'ri-check-double-line','danger'=>'ri-error-warning-line','warning'=>'ri-alert-line','info'=>'ri-information-line','primary'=>'ri-notification-3-line'];
                        $alertLabels = ['success'=>'Thành công','danger'=>'Lỗi','warning'=>'Cảnh báo','info'=>'Thông báo','primary'=>'Thông báo'];
                    endif; ?>

                    <?= $content ?? '' ?>

                </div>
            </div>

            <?php include __DIR__ . '/footer.php'; ?>

        </div>

    </div>

    <!-- Confirm Modal (Velzon style) -->
    <div class="modal fade zoomIn" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="text-warning mb-4">
                        <i class="ri-error-warning-line" style="font-size:80px"></i>
                    </div>
                    <h4 class="mb-3" id="confirmTitle">Bạn có chắc chắn?</h4>
                    <p class="text-muted fs-15 mb-4" id="confirmMessage">Hành động này không thể hoàn tác.</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn w-sm btn-danger" id="confirmOk">Xác nhận</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Velzon JS -->
    <script src="<?= asset('libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= asset('libs/simplebar/simplebar.min.js') ?>"></script>
    <script src="<?= asset('libs/node-waves/waves.min.js') ?>"></script>
    <script src="<?= asset('libs/feather-icons/feather.min.js') ?>"></script>
    <script src="<?= asset('js/plugins.js') ?>"></script>
    <script src="<?= asset('js/layout.js') ?>"></script>
    <script src="<?= asset('js/app.js') ?>"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <!-- ToryCRM Custom JS -->
    <script src="<?= asset('js/searchable-select.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/torycrm.js') ?>?v=<?= time() ?>"></script>

    <!-- UI Enhancements -->
    <script src="<?= asset('js/command-palette.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/split-view.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/activity-feed.js') ?>?v=<?= time() ?>"></script>

    <!-- AI Chat Widget -->
    <script src="<?= asset('js/ai-chat-widget.js') ?>?v=<?= time() ?>"></script>

    <!-- Flash Toast -->
    <?php if ($flashMsg): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index:9999;margin-top:70px">
        <div id="flashToast" class="toast align-items-center text-bg-<?= $alertType ?> border-0 show" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="<?= $alertIcons[$alertType] ?> me-2"></i>
                    <strong><?= $alertLabels[$alertType] ?></strong> - <?= e($flashMsg['message']) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
    <script>
    setTimeout(function() {
        var t = document.getElementById('flashToast');
        if (t) { t.classList.remove('show'); setTimeout(function() { t.parentElement.remove(); }, 300); }
    }, 4000);
    </script>
    <?php endif; ?>

    <!-- Theme Customizer Toggle -->
    <button class="btn btn-danger btn-icon" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" style="position:fixed;bottom:24px;right:24px;z-index:1000;width:40px;height:40px;border-radius:50%;box-shadow:0 4px 12px rgba(0,0,0,.25)">
        <i class="ri-settings-5-line fs-20" style="animation:spin 3s linear infinite"></i>
    </button>
    <style>@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}</style>

    <!-- Theme Customizer Offcanvas -->
    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas">
        <div class="d-flex align-items-center bg-primary p-3 offcanvas-header">
            <h5 class="text-white mb-0 me-2">Theme Customizer</h5>
            <button type="button" class="btn-close btn-close-white ms-auto" id="customizerclose-btn" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0" data-simplebar>
            <div class="p-4">
                <!-- Color Scheme -->
                <h6 class="fw-semibold fs-13 mb-3">Chế độ sáng / tối</h6>
                <div class="row g-2" id="themeOptions">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-light" value="light" <?= $userTheme === 'light' ? 'checked' : '' ?>>
                            <label class="form-check-label bg-light p-2 rounded text-center" for="layout-mode-light">
                                <i class="ri-sun-line fs-20 d-block mb-1"></i> Sáng
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-dark" value="dark" <?= $userTheme === 'dark' ? 'checked' : '' ?>>
                            <label class="form-check-label bg-dark text-white p-2 rounded text-center" for="layout-mode-dark">
                                <i class="ri-moon-line fs-20 d-block mb-1"></i> Tối
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Topbar Color -->
                <h6 class="fw-semibold fs-13 mt-4 mb-3">Topbar</h6>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-topbar" id="topbar-light" value="light" checked>
                            <label class="form-check-label p-2 rounded text-center" for="topbar-light">Sáng</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-topbar" id="topbar-dark" value="dark">
                            <label class="form-check-label bg-dark text-white p-2 rounded text-center" for="topbar-dark">Tối</label>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Color -->
                <h6 class="fw-semibold fs-13 mt-4 mb-3">Sidebar</h6>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-dark" value="dark" checked>
                            <label class="form-check-label bg-dark text-white p-2 rounded text-center" for="sidebar-dark">Tối</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-light" value="light">
                            <label class="form-check-label p-2 rounded text-center" for="sidebar-light">Sáng</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-gradient" value="gradient">
                            <label class="form-check-label p-2 rounded text-center" style="background:linear-gradient(135deg,#405189,#0ab39c)"><span class="text-white">Gradient</span></label>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Size -->
                <h6 class="fw-semibold fs-13 mt-4 mb-3">Kích thước Sidebar</h6>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-lg" value="lg" checked>
                            <label class="form-check-label p-2 rounded text-center" for="sidebar-size-lg">Lớn</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-sidebar-size" id="sidebar-size-sm-hover" value="sm-hover">
                            <label class="form-check-label p-2 rounded text-center" for="sidebar-size-sm-hover">Icon</label>
                        </div>
                    </div>
                </div>

                <!-- Layout Width -->
                <h6 class="fw-semibold fs-13 mt-4 mb-3">Chiều rộng</h6>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-layout-width" id="layout-width-fluid" value="fluid" checked>
                            <label class="form-check-label p-2 rounded text-center" for="layout-width-fluid">Rộng</label>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-layout-width" id="layout-width-boxed" value="boxed">
                            <label class="form-check-label p-2 rounded text-center" for="layout-width-boxed">Hẹp</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Theme customizer: apply changes to document and save to server
    document.querySelectorAll('#theme-settings-offcanvas input[type="radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var attr = this.name;
            var val = this.value;
            document.documentElement.setAttribute(attr, val);

            // Save theme to server if it's the color scheme
            if (attr === 'data-bs-theme') {
                fetch('<?= url("theme/toggle") ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: '_token=<?= csrf_token() ?>&theme=' + val
                });
                // Also update topbar
                document.documentElement.setAttribute('data-topbar', val === 'dark' ? 'dark' : 'light');
            }
        });
    });
    </script>

    <!-- Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(function() {});
    }
    </script>
</body>
</html>
