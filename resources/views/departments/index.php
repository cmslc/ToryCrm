<?php
$pageTitle = 'Phòng ban';
$tree = []; $byId = [];
foreach ($departments as $d) { $byId[$d['id']] = $d; $byId[$d['id']]['children'] = []; }
foreach ($byId as &$d) {
    if ($d['parent_id'] && isset($byId[$d['parent_id']])) { $byId[$d['parent_id']]['children'][] = &$d; }
    else { $tree[] = &$d; }
}
unset($d);

if (!function_exists('renderOrgBox')) {
    function renderOrgBox($node) { ?>
        <li>
            <div class="org-box" style="border-top:3px solid <?= e($node['color']) ?>">
                <div class="org-actions">
                    <div class="dropdown">
                        <button class="btn btn-link p-0 text-muted" data-bs-toggle="dropdown"><i class="ri-more-2-fill fs-14"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= url('departments/' . $node['id']) ?>"><i class="ri-eye-line me-2"></i>Chi tiết</a></li>
                            <li><a class="dropdown-item" href="<?= url('departments/' . $node['id'] . '/members') ?>"><i class="ri-team-line me-2"></i>Thành viên</a></li>
                            <li><a class="dropdown-item edit-dept" href="#" data-id="<?= $node['id'] ?>" data-name="<?= e($node['name']) ?>" data-parent="<?= $node['parent_id'] ?? '' ?>" data-manager="<?= $node['manager_id'] ?? '' ?>" data-description="<?= e($node['description'] ?? '') ?>" data-color="<?= e($node['color']) ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form method="POST" action="<?= url('departments/' . $node['id'] . '/delete') ?>" data-confirm="Xóa phòng ban <?= e($node['name']) ?>?"><?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button></form></li>
                        </ul>
                    </div>
                </div>
                <a href="<?= url('departments/' . $node['id']) ?>" class="fw-semibold text-dark d-block mb-1"><?= e($node['name']) ?></a>
                <?php if ($node['manager_name']): ?>
                <div class="d-flex align-items-center justify-content-center gap-1 mb-1">
                    <?php if (!empty($node['manager_avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $node['manager_avatar'])): ?>
                        <img src="<?= url('uploads/avatars/' . $node['manager_avatar']) ?>" class="rounded-circle" style="width:20px;height:20px;object-fit:cover">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:20px;height:20px;font-size:9px"><?= mb_strtoupper(mb_substr($node['manager_name'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <span class="fs-11 text-muted"><?= e($node['manager_name']) ?></span>
                </div>
                <?php endif; ?>
                <span class="fs-11 text-muted"><i class="ri-team-line me-1"></i><?= $node['member_count'] ?></span>
            </div>
            <?php if (!empty($node['children'])): ?>
            <ul>
                <?php foreach ($node['children'] as $child) renderOrgBox($child); ?>
            </ul>
            <?php endif; ?>
        </li>
    <?php }
}
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sơ đồ tổ chức</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Thêm phòng ban</button>
</div>

<div class="card">
    <div class="card-body" style="overflow-x:auto">
        <?php if (!empty($tree)): ?>
        <div class="orgchart">
            <ul>
                <?php foreach ($tree as $root) renderOrgBox($root); ?>
            </ul>
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

<style>
.orgchart { display: flex; justify-content: center; }
.orgchart ul { display: flex; padding: 0; margin: 0; list-style: none; position: relative; justify-content: center; padding-top: 30px; }
.orgchart ul::before { content:''; position:absolute; top:0; left:50%; border-left:2px solid var(--vz-primary); height:30px; }
.orgchart > ul::before { display:none; }
.orgchart > ul { padding-top:0; }
.orgchart li { display:flex; flex-direction:column; align-items:center; position:relative; padding:0 8px; }

/* Horizontal connector between siblings */
.orgchart li::before, .orgchart li::after {
    content:''; position:absolute; top:0; width:50%; border-top:2px solid var(--vz-primary); height:30px;
}
.orgchart li::before { left:0; }
.orgchart li::after { right:0; }
.orgchart li:first-child::before { left:50%; }
.orgchart li:last-child::after { right:50%; }
.orgchart li:only-child::before, .orgchart li:only-child::after { display:none; }
.orgchart > ul > li::before, .orgchart > ul > li::after { display:none; }

/* Vertical connector from horizontal line down to box */
.orgchart li > .org-box::before {
    content:''; position:absolute; top:-30px; left:50%; border-left:2px solid var(--vz-primary); height:30px;
}
.orgchart > ul > li > .org-box::before { display:none; }

/* The box */
.org-box {
    position:relative; background:var(--vz-card-bg); border:1px solid var(--vz-border-color);
    border-radius:6px; padding:12px 16px; min-width:140px; max-width:200px;
    text-align:center; box-shadow:0 1px 3px rgba(0,0,0,.08); transition:box-shadow .2s;
}
.org-box:hover { box-shadow:0 4px 12px rgba(0,0,0,.12); }
.org-actions { position:absolute; top:6px; right:6px; opacity:0; transition:opacity .15s; }
.org-box:hover .org-actions { opacity:1; }

/* Connector from box down to children */
.orgchart li > ul::before { content:''; position:absolute; top:0; left:50%; border-left:2px solid var(--vz-primary); height:30px; }

@media (max-width: 768px) {
    .orgchart, .orgchart ul { flex-direction: column; align-items: center; }
    .orgchart li { padding: 0; }
    .orgchart li::before, .orgchart li::after { display:none; }
    .orgchart li > .org-box::before { display:block; }
    .orgchart > ul > li > .org-box::before { display:none; }
    .orgchart ul { padding-top:20px; }
    .orgchart li > .org-box::before { height:20px; top:-20px; }
    .orgchart li > ul::before { height:20px; }
}
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
                    <div class="mb-3"><label class="form-label">Tên phòng ban <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" id="deptName" required></div>
                    <div class="mb-3">
                        <label class="form-label">Phòng ban cha</label>
                        <select name="parent_id" class="form-select" id="deptParent"><option value="">Không</option>
                            <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trưởng phòng</label>
                        <select name="manager_id" class="form-select searchable-select" id="deptManager"><option value="">Chưa chọn</option>
                            <?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?>
                        </select>
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
    document.getElementById('deptDesc').value = '';
});
</script>
