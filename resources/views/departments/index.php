<?php $pageTitle = 'Phòng ban'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sơ đồ tổ chức</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Thêm phòng ban</button>
</div>

<div class="card">
    <div class="card-body">
        <?php
        // Build tree
        $tree = []; $byId = [];
        foreach ($departments as $d) { $byId[$d['id']] = $d; $byId[$d['id']]['children'] = []; }
        foreach ($byId as &$d) {
            if ($d['parent_id'] && isset($byId[$d['parent_id']])) { $byId[$d['parent_id']]['children'][] = &$d; }
            else { $tree[] = &$d; }
        }
        unset($d);

        if (!function_exists('renderDeptTree')) {
            function renderDeptTree($node, $level) {
                $indent = $level * 32;
                ?>
                <div class="d-flex align-items-center py-3 <?= $level > 0 ? 'border-top' : '' ?>" style="padding-left:<?= $indent ?>px">
                    <?php if ($level > 0): ?><span class="text-muted me-2"><i class="ri-corner-down-right-line fs-16"></i></span><?php endif; ?>
                    <span class="d-inline-block rounded-circle me-3 flex-shrink-0" style="width:12px;height:12px;background:<?= e($node['color']) ?>"></span>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center">
                            <a href="<?= url('departments/' . $node['id']) ?>" class="fw-semibold text-dark fs-15 me-2"><?= e($node['name']) ?></a>
                            <span class="badge bg-secondary-subtle text-secondary"><?= $node['member_count'] ?> thành viên</span>
                        </div>
                        <?php if ($node['manager_name']): ?>
                        <div class="d-flex align-items-center mt-1">
                            <?php if (!empty($node['manager_avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $node['manager_avatar'])): ?>
                                <img src="<?= url('uploads/avatars/' . $node['manager_avatar']) ?>" class="rounded-circle me-2" style="width:22px;height:22px;object-fit:cover">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary me-2" style="width:22px;height:22px;font-size:9px"><?= mb_strtoupper(mb_substr($node['manager_name'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <span class="text-muted fs-12"><?= e($node['manager_name']) ?> · Trưởng phòng</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <a href="<?= url('departments/' . $node['id']) ?>" class="btn btn-soft-primary btn-icon" title="Chi tiết"><i class="ri-eye-line"></i></a>
                        <a href="<?= url('departments/' . $node['id'] . '/members') ?>" class="btn btn-soft-info btn-icon" title="Thành viên"><i class="ri-team-line"></i></a>
                        <a href="#" class="btn btn-soft-secondary btn-icon edit-dept" data-id="<?= $node['id'] ?>" data-name="<?= e($node['name']) ?>" data-parent="<?= $node['parent_id'] ?? '' ?>" data-manager="<?= $node['manager_id'] ?? '' ?>" data-description="<?= e($node['description'] ?? '') ?>" data-color="<?= e($node['color']) ?>" title="Sửa"><i class="ri-pencil-line"></i></a>
                        <form method="POST" action="<?= url('departments/' . $node['id'] . '/delete') ?>" data-confirm="Xóa?" class="d-inline"><?= csrf_field() ?><button class="btn btn-soft-danger btn-icon" title="Xóa"><i class="ri-delete-bin-line"></i></button></form>
                    </div>
                </div>
                <?php
                if (!empty($node['children'])) {
                    foreach ($node['children'] as $child) renderDeptTree($child, $level + 1);
                }
            }
        }
        ?>

        <?php if (!empty($tree)): ?>
            <?php foreach ($tree as $root): ?>
                <?php renderDeptTree($root, 0); ?>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="ri-organization-chart fs-1 d-block mb-2"></i>
                <h5 class="text-muted">Chưa có phòng ban nào</h5>
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
                    <div class="mb-3">
                        <label class="form-label">Tên phòng ban <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="deptName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phòng ban cha</label>
                        <select name="parent_id" class="form-select" id="deptParent">
                            <option value="">Không (phòng cấp cao nhất)</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trưởng phòng</label>
                        <select name="manager_id" class="form-select searchable-select" id="deptManager">
                            <option value="">Chưa chọn</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu sắc</label>
                        <div class="d-flex gap-2">
                            <?php foreach (['#405189','#0ab39c','#299cdb','#f7b84b','#f06548','#3577f1','#878a99'] as $c): ?>
                                <label style="cursor:pointer">
                                    <input type="radio" name="color" value="<?= $c ?>" class="d-none" <?= $c === '#405189' ? 'checked' : '' ?>>
                                    <span class="d-inline-block rounded-circle border border-2" style="width:28px;height:28px;background:<?= $c ?>"></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" id="deptDesc" rows="2"></textarea>
                    </div>
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
        var form = document.getElementById('deptForm');
        form.action = '<?= url('departments/') ?>' + this.dataset.id + '/update';
        document.getElementById('deptModalTitle').textContent = 'Sửa phòng ban';
        document.getElementById('deptName').value = this.dataset.name;
        document.getElementById('deptParent').value = this.dataset.parent;
        document.getElementById('deptManager').value = this.dataset.manager;
        document.getElementById('deptDesc').value = this.dataset.description;
        var color = this.dataset.color;
        document.querySelectorAll('[name=color]').forEach(function(r) { r.checked = r.value === color; });
        new bootstrap.Modal(document.getElementById('addDeptModal')).show();
    });
});

document.getElementById('addDeptModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('deptForm').action = '<?= url('departments/store') ?>';
    document.getElementById('deptModalTitle').textContent = 'Thêm phòng ban';
    document.getElementById('deptName').value = '';
    document.getElementById('deptParent').value = '';
    document.getElementById('deptManager').value = '';
    document.getElementById('deptDesc').value = '';
});
</script>
