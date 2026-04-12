<?php $pageTitle = 'Sửa người dùng'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Sửa người dùng</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?= url('users') ?>">Người dùng</a></li>
                            <li class="breadcrumb-item active">Sửa</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= url('users/' . $editUser['id'] . '/update') ?>">
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
                                    <input type="text" class="form-control" name="name" value="<?= e($editUser['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?= e($editUser['email']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu</label>
                                    <input type="password" class="form-control" name="password" placeholder="Để trống nếu không đổi">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control" name="phone" value="<?= e($editUser['phone'] ?? '') ?>">
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
                                    <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="manager" <?= ($editUser['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager</option>
                                    <option value="staff" <?= ($editUser['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phòng ban</label>
                                <input type="text" class="form-control" name="department" value="<?= e($editUser['department'] ?? '') ?>" placeholder="Nhập phòng ban">
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= ($editUser['is_active'] ?? false) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="isActive">Kích hoạt tài khoản</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (plugin_active('attendance-payroll')): ?>
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-2"></i> Lương & Phụ cấp</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Lương cơ bản</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="base_salary" value="<?= $editUser['base_salary'] ?? 0 ?>" step="100000" min="0">
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">PC Ăn trưa</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="allowance_lunch" value="<?= $editUser['allowance_lunch'] ?? 0 ?>" step="100000" min="0">
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">PC Xăng xe</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="allowance_transport" value="<?= $editUser['allowance_transport'] ?? 0 ?>" step="100000" min="0">
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">PC Điện thoại</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="allowance_phone" value="<?= $editUser['allowance_phone'] ?? 0 ?>" step="100000" min="0">
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">PC Khác</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="allowance_other" value="<?= $editUser['allowance_other'] ?? 0 ?>" step="100000" min="0">
                                    <span class="input-group-text">đ</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Người phụ thuộc</label>
                                    <input type="number" class="form-control" name="dependents" value="<?= $editUser['dependents'] ?? 0 ?>" min="0">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Ngày phép còn</label>
                                    <input type="number" class="form-control" name="leave_balance" value="<?= $editUser['leave_balance'] ?? 12 ?>" min="0" step="0.5">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="ri-save-line me-1"></i> Cập nhật
                                </button>
                                <a href="<?= url('users') ?>" class="btn btn-soft-secondary">Hủy</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
