<?php
$actionLabels = [
    'view' => 'Truy cập', 'create' => 'Thêm mới', 'edit' => 'Sửa', 'delete' => 'Xóa',
    'approve' => 'Duyệt', 'view_all' => 'Xem tất cả', 'confirm' => 'Xác nhận', 'manage' => 'Quản lý', 'use' => 'Sử dụng',
];
$moduleLabels = [
    'contacts' => 'Khách hàng', 'deals' => 'Cơ hội', 'tasks' => 'Công việc',
    'products' => 'Sản phẩm', 'orders' => 'Đơn hàng', 'tickets' => 'Hỗ trợ', 'campaigns' => 'Chiến dịch',
    'fund' => 'Quỹ thu/chi', 'users' => 'Người dùng', 'reports' => 'Báo cáo', 'automation' => 'Tự động hóa',
    'webhooks' => 'Webhooks', 'settings' => 'Cài đặt', 'import_export' => 'Import/Export',
];
$group = $group ?? $selectedGroup ?? null;
if (!$group) return;
$selectedGroupId = $group['id'];
?>

<div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-1"><?= e($group['name']) ?></h5>
            <?php if ($group['description']): ?>
            <p class="text-muted mb-0 fs-13"><?= e($group['description']) ?></p>
            <?php endif; ?>
        </div>
        <?php if ($group['is_system']): ?>
        <span class="badge bg-warning-subtle text-warning"><i class="ri-shield-check-line me-1"></i>Toàn quyền</span>
        <?php endif; ?>
    </div>
</div>
<div class="card-body border-bottom">
    <h6 class="mb-2">Người dùng trong nhóm</h6>
    <div class="d-flex flex-wrap gap-2 align-items-center" id="groupUserList">
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

<?php if (!$group['is_system']): ?>
<form method="POST" action="<?= url('settings/perm-groups/' . $selectedGroupId . '/save-perms') ?>" id="permForm">
    <?= csrf_field() ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="min-width:200px">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="permMasterToggle">
                            <label class="form-check-label fw-medium" for="permMasterToggle">Chọn tất cả</label>
                        </div>
                    </th>
                    <?php foreach ($allActions as $idx => $act): ?>
                    <th class="text-center" style="min-width:80px">
                        <div class="form-check d-flex flex-column align-items-center gap-1">
                            <input type="checkbox" class="form-check-input perm-col-toggle" data-col="<?= $idx ?>">
                            <label class="form-check-label"><?= $actionLabels[$act] ?? ucfirst($act) ?></label>
                        </div>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modules as $mod => $perms):
                    $permByAction = [];
                    foreach ($perms as $p) $permByAction[$p['action']] = $p;
                ?>
                <tr data-module="<?= $mod ?>">
                    <td class="fw-medium">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input perm-row-toggle">
                            <label class="form-check-label"><?= $moduleLabels[$mod] ?? ucfirst($mod) ?></label>
                        </div>
                    </td>
                    <?php foreach ($allActions as $idx => $act):
                        $p = $permByAction[$act] ?? null;
                    ?>
                    <td class="text-center">
                        <?php if ($p): ?>
                        <input type="checkbox" class="form-check-input perm-checkbox" name="perms[]" value="<?= $p['id'] ?>" data-col="<?= $idx ?>" <?= in_array($p['id'], $groupPermIds) ? 'checked' : '' ?>>
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
<script>
(function() {
    var form = document.getElementById('permForm');
    if (!form) return;

    var master = form.querySelector('#permMasterToggle');
    var colToggles = form.querySelectorAll('.perm-col-toggle');
    var rowToggles = form.querySelectorAll('.perm-row-toggle');
    var allCheckboxes = form.querySelectorAll('.perm-checkbox');

    function setIndeterminate(toggle, checked, total) {
        if (total === 0) {
            toggle.checked = false;
            toggle.indeterminate = false;
        } else if (checked === total) {
            toggle.checked = true;
            toggle.indeterminate = false;
        } else if (checked === 0) {
            toggle.checked = false;
            toggle.indeterminate = false;
        } else {
            toggle.checked = false;
            toggle.indeterminate = true;
        }
    }

    function updateColToggle(colIdx) {
        var cbs = form.querySelectorAll('.perm-checkbox[data-col="' + colIdx + '"]');
        var total = cbs.length;
        var checked = 0;
        cbs.forEach(function(cb) { if (cb.checked) checked++; });
        colToggles.forEach(function(ct) {
            if (ct.dataset.col == colIdx) setIndeterminate(ct, checked, total);
        });
    }

    function updateRowToggle(row) {
        var rt = row.querySelector('.perm-row-toggle');
        if (!rt) return;
        var cbs = row.querySelectorAll('.perm-checkbox');
        var total = cbs.length;
        var checked = 0;
        cbs.forEach(function(cb) { if (cb.checked) checked++; });
        setIndeterminate(rt, checked, total);
    }

    function updateMaster() {
        var total = allCheckboxes.length;
        var checked = 0;
        allCheckboxes.forEach(function(cb) { if (cb.checked) checked++; });
        setIndeterminate(master, checked, total);
    }

    function updateAllToggles() {
        colToggles.forEach(function(ct) { updateColToggle(ct.dataset.col); });
        rowToggles.forEach(function(rt) { updateRowToggle(rt.closest('tr')); });
        updateMaster();
    }

    // Master toggle
    master.addEventListener('change', function() {
        var state = this.checked;
        allCheckboxes.forEach(function(cb) { cb.checked = state; });
        colToggles.forEach(function(ct) { ct.checked = state; ct.indeterminate = false; });
        rowToggles.forEach(function(rt) { rt.checked = state; rt.indeterminate = false; });
    });

    // Column toggle
    colToggles.forEach(function(ct) {
        ct.addEventListener('change', function() {
            var state = this.checked;
            var col = this.dataset.col;
            form.querySelectorAll('.perm-checkbox[data-col="' + col + '"]').forEach(function(cb) {
                cb.checked = state;
            });
            rowToggles.forEach(function(rt) { updateRowToggle(rt.closest('tr')); });
            updateMaster();
        });
    });

    // Row toggle
    rowToggles.forEach(function(rt) {
        rt.addEventListener('change', function() {
            var state = this.checked;
            var row = this.closest('tr');
            row.querySelectorAll('.perm-checkbox').forEach(function(cb) {
                cb.checked = state;
            });
            colToggles.forEach(function(ct) { updateColToggle(ct.dataset.col); });
            updateMaster();
        });
    });

    // Individual checkbox
    allCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            updateColToggle(this.dataset.col);
            updateRowToggle(this.closest('tr'));
            updateMaster();
        });
    });

    // Init states on load
    updateAllToggles();
})();
</script>
<?php else: ?>
<div class="card-body text-center py-4 text-muted">
    <i class="ri-shield-check-line fs-36 text-warning d-block mb-2"></i>
    <p>Nhóm hệ thống có toàn quyền truy cập tất cả chức năng.</p>
</div>
<?php endif; ?>
