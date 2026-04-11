<?php $pageTitle = 'Bao hàng';
$stLabels = ['open'=>'Mở','sealed'=>'Đã niêm','shipping'=>'Vận chuyển','arrived'=>'Đã đến','completed'=>'Hoàn thành'];
$stColors = ['open'=>'warning','sealed'=>'primary','shipping'=>'info','arrived'=>'success','completed'=>'success'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Bao hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('logistics') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Dashboard</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBagModal"><i class="ri-add-line me-1"></i> Tạo bao</button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Mã bao</th><th>Số kiện</th><th>Tổng cân</th><th>Trạng thái</th><th>Người tạo</th><th>Ngày tạo</th></tr></thead>
                <tbody>
                <?php foreach ($bags as $b): ?>
                <tr>
                    <td class="fw-medium"><?= e($b['bag_code']) ?></td>
                    <td><span class="badge bg-primary-subtle text-primary"><?= $b['pkg_count'] ?></span></td>
                    <td><?= $b['total_weight'] ? number_format($b['total_weight'], 2) . ' kg' : '-' ?></td>
                    <td><span class="badge bg-<?= $stColors[$b['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$b['status']] ?? 'secondary' ?>"><?= $stLabels[$b['status']] ?? $b['status'] ?></span></td>
                    <td><?= user_avatar($b['created_by_name'] ?? null) ?></td>
                    <td class="text-muted fs-12"><?= created_ago($b['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bags)): ?><tr><td colspan="6" class="text-center text-muted py-4">Chưa có bao hàng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addBagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('logistics/bags/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Tạo bao hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Mã bao</label><input type="text" class="form-control" name="bag_code" placeholder="Tự tạo nếu để trống"></div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea class="form-control" name="note" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Tạo</button></div>
            </form>
        </div>
    </div>
</div>
