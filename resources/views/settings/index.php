<?php
$pageTitle = 'Cài đặt';
$tab = $_GET['tab'] ?? 'profile';
$role = $user['role'] ?? 'staff';
$isAdmin = $role === 'admin';
$isManager = in_array($role, ['admin', 'manager']);
?>

<div class="page-title-box"><h4 class="mb-0">Cài đặt</h4></div>

<div class="row">
    <!-- Left: Menu -->
    <div class="col-xl-3">
        <div class="card">
            <div class="card-body p-2 text-center">
                <div class="mx-auto mb-2" style="width:60px;height:60px">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary fw-bold" style="width:60px;height:60px;font-size:24px">
                        <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
                    </div>
                </div>
                <h6 class="mb-0"><?= e($user['name'] ?? '') ?></h6>
                <small class="text-muted"><?= e($user['email'] ?? '') ?></small><br>
                <span class="badge bg-primary mt-1"><?= ucfirst($role) ?></span>
            </div>
        </div>

        <div class="card">
            <div class="list-group list-group-flush">
                <a href="<?= url('settings?tab=profile') ?>" class="list-group-item list-group-item-action <?= $tab === 'profile' ? 'active' : '' ?>">
                    <i class="ri-user-line me-2"></i> Thông tin cá nhân
                </a>
                <a href="<?= url('settings?tab=password') ?>" class="list-group-item list-group-item-action <?= $tab === 'password' ? 'active' : '' ?>">
                    <i class="ri-lock-line me-2"></i> Đổi mật khẩu
                </a>
                <?php if ($isManager): ?>
                <a href="<?= url('settings?tab=dashboard') ?>" class="list-group-item list-group-item-action <?= $tab === 'dashboard' ? 'active' : '' ?>">
                    <i class="ri-layout-grid-line me-2"></i> Tùy chỉnh Dashboard
                </a>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                <a href="<?= url('settings?tab=permissions') ?>" class="list-group-item list-group-item-action <?= $tab === 'permissions' ? 'active' : '' ?>">
                    <i class="ri-shield-check-line me-2"></i> Phân quyền
                </a>
                <a href="<?= url('settings?tab=branding') ?>" class="list-group-item list-group-item-action <?= $tab === 'branding' ? 'active' : '' ?>">
                    <i class="ri-palette-line me-2"></i> Thương hiệu
                </a>
                <a href="<?= url('settings/api') ?>" class="list-group-item list-group-item-action">
                    <i class="ri-plug-line me-2"></i> Cấu hình API
                </a>
                <a href="<?= url('settings/contact-statuses') ?>" class="list-group-item list-group-item-action">
                    <i class="ri-list-settings-line me-2"></i> Trạng thái KH
                </a>
                <a href="<?= url('custom-fields') ?>" class="list-group-item list-group-item-action">
                    <i class="ri-input-method-line me-2"></i> Trường tùy chỉnh
                </a>
                <a href="<?= url('tags') ?>" class="list-group-item list-group-item-action">
                    <i class="ri-price-tag-3-line me-2"></i> Nhãn
                </a>
                <a href="<?= url('settings/api-keys') ?>" class="list-group-item list-group-item-action">
                    <i class="ri-key-line me-2"></i> API Keys
                </a>
                <a href="<?= url('settings/audit-log') ?>" class="list-group-item list-group-item-action">
                    <i class="ri-history-line me-2"></i> Audit Log
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Content -->
    <div class="col-xl-9">
        <?php if ($tab === 'profile'): ?>
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

        <?php elseif ($tab === 'password'): ?>
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

        <?php elseif ($tab === 'dashboard' && $isManager): ?>
        <!-- Tùy chỉnh Dashboard -->
        <div class="card">
            <div class="card-header p-2"><h5 class="card-title mb-0"><i class="ri-layout-grid-line me-1"></i> Tùy chỉnh Dashboard</h5></div>
            <div class="card-body text-center py-5">
                <p class="text-muted">Chuyển đến trang tùy chỉnh Dashboard widgets</p>
                <a href="<?= url('settings/widgets') ?>" class="btn btn-primary"><i class="ri-layout-grid-line me-1"></i> Mở tùy chỉnh Dashboard</a>
            </div>
        </div>

        <?php elseif ($tab === 'permissions' && $isAdmin): ?>
        <!-- Phân quyền -->
        <div class="card">
            <div class="card-header p-2"><h5 class="card-title mb-0"><i class="ri-shield-check-line me-1"></i> Phân quyền vai trò</h5></div>
            <div class="card-body text-center py-5">
                <p class="text-muted">Quản lý quyền cho Admin, Manager, Staff</p>
                <a href="<?= url('settings/permissions') ?>" class="btn btn-primary"><i class="ri-shield-check-line me-1"></i> Mở trang phân quyền</a>
            </div>
        </div>

        <?php elseif ($tab === 'branding' && $isAdmin): ?>
        <!-- Thương hiệu -->
        <div class="card">
            <div class="card-header p-2"><h5 class="card-title mb-0"><i class="ri-palette-line me-1"></i> Thương hiệu (White-label)</h5></div>
            <div class="card-body text-center py-5">
                <p class="text-muted">Tùy chỉnh logo, màu sắc, tên hệ thống</p>
                <a href="<?= url('settings/white-label') ?>" class="btn btn-primary"><i class="ri-palette-line me-1"></i> Mở cài đặt thương hiệu</a>
            </div>
        </div>

        <?php else: ?>
        <!-- Default: profile -->
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
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Cập nhật</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
