<?php
$actionLabels = [
    'view' => 'Truy cập', 'create' => 'Thêm mới', 'edit' => 'Sửa', 'delete' => 'Xóa',
    'approve' => 'Duyệt', 'view_all' => 'Xem tất cả', 'confirm' => 'Xác nhận', 'manage' => 'Quản lý', 'use' => 'Sử dụng',
];
$moduleLabels = [
    'contacts' => 'Khách hàng', 'companies' => 'Doanh nghiệp', 'deals' => 'Cơ hội', 'tasks' => 'Công việc',
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
<form method="POST" action="<?= url('settings/permissions/' . $selectedGroupId . '/save-perms') ?>" id="permForm">
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
