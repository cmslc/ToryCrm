<?php
$pageTitle = 'Phòng ban';
$tree = []; $byId = [];
foreach ($departments as $d) { $byId[$d['id']] = $d; $byId[$d['id']]['children'] = []; }
foreach ($byId as &$d) {
    if ($d['parent_id'] && isset($byId[$d['parent_id']])) { $byId[$d['parent_id']]['children'][] = &$d; }
    else { $tree[] = &$d; }
}
unset($d);

// Flatten tree with level + last-child info
$flatList = [];
if (!function_exists('flattenDeptTree')) {
    function flattenDeptTree($nodes, $level, &$out) {
        $count = count($nodes);
        foreach ($nodes as $i => $node) {
            $node['_level'] = $level;
            $node['_last'] = ($i === $count - 1);
            $out[] = $node;
            if (!empty($node['children'])) {
                flattenDeptTree($node['children'], $level + 1, $out);
            }
        }
    }
}
flattenDeptTree($tree, 0, $flatList);
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sơ đồ tổ chức</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Thêm phòng ban</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($flatList)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:300px">Phòng ban</th>
                        <th style="width:180px">Trưởng phòng</th>
                        <th style="width:180px">Phó phòng</th>
                        <th style="width:90px" class="text-center">Thành viên</th>
                        <th style="width:120px" class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($flatList as $dept):
                        $indent = $dept['_level'] * 24;
                        $prefix = '';
                        if ($dept['_level'] > 0) {
                            $prefix = $dept['_last'] ? '└' : '├';
                        }
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center" style="padding-left:<?= $indent ?>px">
                                <?php if ($prefix): ?>
                                    <span class="text-muted me-2 fs-12" style="font-family:monospace;line-height:1"><?= $prefix ?></span>
                                <?php endif; ?>
                                <span class="d-inline-block rounded-circle me-2 flex-shrink-0" style="width:10px;height:10px;background:<?= e($dept['color']) ?>"></span>
                                <a href="<?= url('departments/' . $dept['id']) ?>" class="fw-semibold text-dark"><?= e($dept['name']) ?></a>
                            </div>
                        </td>
                        <td><?= $dept['manager_name'] ? user_avatar($dept['manager_name'], 'primary', $dept['manager_avatar'] ?? null) : '<span class="text-muted">—</span>' ?></td>
                        <td><?= $dept['vice_manager_name'] ? user_avatar($dept['vice_manager_name'], 'info', $dept['vice_manager_avatar'] ?? null) : '<span class="text-muted">—</span>' ?></td>
                        <td class="text-center">
                            <a href="<?= url('departments/' . $dept['id'] . '/members') ?>" class="badge bg-secondary-subtle text-secondary"><?= $dept['member_count'] ?></a>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="<?= url('departments/' . $dept['id']) ?>" class="btn btn-soft-primary btn-icon" title="Chi tiết"><i class="ri-eye-line"></i></a>
                                <a href="#" class="btn btn-soft-secondary btn-icon edit-dept"
                                    data-id="<?= $dept['id'] ?>" data-name="<?= e($dept['name']) ?>"
                                    data-parent="<?= $dept['parent_id'] ?? '' ?>" data-manager="<?= $dept['manager_id'] ?? '' ?>"
                                    data-vicemanager="<?= $dept['vice_manager_id'] ?? '' ?>"
                                    data-description="<?= e($dept['description'] ?? '') ?>" data-color="<?= e($dept['color']) ?>"
                                    title="Sửa"><i class="ri-pencil-line"></i></a>
                                <form method="POST" action="<?= url('departments/' . $dept['id'] . '/delete') ?>" data-confirm="Xóa phòng ban <?= e($dept['name']) ?>?" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-soft-danger btn-icon" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5 text-muted">
            <i class="ri-organization-chart fs-1 d-block mb-2"></i>
            <h5>Chưa có phòng ban nào</h5>
            <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Tạo phòng ban đầu tiên</button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('departments/store') ?>" id="deptForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="deptModalTitle">Thêm phòng ban</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Tên phòng ban <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" id="deptName" required></div>
                    <div class="mb-3">
                        <label class="form-label">Phòng ban cha</label>
                        <select name="parent_id" class="form-select" id="deptParent"><option value="">Không</option>
                            <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Trưởng phòng</label>
                            <select name="manager_id" class="form-select searchable-select" id="deptManager"><option value="">Chưa chọn</option>
                                <?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Phó phòng</label>
                            <select name="vice_manager_id" class="form-select searchable-select" id="deptViceManager"><option value="">Chưa chọn</option>
                                <?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu sắc</label>
                        <div class="d-flex gap-2">
                            <?php foreach (['#405189','#0ab39c','#299cdb','#f7b84b','#f06548','#3577f1','#878a99'] as $c): ?>
                                <label style="cursor:pointer"><input type="radio" name="color" value="<?= $c ?>" class="d-none" <?= $c === '#405189' ? 'checked' : '' ?>><span class="d-inline-block rounded-circle border border-2" style="width:28px;height:28px;background:<?= $c ?>"></span></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Mô tả</label><textarea class="form-control" name="description" id="deptDesc" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-dept').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('deptForm').action = '<?= url('departments/') ?>' + this.dataset.id + '/update';
        document.getElementById('deptModalTitle').textContent = 'Sửa phòng ban';
        document.getElementById('deptName').value = this.dataset.name;
        document.getElementById('deptParent').value = this.dataset.parent;
        document.getElementById('deptManager').value = this.dataset.manager;
        document.getElementById('deptViceManager').value = this.dataset.vicemanager || '';
        document.getElementById('deptDesc').value = this.dataset.description;
        var c = this.dataset.color;
        document.querySelectorAll('[name=color]').forEach(function(r) { r.checked = r.value === c; });
        new bootstrap.Modal(document.getElementById('addDeptModal')).show();
    });
});
document.getElementById('addDeptModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('deptForm').action = '<?= url('departments/store') ?>';
    document.getElementById('deptModalTitle').textContent = 'Thêm phòng ban';
    document.getElementById('deptName').value = '';
    document.getElementById('deptParent').value = '';
    document.getElementById('deptManager').value = '';
    document.getElementById('deptViceManager').value = '';
    document.getElementById('deptDesc').value = '';
});
</script>
