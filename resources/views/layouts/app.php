<?php
$pageTitle = $pageTitle ?? 'ToryCRM';
?>
<!DOCTYPE html>
<html lang="vi" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-bs-theme="light" data-layout-width="fluid" data-layout-position="fixed" data-layout-style="default">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | ToryCRM</title>

    <!-- Velzon CSS -->
    <link href="<?= asset('css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('libs/simplebar/simplebar.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('libs/node-waves/waves.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/icons.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/app.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/custom.css') ?>" rel="stylesheet">

    <!-- PWA -->
    <link rel="manifest" href="<?= asset('manifest.json') ?>">
    <meta name="theme-color" content="#405189">
</head>
<body>

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
                    ?>
                        <div class="alert alert-<?= $alertType ?> alert-border-left alert-dismissible fade show mb-3" role="alert">
                            <i class="<?= $alertIcons[$alertType] ?? 'ri-notification-3-line' ?> me-3 align-middle fs-16"></i>
                            <strong><?= $alertType === 'success' ? 'Thành công' : ($alertType === 'danger' ? 'Lỗi' : ($alertType === 'warning' ? 'Cảnh báo' : 'Thông báo')) ?></strong> - <?= e($flashMsg['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

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
    <script src="<?= asset('js/torycrm.js') ?>?v=<?= time() ?>"></script>

    <!-- Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(function() {});
    }
    </script>
</body>
</html>
