<?php $pageTitle = 'Thành viên - ' . e($department['name']); ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0"><?= e($department['name']) ?></h4>
        <p class="text-muted mb-0"><?= e($department['description'] ?? 'Quản lý thành viên phòng ban') ?></p>
    </div>
    <a href="<?= url('departments') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Thành viên <span class="badge bg-primary ms-1"><?= count($members) ?></span></h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nhân viên</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                                <th>Vai trò</th>
                                <th>Đăng nhập cuối</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($members)): ?>
                                <?php
                                $roleLabels = ['admin'=>'Admin','manager'=>'Quản lý','staff'=>'Nhân viên'];
                                $roleColors = ['admin'=>'danger','manager'=>'warning','staff'=>'info'];
                                foreach ($members as $m): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle"><?= strtoupper(substr($m['name'], 0, 1)) ?></span>
                                            </div>
                                            <a href="<?= url('users/' . $m['id'] . '/edit') ?>" class="fw-medium text-dark"><?= e($m['name']) ?></a>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?= e($m['email']) ?></td>
                                    <td class="text-muted"><?= e($m['phone'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= $roleColors[$m['role']] ?? 'secondary' ?>-subtle text-<?= $roleColors[$m['role']] ?? 'secondary' ?>"><?= $roleLabels[$m['role']] ?? $m['role'] ?></span></td>
                                    <td class="text-muted fs-12"><?= $m['last_login'] ? time_ago($m['last_login']) : 'Chưa đăng nhập' ?></td>
                                    <td>
                                        <form method="POST" action="<?= url('departments/' . $department['id'] . '/members/' . $m['id'] . '/remove') ?>" data-confirm="Xóa <?= e($m['name']) ?> khỏi phòng ban?">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-soft-danger"><i class="ri-user-unfollow-line me-1"></i> Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có thành viên</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thêm thành viên</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('departments/' . $department['id'] . '/members/add') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Chọn nhân viên</label>
                        <select name="user_id" class="form-select searchable-select" required>
                            <option value="">Chọn nhân viên...</option>
                            <?php foreach ($allUsers as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="ri-user-add-line me-1"></i> Thêm vào phòng ban</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thông tin phòng ban</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted" width="40%">Tên</th><td><?= e($department['name']) ?></td></tr>
                    <tr><th class="text-muted">Màu</th><td><span class="d-inline-block rounded-circle me-1" style="width:12px;height:12px;background:<?= e($department['color']) ?>"></span><?= e($department['color']) ?></td></tr>
                    <tr><th class="text-muted">Thành viên</th><td><?= count($members) ?> người</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
