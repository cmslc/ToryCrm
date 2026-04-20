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
// Module labels (flat map for display)
$moduleLabels = [
    'contacts' => 'Khách hàng', 'deals' => 'Cơ hội', 'quotations' => 'Báo giá',
    'contracts' => 'Hợp đồng', 'orders' => 'Đơn hàng', 'debts' => 'Công nợ',
    'products' => 'Sản phẩm', 'tasks' => 'Công việc', 'activities' => 'Hoạt động',
    'tickets' => 'Hỗ trợ', 'fund' => 'Quỹ thu/chi', 'users' => 'Người dùng',
    'reports' => 'Báo cáo', 'settings' => 'Cài đặt', 'import_export' => 'Import/Export',
    'calendar' => 'Lịch hẹn', 'logistics' => 'Kho hàng', 'email' => 'Email',
    'campaigns' => 'Chiến dịch', 'commissions' => 'Hoa hồng',
    'automation' => 'Tự động hóa', 'webhooks' => 'Webhooks',
];
// Keep for backward compat
foreach ($moduleLabels as $mod => $label) {
}

// All unique actions across modules
$allActions = [];
foreach ($modules as $mod => $perms) {
    foreach ($perms as $p) {
        if (!in_array($p['action'], $allActions)) $allActions[] = $p['action'];
    }
}

function renderGroupTree($nodes, $selectedId, $level = 0) {
    foreach ($nodes as $g):
        $isActive = ($g['id'] == $selectedId);
        $indent = $level * 20;
?>
    <li class="list-group-item list-group-item-action d-flex align-items-center px-3 py-2 <?= $isActive ? 'active' : '' ?>" style="padding-left:<?= 16 + $indent ?>px!important;cursor:pointer" data-group-id="<?= $g['id'] ?>" onclick="if(!event.target.closest('button,form'))loadGroupPanel(<?= $g['id'] ?>,this)">
        <?php if ($level > 0): ?><i class="ri-arrow-right-s-line text-muted me-1"></i><?php else: ?><i class="ri-folder-line me-2 <?= $isActive ? '' : 'text-muted' ?>"></i><?php endif; ?>
        <span class="flex-grow-1 <?= $isActive ? 'fw-medium' : '' ?>">
            <?= e($g['name']) ?>
            <?php if ($g['is_system']): ?><i class="ri-shield-check-line text-warning ms-1"></i><?php endif; ?>
        </span>
        <span class="badge <?= $isActive ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' ?> rounded-pill"><?= $g['user_count'] ?></span>
        <?php if (!$g['is_system']): ?>
        <form method="POST" action="<?= url('settings/perm-groups/' . $g['id'] . '/clone') ?>" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost-info btn-icon btn-sm ms-1" title="Nhân bản"><i class="ri-file-copy-line"></i></button>
        </form>
        <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm ms-1 edit-group" data-id="<?= $g['id'] ?>" data-name="<?= e($g['name']) ?>" data-parent="<?= $g['parent_id'] ?>" data-desc="<?= e($g['description'] ?? '') ?>" data-color="<?= e($g['color'] ?? '#405189') ?>" title="Sửa"><i class="ri-pencil-line"></i></button>
        <form method="POST" action="<?= url('settings/perm-groups/' . $g['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa nhóm <?= e($g['name']) ?>?">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost-danger btn-icon btn-sm" title="Xóa"><i class="ri-delete-bin-line"></i></button>
        </form>
        <?php endif; ?>
    </li>
    <?php if (!empty($g['children'])) renderGroupTree($g['children'], $selectedId, $level + 1); ?>
<?php endforeach;
}
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Phân quyền</h4>
</div>

<div class="card mb-0">
    <div class="card-header p-0 border-0">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-groups" role="tab"><i class="ri-shield-user-line me-1"></i>Nhóm quyền</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-userlist" role="tab"><i class="ri-group-line me-1"></i>Danh sách người dùng</a>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content">
<!-- Tab 1: Nhóm quyền -->
<div class="tab-pane fade show active" id="tab-groups" role="tabpanel">
<div class="row">
    <!-- Left: Group Tree -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Nhóm quyền</h5>
                <button type="button" class="btn btn-primary py-1 px-2" data-bs-toggle="modal" data-bs-target="#groupModal" id="btnAddGroup"><i class="ri-add-line"></i></button>
            </div>
            <ul class="list-group list-group-flush">
                <?php renderGroupTree($tree, $selectedGroupId); ?>
            </ul>
        </div>
    </div>

    <!-- Right: Permission Matrix (AJAX loaded) -->
    <div class="col-lg-9">
        <div class="card" id="permPanel">
            <?php if ($selectedGroup): ?>
                <?php include __DIR__ . '/_permission-panel.php'; ?>
            <?php else: ?>
            <div class="card-body text-center py-5 text-muted">
                <i class="ri-shield-user-line fs-48 d-block mb-3"></i>
                <h5>Chọn nhóm quyền bên trái</h5>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<!-- Tab 2: Danh sách người dùng -->
<div class="tab-pane fade" id="tab-userlist" role="tabpanel">
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Danh sách người dùng</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Người dùng</th>
                    <th>Email</th>
                    <th>Nhóm quyền</th>
                    <th class="text-center" style="width:120px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allUsers as $u): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($u['avatar'])): ?>
                            <img src="<?= asset($u['avatar']) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
                            <?php else: ?>
                            <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px"><?= strtoupper(substr($u['name'], 0, 1)) ?></span>
                            <?php endif; ?>
                            <span class="fw-medium"><?= e($u['name']) ?></span>
                        </div>
                    </td>
                    <td class="text-muted"><?= e($u['email']) ?></td>
                    <td>
                        <?php if (!empty($userGroupMap[$u['id']])): ?>
                            <?php foreach ($userGroupMap[$u['id']] as $ug): ?>
                            <span class="badge rounded-pill me-1" style="background-color:<?= e($ug['color'] ?? '#405189') ?>"><?= e($ug['name']) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted fs-12">Chưa có nhóm</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-soft-primary py-1 px-2 change-user-group" data-user-id="<?= $u['id'] ?>" data-user-name="<?= e($u['name']) ?>" data-bs-toggle="modal" data-bs-target="#changeGroupModal"><i class="ri-exchange-line me-1"></i>Đổi nhóm</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</div>

<!-- Modal: Add/Edit Group -->
<div class="modal fade" id="groupModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('settings/perm-groups/store') ?>" id="groupForm">
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

<!-- Modal: Change User Group -->
<div class="modal fade" id="changeGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đổi nhóm quyền - <span id="changeGroupUserName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="changeGroupUserId">
                <div id="changeGroupList">
                    <?php foreach ($groups as $g): ?>
                    <div class="form-check py-2 border-bottom">
                        <input class="form-check-input change-group-checkbox" type="checkbox" value="<?= $g['id'] ?>" id="chgGroup<?= $g['id'] ?>" data-group-name="<?= e($g['name']) ?>">
                        <label class="form-check-label" for="chgGroup<?= $g['id'] ?>">
                            <span class="badge rounded-pill me-1" style="background-color:<?= e($g['color'] ?? '#405189') ?>">&nbsp;</span>
                            <?= e($g['name']) ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btnSaveChangeGroup"><i class="ri-save-line me-1"></i>Lưu</button>
            </div>
        </div>
    </div>
</div>

<script>
var currentGroupId = <?= $selectedGroupId ?>;
var csrfToken = '<?= csrf_token() ?>';
var baseUrl = '<?= url('settings/permissions') ?>';
var apiUrl = '<?= url('settings/perm-groups') ?>';

// Load group panel via AJAX
function loadGroupPanel(groupId, el) {
    currentGroupId = groupId;
    // Update active state in sidebar
    document.querySelectorAll('.list-group-item').forEach(function(li) { li.classList.remove('active'); });
    if (el) el.classList.add('active');
    // Update URL without reload
    history.replaceState(null, '', baseUrl + '?group=' + groupId);

    var panel = document.getElementById('permPanel');
    panel.innerHTML = '<div class="card-body text-center py-5"><div class="spinner-border text-primary"></div></div>';

    fetch(apiUrl + '/' + groupId + '/panel', {headers: {'X-Requested-With': 'XMLHttpRequest'}})
    .then(function(r) { return r.json(); })
    .then(function(data) {
        panel.innerHTML = data.html;
        // Execute inline scripts from loaded HTML
        panel.querySelectorAll('script').forEach(function(oldScript) {
            var newScript = document.createElement('script');
            newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
        bindPanelEvents();
    })
    .catch(function() { panel.innerHTML = '<div class="card-body text-center py-5 text-danger">Lỗi tải dữ liệu</div>'; });
}

// Bind events for dynamically loaded panel
function bindPanelEvents() {
    // Remove user
    document.querySelectorAll('.remove-user').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Xóa người dùng khỏi nhóm?')) return;
            fetch(apiUrl + '/' + currentGroupId + '/remove-user', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                body: JSON.stringify({user_id: this.dataset.userId})
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.success) loadGroupPanel(currentGroupId);
            });
        });
    });
}

// Add user (modal)
document.querySelectorAll('.add-user-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        fetch(apiUrl + '/' + currentGroupId + '/add-user', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
            body: JSON.stringify({user_id: this.dataset.userId})
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addUserModal'))?.hide();
                loadGroupPanel(currentGroupId);
                // Reload panel to update user list
                loadGroupPanel(currentGroupId);
            } else alert(data.error || 'Lỗi');
        });
    });
});

// Search users in modal
document.getElementById('userSearch')?.addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.user-item').forEach(function(el) {
        el.style.display = el.dataset.name.includes(q) ? '' : 'none';
    });
});

// Edit group
document.querySelectorAll('.edit-group').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault(); e.stopPropagation();
        document.getElementById('groupForm').action = apiUrl + '/' + this.dataset.id + '/update';
        document.getElementById('groupModalTitle').textContent = 'Sửa nhóm quyền';
        document.getElementById('groupName').value = this.dataset.name;
        document.getElementById('groupParent').value = this.dataset.parent || '';
        document.getElementById('groupDesc').value = this.dataset.desc;
        document.getElementById('groupColor').value = this.dataset.color;
        new bootstrap.Modal(document.getElementById('groupModal')).show();
    });
});

// Add group modal reset
document.getElementById('btnAddGroup')?.addEventListener('click', function() {
    document.getElementById('groupForm').action = apiUrl + '/store';
    document.getElementById('groupModalTitle').textContent = 'Thêm nhóm quyền';
    document.getElementById('groupName').value = '';
    document.getElementById('groupParent').value = '';
    document.getElementById('groupDesc').value = '';
    document.getElementById('groupColor').value = '#405189';
});

// Change user group modal
document.querySelectorAll('.change-user-group').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var userId = this.dataset.userId;
        var userName = this.dataset.userName;
        document.getElementById('changeGroupUserId').value = userId;
        document.getElementById('changeGroupUserName').textContent = userName;
        // Check current groups for this user
        document.querySelectorAll('.change-group-checkbox').forEach(function(cb) { cb.checked = false; });
        // Find current groups from badges in the same row
        var row = this.closest('tr');
        var badges = row.querySelectorAll('.badge');
        var currentGroups = [];
        badges.forEach(function(b) { currentGroups.push(b.textContent.trim()); });
        document.querySelectorAll('.change-group-checkbox').forEach(function(cb) {
            if (currentGroups.includes(cb.dataset.groupName)) cb.checked = true;
        });
    });
});

// Save group changes
document.getElementById('btnSaveChangeGroup')?.addEventListener('click', function() {
    var userId = document.getElementById('changeGroupUserId').value;
    var checkedGroups = [];
    document.querySelectorAll('.change-group-checkbox:checked').forEach(function(cb) {
        checkedGroups.push(cb.value);
    });
    // First remove from all groups, then add to selected
    var allGroupIds = [];
    document.querySelectorAll('.change-group-checkbox').forEach(function(cb) { allGroupIds.push(cb.value); });

    var promises = [];
    // Remove from all first
    allGroupIds.forEach(function(gid) {
        promises.push(fetch(apiUrl + '/' + gid + '/remove-user', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
            body: JSON.stringify({user_id: userId})
        }));
    });

    Promise.all(promises).then(function() {
        // Add to selected groups
        var addPromises = [];
        checkedGroups.forEach(function(gid) {
            addPromises.push(fetch(apiUrl + '/' + gid + '/add-user', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                body: JSON.stringify({user_id: userId})
            }));
        });
        return Promise.all(addPromises);
    }).then(function() {
        bootstrap.Modal.getInstance(document.getElementById('changeGroupModal'))?.hide();
        location.reload();
    });
});

// Bind initial panel events
bindPanelEvents();
</script>
