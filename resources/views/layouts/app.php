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
    <?php
    $__cssVer = fn(string $p) => '?v=' . (@filemtime(BASE_PATH . '/public/' . $p) ?: time());
    ?>
    <link href="<?= asset('css/app.min.css') . $__cssVer('css/app.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/custom.css') . $__cssVer('css/custom.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/app.css') . $__cssVer('css/app.css') ?>" rel="stylesheet">

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

    <style>#back-to-top { display: none !important; }</style>

    <!-- Velzon Theme Customizer (must be before app.js) -->
    <?php include BASE_PATH . '/public/velzon/layouts/customizer.php'; ?>

    <script src="<?= asset('js/app.js') ?>"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <!-- ToryCRM Custom JS -->
    <script src="<?= asset('js/searchable-select.js') ?>?v=20260416"></script>
    <script src="<?= asset('js/torycrm.js') ?>?v=<?= time() ?>"></script>

    <!-- UI Enhancements -->
    <script src="<?= asset('js/command-palette.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/split-view.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/activity-feed.js') ?>?v=<?= time() ?>"></script>

    <!-- AI Chat Widget (controlled by settings/api toggle) -->
    <?php
    $__tenant = \Core\Database::fetch("SELECT settings FROM tenants WHERE id = ?", [$_SESSION['tenant_id'] ?? 1]);
    $__aiCfg = json_decode($__tenant['settings'] ?? '{}', true)['ai'] ?? [];
    if ($__aiCfg['show_widget'] ?? false):
    ?>
    <script src="<?= asset('js/ai-chat-widget.js') ?>?v=<?= time() ?>"></script>
    <?php endif; ?>

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

    <script>
    // Save theme to server when color scheme changes
    document.addEventListener('change', function(e) {
        if (e.target.name === 'data-bs-theme' && e.target.closest('#theme-settings-offcanvas')) {
            fetch('<?= url("theme/toggle") ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: '_token=<?= csrf_token() ?>&theme=' + e.target.value
            });
        }
    });
    </script>

    <!-- Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(function() {});
    }
    </script>

    <!-- Sidebar edge toggle (always-visible expand/collapse button) -->
    <button type="button" id="sidebarEdgeToggle" title="Thu gọn / mở rộng sidebar">
        <i class="ri-arrow-left-s-line"></i>
    </button>
    <script>
    (function(){
        var btn = document.getElementById('sidebarEdgeToggle');
        var html = document.documentElement;
        function syncIcon(){
            var size = html.getAttribute('data-sidebar-size') || 'lg';
            var collapsed = (size === 'sm' || size === 'sm-hover');
            btn.querySelector('i').className = collapsed ? 'ri-arrow-right-s-line' : 'ri-arrow-left-s-line';
        }
        syncIcon();
        btn.addEventListener('click', function(){
            var size = html.getAttribute('data-sidebar-size') || 'lg';
            var next = (size === 'sm' || size === 'sm-hover') ? 'lg' : 'sm';
            html.setAttribute('data-sidebar-size', next);
            try { localStorage.setItem('sidebar-size', next); } catch(e){}
            syncIcon();
        });
        // Restore saved preference
        try {
            var saved = localStorage.getItem('sidebar-size');
            if (saved) { html.setAttribute('data-sidebar-size', saved); syncIcon(); }
        } catch(e){}
        // Keep icon in sync if something else changes it (Velzon hamburger)
        new MutationObserver(syncIcon).observe(html, { attributes: true, attributeFilter: ['data-sidebar-size'] });
    })();
    </script>

    <!-- Chat unread badge poller -->
    <script>
    (function(){
        var badge = document.getElementById('chat-unread-badge');
        if (!badge) return;
        var url = '<?= url('chat/unread-total') ?>';
        function tick(){
            fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.ok?r.json():null).then(function(d){
                if (!d) return;
                var n = parseInt(d.total||0, 10);
                if (n > 0) { badge.textContent = n > 99 ? '99+' : n; badge.style.display = ''; }
                else { badge.style.display = 'none'; }
            }).catch(function(){});
        }
        // Poll 20s when visible, 60s when tab hidden
        var tmr = null;
        function schedule(){
            if (tmr) clearTimeout(tmr);
            var delay = document.hidden ? 60000 : 20000;
            tmr = setTimeout(function(){ tick(); schedule(); }, delay);
        }
        schedule();
        document.addEventListener('visibilitychange', function(){
            if (!document.hidden) { tick(); schedule(); } else schedule();
        });
    })();
    </script>
</body>
</html>
