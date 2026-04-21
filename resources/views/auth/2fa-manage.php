<?php $pageTitle = 'Quản lý 2FA'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Xác thực 2 bước</h4>
    <span class="badge bg-success-subtle text-success"><i class="ri-shield-check-line me-1"></i> Đang bật</span>
</div>

<div class="row">
    <div class="col-lg-8 mx-auto">

        <?php if (!empty($_SESSION['_2fa_show_backup_codes'])): ?>
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning-subtle"><h5 class="card-title mb-0 text-warning"><i class="ri-alert-line me-1"></i> Backup codes — Lưu ngay</h5></div>
            <div class="card-body">
                <p class="text-muted">Mỗi code dùng 1 lần. Nếu mất điện thoại → dùng 1 trong các code này để đăng nhập. Trang này chỉ hiện **1 lần duy nhất**.</p>
                <div class="row g-2 mb-3">
                    <?php foreach ($_SESSION['_2fa_show_backup_codes'] as $bc): ?>
                    <div class="col-md-4"><code class="d-block p-2 bg-light rounded border text-center fs-16 user-select-all"><?= e($bc) ?></code></div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-soft-primary" onclick="window.print()"><i class="ri-printer-line me-1"></i> In</button>
                <button type="button" class="btn btn-soft-secondary" onclick="navigator.clipboard.writeText(<?= json_encode(implode(\"\n\", $_SESSION['_2fa_show_backup_codes'])) ?>); this.innerHTML='<i class=\"ri-check-line me-1\"></i> Đã copy'"><i class="ri-file-copy-line me-1"></i> Copy tất cả</button>
            </div>
        </div>
        <?php unset($_SESSION['_2fa_show_backup_codes']); endif; ?>

        <div class="card">
            <div class="card-body">
                <p class="mb-1">2FA đang bật cho tài khoản: <strong><?= e($user['email']) ?></strong></p>
                <p class="text-muted mb-4">Bật từ <?= e($user['totp_enabled_at'] ?? '') ?></p>

                <h6 class="mb-2">Tạo lại backup codes</h6>
                <form method="POST" action="<?= url('settings/2fa/regenerate-backup') ?>" class="d-flex gap-2 mb-4" data-confirm="Tạo lại sẽ huỷ các backup code cũ. Tiếp tục?">
                    <?= csrf_field() ?>
                    <input type="text" name="code" inputmode="numeric" maxlength="6" class="form-control" placeholder="Mã OTP hiện tại" required>
                    <button type="submit" class="btn btn-soft-primary"><i class="ri-refresh-line me-1"></i> Tạo lại</button>
                </form>

                <h6 class="mb-2 text-danger">Tắt 2FA</h6>
                <p class="text-muted fs-13">Khuyến cáo: giữ 2FA bật để bảo vệ tài khoản khỏi truy cập trái phép.</p>
                <form method="POST" action="<?= url('settings/2fa/disable') ?>" class="d-flex gap-2" data-confirm="Chắc chắn tắt 2FA?">
                    <?= csrf_field() ?>
                    <input type="text" name="code" inputmode="numeric" maxlength="6" class="form-control" placeholder="Mã OTP hiện tại" required>
                    <button type="submit" class="btn btn-soft-danger"><i class="ri-shield-cross-line me-1"></i> Tắt 2FA</button>
                </form>
            </div>
        </div>
    </div>
</div>
