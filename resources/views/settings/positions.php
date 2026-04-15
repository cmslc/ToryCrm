<?php $pageTitle = 'Quản lý chức vụ'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Quản lý chức vụ</h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#posModal" id="btnAdd"><i class="ri-add-line me-1"></i>Thêm chức vụ</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Chức vụ</th>
                        <th>Mô tả</th>
                        <th class="text-center">Số nhân viên</th>
                        <th class="text-center" style="width:120px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($positions)): ?>
                    <?php foreach ($positions as $i => $p): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="fw-medium"><?= e($p['name']) ?></td>
                        <td class="text-muted fs-13"><?= e($p['description'] ?? '-') ?></td>
                        <td class="text-center"><span class="badge bg-primary-subtle text-primary rounded-pill"><?= $p['user_count'] ?></span></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-ghost-secondary btn-icon btn-sm edit-pos" data-id="<?= $p['id'] ?>" data-name="<?= e($p['name']) ?>" data-desc="<?= e($p['description'] ?? '') ?>" title="Sửa"><i class="ri-pencil-line"></i></button>
                            <?php if ($p['user_count'] == 0): ?>
                            <form method="POST" action="<?= url('settings/positions/' . $p['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa chức vụ <?= e($p['name']) ?>?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost-danger btn-icon btn-sm" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="ri-briefcase-line fs-36 d-block mb-2"></i>
                            <h5>Chưa có chức vụ nào</h5>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="posModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('settings/positions/store') ?>" id="posForm">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="posModalTitle">Thêm chức vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tên chức vụ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="posName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="description" id="posDesc" rows="2"></textarea>
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

<script>
document.querySelectorAll('.edit-pos').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('posForm').action = '<?= url('settings/positions/') ?>' + this.dataset.id + '/update';
        document.getElementById('posModalTitle').textContent = 'Sửa chức vụ';
        document.getElementById('posName').value = this.dataset.name;
        document.getElementById('posDesc').value = this.dataset.desc;
        new bootstrap.Modal(document.getElementById('posModal')).show();
    });
});
document.getElementById('btnAdd')?.addEventListener('click', function() {
    document.getElementById('posForm').action = '<?= url('settings/positions/store') ?>';
    document.getElementById('posModalTitle').textContent = 'Thêm chức vụ';
    document.getElementById('posName').value = '';
    document.getElementById('posDesc').value = '';
});
</script>
