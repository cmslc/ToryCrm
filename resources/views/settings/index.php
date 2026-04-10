<?php $pageTitle = 'Cài đặt'; ?>

        <div class="page-title-box"><h4 class="mb-0">Cài đặt tài khoản</h4></div>

        <?php $flashMsg = flash(); if ($flashMsg): ?>
            <div class="alert alert-<?= $flashMsg['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= e($flashMsg['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thông tin cá nhân</h5></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('settings/profile') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label class="form-label">Họ tên</label><input type="text" class="form-control" name="name" value="<?= e($user['name'] ?? '') ?>" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e($user['email'] ?? '') ?>" required></div>
                                <div class="col-md-6 mb-3"><label class="form-label">Điện thoại</label><input type="text" class="form-control" name="phone" value="<?= e($user['phone'] ?? '') ?>"></div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Cập nhật</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Đổi mật khẩu</h5></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('settings/password') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-4 mb-3"><label class="form-label">Mật khẩu hiện tại</label><input type="password" class="form-control" name="current_password" required></div>
                                <div class="col-md-4 mb-3"><label class="form-label">Mật khẩu mới</label><input type="password" class="form-control" name="new_password" required minlength="6"></div>
                                <div class="col-md-4 mb-3"><label class="form-label">Xác nhận mật khẩu</label><input type="password" class="form-control" name="confirm_password" required></div>
                            </div>
                            <button type="submit" class="btn btn-warning"><i class="ri-lock-line me-1"></i> Đổi mật khẩu</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="avatar-xl mx-auto mb-3">
                            <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-24">
                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                            </div>
                        </div>
                        <h5><?= e($user['name'] ?? '') ?></h5>
                        <p class="text-muted"><?= e($user['email'] ?? '') ?></p>
                        <span class="badge bg-primary"><?= ucfirst($user['role'] ?? 'staff') ?></span>
                    </div>
                </div>

                <?php if (($user['role'] ?? 'staff') !== 'staff'): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Quản trị hệ thống</h5></div>
                    <div class="list-group list-group-flush">
                        <a href="<?= url('settings/widgets') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="ri-layout-grid-line me-2 text-primary"></i> Tùy chỉnh Dashboard
                        </a>
                        <?php if (($user['role'] ?? '') === 'admin'): ?>
                        <a href="<?= url('settings/permissions') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="ri-shield-check-line me-2 text-warning"></i> Phân quyền vai trò
                        </a>
                        <a href="<?= url('settings/api-keys') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="ri-key-line me-2 text-success"></i> API Keys
                        </a>
                        <a href="<?= url('settings/audit-log') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="ri-history-line me-2 text-info"></i> Audit Log
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (($user['role'] ?? '') === 'admin'): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thương hiệu</h5></div>
                    <div class="list-group list-group-flush">
                        <a href="<?= url('settings/white-label') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="ri-palette-line me-2 text-primary"></i> White-label (Logo, màu sắc)
                        </a>
                        <a href="<?= url('settings/api') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="ri-plug-line me-2 text-success"></i> Cấu hình API
                        </a>
                        <a href="<?= url('settings/contact-statuses') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="ri-list-settings-line me-2 text-info"></i> Trạng thái KH
                        </a>
                        <a href="<?= url('custom-fields') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="ri-input-method-line me-2 text-warning"></i> Trường tùy chỉnh
                        </a>
                        <a href="<?= url('tags') ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="ri-price-tag-3-line me-2 text-danger"></i> Nhãn
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
