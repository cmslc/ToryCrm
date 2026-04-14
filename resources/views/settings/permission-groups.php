<?php
$pageTitle = 'Phân quyền';
$actionLabels = [
    'view' => 'Truy cập',
    'create' => 'Thêm mới',
    'edit' => 'Sửa',
    'delete' => 'Xóa',
    'approve' => 'Duyệt',
    'view_all' => 'Xem tất cả',
    'confirm' => 'Xác nhận',
    'manage' => 'Quản lý',
    'use' => 'Sử dụng',
];
$moduleLabels = [
    'contacts' => 'Khách hàng',
    'companies' => 'Doanh nghiệp',
    'deals' => 'Cơ hội',
    'tasks' => 'Công việc',
    'products' => 'Sản phẩm',
    'orders' => 'Đơn hàng',
    'tickets' => 'Hỗ trợ',
    'campaigns' => 'Chiến dịch',
    'fund' => 'Quỹ thu/chi',
    'users' => 'Người dùng',
    'reports' => 'Báo cáo',
    'automation' => 'Tự động hóa',
    'webhooks' => 'Webhooks',
    'settings' => 'Cài đặt',
    'import_export' => 'Import/Export',
];

// All unique actions across modules
$allActions = [];
foreach ($modules as $mod => $perms) {
    foreach ($perms as $p) {
        if (!in_array($p['action'], $allActions)) $allActions[] = $p['action'];
    }
}

function renderGroupTree($nodes, $selectedId, $level = 0) {
    foreach ($nodes as $g):
        $active = ($g['id'] == $selectedId) ? 'active bg-primary-subtle' : '';
        $indent = $level * 20;
?>
    <a href="<?= url('settings/permissions?group=' . $g['id']) ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 <?= $active ?>" style="padding-left:<?= 12 + $indent ?>px">
        <?php if ($level > 0): ?><span class="text-muted">|<?= str_repeat('--', $level) ?></span><?php endif; ?>
        <span class="flex-grow-1">
            <?= e($g['name']) ?>
            <?php if ($g['is_system']): ?><i class="ri-shield-check-line text-warning ms-1" title="Nhóm hệ thống"></i><?php endif; ?>
        </span>
        <span class="badge bg-secondary-subtle text-secondary rounded-pill"><?= $g['user_count'] ?></span>
        <?php if (!$g['is_system']): ?>
        <button type="button" class="btn btn-link p-0 text-muted edit-group" data-id="<?= $g['id'] ?>" data-name="<?= e($g['name']) ?>" data-parent="<?= $g['parent_id'] ?>" data-desc="<?= e($g['description'] ?? '') ?>" data-color="<?= e($g['color']) ?>" title="Sửa"><i class="ri-pencil-line"></i></button>
        <form method="POST" action="<?= url('settings/permissions/' . $g['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa nhóm <?= e($g['name']) ?>?">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-link p-0 text-danger" title="Xóa"><i class="ri-delete-bin-line"></i></button>
        </form>
        <?php endif; ?>
    </a>
    <?php if (!empty($g['children'])) renderGroupTree($g['children'], $selectedId, $level + 1); ?>
<?php endforeach;
}
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Phân quyền</h4>
</div>

<div class="row">
    <!-- Left: Group Tree -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Nhóm quyền</h5>
                <button type="button" class="btn btn-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#groupModal" id="btnAddGroup"><i class="ri-add-line"></i></button>
            </div>
            <div class="list-group list-group-flush">
                <?php renderGroupTree($tree, $selectedGroupId); ?>
            </div>
        </div>
    </div>

    <!-- Right: Permission Matrix -->
    <div class="col-lg-9">
        <?php if ($selectedGroup): ?>
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1"><?= e($selectedGroup['name']) ?></h5>
                        <?php if ($selectedGroup['description']): ?>
                        <p class="text-muted mb-0 fs-13"><?= e($selectedGroup['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($selectedGroup['is_system']): ?>
                    <span class="badge bg-warning-subtle text-warning"><i class="ri-shield-check-line me-1"></i>Toàn quyền</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body border-bottom">
                <h6 class="mb-2">Người dùng trong nhóm</h6>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <?php foreach ($groupUsers as $gu): ?>
                    <div class="d-flex align-items-center gap-1 border rounded-pill py-1 px-2 bg-light">
                        <?php if (!empty($gu['avatar'])): ?>
                        <img src="<?= asset($gu['avatar']) ?>" class="rounded-circle" width="24" height="24" style="object-fit:cover">
                        <?php else: ?>
                        <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:24px;height:24px;font-size:11px"><?= strtoupper(substr($gu['name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <span class="fs-13"><?= e($gu['name']) ?></span>
                        <button type="button" class="btn btn-link p-0 text-danger ms-1 remove-user" data-user-id="<?= $gu['id'] ?>" data-group-id="<?= $selectedGroupId ?>" title="Xóa khỏi nhóm"><i class="ri-close-line fs-14"></i></button>
                    </div>
                    <?php endforeach; ?>
                    <button type="button" class="btn btn-soft-primary py-1 px-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="ri-add-line me-1"></i>Thêm</button>
                </div>
            </div>

            <?php if (!$selectedGroup['is_system']): ?>
            <form method="POST" action="<?= url('settings/permissions/' . $selectedGroupId . '/save-perms') ?>">
                <?= csrf_field() ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:200px">Chức năng</th>
                                <?php foreach ($allActions as $act): ?>
                                <th class="text-center" style="min-width:80px"><?= $actionLabels[$act] ?? ucfirst($act) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($modules as $mod => $perms):
                                $permByAction = [];
                                foreach ($perms as $p) $permByAction[$p['action']] = $p;
                            ?>
                            <tr>
                                <td class="fw-medium"><?= $moduleLabels[$mod] ?? ucfirst($mod) ?></td>
                                <?php foreach ($allActions as $act):
                                    $p = $permByAction[$act] ?? null;
                                ?>
                                <td class="text-center">
                                    <?php if ($p): ?>
                                    <input type="checkbox" class="form-check-input" name="perms[]" value="<?= $p['id'] ?>" <?= in_array($p['id'], $groupPermIds) ? 'checked' : '' ?>>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Lưu phân quyền</button>
                </div>
            </form>
            <?php else: ?>
            <div class="card-body text-center py-4 text-muted">
                <i class="ri-shield-check-line fs-36 text-warning d-block mb-2"></i>
                <p>Nhóm hệ thống có toàn quyền truy cập tất cả chức năng.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="ri-shield-user-line fs-48 d-block mb-3"></i>
                <h5>Chọn nhóm quyền bên trái</h5>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Add/Edit Group -->
<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('settings/permissions/store') ?>" id="groupForm">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="groupModalTitle">Thêm nhóm quyền</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên nhóm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="groupName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhóm cha</label>
                        <select name="parent_id" class="form-select" id="groupParent">
                            <option value="">Không có</option>
                            <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= e($g['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="2" id="groupDesc"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu</label>
                        <input type="color" class="form-control form-control-color" name="color" id="groupColor" value="#405189">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i>Lưu</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add User to Group -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm người dùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="userSearch" placeholder="Tìm theo tên...">
                <div id="userList" style="max-height:300px;overflow-y:auto">
                    <?php foreach ($allUsers as $u): ?>
                    <div class="d-flex align-items-center gap-2 py-2 px-2 border-bottom user-item" data-name="<?= e(strtolower($u['name'])) ?>">
                        <?php if (!empty($u['avatar'])): ?>
                        <img src="<?= asset($u['avatar']) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
                        <?php else: ?>
                        <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px"><?= strtoupper(substr($u['name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <div class="fw-medium"><?= e($u['name']) ?></div>
                            <div class="text-muted fs-12"><?= e($u['email']) ?> <?= $u['dept_name'] ? '- ' . e($u['dept_name']) : '' ?></div>
                        </div>
                        <button type="button" class="btn btn-soft-primary py-1 px-2 add-user-btn" data-user-id="<?= $u['id'] ?>"><i class="ri-add-line"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Edit group
document.querySelectorAll('.edit-group').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        document.getElementById('groupForm').action = '<?= url('settings/permissions/') ?>' + this.dataset.id + '/update';
        document.getElementById('groupModalTitle').textContent = 'Sửa nhóm quyền';
        document.getElementById('groupName').value = this.dataset.name;
        document.getElementById('groupParent').value = this.dataset.parent || '';
        document.getElementById('groupDesc').value = this.dataset.desc;
        document.getElementById('groupColor').value = this.dataset.color;
        new bootstrap.Modal(document.getElementById('groupModal')).show();
    });
});

// Reset modal for new group
document.getElementById('btnAddGroup')?.addEventListener('click', function() {
    document.getElementById('groupForm').action = '<?= url('settings/permissions/store') ?>';
    document.getElementById('groupModalTitle').textContent = 'Thêm nhóm quyền';
    document.getElementById('groupName').value = '';
    document.getElementById('groupParent').value = '';
    document.getElementById('groupDesc').value = '';
    document.getElementById('groupColor').value = '#405189';
});

// Search users
document.getElementById('userSearch')?.addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.user-item').forEach(function(el) {
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
});

// Add user to group
document.querySelectorAll('.add-user-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var userId = this.dataset.userId;
        fetch('<?= url('settings/permissions/' . $selectedGroupId . '/add-user') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrf_token() ?>'},
            body: JSON.stringify({user_id: userId})
        }).then(r => r.json()).then(function(data) {
            if (data.success) location.reload();
            else alert(data.error || 'Lỗi');
        });
    });
});

// Remove user from group
document.querySelectorAll('.remove-user').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!confirm('Xóa người dùng khỏi nhóm?')) return;
        fetch('<?= url('settings/permissions/' . $selectedGroupId . '/remove-user') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrf_token() ?>'},
            body: JSON.stringify({user_id: this.dataset.userId})
        }).then(r => r.json()).then(function(data) {
            if (data.success) location.reload();
            else alert(data.error || 'Lỗi');
        });
    });
});
</script>
