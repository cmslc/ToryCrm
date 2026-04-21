<?php $noLayout = true; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác thực 2FA - ToryCRM</title>
    <link rel="stylesheet" href="<?= asset('velzon/assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('velzon/assets/css/icons.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('velzon/assets/css/app.min.css') ?>">
</head>
<body style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:#f3f3f9">
<div class="card shadow" style="width:420px;max-width:100%">
    <div class="card-body p-4 text-center">
        <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px">
            <i class="ri-shield-check-line fs-24"></i>
        </div>
        <h4 class="mb-1">Xác thực 2 bước</h4>
        <p class="text-muted mb-4">Nhập mã 6 chữ số từ ứng dụng xác thực (Google Authenticator/Authy).<br>Hoặc dùng backup code nếu mất điện thoại.</p>

        <?php if (!empty($_SESSION['flash']['message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash']['type'] ?? 'info' ?> py-2 mb-3"><?= e($_SESSION['flash']['message']) ?></div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <form method="POST" action="<?= url('login/2fa') ?>">
            <?= csrf_field() ?>
            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" class="form-control form-control-lg text-center mb-3" placeholder="123456" maxlength="10" required autofocus style="letter-spacing:8px;font-size:24px">
            <button type="submit" class="btn btn-primary w-100">Xác nhận</button>
        </form>

        <a href="<?= url('login') ?>" class="btn btn-link mt-2 text-muted">← Đăng nhập lại</a>
    </div>
</div>
</body>
</html>
