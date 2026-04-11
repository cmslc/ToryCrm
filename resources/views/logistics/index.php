<?php
$pageTitle = 'Kho Logistics';
$statusLabels = ['pending'=>'Chờ','warehouse_cn'=>'Kho TQ','packed'=>'Đóng gói','shipping'=>'Vận chuyển','warehouse_vn'=>'Kho VN','delivering'=>'Đang giao','delivered'=>'Đã giao','returned'=>'Hoàn','damaged'=>'Hư hỏng'];
$statusColors = ['pending'=>'secondary','warehouse_cn'=>'info','packed'=>'primary','shipping'=>'warning','warehouse_vn'=>'success','delivering'=>'info','delivered'=>'success','returned'=>'danger','damaged'=>'danger'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Kho Logistics</h4>
    <a href="<?= url('logistics/receive') ?>" class="btn btn-primary"><i class="ri-qr-scan-2-line me-1"></i> Nhập kho (Quét mã)</a>
</div>

<!-- Stats -->
<div class="row">
    <?php
    $statCards = [
        ['label'=>'Tổng kiện','value'=>$stats['total'],'icon'=>'ri-box-3-line','color'=>'primary'],
        ['label'=>'Đang vận chuyển','value'=>$stats['shipping'],'icon'=>'ri-truck-line','color'=>'warning'],
        ['label'=>'Đã nhập kho VN','value'=>$stats['warehouse_vn'],'icon'=>'ri-store-2-line','color'=>'success'],
        ['label'=>'Đã giao','value'=>$stats['delivered'],'icon'=>'ri-checkbox-circle-line','color'=>'info'],
    ];
    foreach ($statCards as $sc): ?>
    <div class="col-md-3">
        <div class="card card-animate"><div class="card-body">
            <div class="d-flex align-items-center">
                <div class="avatar-sm me-3"><span class="avatar-title bg-<?= $sc['color'] ?>-subtle rounded-circle"><i class="<?= $sc['icon'] ?> text-<?= $sc['color'] ?> fs-20"></i></span></div>
                <div><p class="text-muted mb-0 text-uppercase fs-11"><?= $sc['label'] ?></p><h4 class="mb-0"><?= number_format($sc['value']) ?></h4></div>
            </div>
        </div></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex gap-2">
            <a href="<?= url('logistics/receive') ?>" class="btn btn-soft-success"><i class="ri-qr-scan-2-line me-1"></i> Nhập kho</a>
            <a href="<?= url('logistics/packages') ?>" class="btn btn-soft-primary"><i class="ri-box-3-line me-1"></i> Kiện hàng</a>
            <a href="<?= url('logistics/bags') ?>" class="btn btn-soft-info"><i class="ri-shopping-bag-3-line me-1"></i> Bao hàng</a>
        </div>
    </div>
</div>

<!-- Recent Packages -->
<div class="card">
    <div class="card-header d-flex align-items-center">
        <h5 class="card-title mb-0 flex-grow-1">Kiện hàng gần đây</h5>
        <a href="<?= url('logistics/packages') ?>" class="btn btn-soft-primary">Xem tất cả</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Mã kiện</th><th>Tracking</th><th>Sản phẩm</th><th>Khách hàng</th><th>Cân nặng</th><th>Trạng thái</th><th>Cập nhật</th></tr></thead>
                <tbody>
                <?php foreach ($recentPackages as $p): ?>
                <tr>
                    <td><a href="<?= url('logistics/packages/' . $p['id']) ?>" class="fw-medium"><?= e($p['package_code']) ?></a></td>
                    <td class="text-muted fs-12"><?= e($p['tracking_code'] ?? '-') ?></td>
                    <td class="fs-12"><?= e($p['product_name'] ?? '-') ?></td>
                    <td class="fs-12"><?= e($p['customer_name'] ?? '-') ?></td>
                    <td><?= $p['weight_actual'] ? number_format($p['weight_actual'], 2) . ' kg' : '-' ?></td>
                    <td><span class="badge bg-<?= $statusColors[$p['status']] ?? 'secondary' ?>-subtle text-<?= $statusColors[$p['status']] ?? 'secondary' ?>"><?= $statusLabels[$p['status']] ?? $p['status'] ?></span></td>
                    <td class="text-muted fs-12"><?= created_ago($p['updated_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentPackages)): ?><tr><td colspan="7" class="text-center text-muted py-4">Chưa có kiện hàng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
