<?php
$pageTitle = 'Phòng ban';

// Build tree
$tree = []; $byId = [];
foreach ($departments as $d) { $byId[$d['id']] = $d; $byId[$d['id']]['children'] = []; }
foreach ($byId as &$d) {
    if ($d['parent_id'] && isset($byId[$d['parent_id']])) { $byId[$d['parent_id']]['children'][] = &$d; }
    else { $tree[] = &$d; }
}
unset($d);
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Quản lý phòng ban</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Thêm phòng ban</button>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($tree)): ?>
            <ul class="dept-tree list-unstyled mb-0">
                <?php foreach ($tree as $root) renderDeptItem($root); ?>
            </ul>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="ri-organization-chart fs-1 d-block mb-2"></i>
                <h5 class="text-muted">Chưa có phòng ban nào</h5>
                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Tạo phòng ban đầu tiên</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
if (!function_exists('renderDeptItem')) {
    function renderDeptItem($node) { ?>
        <li class="dept-tree-item">
            <div class="d-flex align-items-center py-2 px-2 rounded dept-tree-row">
                <!-- Toggle -->
                <?php if (!empty($node['children'])): ?>
                    <a href="javascript:void(0)" class="me-2 dept-toggle" data-bs-toggle="collapse" data-bs-target="#dept-children-<?= $node['id'] ?>">
                        <i class="ri-arrow-right-s-fill fs-18 text-muted dept-arrow"></i>
                    </a>
                <?php else: ?>
                    <span class="me-2" style="width:18px;display:inline-block"></span>
                <?php endif; ?>

                <!-- Color dot -->
                <span class="d-inline-block rounded-circle me-2 flex-shrink-0" style="width:10px;height:10px;background:<?= e($node['color']) ?>"></span>

                <!-- Name + parent -->
                <div class="flex-grow-1 me-3">
                    <a href="<?= url('departments/' . $node['id']) ?>" class="fw-semibold text-dark"><?= e($node['name']) ?></a>
                    <?php if ($node['parent_name']): ?>
                        <span class="text-muted fs-11 ms-1">← <?= e($node['parent_name']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Manager -->
                <div class="d-none d-md-flex align-items-center me-3" style="min-width:160px">
                    <?php if ($node['manager_name']): ?>
                        <?php if (!empty($node['manager_avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $node['manager_avatar'])): ?>
                            <img src="<?= url('uploads/avatars/' . $node['manager_avatar']) ?>" class="rounded-circle me-2" style="width:24px;height:24px;object-fit:cover">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary me-2" style="width:24px;height:24px;font-size:10px"><?= mb_strtoupper(mb_substr($node['manager_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <span class="fs-12"><?= e($node['manager_name']) ?></span>
                    <?php else: ?>
                        <span class="text-muted fs-12">—</span>
                    <?php endif; ?>
                </div>

                <!-- Member count -->
                <span class="badge bg-secondary-subtle text-secondary me-3"><?= $node['member_count'] ?></span>

                <!-- Actions -->
                <div class="dropdown">
                    <button class="btn btn-soft-secondary btn-icon" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('departments/' . $node['id']) ?>"><i class="ri-eye-line me-2"></i>Chi tiết</a></li>
                        <li><a class="dropdown-item" href="<?= url('departments/' . $node['id'] . '/members') ?>"><i class="ri-team-line me-2"></i>Thành viên</a></li>
                        <li><a class="dropdown-item edit-dept" href="#"
                            data-id="<?= $node['id'] ?>" data-name="<?= e($node['name']) ?>"
                            data-parent="<?= $node['parent_id'] ?? '' ?>" data-manager="<?= $node['manager_id'] ?? '' ?>"
                            data-description="<?= e($node['description'] ?? '') ?>" data-color="<?= e($node['color']) ?>">
                            <i class="ri-pencil-line me-2"></i>Sửa</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><form method="POST" action="<?= url('departments/' . $node['id'] . '/delete') ?>" data-confirm="Xóa phòng ban <?= e($node['name']) ?>?"><?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button></form></li>
                    </ul>
                </div>
            </div>

            <?php if (!empty($node['children'])): ?>
                <div class="collapse show" id="dept-children-<?= $node['id'] ?>">
                    <ul class="list-unstyled mb-0 ms-4 border-start ps-2">
                        <?php foreach ($node['children'] as $child) renderDeptItem($child); ?>
                    </ul>
                </div>
            <?php endif; ?>
        </li>
    <?php }
}
?>

<style>
.dept-tree-row:hover { background: var(--vz-light); }
.dept-toggle .dept-arrow { transition: transform .2s; }
.dept-toggle[aria-expanded="false"] .dept-arrow { transform: rotate(0deg); }
.dept-toggle[aria-expanded="true"] .dept-arrow, .dept-toggle:not(.collapsed) .dept-arrow { transform: rotate(90deg); }
</style>

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
