<?php
$pageTitle = 'Sửa người dùng';
$u = $editUser;
$rc = ['admin'=>'danger','manager'=>'warning','staff'=>'info'];
$rl = ['admin'=>'Admin','manager'=>'Manager','staff'=>'Staff'];
$initials = strtoupper(mb_substr($u['name'], 0, 1));
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sửa người dùng</h4>
    <a href="<?= url('users') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Danh sách</a>
</div>

<form method="POST" action="<?= url('users/' . $u['id'] . '/update') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row">
        <!-- Left: Profile Card + Tabs -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <div class="avatar-xl mx-auto">
                            <?php if (!empty($u['avatar'])): ?>
                            <img src="<?= asset($u['avatar']) ?>" class="rounded-circle img-fluid" id="avatarPreview" alt="" style="width:80px;height:80px;object-fit:cover">
                            <?php else: ?>
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-24" id="avatarInitial"><?= $initials ?></div>
                            <img src="" class="rounded-circle img-fluid d-none" id="avatarPreview" style="width:80px;height:80px;object-fit:cover">
                            <?php endif; ?>
                        </div>
                        <label for="avatarInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;cursor:pointer" title="Đổi ảnh">
                            <i class="ri-camera-line fs-14"></i>
                        </label>
                        <input type="file" name="avatar" id="avatarInput" accept="image/*" class="d-none">
                    </div>
                    <script>
                    document.getElementById('avatarInput')?.addEventListener('change', function() {
                        if (this.files && this.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                var preview = document.getElementById('avatarPreview');
                                var initial = document.getElementById('avatarInitial');
                                preview.src = e.target.result;
                                preview.classList.remove('d-none');
                                if (initial) initial.classList.add('d-none');
                            };
                            reader.readAsDataURL(this.files[0]);
                        }
                    });
                    </script>
                    <h5 class="mb-1"><?= e($u['name']) ?></h5>
                    <p class="text-muted mb-2"><?= e($u['email']) ?></p>
                    <span class="badge bg-<?= $rc[$u['role']] ?? 'secondary' ?>"><?= $rl[$u['role']] ?? $u['role'] ?></span>
                    <?php if ($u['is_active']): ?>
                    <span class="badge bg-success-subtle text-success ms-1">Hoạt động</span>
                    <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger ms-1">Bị khóa</span>
                    <?php endif; ?>
                </div>
                <div class="card-body border-top">
                    <div class="d-flex align-items-center mb-2">
                        <i class="ri-phone-line text-muted me-2 fs-16"></i>
                        <span><?= e($u['phone'] ?? 'Chưa có') ?></span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="ri-building-line text-muted me-2 fs-16"></i>
                        <span><?= e($u['department'] ?? 'Chưa có') ?></span>
                    </div>
                    <?php if (!empty($u['address'])): ?>
                    <div class="d-flex align-items-center mb-2">
                        <i class="ri-map-pin-line text-muted me-2 fs-16"></i>
                        <span class="text-muted fs-13"><?= e($u['address']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex align-items-center">
                        <i class="ri-time-line text-muted me-2 fs-16"></i>
                        <span class="text-muted fs-12">Đăng nhập: <?= !empty($u['last_login']) ? time_ago($u['last_login']) : 'Chưa' ?></span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2"><i class="ri-save-line me-1"></i> Lưu thay đổi</button>
                    <a href="<?= url('users') ?>" class="btn btn-soft-secondary w-100">Hủy</a>
                </div>
            </div>
        </div>

        <!-- Right: Tabs -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabInfo"><i class="ri-user-line me-1"></i> Thông tin</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabProfile"><i class="ri-profile-line me-1"></i> Hồ sơ</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAccess"><i class="ri-shield-check-line me-1"></i> Quyền hạn</a></li>
                        <?php if (plugin_active('attendance-payroll')): ?>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabSalary"><i class="ri-money-dollar-circle-line me-1"></i> Lương</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Tab: Thông tin -->
                        <div class="tab-pane active" id="tabInfo">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= e($u['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?= e($u['email']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" class="form-control" name="phone" value="<?= e($u['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" name="address" value="<?= e($u['address'] ?? '') ?>" placeholder="Địa chỉ thường trú">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control pe-5" name="password" id="newPassword" placeholder="Để trống nếu không đổi">
                                        <button class="btn btn-link position-absolute end-0 top-0 text-muted" type="button" onclick="var p=document.getElementById('newPassword'); p.type=p.type==='password'?'text':'password'">
                                            <i class="ri-eye-off-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Hồ sơ -->
                        <div class="tab-pane" id="tabProfile">
                            <h6 class="text-muted border-bottom pb-2 mb-3"><i class="ri-user-heart-line me-1"></i> Thông tin cá nhân</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ngày sinh</label>
                                    <input type="date" class="form-control" name="date_of_birth" value="<?= $u['date_of_birth'] ?? '' ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Giới tính</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Chưa chọn</option>
                                        <option value="male" <?= ($u['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Nam</option>
                                        <option value="female" <?= ($u['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Nữ</option>
                                        <option value="other" <?= ($u['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Khác</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ngày vào làm</label>
                                    <input type="date" class="form-control" name="join_date" value="<?= $u['join_date'] ?? '' ?>">
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 mt-2"><i class="ri-bank-card-line me-1"></i> CMND / CCCD</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Số CMND/CCCD</label>
                                    <input type="text" class="form-control" name="id_number" value="<?= e($u['id_number'] ?? '') ?>" placeholder="VD: 001234567890">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ngày cấp</label>
                                    <input type="date" class="form-control" name="id_issued_date" value="<?= $u['id_issued_date'] ?? '' ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nơi cấp</label>
                                    <input type="text" class="form-control" name="id_issued_place" value="<?= e($u['id_issued_place'] ?? '') ?>" placeholder="VD: CA TP.HCM">
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 mt-2"><i class="ri-bank-line me-1"></i> Tài khoản ngân hàng</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Ngân hàng</label>
                                    <input type="text" class="form-control" name="bank_name" value="<?= e($u['bank_name'] ?? '') ?>" placeholder="VD: Vietcombank">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Số tài khoản</label>
                                    <input type="text" class="form-control" name="bank_account" value="<?= e($u['bank_account'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Chi nhánh</label>
                                    <input type="text" class="form-control" name="bank_branch" value="<?= e($u['bank_branch'] ?? '') ?>">
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 mt-2"><i class="ri-phone-line me-1"></i> Liên hệ khẩn cấp</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Người liên hệ</label>
                                    <input type="text" class="form-control" name="emergency_contact" value="<?= e($u['emergency_contact'] ?? '') ?>" placeholder="Tên người thân">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">SĐT khẩn cấp</label>
                                    <input type="text" class="form-control" name="emergency_phone" value="<?= e($u['emergency_phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Tab: Quyền hạn -->
                        <div class="tab-pane" id="tabAccess">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Chức vụ</label>
                                    <select name="position_id" class="form-select">
                                        <option value="">Chọn chức vụ</option>
                                        <?php foreach ($positions ?? [] as $pos): ?>
                                        <option value="<?= $pos['id'] ?>" <?= ($u['position_id'] ?? '') == $pos['id'] ? 'selected' : '' ?>><?= e($pos['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phòng ban</label>
                                    <input type="text" class="form-control" name="department" value="<?= e($u['department'] ?? '') ?>" placeholder="Nhập phòng ban">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nhóm quyền</label>
                                    <select name="permission_groups[]" class="form-select" multiple size="4">
                                        <?php foreach ($permGroups ?? [] as $pg): ?>
                                        <option value="<?= $pg['id'] ?>" <?= in_array($pg['id'], $userGroupIds ?? []) ? 'selected' : '' ?>><?= e($pg['name']) ?><?= $pg['is_system'] ? ' (Hệ thống)' : '' ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Giữ Ctrl để chọn nhiều nhóm</small>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= ($u['is_active'] ?? false) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="isActive">Kích hoạt tài khoản</label>
                                    </div>
                                    <small class="text-muted">Tắt để khóa tài khoản, người dùng sẽ không thể đăng nhập.</small>
                                </div>
                            </div>
                        </div>

                        <?php if (plugin_active('attendance-payroll')): ?>
                        <!-- Tab: Lương & Phụ cấp -->
                        <div class="tab-pane" id="tabSalary">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Lương cơ bản</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="base_salary" value="<?= $u['base_salary'] ?? 0 ?>" step="100000" min="0">
                                        <span class="input-group-text">đ/tháng</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Người phụ thuộc <small class="text-muted">(thuế TNCN)</small></label>
                                    <input type="number" class="form-control" name="dependents" value="<?= $u['dependents'] ?? 0 ?>" min="0">
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 mt-2"><i class="ri-hand-heart-line me-1"></i> Phụ cấp hàng tháng</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ăn trưa</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="allowance_lunch" value="<?= $u['allowance_lunch'] ?? 0 ?>" step="100000" min="0">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Xăng xe</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="allowance_transport" value="<?= $u['allowance_transport'] ?? 0 ?>" step="100000" min="0">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Điện thoại</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="allowance_phone" value="<?= $u['allowance_phone'] ?? 0 ?>" step="100000" min="0">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Khác</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="allowance_other" value="<?= $u['allowance_other'] ?? 0 ?>" step="100000" min="0">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 mt-2"><i class="ri-calendar-check-line me-1"></i> Nghỉ phép</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số ngày phép còn lại</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="leave_balance" value="<?= $u['leave_balance'] ?? 12 ?>" min="0" step="0.5">
                                        <span class="input-group-text">ngày</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tổng thu nhập ước tính</label>
                                    <div class="form-control bg-light" id="estSalary">
                                        <?php
                                        $est = ($u['base_salary'] ?? 0) + ($u['allowance_lunch'] ?? 0) + ($u['allowance_transport'] ?? 0) + ($u['allowance_phone'] ?? 0) + ($u['allowance_other'] ?? 0);
                                        echo number_format($est) . ' đ/tháng';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
