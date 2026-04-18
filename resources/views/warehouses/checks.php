<?php $pageTitle = 'Kiểm kho'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Kiểm kho</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('warehouses') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCheckModal"><i class="ri-add-line me-1"></i> Tạo phiếu kiểm</button>
    </div>
</div>

<div class="card">
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Mã phiếu</th><th>Kho</th><th>Số SP</th><th>Người kiểm</th><th>Trạng thái</th><th>Ngày</th><th></th></tr></thead>
                <tbody>
                <?php
                $stColors = ['draft'=>'warning','completed'=>'success','cancelled'=>'danger'];
                $stLabels = ['draft'=>'Đang kiểm','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
                foreach ($checks as $c): ?>
                <tr>
                    <td><a href="<?= url('warehouses/checks/' . $c['id']) ?>" class="fw-medium"><?= e($c['code']) ?></a></td>
                    <td><?= e($c['warehouse_name']) ?></td>
                    <td><?= $c['item_count'] ?></td>
                    <td><?= user_avatar($c['checked_by_name'] ?? null) ?></td>
                    <td><span class="badge bg-<?= $stColors[$c['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$c['status']] ?? 'secondary' ?>"><?= $stLabels[$c['status']] ?? $c['status'] ?></span></td>
                    <td class="text-muted"><?= created_ago($c['created_at']) ?></td>
                    <td><a href="<?= url('warehouses/checks/' . $c['id']) ?>" class="btn btn-soft-primary btn-icon"><i class="ri-eye-line"></i></a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($checks)): ?><tr><td colspan="7" class="text-center text-muted py-4">Chưa có phiếu kiểm kho</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="newCheckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('warehouses/checks/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Tạo phiếu kiểm kho</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn kho <span class="text-danger">*</span></label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">Chọn...</option>
                            <?php foreach ($warehouses as $wh): ?><option value="<?= $wh['id'] ?>"><?= e($wh['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <p class="text-muted fs-12">Hệ thống sẽ tự tạo danh sách sản phẩm từ tồn kho hiện tại.</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Tạo</button></div>
            </form>
        </div>
    </div>
</div>
