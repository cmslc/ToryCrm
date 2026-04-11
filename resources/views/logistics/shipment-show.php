<?php
$pageTitle = 'Lô ' . e($shipment['shipment_code']);
$stLabels = ['preparing'=>'Đang chuẩn bị','in_transit'=>'Đang vận chuyển','arrived'=>'Đã đến','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$stColors = ['preparing'=>'secondary','in_transit'=>'warning','arrived'=>'success','completed'=>'success','cancelled'=>'danger'];
$pkgLabels = ['pending'=>'Chờ','warehouse_cn'=>'Kho TQ','packed'=>'Đóng gói','shipping'=>'Vận chuyển','warehouse_vn'=>'Kho VN','delivering'=>'Đang giao','delivered'=>'Đã giao','returned'=>'Hoàn','damaged'=>'Hư hỏng'];
$pkgColors = ['pending'=>'secondary','warehouse_cn'=>'info','packed'=>'primary','shipping'=>'warning','warehouse_vn'=>'success','delivering'=>'info','delivered'=>'success','returned'=>'danger','damaged'=>'danger'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= e($shipment['shipment_code']) ?> <span class="badge bg-<?= $stColors[$shipment['status']] ?? 'secondary' ?>"><?= $stLabels[$shipment['status']] ?? '' ?></span></h4>
    <div class="d-flex gap-2">
        <?php if ($shipment['status'] === 'preparing'): ?>
            <form method="POST" action="<?= url('logistics/shipments/' . $shipment['id'] . '/status') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="in_transit"><button class="btn btn-warning"><i class="ri-truck-line me-1"></i> Xuất phát</button></form>
        <?php elseif ($shipment['status'] === 'in_transit'): ?>
            <form method="POST" action="<?= url('logistics/shipments/' . $shipment['id'] . '/status') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="arrived"><button class="btn btn-success"><i class="ri-checkbox-circle-line me-1"></i> Đã đến</button></form>
        <?php elseif ($shipment['status'] === 'arrived'): ?>
            <form method="POST" action="<?= url('logistics/shipments/' . $shipment['id'] . '/status') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="completed"><button class="btn btn-primary"><i class="ri-check-double-line me-1"></i> Hoàn thành</button></form>
        <?php endif; ?>
        <a href="<?= url('logistics/shipments') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
    </div>
</div>

<!-- Stats -->
<div class="row mb-2">
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= e($shipment['origin']) ?> → <?= e($shipment['destination']) ?></h5><span class="text-muted fs-11">Tuyến</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= $shipment['total_packages'] ?></h5><span class="text-muted fs-11">Kiện</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= $shipment['total_bags'] ?></h5><span class="text-muted fs-11">Bao</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= $shipment['total_weight'] > 0 ? rtrim(rtrim(number_format($shipment['total_weight'], 2), '0'), '.') : '-' ?></h5><span class="text-muted fs-11">Kg</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= $shipment['total_cbm'] > 0 ? rtrim(rtrim(number_format($shipment['total_cbm'], 4), '0'), '.') : '-' ?></h5><span class="text-muted fs-11">m³</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= e($shipment['vehicle_info'] ?? '-') ?></h5><span class="text-muted fs-11">Phương tiện</span></div></div></div>
</div>

<!-- Tabs -->
<div class="card">
    <div class="card-header p-0">
        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabPkgs">Kiện hàng <span class="badge bg-primary-subtle text-primary ms-1"><?= count($packages) ?></span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBags">Bao hàng <span class="badge bg-info-subtle text-info ms-1"><?= count($bags) ?></span></a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane active" id="tabPkgs">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Mã kiện</th><th>Tracking</th><th>Sản phẩm</th><th>KH</th><th>Cân</th><th>Trạng thái</th></tr></thead>
                        <tbody>
                        <?php foreach ($packages as $p): ?>
                        <tr>
                            <td><a href="<?= url('logistics/packages/' . $p['id']) ?>" class="fw-medium"><?= e($p['package_code']) ?></a></td>
                            <td class="text-muted fs-12"><?= e($p['tracking_code'] ?? '-') ?></td>
                            <td class="fs-12"><?= e(mb_substr($p['product_name'] ?? '-', 0, 30)) ?></td>
                            <td class="fs-12"><?= e($p['customer_name'] ?? '-') ?></td>
                            <td><?= $p['weight_actual'] ? rtrim(rtrim(number_format($p['weight_actual'], 2), '0'), '.') . ' kg' : '-' ?></td>
                            <td><span class="badge bg-<?= $pkgColors[$p['status']] ?? 'secondary' ?>-subtle text-<?= $pkgColors[$p['status']] ?? 'secondary' ?>"><?= $pkgLabels[$p['status']] ?? $p['status'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($packages)): ?><tr><td colspan="6" class="text-center text-muted py-3">Chưa có kiện</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane" id="tabBags">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Mã bao</th><th>Số kiện</th><th>Cân nặng</th><th>Trạng thái</th></tr></thead>
                        <tbody>
                        <?php
                        $bagStLabels = ['open'=>'Mở','sealed'=>'Niêm phong','shipping'=>'Vận chuyển','arrived'=>'Đã đến','completed'=>'Hoàn thành'];
                        $bagStColors = ['open'=>'warning','sealed'=>'primary','shipping'=>'info','arrived'=>'success','completed'=>'success'];
                        foreach ($bags as $b): ?>
                        <tr>
                            <td class="fw-medium"><?= e($b['bag_code']) ?></td>
                            <td><?= $b['pkg_count'] ?></td>
                            <td><?= $b['total_weight'] > 0 ? rtrim(rtrim(number_format($b['total_weight'], 2), '0'), '.') . ' kg' : '-' ?></td>
                            <td><span class="badge bg-<?= $bagStColors[$b['status']] ?? 'secondary' ?>-subtle text-<?= $bagStColors[$b['status']] ?? 'secondary' ?>"><?= $bagStLabels[$b['status']] ?? $b['status'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bags)): ?><tr><td colspan="4" class="text-center text-muted py-3">Chưa có bao</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

