<?php
$pageTitle = 'Quản lý người dùng';
$rc = ['admin'=>'danger','manager'=>'warning','staff'=>'info'];
$rl = ['admin'=>'Admin','manager'=>'Manager','staff'=>'Staff'];
$hasFilter = ($filters['search'] ?? '') || ($filters['role'] ?? '') || ($filters['status'] ?? '') !== '' || ($filters['department'] ?? '');
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Quản lý người dùng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('users/export') ?>" class="btn btn-soft-success"><i class="ri-file-excel-line me-1"></i> Xuất Excel</a>
        <a href="<?= url('users/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm người dùng</a>
    </div>
</div>

<!-- Bulk bar -->
<div class="card mb-2 d-none" id="bulkBar" style="position:sticky;top:70px;z-index:100">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="fw-medium"><span id="bulkCount">0</span> đã chọn</span>
            <span class="text-muted">|</span>
            <form method="POST" action="<?= url('users/bulk-action') ?>" id="bulkForm" class="d-flex gap-2">
                <?= csrf_field() ?>
                <div id="bulkIds"></div>
                <button type="submit" name="action" value="activate" class="btn btn-soft-success"><i class="ri-lock-unlock-line me-1"></i> Mở khóa</button>
                <button type="submit" name="action" value="deactivate" class="btn btn-soft-warning"><i class="ri-lock-line me-1"></i> Khóa</button>
                <?php if (!empty($departments)): ?>
                <select name="move_dept" class="form-select" style="width:auto" onchange="if(this.value){document.querySelector('[name=action]').value='move_dept';document.getElementById('bulkForm').submit()}">
                    <option value="">Chuyển phòng ban...</option>
                    <?php foreach ($departments as $d): ?>
                    <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('users') ?>" class="d-flex align-items-center gap-2 flex-wrap">
            <div class="search-box" style="min-width:180px;max-width:280px">
                <input type="text" class="form-control" name="search" placeholder="Tên, email, SĐT..." value="<?= e($filters['search'] ?? '') ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <select name="role" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                <option value="">Tất cả vai trò</option>
                <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="manager" <?= ($filters['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager</option>
                <option value="staff" <?= ($filters['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
            </select>
            <select name="status" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Hoạt động</option>
                <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Bị khóa</option>
            </select>
            <?php if (!empty($departments)): ?>
            <select name="department" class="form-select" style="width:auto;min-width:140px" onchange="this.form.submit()">
                <option value="">Tất cả phòng ban</option>
                <?php foreach ($departments as $d): ?>
                <option value="<?= $d['id'] ?>" <?= ($filters['department'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
            <?php if ($hasFilter): ?>
            <a href="<?= url('users') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                        <th>Nhân viên</th>
                        <th>Phòng ban</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Đăng nhập cuối</th>
                        <th style="width:140px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users['items'])): ?>
                        <?php foreach ($users['items'] as $u): ?>
                        <?php $initials = strtoupper(mb_substr($u['name'], 0, 1)); ?>
                        <tr>
                            <td><input type="checkbox" class="form-check-input row-check" value="<?= $u['id'] ?>"></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-2">
                                        <?php if (!empty($u['avatar'])): ?>
                                        <img src="<?= asset($u['avatar']) ?>" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                                        <?php else: ?>
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-medium" style="width:36px;height:36px"><?= $initials ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="javascript:void(0)" class="fw-medium text-body quick-view-btn" data-id="<?= $u['id'] ?>"><?= e($u['name']) ?></a>
                                        <div class="text-muted fs-12"><?= e($u['email']) ?><?php if (!empty($u['phone'])): ?> &bull; <?= e($u['phone']) ?><?php endif; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($u['dept_name'] ?? $u['department'] ?? '-') ?></td>
                            <td><span class="badge bg-<?= $rc[$u['role']] ?? 'secondary' ?>"><?= $rl[$u['role']] ?? '' ?></span></td>
                            <td>
                                <?= ($u['is_active'] ?? false)
                                    ? '<span class="badge bg-success-subtle text-success">Hoạt động</span>'
                                    : '<span class="badge bg-danger-subtle text-danger">Bị khóa</span>' ?>
                            </td>
                            <td class="text-muted fs-12"><?= !empty($u['last_login']) ? time_ago($u['last_login']) : '-' ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-soft-info btn-icon quick-view-btn" data-id="<?= $u['id'] ?>" title="Xem nhanh"><i class="ri-eye-line"></i></button>
                                    <a href="<?= url('users/' . $u['id'] . '/edit') ?>" class="btn btn-soft-primary btn-icon" title="Sửa"><i class="ri-pencil-line"></i></a>
                                    <form method="POST" action="<?= url('users/' . $u['id'] . '/reset-password') ?>" onsubmit="return confirm('Reset mật khẩu về mặc định (123456)?')">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-soft-warning btn-icon" title="Reset MK"><i class="ri-key-line"></i></button>
                                    </form>
                                    <form method="POST" action="<?= url('users/' . $u['id'] . '/toggle-active') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-soft-<?= $u['is_active'] ? 'danger' : 'success' ?> btn-icon" title="<?= $u['is_active'] ? 'Khóa' : 'Mở khóa' ?>"><i class="ri-<?= $u['is_active'] ? 'lock-line' : 'lock-unlock-line' ?>"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-user-line fs-1 d-block mb-2"></i>Chưa có người dùng</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($users['total_pages'] ?? 0) > 1): ?>
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted fs-12">Tổng <?= $users['total'] ?> người dùng</span>
            <ul class="pagination pagination-separated mb-0">
                <li class="page-item <?= $users['page'] <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('users?' . http_build_query(array_merge(array_filter($filters), ['page' => $users['page'] - 1]))) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
                <?php for ($i = 1; $i <= $users['total_pages']; $i++): ?>
                <li class="page-item <?= $i === $users['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= url('users?' . http_build_query(array_merge(array_filter($filters), ['page' => $i]))) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= $users['page'] >= $users['total_pages'] ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('users?' . http_build_query(array_merge(array_filter($filters), ['page' => $users['page'] + 1]))) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick View Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="quickViewUser" style="width:420px">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title">Thông tin nhân viên</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" id="quickViewBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
    </div>
</div>

<script>
(function() {
    // Checkbox bulk
    var checkAll = document.getElementById('checkAll');
    var bulkBar = document.getElementById('bulkBar');

    function updateBulk() {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length > 0) {
            bulkBar.classList.remove('d-none');
            document.getElementById('bulkCount').textContent = checked.length;
            var idsDiv = document.getElementById('bulkIds');
            idsDiv.innerHTML = '';
            checked.forEach(function(cb) {
                var inp = document.createElement('input'); inp.type='hidden'; inp.name='user_ids[]'; inp.value=cb.value;
                idsDiv.appendChild(inp);
            });
        } else {
            bulkBar.classList.add('d-none');
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = checkAll.checked; });
            updateBulk();
        });
    }
    document.querySelectorAll('.row-check').forEach(function(cb) { cb.addEventListener('change', updateBulk); });

    // Quick view
    document.querySelectorAll('.quick-view-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var userId = this.dataset.id;
            var body = document.getElementById('quickViewBody');
            body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
            bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('quickViewUser')).show();

            fetch('<?= url("users/") ?>' + userId + '/quick-view')
                .then(function(r) { return r.text(); })
                .then(function(html) { body.innerHTML = html; })
                .catch(function() { body.innerHTML = '<div class="text-center py-4 text-danger">Lỗi tải dữ liệu</div>'; });
        });
    });
})();
</script>
