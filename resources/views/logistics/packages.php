<?php
$pageTitle = 'Kiện hàng';
$statusLabels = ['pending'=>'Chờ','warehouse_cn'=>'Kho TQ','packed'=>'Đóng gói','shipping'=>'Vận chuyển','warehouse_vn'=>'Kho VN','delivering'=>'Đang giao','delivered'=>'Đã giao','returned'=>'Hoàn','damaged'=>'Hư hỏng'];
$statusColors = ['pending'=>'secondary','warehouse_cn'=>'info','packed'=>'primary','shipping'=>'warning','warehouse_vn'=>'success','delivering'=>'info','delivered'=>'success','returned'=>'danger','damaged'=>'danger'];
$currentStatus = $filters['status'] ?? '';
$totalAll = 0; $countMap = [];
foreach ($statusCounts as $sc) { $countMap[$sc['status']] = $sc['count']; $totalAll += $sc['count']; }
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Kiện hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('logistics/receive') ?>" class="btn btn-soft-success"><i class="ri-qr-scan-2-line me-1"></i> Nhập kho</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPkgModal"><i class="ri-add-line me-1"></i> Thêm kiện</button>
    </div>
</div>

<!-- Filter -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('logistics/packages') ?>" class="d-flex align-items-center gap-2 flex-wrap">
            <div class="search-box" style="min-width:200px;max-width:300px">
                <input type="text" class="form-control" name="search" placeholder="Mã kiện, tracking, KH..." value="<?= e($filters['search'] ?? '') ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
            <?php if (!empty(array_filter($filters))): ?><a href="<?= url('logistics/packages') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a><?php endif; ?>
        </form>
    </div>
</div>

<!-- Status Tabs -->
<div class="card mb-2">
    <div class="card-header p-2">
        <ul class="nav nav-custom nav-custom-light mb-0">
            <li class="nav-item"><a class="nav-link <?= !$currentStatus ? 'active' : '' ?>" href="<?= url('logistics/packages') ?>">Tất cả <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1"><?= $totalAll ?></span></a></li>
            <?php foreach ($statusLabels as $k => $v):
                $c = $countMap[$k] ?? 0;
                if ($c == 0 && $currentStatus !== $k) continue;
            ?>
            <li class="nav-item"><a class="nav-link <?= $currentStatus === $k ? 'active' : '' ?>" href="<?= url('logistics/packages?status=' . $k) ?>"><?= $v ?> <span class="badge bg-<?= $statusColors[$k] ?>-subtle text-<?= $statusColors[$k] ?> rounded-pill ms-1"><?= $c ?></span></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Mã kiện</th><th>Tracking</th><th>Sản phẩm</th><th>Khách hàng</th><th>Cân nặng</th><th>Số khối</th><th>SL</th><th>Trạng thái</th><th>Người nhận</th><th>Cập nhật</th></tr></thead>
                <tbody>
                <?php foreach ($packages as $p): ?>
                <tr>
                    <td><a href="<?= url('logistics/packages/' . $p['id']) ?>" class="fw-medium"><?= e($p['package_code']) ?></a></td>
                    <td class="text-muted fs-12"><?= e($p['tracking_code'] ?? '-') ?></td>
                    <td class="fs-12"><?= e(mb_substr($p['product_name'] ?? '-', 0, 40)) ?></td>
                    <td class="fs-12"><?= e($p['customer_name'] ?? '-') ?></td>
                    <td><?= $p['weight_actual'] ? rtrim(rtrim(number_format($p['weight_actual'], 2), '0'), '.') . ' kg' : '-' ?></td>
                    <td class="text-muted fs-12"><?= ($p['cbm'] ?? 0) > 0 ? rtrim(rtrim(number_format($p['cbm'], 4), '0'), '.') . ' m³' : ($p['length_cm'] && $p['width_cm'] && $p['height_cm'] ? rtrim(rtrim(number_format($p['length_cm'] * $p['width_cm'] * $p['height_cm'] / 1000000, 4), '0'), '.') . ' m³' : '-') ?></td>
                    <td><?= $p['quantity'] ?></td>
                    <td><span class="badge bg-<?= $statusColors[$p['status']] ?? 'secondary' ?>-subtle text-<?= $statusColors[$p['status']] ?? 'secondary' ?>"><?= $statusLabels[$p['status']] ?? $p['status'] ?></span></td>
                    <td><?= user_avatar($p['received_by_name'] ?? null) ?></td>
                    <td class="text-muted fs-12"><?= created_ago($p['updated_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($packages)): ?><tr><td colspan="10" class="text-center text-muted py-4">Chưa có kiện hàng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (($pagination['total_pages'] ?? 0) > 1): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-top">
            <div class="text-muted"><?= count($packages) ?> / <?= $pagination['total'] ?></div>
            <nav><ul class="pagination mb-0">
                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= url('logistics/packages?' . http_build_query(array_merge($filters, ['page'=>$i]))) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Package Modal -->
<div class="modal fade" id="addPkgModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('logistics/packages/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Thêm kiện hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Mã kiện</label><input type="text" class="form-control" name="package_code" placeholder="Tự tạo nếu để trống"></div>
                        <div class="col-6 mb-3"><label class="form-label">Tracking</label><input type="text" class="form-control" name="tracking_code"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Tên KH</label><input type="text" class="form-control" name="customer_name"></div>
                        <div class="col-6 mb-3"><label class="form-label">SĐT KH</label><input type="text" class="form-control" name="customer_phone"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Sản phẩm</label><input type="text" class="form-control" name="product_name"></div>
                    <div class="row">
                        <div class="col-4 mb-3"><label class="form-label">Cân nặng (kg)</label><input type="number" class="form-control" name="weight_actual" step="0.01" min="0"></div>
                        <div class="col-4 mb-3"><label class="form-label">Số lượng</label><input type="number" class="form-control" name="quantity" value="1" min="1"></div>
                        <div class="col-4 mb-3"><label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select"><option value="pending">Chờ</option><option value="warehouse_cn">Kho TQ</option><option value="shipping">Vận chuyển</option><option value="warehouse_vn">Kho VN</option></select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea class="form-control" name="description" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Tạo</button></div>
            </form>
        </div>
    </div>
</div>
