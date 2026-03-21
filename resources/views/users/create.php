<?php $pageTitle = 'Thêm người dùng'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Thêm người dùng</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= url('users') ?>">Người dùng</a></li>
                            <li class="breadcrumb-item active">Thêm mới</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= url('users/store') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Thông tin người dùng</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= e($old['name'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?= e($old['email'] ?? '') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control" name="phone" value="<?= e($old['phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Phân quyền</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required>
                                    <option value="">Chọn vai trò</option>
                                    <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="manager" <?= ($old['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager</option>
                                    <option value="staff" <?= ($old['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phòng ban</label>
                                <input type="text" class="form-control" name="department" value="<?= e($old['department'] ?? '') ?>" placeholder="Nhập phòng ban">
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= ($old['is_active'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="isActive">Kích hoạt tài khoản</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="ri-save-line me-1"></i> Lưu
                                </button>
                                <a href="<?= url('users') ?>" class="btn btn-soft-secondary">Hủy</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
