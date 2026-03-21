<?php
$noLayout = true;
$pageTitle = 'Quên mật khẩu';
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
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #405189 0%, #0ab39c 100%);
        }

        .auth-page::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='rgba(255,255,255,0.05)' d='M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,218.7C672,235,768,245,864,234.7C960,224,1056,192,1152,181.3C1248,171,1344,181,1392,186.7L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'/%3E%3C/svg%3E") no-repeat bottom;
            background-size: cover;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .auth-brand {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-brand h2 {
            color: #fff;
            font-weight: 700;
            font-size: 1.75rem;
            letter-spacing: 1px;
        }

        .auth-brand h2 i {
            font-size: 2rem;
            vertical-align: middle;
            margin-right: 0.5rem;
        }

        .btn-primary-custom {
            background-color: #405189;
            border-color: #405189;
        }

        .btn-primary-custom:hover {
            background-color: #354370;
            border-color: #354370;
        }
    </style>
</head>
<body class="auth-page">

    <div class="auth-card">
        <!-- Brand -->
        <div class="auth-brand">
            <h2><i class="ri-customer-service-2-line"></i> ToryCRM</h2>
        </div>

        <!-- Card -->
        <div class="card shadow-lg border-0">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="ri-mail-send-line" style="font-size: 3rem; color: #405189;"></i>
                    </div>
                    <h5 class="text-primary" style="color: #405189 !important;">Quên mật khẩu?</h5>
                    <p class="text-muted">Nhập email của bạn, chúng tôi sẽ gửi link đặt lại mật khẩu.</p>
                </div>

                <?php $flashMsg = flash(); if ($flashMsg): ?>
                    <?php $alertType = ($flashMsg['type'] === 'success') ? 'success' : 'danger'; ?>
                    <div class="alert alert-<?= $alertType ?> alert-dismissible fade show" role="alert">
                        <?= e($flashMsg['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="<?= url('forgot-password') ?>" method="POST">
                    <?= csrf_field() ?>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e(old('email')) ?>" placeholder="Nhập email đã đăng ký" required>
                    </div>

                    <!-- Submit -->
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary-custom w-100">
                            <i class="ri-send-plane-line me-1"></i> Gửi link đặt lại mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Back to login -->
        <div class="text-center mt-3">
            <p class="text-white mb-0">
                <a href="<?= url('login') ?>" class="fw-semibold text-white">
                    <i class="ri-arrow-left-line me-1"></i> Quay lại đăng nhập
                </a>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
