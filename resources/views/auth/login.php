<?php
$noLayout = true;
$pageTitle = 'Đăng nhập';
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
                                    <h5 class="text-primary">Chào mừng trở lại!</h5>
                                    <p class="text-muted">Đăng nhập để tiếp tục sử dụng ToryCRM.</p>
                                </div>

                                <?php $flashMsg = flash(); if ($flashMsg): ?>
                                    <?php $alertType = ($flashMsg['type'] === 'success') ? 'success' : (($flashMsg['type'] === 'warning') ? 'warning' : 'danger'); ?>
                                    <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
                                        <?= e($flashMsg['message']) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <div class="p-2 mt-4">
                                    <form action="<?= url('login') ?>" method="POST">
                                        <?= csrf_field() ?>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email')) ?>" placeholder="Nhập email" required>
                                        </div>

                                        <div class="mb-3">
                                            <div class="float-end"><a href="<?= url('forgot-password') ?>" class="text-muted">Quên mật khẩu?</a></div>
                                            <label for="password" class="form-label">Mật khẩu</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" class="form-control pe-5 password-input" id="password" name="password" placeholder="Nhập mật khẩu" required>
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" onclick="togglePassword('password', this)">
                                                    <i class="ri-eye-off-fill align-middle"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                                            <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-success w-100" type="submit">Đăng nhập</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="mb-0">Chưa có tài khoản? <a href="<?= url('register') ?>" class="fw-semibold text-primary text-decoration-underline">Đăng ký</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy; <?= date('Y') ?> ToryCRM</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="<?= asset('libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script>
        function togglePassword(inputId, btn) {
            var input = document.getElementById(inputId);
            var icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('ri-eye-off-fill', 'ri-eye-fill');
            } else {
                input.type = 'password';
                icon.classList.replace('ri-eye-fill', 'ri-eye-off-fill');
            }
        }
    </script>
</body>
</html>
