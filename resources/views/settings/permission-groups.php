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
                // Update user count badge in sidebar
                var li = document.querySelector('[data-group-id="' + currentGroupId + '"]');
                if (li) {
                    var badge = li.querySelector('.badge');
                    if (badge) badge.textContent = parseInt(badge.textContent) + 1;
                }
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

// Bind initial panel events
bindPanelEvents();
</script>
