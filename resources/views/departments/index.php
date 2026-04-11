<?php $pageTitle = 'Phòng ban'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Quản lý phòng ban</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('departments/org-chart') ?>" class="btn btn-soft-info"><i class="ri-organization-chart me-1"></i> Sơ đồ tổ chức</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Thêm phòng ban</button>
    </div>
</div>

<!-- Org chart style -->
<div class="row">
    <?php foreach ($departments as $dept): ?>
    <div class="col-xl-4 col-md-6">
        <div class="card" style="border-left: 4px solid <?= e($dept['color']) ?>">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="mb-1"><?= e($dept['name']) ?></h5>
                        <?php if ($dept['parent_name']): ?>
                            <span class="text-muted fs-12"><i class="ri-arrow-up-line me-1"></i><?= e($dept['parent_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= url('departments/' . $dept['id']) ?>"><i class="ri-eye-line me-2"></i>Chi tiết</a></li>
                            <li><a class="dropdown-item" href="<?= url('departments/' . $dept['id'] . '/members') ?>"><i class="ri-team-line me-2"></i>Thành viên</a></li>
                            <li><a class="dropdown-item edit-dept" href="#"
                                data-id="<?= $dept['id'] ?>"
                                data-name="<?= e($dept['name']) ?>"
                                data-parent="<?= $dept['parent_id'] ?? '' ?>"
                                data-manager="<?= $dept['manager_id'] ?? '' ?>"
                                data-description="<?= e($dept['description'] ?? '') ?>"
                                data-color="<?= e($dept['color']) ?>">
                                <i class="ri-pencil-line me-2"></i>Sửa</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?= url('departments/' . $dept['id'] . '/delete') ?>" data-confirm="Xóa phòng ban <?= e($dept['name']) ?>?">
                                    <?= csrf_field() ?>
                                    <button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <?php if ($dept['manager_name']): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-xs me-2">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle"><?= strtoupper(substr($dept['manager_name'], 0, 1)) ?></span>
                    </div>
                    <div>
                        <p class="mb-0 fw-medium"><?= e($dept['manager_name']) ?></p>
                        <span class="text-muted fs-12">Trưởng phòng</span>
                    </div>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="<?= url('departments/' . $dept['id'] . '/members') ?>" class="text-muted">
                        <i class="ri-team-line me-1"></i><?= $dept['member_count'] ?> thành viên
                    </a>
                    <span class="badge" style="background-color: <?= e($dept['color']) ?>"><?= e($dept['name']) ?></span>
                </div>

                <?php if ($dept['description']): ?>
                    <p class="text-muted fs-12 mt-2 mb-0"><?= e($dept['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($departments)): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ri-team-line fs-1 text-muted d-block mb-2"></i>
                <h5 class="text-muted">Chưa có phòng ban nào</h5>
                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Tạo phòng ban đầu tiên</button>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

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
