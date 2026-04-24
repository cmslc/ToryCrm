<?php
$noLayout = true;
$pageTitle = 'Đặt lại mật khẩu';
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | ToryCRM</title>
    <link href="<?= asset('css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/icons.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/app.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('css/custom.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
            <div class="bg-overlay"></div>
            <div class="shape">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120"><path fill="var(--vz-body-bg)" d="M0,36L48,42.7C96,49,192,63,288,58.7C384,55,480,33,576,33.3C672,33,768,55,864,66C960,77,1056,77,1152,69.3C1248,62,1344,48,1392,40.7L1440,33V120H0Z"></path></svg>
            </div>
        </div>

        <div class="auth-page-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="text-center mt-sm-5 mb-4">
                            <h2 class="text-white"><i class="ri-customer-service-2-line me-2"></i>ToryCRM</h2>
                        </div>
                        <div class="card mt-4 card-bg-fill">
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Đặt mật khẩu mới</h5>
                                    <p class="text-muted">Tối thiểu 8 ký tự.</p>
                                </div>

                                <?php $flashMsg = flash(); if ($flashMsg): ?>
                                    <?php $alertType = ($flashMsg['type'] === 'success') ? 'success' : 'danger'; ?>
                                    <div class="alert alert-borderless alert-<?= $alertType ?> text-center" role="alert">
                                        <?= e($flashMsg['message']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="p-2 mt-4">
                                    <form action="<?= url('reset-password/' . e($token)) ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Mật khẩu mới</label>
                                            <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password_confirm" class="form-label">Nhập lại mật khẩu</label>
                                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" required>
                                        </div>
                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Đặt lại mật khẩu</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="mb-0"><a href="<?= url('login') ?>" class="fw-semibold text-primary text-decoration-underline">Quay lại đăng nhập</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= asset('libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
