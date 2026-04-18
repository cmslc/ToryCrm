<?php
$actionCols = [
    'view' => 'Truy cập',
    'create' => 'Thêm mới',
    'edit' => 'Sửa',
    'delete' => 'Xóa',
    'approve' => 'Duyệt',
    'payment' => 'Thanh toán',
    'view_all' => 'Xem tất cả',
];

// Getfly-style nested hierarchy
$hierarchy = [
    'Quản lý phòng ban' => ['users' => ['view']],
    'Quản lý người dùng' => ['users' => ['view', 'create', 'edit', 'delete']],
    'Quản lý công việc' => [
        'tasks' => ['view', 'create', 'edit', 'delete', 'approve', 'view_all'],
    ],
    'Quản lý quyền' => ['settings' => ['view']],
    'Chiến dịch - Cơ hội' => [
        '_children' => [
            'Chiến dịch' => ['campaigns' => ['view', 'create', 'edit', 'delete', 'view_all']],
            'Cơ hội' => ['deals' => ['view', 'create', 'edit', 'delete', 'approve', 'view_all']],
        ],
    ],
    'CRM' => [
        '_children' => [
            'Cài đặt CRM' => ['settings' => ['view']],
            'Bán hàng' => [
                '_children' => [
                    'Báo giá' => ['quotations' => ['view', 'create', 'edit', 'delete', 'approve', 'view_all']],
                    'Hợp đồng bán' => ['contracts' => ['view', 'create', 'edit', 'delete', 'approve', 'payment', 'view_all']],
                    'Đơn hàng bán' => ['orders' => ['view', 'create', 'edit', 'delete', 'approve', 'payment', 'view_all']],
                ],
            ],
            'Dùng chung' => [
                '_children' => [
                    'Sản phẩm' => ['products' => ['view', 'create', 'edit', 'delete']],
                    'Khách hàng' => ['contacts' => ['view', 'create', 'edit', 'delete', 'approve', 'view_all']],
                ],
            ],
            'Hạch toán' => ['debts' => ['view', 'create', 'edit', 'delete', 'payment', 'approve']],
            'Báo cáo tài chính' => ['reports' => ['view']],
        ],
    ],
    'Tài chính' => [
        '_children' => [
            'Quỹ thu/chi' => ['fund' => ['view', 'create', 'delete', 'confirm', 'payment', 'view_all']],
        ],
    ],
    'Thống kê KPI' => [
        '_children' => [
            'Khách hàng' => ['contacts' => ['view_all']],
            'Nhân viên' => ['users' => ['view_all']],
            'Chiến dịch' => ['campaigns' => ['view_all']],
            'Sản phẩm' => ['products' => ['view_all']],
            'Công việc' => ['tasks' => ['view_all']],
        ],
    ],
    'Hoạt động' => ['activities' => ['view', 'create', 'edit', 'delete']],
    'Hỗ trợ' => ['tickets' => ['view', 'create', 'edit', 'delete', 'view_all']],
    'Import/Export' => ['import_export' => ['use']],
];

// Inject plugin modules
if (function_exists('plugin_active')) {
    if (plugin_active('warehouse')) {
        $hierarchy['CRM']['_children']['Dùng chung']['_children']['Kho hàng'] = ['logistics' => ['view', 'create', 'edit']];
    }
    if (plugin_active('booking')) {
        $hierarchy['Lịch hẹn'] = ['calendar' => ['view', 'create', 'edit', 'delete']];
    }
    if (plugin_active('email')) {
        $hierarchy['Email Marketing'] = ['email' => ['view', 'create']];
    }
    if (plugin_active('lead-forms')) {
        $hierarchy['Automation'] = ['automation' => ['view', 'create', 'edit', 'delete']];
    }
    if (plugin_active('gamification')) {
        $hierarchy['Hoa hồng'] = ['commissions' => ['view', 'create', 'edit']];
    }
}

// Build permission lookup: module.action => permission id
$permLookup = [];
foreach ($modules as $mod => $perms) {
    foreach ($perms as $p) {
        $permLookup[$mod . '.' . $p['action']] = $p['id'];
    }
}

$group = $group ?? $selectedGroup ?? null;
if (!$group) return;
$selectedGroupId = $group['id'];

// Render hierarchy row
function renderHierarchyRow($label, $config, $level, $actionCols, $permLookup, $groupPermIds) {
    $indent = $level * 24;
    $prefix = $level > 0 ? str_repeat('|', $level - 1) . '--- ' : '';
    $hasChildren = isset($config['_children']);

    if ($hasChildren) {
        // Parent row - just a label, no checkboxes (or aggregate)
        echo '<tr class="' . ($level === 0 ? 'table-light' : '') . '">';
        echo '<td style="padding-left:' . (16 + $indent) . 'px"><strong>' . e($label) . '</strong></td>';
        // Check if this parent has direct module permissions too
        $directModules = array_filter($config, function($k) { return $k !== '_children'; }, ARRAY_FILTER_USE_KEY);
        if (!empty($directModules)) {
            foreach ($actionCols as $act => $actLabel) {
                $permId = null;
                foreach ($directModules as $mod => $actions) {
                    if (in_array($act, $actions)) {
                        $permId = $permLookup[$mod . '.' . $act] ?? null;
                        break;
                    }
                }
                echo '<td class="text-center">';
                if ($permId) {
                    echo '<input type="checkbox" class="form-check-input perm-checkbox" name="perms[]" value="' . $permId . '" ' . (in_array($permId, $groupPermIds) ? 'checked' : '') . '>';
                }
                echo '</td>';
            }
        } else {
            foreach ($actionCols as $act => $actLabel) {
                echo '<td class="text-center"></td>';
            }
        }
        echo '</tr>';

        // Render children
        foreach ($config['_children'] as $childLabel => $childConfig) {
            renderHierarchyRow($childLabel, $childConfig, $level + 1, $actionCols, $permLookup, $groupPermIds);
        }
    } else {
        // Leaf row - has module permissions
        echo '<tr>';
        echo '<td style="padding-left:' . (16 + $indent) . 'px">';
        if ($level > 0) echo '<span class="text-muted">' . str_repeat('|&nbsp;&nbsp;&nbsp;', max(0, $level - 1)) . '|--- </span>';
        echo e($label);
        echo '</td>';

        foreach ($actionCols as $act => $actLabel) {
            $permId = null;
            foreach ($config as $mod => $actions) {
                if (in_array($act, $actions)) {
                    $permId = $permLookup[$mod . '.' . $act] ?? null;
                    break;
                }
            }
            echo '<td class="text-center">';
            if ($permId) {
                echo '<input type="checkbox" class="form-check-input perm-checkbox" name="perms[]" value="' . $permId . '" ' . (in_array($permId, $groupPermIds) ? 'checked' : '') . '>';
            }
            echo '</td>';
        }
        echo '</tr>';
    }
}
?>

<div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">Thiết lập quyền</h5>
            <p class="text-muted mb-0 mt-1">Người dùng trong <strong><?= e($group['name']) ?></strong>:</p>
        </div>
        <?php if ($group['is_system']): ?>
        <span class="badge bg-warning-subtle text-warning"><i class="ri-shield-check-line me-1"></i>Toàn quyền</span>
        <?php endif; ?>
    </div>
</div>
<div class="card-body border-bottom py-3">
    <h6 class="mb-2">Người dùng trong nhóm</h6>
    <div class="d-flex flex-wrap gap-2 align-items-center" id="groupUserList">
        <?php foreach ($groupUsers as $gu): ?>
        <div class="d-flex align-items-center gap-1 border rounded-pill py-1 px-2 bg-light">
            <?php if (!empty($gu['avatar'])): ?>
            <img src="<?= asset($gu['avatar']) ?>" class="rounded-circle" width="24" height="24" style="object-fit:cover">
            <?php else: ?>
            <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:24px;height:24px;font-size:11px"><?= strtoupper(mb_substr($gu['name'], 0, 1)) ?></span>
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
        <table class="table table-hover align-middle mb-0" style="font-size:13px">
            <thead class="table-light sticky-top">
                <tr>
                    <th style="min-width:280px">
                        <strong>Chức năng</strong>
                    </th>
                    <?php foreach ($actionCols as $act => $actLabel): ?>
                    <th class="text-center" style="min-width:75px"><?= $actLabel ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hierarchy as $label => $config): ?>
                    <?php renderHierarchyRow($label, $config, 0, $actionCols, $permLookup, $groupPermIds); ?>
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
