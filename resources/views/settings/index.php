<?php $pageTitle = 'Cài đặt tài khoản'; ?>

<div class="page-title-box"><h4 class="mb-0">Cài đặt tài khoản</h4></div>

<div class="row">
    <div class="col-xl-8">
        <!-- Thông tin cá nhân -->
        <div class="card">
            <div class="card-header p-2"><h5 class="card-title mb-0"><i class="ri-user-line me-1"></i> Thông tin cá nhân</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/profile') ?>">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Họ tên</label>
                            <input type="text" class="form-control" name="name" value="<?= e($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= e($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Điện thoại</label>
                            <input type="text" class="form-control" name="phone" value="<?= e($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phòng ban</label>
                            <input type="text" class="form-control" value="<?= e($user['department'] ?? 'Chưa gán') ?>" disabled>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Cập nhật</button>
                </form>
            </div>
        </div>

        <!-- Đổi mật khẩu -->
        <div class="card">
            <div class="card-header p-2"><h5 class="card-title mb-0"><i class="ri-lock-line me-1"></i> Đổi mật khẩu</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/password') ?>">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="ri-lock-line me-1"></i> Đổi mật khẩu</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <!-- Avatar + Info -->
        <div class="card">
            <div class="card-body text-center">
                <div class="mx-auto mb-2" style="width:70px;height:70px">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary fw-bold" style="width:70px;height:70px;font-size:28px">
                        <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
                    </div>
                </div>
                <h5 class="mb-0"><?= e($user['name'] ?? '') ?></h5>
                <p class="text-muted mb-1"><?= e($user['email'] ?? '') ?></p>
                <span class="badge bg-primary"><?= ucfirst($user['role'] ?? 'staff') ?></span>
            </div>
        </div>
    </div>
</div>
