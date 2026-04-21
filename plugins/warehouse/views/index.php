<?php $pageTitle = 'Quản lý kho'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Quản lý kho</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('warehouses/movements') ?>" class="btn btn-soft-info"><i class="ri-arrow-left-right-line me-1"></i> Xuất nhập</a>
        <a href="<?= url('warehouses/checks') ?>" class="btn btn-soft-warning"><i class="ri-clipboard-line me-1"></i> Kiểm kho</a>
        <a href="<?= url('warehouses/report') ?>" class="btn btn-soft-success"><i class="ri-bar-chart-box-line me-1"></i> Báo cáo</a>
        <a href="<?= url('warehouses/settings') ?>" class="btn btn-soft-secondary"><i class="ri-settings-3-line me-1"></i> Cài đặt</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWhModal"><i class="ri-add-line me-1"></i> Thêm kho</button>
    </div>
</div>

<div class="row">
    <?php foreach ($warehouses as $wh): ?>
    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <a href="<?= url('warehouses/' . $wh['id']) ?>" class="fw-semibold text-dark fs-15"><?= e($wh['name']) ?></a>
                        <?php if ($wh['code']): ?><span class="badge bg-secondary-subtle text-secondary ms-2"><?= e($wh['code']) ?></span><?php endif; ?>
                        <?php if ($wh['is_default']): ?><span class="badge bg-success ms-1">Mặc định</span><?php endif; ?>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-soft-secondary btn-icon" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= url('warehouses/' . $wh['id']) ?>"><i class="ri-eye-line me-2"></i>Chi tiết</a></li>
                            <li><a class="dropdown-item edit-wh" href="#" data-id="<?= $wh['id'] ?>" data-name="<?= e($wh['name']) ?>" data-code="<?= e($wh['code'] ?? '') ?>" data-address="<?= e($wh['address'] ?? '') ?>" data-phone="<?= e($wh['phone'] ?? '') ?>" data-manager="<?= $wh['manager_id'] ?? '' ?>" data-description="<?= e($wh['description'] ?? '') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form method="POST" action="<?= url('warehouses/' . $wh['id'] . '/delete') ?>" data-confirm="Xóa kho?"><?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button></form></li>
                        </ul>
                    </div>
                </div>
                <?php if ($wh['address']): ?><p class="text-muted fs-12 mb-2"><i class="ri-map-pin-line me-1"></i><?= e($wh['address']) ?></p><?php endif; ?>
                <?php if ($wh['manager_name']): ?>
                <div class="mb-3"><?= user_avatar($wh['manager_name']) ?></div>
                <?php endif; ?>
                <div class="d-flex gap-3">
                    <div><span class="text-muted fs-12">Sản phẩm</span><h5 class="mb-0"><?= $wh['product_count'] ?></h5></div>
                    <div><span class="text-muted fs-12">Tổng tồn</span><h5 class="mb-0"><?= number_format($wh['total_stock']) ?></h5></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($warehouses)): ?>
    <div class="col-12"><div class="card"><div class="card-body text-center py-5 text-muted">
        <i class="ri-store-2-line fs-1 d-block mb-2"></i><h5>Chưa có kho nào</h5>
        <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addWhModal"><i class="ri-add-line me-1"></i> Tạo kho đầu tiên</button>
    </div></div></div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addWhModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('warehouses/store') ?>" id="whForm">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title" id="whModalTitle">Thêm kho</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-8 mb-3"><label class="form-label">Tên kho <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" id="whName" required></div>
                        <div class="col-4 mb-3"><label class="form-label">Mã kho</label><input type="text" class="form-control" name="code" id="whCode" placeholder="WH01"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Địa chỉ</label><input type="text" class="form-control" name="address" id="whAddress"></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Điện thoại</label><input type="text" class="form-control" name="phone" id="whPhone"></div>
                        <div class="col-6 mb-3"><label class="form-label">Quản lý kho</label>
                            <?php $deptGrouped = []; foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                            <select name="manager_id" class="form-select" id="whManager"><option value="">Chưa chọn</option>
                            <?php foreach ($deptGrouped as $dept => $dUsers): ?><optgroup label="<?= e($dept) ?>"><?php foreach ($dUsers as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Mô tả</label><textarea class="form-control" name="description" id="whDesc" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Lưu</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-wh').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('whForm').action = '<?= url('warehouses/') ?>' + this.dataset.id + '/update';
        document.getElementById('whModalTitle').textContent = 'Sửa kho';
        document.getElementById('whName').value = this.dataset.name;
        document.getElementById('whCode').value = this.dataset.code;
        document.getElementById('whAddress').value = this.dataset.address;
        document.getElementById('whPhone').value = this.dataset.phone;
        document.getElementById('whManager').value = this.dataset.manager;
        document.getElementById('whDesc').value = this.dataset.description;
        new bootstrap.Modal(document.getElementById('addWhModal')).show();
    });
});
document.getElementById('addWhModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('whForm').action = '<?= url('warehouses/store') ?>';
    document.getElementById('whModalTitle').textContent = 'Thêm kho';
    ['whName','whCode','whAddress','whPhone','whManager','whDesc'].forEach(function(id) { document.getElementById(id).value = ''; });
});
</script>
