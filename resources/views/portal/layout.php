<?php
$pageTitle = $pageTitle ?? 'Client Portal';
$portalContact = $portalContact ?? ($_SESSION['portal_contact'] ?? null);
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | ToryCRM Portal</title>
    <link href="<?= asset('css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/icons.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/app.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/custom.css') ?>" rel="stylesheet">
    <style>
        body { background: #f3f3f9; }
        .portal-header {
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,.1);
            padding: .75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .portal-content {
            padding: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="portal-header d-flex align-items-center justify-content-between">
        <a href="<?= url('portal') ?>" class="d-flex align-items-center gap-2 text-decoration-none">
            <i class="ri-customer-service-2-fill fs-4 text-primary"></i>
            <span class="fw-bold fs-5 text-dark">ToryCRM</span>
            <span class="badge bg-primary-subtle text-primary">Portal</span>
        </a>
        <?php if ($portalContact): ?>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted">
                <i class="ri-user-line me-1"></i>
                <?= e(($portalContact['first_name'] ?? '') . ' ' . ($portalContact['last_name'] ?? '')) ?>
            </span>
            <a href="<?= url('portal/logout') ?>" class="btn btn-light">
                <i class="ri-logout-box-r-line me-1"></i> Đăng xuất
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="portal-content">
        <?php $flashMsg = flash(); if ($flashMsg):
            $alertType = match($flashMsg['type']) {
                'success' => 'success', 'error' => 'danger',
                'warning' => 'warning', 'info' => 'info', default => 'primary',
            };
        ?>
        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show mb-3" role="alert">
            <?= e($flashMsg['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>

    <footer class="text-center py-3 text-muted">
        <small>&copy; <?= date('Y') ?> ToryCRM Portal</small>
    </footer>

    <script src="<?= asset('libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
