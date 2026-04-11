<?php $pageTitle = 'Phòng ban'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sơ đồ tổ chức</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Thêm phòng ban</button>
</div>

<div class="card">
    <div class="card-body">
        <?php
        $tree = []; $byId = [];
        foreach ($departments as $d) { $byId[$d['id']] = $d; $byId[$d['id']]['children'] = []; }
        foreach ($byId as &$d) {
            if ($d['parent_id'] && isset($byId[$d['parent_id']])) { $byId[$d['parent_id']]['children'][] = &$d; }
            else { $tree[] = &$d; }
        }
        unset($d);

        if (!function_exists('renderDeptNode')) { function renderDeptNode($node, $departments, $users) { ?>
            <li>
                <div class="card border shadow-none mb-0 org-node" style="border-left:4px solid <?= e($node['color']) ?> !important;min-width:220px">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <a href="<?= url('departments/' . $node['id']) ?>" class="fw-semibold text-dark"><?= e($node['name']) ?></a>
                            <div class="dropdown">
                                <button class="btn btn-link p-0 text-muted" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?= url('departments/' . $node['id']) ?>"><i class="ri-eye-line me-2"></i>Chi tiết</a></li>
                                    <li><a class="dropdown-item" href="<?= url('departments/' . $node['id'] . '/members') ?>"><i class="ri-team-line me-2"></i>Thành viên</a></li>
                                    <li><a class="dropdown-item edit-dept" href="#" data-id="<?= $node['id'] ?>" data-name="<?= e($node['name']) ?>" data-parent="<?= $node['parent_id'] ?? '' ?>" data-manager="<?= $node['manager_id'] ?? '' ?>" data-description="<?= e($node['description'] ?? '') ?>" data-color="<?= e($node['color']) ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><form method="POST" action="<?= url('departments/' . $node['id'] . '/delete') ?>" data-confirm="Xóa?"><?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button></form></li>
                                </ul>
                            </div>
                        </div>
                        <?php if ($node['manager_name']): ?>
                        <div class="d-flex align-items-center mb-2">
                            <?php if (!empty($node['manager_avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $node['manager_avatar'])): ?>
                                <img src="<?= url('uploads/avatars/' . $node['manager_avatar']) ?>" class="rounded-circle me-2" style="width:28px;height:28px;object-fit:cover">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary me-2" style="width:28px;height:28px;font-size:11px"><?= mb_strtoupper(mb_substr($node['manager_name'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <div>
                                <p class="mb-0 fs-13 fw-medium"><?= e($node['manager_name']) ?></p>
                                <span class="text-muted fs-11">Trưởng phòng</span>
                            </div>
                        </div>
                        <?php endif; ?>
                        <span class="text-muted fs-12"><i class="ri-team-line me-1"></i><?= $node['member_count'] ?> thành viên</span>
                    </div>
                </div>
                <?php if (!empty($node['children'])): ?>
                <ul>
                    <?php foreach ($node['children'] as $child) renderDeptNode($child, $departments, $users); ?>
                </ul>
                <?php endif; ?>
            </li>
        <?php } } // end function + function_exists
        ?>

        <?php if (!empty($tree)): ?>
        <div style="overflow-x:auto">
            <ul class="org-tree">
                <?php foreach ($tree as $root) renderDeptNode($root, $departments, $users); ?>
            </ul>
        </div>
        <?php else: ?>
        <div class="text-center py-5 text-muted">
            <i class="ri-organization-chart fs-1 d-block mb-2"></i>
            <h5 class="text-muted">Chưa có phòng ban nào</h5>
            <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Tạo phòng ban đầu tiên</button>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.org-tree, .org-tree ul { list-style: none; padding: 0; margin: 0; }
.org-tree { display: flex; justify-content: center; }
.org-tree ul { display: flex; justify-content: center; margin-top: 20px; position: relative; }
.org-tree ul::before { content:''; position:absolute; top:0; left:25%; right:25%; border-top:2px solid var(--vz-border-color); }
.org-tree li { display:flex; flex-direction:column; align-items:center; padding:0 12px; position:relative; }
.org-tree li::before, .org-tree li::after { content:''; position:absolute; top:0; width:50%; border-top:2px solid var(--vz-border-color); height:20px; }
.org-tree li::before { left:0; } .org-tree li::after { right:0; }
.org-tree li:first-child::before, .org-tree li:last-child::after { border:0; }
.org-tree li:only-child::before, .org-tree li:only-child::after { border:0; }
.org-tree > li::before, .org-tree > li::after { border:0; }
.org-tree li > .org-node { position:relative; margin-top:20px; }
.org-tree li > .org-node::before { content:''; position:absolute; top:-20px; left:50%; border-left:2px solid var(--vz-border-color); height:20px; }
.org-tree > li > .org-node::before { border:0; }
.org-tree > li > .org-node { margin-top:0; }
.org-node:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); }
</style>

<!-- Add Modal -->
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
                                <label class="position-relative" style="cursor:pointer">
                                    <input type="radio" name="color" value="<?= $c ?>" class="position-absolute" style="opacity:0" <?= $c === '#405189' ? 'checked' : '' ?>>
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
