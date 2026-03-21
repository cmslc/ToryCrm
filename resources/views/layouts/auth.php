<?php
$pageTitle = $pageTitle ?? 'ToryCRM';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | ToryCRM</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <!-- App CSS -->
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">

    <style>
        .auth-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #405189 0%, #0ab39c 100%);
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        .auth-logo {
            font-size: 28px;
            font-weight: 700;
            color: #405189;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    <div class="auth-page">
        <div class="auth-card card">
            <div class="card-body p-4">

                <!-- Logo -->
                <div class="text-center mb-4">
                    <span class="auth-logo">ToryCRM</span>
                    <p class="text-muted mt-1">H&#7879; th&#7889;ng qu&#7843;n l&yacute; kh&aacute;ch h&agrave;ng</p>
                </div>

                <?php if (!empty($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= e($_SESSION['flash_success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['flash_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= e($_SESSION['flash_error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>

                <?= $content ?? '' ?>

            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- App JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
