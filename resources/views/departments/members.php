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
                                            <input type="checkbox" class="form-check-input me-2 bulk-member-check" value="<?= $m['id'] ?>">
                                            <?= user_avatar($m['name'] ?? null) ?>
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
                        <?php $deptGrouped = []; foreach ($allUsers ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                        <select name="user_id" class="form-select searchable-select" required>
                            <option value="">Chọn nhân viên...</option>
                            <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                            <optgroup label="<?= e($dept) ?>">
                                <?php foreach ($dUsers as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
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

<!-- Bulk Move Bar -->
<div class="card d-none" id="bulkMoveBar">
    <div class="card-body py-2">
        <form method="POST" action="<?= url('departments/bulk-move') ?>" class="d-flex align-items-center gap-2">
            <?= csrf_field() ?>
            <span class="fw-medium"><span id="bulkMoveCount">0</span> đã chọn</span>
            <div id="bulkMoveIds"></div>
            <?php
            $allDepts = \Core\Database::fetchAll("SELECT id, name FROM departments WHERE tenant_id = ? AND id != ? ORDER BY name", [$_SESSION['tenant_id'] ?? 1, $department['id']]);
            ?>
            <select name="target_department_id" class="form-select" style="width:auto" required>
                <option value="">Chuyển đến phòng...</option>
                <?php foreach ($allDepts as $ad): ?><option value="<?= $ad['id'] ?>"><?= e($ad['name']) ?></option><?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary"><i class="ri-arrow-right-line me-1"></i> Chuyển</button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.bulk-member-check').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var checked = document.querySelectorAll('.bulk-member-check:checked');
        var bar = document.getElementById('bulkMoveBar');
        if (checked.length > 0) {
            bar.classList.remove('d-none');
            document.getElementById('bulkMoveCount').textContent = checked.length;
            var ids = document.getElementById('bulkMoveIds');
            ids.innerHTML = '';
            checked.forEach(function(c) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'user_ids[]'; inp.value = c.value;
                ids.appendChild(inp);
            });
        } else {
            bar.classList.add('d-none');
        }
    });
});
</script>
