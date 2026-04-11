<?php
$pageTitle = e($package['package_code']);
$statusLabels = ['pending'=>'Chờ','warehouse_cn'=>'Kho TQ','packed'=>'Đóng gói','shipping'=>'Vận chuyển','warehouse_vn'=>'Kho VN','delivering'=>'Đang giao','delivered'=>'Đã giao','returned'=>'Hoàn','damaged'=>'Hư hỏng'];
$statusColors = ['pending'=>'secondary','warehouse_cn'=>'info','packed'=>'primary','shipping'=>'warning','warehouse_vn'=>'success','delivering'=>'info','delivered'=>'success','returned'=>'danger','damaged'=>'danger'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= e($package['package_code']) ?> <span class="badge bg-<?= $statusColors[$package['status']] ?? 'secondary' ?>"><?= $statusLabels[$package['status']] ?? $package['status'] ?></span></h4>
    <a href="<?= url('logistics/packages') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thông tin kiện hàng</h5></div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr><th class="text-muted" width="160">Mã kiện</th><td class="fw-medium"><?= e($package['package_code']) ?></td></tr>
                    <tr><th class="text-muted">Tracking</th><td><?= e($package['tracking_code'] ?? '-') ?></td></tr>
                    <?php if ($package['tracking_intl']): ?><tr><th class="text-muted">Tracking QT</th><td><?= e($package['tracking_intl']) ?></td></tr><?php endif; ?>
                    <tr><th class="text-muted">Sản phẩm</th><td><?= e($package['product_name'] ?? '-') ?></td></tr>
                    <tr><th class="text-muted">Khách hàng</th><td><?= e($package['customer_name'] ?? '-') ?> <?= $package['customer_phone'] ? '(' . e($package['customer_phone']) . ')' : '' ?></td></tr>
                    <tr><th class="text-muted">Số lượng</th><td><?= $package['quantity'] ?></td></tr>
                    <tr><th class="text-muted">Cân nặng</th><td><?= $package['weight_actual'] ? number_format($package['weight_actual'], 2) . ' kg' : '-' ?><?= $package['weight_volume'] ? ' (QĐ: ' . number_format($package['weight_volume'], 2) . ' kg)' : '' ?></td></tr>
                    <?php if ($package['length_cm']): ?><tr><th class="text-muted">Kích thước</th><td><?= $package['length_cm'] ?> × <?= $package['width_cm'] ?> × <?= $package['height_cm'] ?> cm</td></tr><?php endif; ?>
                    <tr><th class="text-muted">Trạng thái</th><td><span class="badge bg-<?= $statusColors[$package['status']] ?? 'secondary' ?> fs-12"><?= $statusLabels[$package['status']] ?? $package['status'] ?></span></td></tr>
                    <tr><th class="text-muted">Người nhận</th><td><?= user_avatar($package['received_by_name'] ?? null) ?></td></tr>
                    <?php if ($package['received_at']): ?><tr><th class="text-muted">Ngày nhận</th><td><?= date('d/m/Y H:i', strtotime($package['received_at'])) ?></td></tr><?php endif; ?>
                    <tr><th class="text-muted">Người tạo</th><td><?= user_avatar($package['created_by_name'] ?? null, 'success') ?></td></tr>
                    <tr><th class="text-muted">Ngày tạo</th><td><?= date('d/m/Y H:i', strtotime($package['created_at'])) ?></td></tr>
                    <?php if ($package['note']): ?><tr><th class="text-muted">Ghi chú</th><td><?= e($package['note']) ?></td></tr><?php endif; ?>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Lịch sử trạng thái</h5></div>
            <div class="card-body">
                <?php foreach ($history as $h): ?>
                <div class="d-flex mb-3">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-xs"><div class="avatar-title rounded-circle bg-<?= $statusColors[$h['new_status']] ?? 'secondary' ?>-subtle text-<?= $statusColors[$h['new_status']] ?? 'secondary' ?>"><i class="ri-arrow-right-line"></i></div></div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fs-13"><?= $statusLabels[$h['new_status']] ?? $h['new_status'] ?></h6>
                        <?php if ($h['note']): ?><p class="text-muted mb-1 fs-12"><?= e($h['note']) ?></p><?php endif; ?>
                        <small class="text-muted"><?= e($h['changed_by_name'] ?? '') ?> · <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($history)): ?><p class="text-muted text-center mb-0">Chưa có lịch sử</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>
