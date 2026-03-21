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

                    <?php $flashMsg = flash(); if ($flashMsg): ?>
                        <?php
                        $alertType = match($flashMsg['type']) {
                            'success' => 'success',
                            'error' => 'danger',
                            'warning' => 'warning',
                            'info' => 'info',
                            default => 'primary',
                        };
                        ?>
                        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
                            <?= e($flashMsg['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?= $content ?? '' ?>

                </div>
            </div>

            <?php include __DIR__ . '/footer.php'; ?>

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
    <script src="<?= asset('js/torycrm.js') ?>"></script>

    <!-- Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(function() {});
    }
    </script>
</body>
</html>
