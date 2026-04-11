<?php
$pageTitle = 'Lô hàng';
$stLabels = ['preparing'=>'Đang chuẩn bị','in_transit'=>'Đang vận chuyển','arrived'=>'Đã đến','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$stColors = ['preparing'=>'secondary','in_transit'=>'warning','arrived'=>'success','completed'=>'success','cancelled'=>'danger'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Lô hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('logistics') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Dashboard</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShipModal"><i class="ri-add-line me-1"></i> Tạo lô</button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Mã lô</th><th>Tuyến</th><th>Phương tiện</th><th>Kiện</th><th>Bao</th><th>Cân nặng</th><th>Khối</th><th>Trạng thái</th><th>Người tạo</th><th>Ngày tạo</th></tr></thead>
                <tbody>
                <?php foreach ($shipments as $s): ?>
                <tr>
                    <td><a href="<?= url('logistics/shipments/' . $s['id']) ?>" class="fw-medium"><?= e($s['shipment_code']) ?></a></td>
                    <td><?= e($s['origin']) ?> → <?= e($s['destination']) ?></td>
                    <td class="text-muted fs-12"><?= e($s['vehicle_info'] ?? '-') ?></td>
                    <td><?= $s['total_packages'] ?></td>
                    <td><?= $s['total_bags'] ?></td>
                    <td><?= $s['total_weight'] > 0 ? rtrim(rtrim(number_format($s['total_weight'], 2), '0'), '.') . ' kg' : '-' ?></td>
                    <td class="fs-12"><?= $s['total_cbm'] > 0 ? rtrim(rtrim(number_format($s['total_cbm'], 4), '0'), '.') . ' m³' : '-' ?></td>
                    <td><span class="badge bg-<?= $stColors[$s['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$s['status']] ?? 'secondary' ?>"><?= $stLabels[$s['status']] ?? $s['status'] ?></span></td>
                    <td><?= user_avatar($s['created_by_name'] ?? null) ?></td>
                    <td class="text-muted fs-12"><?= created_ago($s['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($shipments)): ?><tr><td colspan="10" class="text-center text-muted py-4">Chưa có lô hàng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addShipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('logistics/shipments/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Tạo lô hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Mã lô</label><input type="text" class="form-control" name="shipment_code" placeholder="Tự tạo"></div>
                        <div class="col-3 mb-3"><label class="form-label">Xuất</label><input type="text" class="form-control" name="origin" value="CN"></div>
                        <div class="col-3 mb-3"><label class="form-label">Đến</label><input type="text" class="form-control" name="destination" value="VN"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Phương tiện</label><input type="text" class="form-control" name="vehicle_info" placeholder="VD: Xe tải 5T - BKS 30A-12345"></div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea class="form-control" name="note" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Tạo</button></div>
            </form>
        </div>
    </div>
</div>
