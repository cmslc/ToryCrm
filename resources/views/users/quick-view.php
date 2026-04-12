<?php
$rc = ['admin'=>'danger','manager'=>'warning','staff'=>'info'];
$rl = ['admin'=>'Admin','manager'=>'Manager','staff'=>'Staff'];
$gl = ['male'=>'Nam','female'=>'Nữ','other'=>'Khác'];
$u = $viewUser;
$initials = strtoupper(mb_substr($u['name'], 0, 1));
?>

<div class="text-center mb-4">
    <div class="avatar-xl mx-auto mb-3">
        <?php if (!empty($u['avatar'])): ?>
        <img src="<?= asset($u['avatar']) ?>" class="rounded-circle" style="width:80px;height:80px;object-fit:cover">
        <?php else: ?>
        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-24"><?= $initials ?></div>
        <?php endif; ?>
    </div>
    <h5 class="mb-1"><?= e($u['name']) ?></h5>
    <p class="text-muted mb-2"><?= e($u['email']) ?></p>
    <span class="badge bg-<?= $rc[$u['role']] ?? 'secondary' ?>"><?= $rl[$u['role']] ?? $u['role'] ?></span>
    <?= ($u['is_active'] ?? false)
        ? '<span class="badge bg-success-subtle text-success ms-1">Hoạt động</span>'
        : '<span class="badge bg-danger-subtle text-danger ms-1">Bị khóa</span>' ?>
</div>

<div class="table-responsive">
    <table class="table table-borderless mb-0">
        <tbody>
            <tr><td class="text-muted" style="width:40%"><i class="ri-phone-line me-1"></i> SĐT</td><td class="fw-medium"><?= e($u['phone'] ?? '-') ?></td></tr>
            <tr><td class="text-muted"><i class="ri-building-line me-1"></i> Phòng ban</td><td class="fw-medium"><?= e($u['dept_name'] ?? $u['department'] ?? '-') ?></td></tr>
            <?php if (!empty($u['address'])): ?><tr><td class="text-muted"><i class="ri-map-pin-line me-1"></i> Địa chỉ</td><td><?= e($u['address']) ?></td></tr><?php endif; ?>
            <?php if (!empty($u['date_of_birth'])): ?><tr><td class="text-muted"><i class="ri-cake-2-line me-1"></i> Ngày sinh</td><td><?= date('d/m/Y', strtotime($u['date_of_birth'])) ?></td></tr><?php endif; ?>
            <?php if (!empty($u['gender'])): ?><tr><td class="text-muted"><i class="ri-user-heart-line me-1"></i> Giới tính</td><td><?= $gl[$u['gender']] ?? '-' ?></td></tr><?php endif; ?>
            <?php if (!empty($u['join_date'])): ?><tr><td class="text-muted"><i class="ri-calendar-line me-1"></i> Ngày vào làm</td><td><?= date('d/m/Y', strtotime($u['join_date'])) ?></td></tr><?php endif; ?>
            <?php if (!empty($u['id_number'])): ?><tr><td class="text-muted"><i class="ri-bank-card-line me-1"></i> CCCD</td><td><?= e($u['id_number']) ?></td></tr><?php endif; ?>
            <?php if (!empty($u['bank_name'])): ?><tr><td class="text-muted"><i class="ri-bank-line me-1"></i> Ngân hàng</td><td><?= e($u['bank_name']) ?><br><small class="text-muted"><?= e($u['bank_account'] ?? '') ?></small></td></tr><?php endif; ?>
            <?php if (!empty($u['emergency_contact'])): ?><tr><td class="text-muted"><i class="ri-phone-line me-1"></i> Khẩn cấp</td><td><?= e($u['emergency_contact']) ?><br><small class="text-muted"><?= e($u['emergency_phone'] ?? '') ?></small></td></tr><?php endif; ?>
            <tr><td class="text-muted"><i class="ri-time-line me-1"></i> Đăng nhập</td><td><?= !empty($u['last_login']) ? time_ago($u['last_login']) : 'Chưa' ?></td></tr>
            <?php if (plugin_active('attendance-payroll') && ($u['base_salary'] ?? 0) > 0): ?>
            <tr><td class="text-muted"><i class="ri-money-dollar-circle-line me-1"></i> Lương CB</td><td class="fw-medium text-success"><?= number_format($u['base_salary']) ?>đ</td></tr>
            <tr><td class="text-muted"><i class="ri-calendar-check-line me-1"></i> Phép còn</td><td><?= rtrim(rtrim(number_format($u['leave_balance'] ?? 0, 1), '0'), '.') ?> ngày</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex gap-2">
    <a href="<?= url('users/' . $u['id'] . '/edit') ?>" class="btn btn-primary flex-grow-1"><i class="ri-pencil-line me-1"></i> Chỉnh sửa</a>
</div>
