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
    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay bg-overlay-pattern"></div>

        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card overflow-hidden m-0">
                            <div class="row g-0">
                                <!-- Cover side -->
                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4 auth-one-bg h-100">
                                        <div class="bg-overlay"></div>
                                        <div class="position-relative h-100 d-flex flex-column">
                                            <div class="mb-4">
                                                <a href="<?= url('/') ?>" class="d-block">
                                                    <h2 class="text-white"><i class="ri-customer-service-2-line me-2"></i>ToryCRM</h2>
                                                </a>
                                            </div>
                                            <div class="mt-auto">
                                                <div class="mb-3">
                                                    <i class="ri-double-quotes-l display-4 text-success"></i>
                                                </div>
                                                <div id="qoutescar498" class="carousel slide" data-bs-ride="carousel">
                                                    <div class="carousel-inner text-center text-white-50 pb-5">
                                                        <div class="carousel-item active">
                                                            <p class="fs-15 fst-italic">"Quản lý khách hàng hiệu quả, tăng trưởng doanh thu bền vững."</p>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <p class="fs-15 fst-italic">"Theo dõi đơn hàng, vận chuyển và kho hàng trong một nền tảng duy nhất."</p>
                                                        </div>
                                                        <div class="carousel-item">
                                                            <p class="fs-15 fst-italic">"Tự động hóa quy trình, tiết kiệm thời gian, nâng cao hiệu suất."</p>
                                                        </div>
                                                    </div>
                                                    <div class="carousel-indicators">
                                                        <button type="button" data-bs-target="#qoutescarousel498" data-bs-slide-to="0" class="active"></button>
                                                        <button type="button" data-bs-target="#qoutescarousel498" data-bs-slide-to="1"></button>
                                                        <button type="button" data-bs-target="#qoutescarousel498" data-bs-slide-to="2"></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form side -->
                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4">
                                        <div>
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

                                        <div class="mt-4">
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

                                        <div class="mt-4 text-center">
                                            <p class="mb-0">Chưa có tài khoản? <a href="<?= url('register') ?>" class="fw-semibold text-primary text-decoration-underline">Đăng ký</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
