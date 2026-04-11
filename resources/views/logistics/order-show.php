<?php
$pageTitle = 'Đơn ' . e($order['order_code']);
$stLabels = ['pending'=>'Chờ','processing'=>'Đang xử lý','partial'=>'Nhận 1 phần','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$stColors = ['pending'=>'secondary','processing'=>'primary','partial'=>'warning','completed'=>'success','cancelled'=>'danger'];
$pkgLabels = ['pending'=>'Chờ','warehouse_cn'=>'Kho TQ','packed'=>'Đóng gói','shipping'=>'Vận chuyển','warehouse_vn'=>'Kho VN','delivering'=>'Đang giao','delivered'=>'Đã giao','returned'=>'Hoàn','damaged'=>'Hư hỏng'];
$pkgColors = ['pending'=>'secondary','warehouse_cn'=>'info','packed'=>'primary','shipping'=>'warning','warehouse_vn'=>'success','delivering'=>'info','delivered'=>'success','returned'=>'danger','damaged'=>'danger'];
$pmLabels = ['cod'=>'COD','transfer'=>'Chuyển khoản','cash'=>'Tiền mặt','prepaid'=>'Đã thanh toán'];
$pct = $order['total_packages'] > 0 ? round($order['received_packages'] / $order['total_packages'] * 100) : 0;
$orderImages = json_decode($order['images'] ?? '[]', true) ?: [];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-1"><?= e($order['order_code']) ?>
            <span class="badge bg-<?= $order['type'] === 'wholesale' ? 'success' : 'info' ?>"><?= $order['type'] === 'wholesale' ? 'Hàng lô' : 'Hàng lẻ' ?></span>
            <span class="badge bg-<?= $stColors[$order['status']] ?? 'secondary' ?>"><?= $stLabels[$order['status']] ?? $order['status'] ?></span>
        </h4>
        <p class="text-muted mb-0"><?= e($order['customer_name'] ?? '') ?><?= $order['customer_phone'] ? ' · ' . e($order['customer_phone']) : '' ?></p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-soft-primary" data-bs-toggle="modal" data-bs-target="#editOrderModal"><i class="ri-pencil-line me-1"></i> Sửa</button>
        <form method="POST" action="<?= url('logistics/orders/' . $order['id'] . '/delete') ?>" data-confirm="Xóa đơn hàng?" class="d-inline"><?= csrf_field() ?><button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button></form>
        <a href="<?= url('logistics/orders') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-1">
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= $order['total_packages'] ?></h5><span class="text-muted fs-11">Tổng kiện</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0 text-success"><?= $order['received_packages'] ?></h5><span class="text-muted fs-11">Đã nhận</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= ($order['total_weight'] ?? 0) > 0 ? rtrim(rtrim(number_format($order['total_weight'], 2), '0'), '.') : '-' ?></h5><span class="text-muted fs-11">Cân (kg)</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= ($order['total_cbm'] ?? 0) > 0 ? rtrim(rtrim(number_format($order['total_cbm'], 4), '0'), '.') : '-' ?></h5><span class="text-muted fs-11">Khối (m³)</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center"><h5 class="mb-0"><?= $order['total_amount'] > 0 ? format_money($order['total_amount']) : '-' ?></h5><span class="text-muted fs-11">Tổng tiền</span></div></div></div>
    <div class="col-md-2"><div class="card card-animate mb-2"><div class="card-body py-3 text-center">
        <div class="d-flex align-items-center justify-content-center gap-2">
            <div class="progress flex-grow-1" style="height:8px"><div class="progress-bar bg-<?= $pct >= 100 ? 'success' : 'warning' ?>" style="width:<?= $pct ?>%"></div></div>
            <span class="fw-semibold fs-12"><?= $pct ?>%</span>
        </div>
        <span class="text-muted fs-11">Tiến độ</span>
    </div></div></div>
</div>

<!-- Main Card with Tabs -->
<div class="card">
    <div class="card-header p-0">
        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabPkgs">Kiện hàng <span class="badge bg-primary-subtle text-primary ms-1"><?= count($packages) ?></span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabInfo">Thông tin</a></li>
            <?php if (!empty($orderImages)): ?><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabImages">Ảnh <span class="badge bg-info-subtle text-info ms-1"><?= count($orderImages) ?></span></a></li><?php endif; ?>
            <?php if (!empty($scanLogs)): ?><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabLogs">Lịch sử quét</a></li><?php endif; ?>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <!-- Kiện hàng -->
            <div class="tab-pane active" id="tabPkgs">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Mã kiện</th><th>Tracking</th><th>Sản phẩm</th><th>Cân nặng</th><th>Khối</th><th>Kích thước</th><th>SL</th><th>Trạng thái</th><th>Người nhận</th><th>Ngày nhận</th></tr></thead>
                        <tbody>
                        <?php foreach ($packages as $p): ?>
                        <tr>
                            <td><a href="<?= url('logistics/packages/' . $p['id']) ?>" class="fw-medium"><?= e($p['package_code']) ?></a></td>
                            <td class="text-muted fs-12"><?= e($p['tracking_code'] ?? '-') ?></td>
                            <td class="fs-12"><?= e(mb_substr($p['product_name'] ?? '-', 0, 30)) ?></td>
                            <td><?= $p['weight_actual'] ? rtrim(rtrim(number_format($p['weight_actual'], 2), '0'), '.') . ' kg' : '-' ?></td>
                            <td class="fs-12"><?php $cbm = ($p['length_cm'] && $p['width_cm'] && $p['height_cm']) ? $p['length_cm'] * $p['width_cm'] * $p['height_cm'] / 1000000 : 0; echo $cbm > 0 ? rtrim(rtrim(number_format($cbm, 4), '0'), '.') . ' m³' : '-'; ?></td>
                            <td class="fs-12 text-muted"><?= ($p['length_cm'] && $p['width_cm'] && $p['height_cm']) ? $p['length_cm'] . '×' . $p['width_cm'] . '×' . $p['height_cm'] : '-' ?></td>
                            <td><?= $p['quantity'] ?></td>
                            <td><span class="badge bg-<?= $pkgColors[$p['status']] ?? 'secondary' ?>-subtle text-<?= $pkgColors[$p['status']] ?? 'secondary' ?>"><?= $pkgLabels[$p['status']] ?? $p['status'] ?></span></td>
                            <td><?= user_avatar($p['received_by_name'] ?? null) ?></td>
                            <td class="text-muted fs-12"><?= $p['received_at'] ? date('d/m H:i', strtotime($p['received_at'])) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($packages)): ?><tr><td colspan="10" class="text-center text-muted py-4">Chưa có kiện hàng</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Thông tin -->
            <div class="tab-pane" id="tabInfo">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th class="text-muted" width="130">Mã đơn</th><td class="fw-medium"><?= e($order['order_code']) ?></td></tr>
                            <tr><th class="text-muted">Loại</th><td><span class="badge bg-<?= $order['type'] === 'wholesale' ? 'success' : 'info' ?>"><?= $order['type'] === 'wholesale' ? 'Hàng lô/sỉ' : 'Hàng lẻ' ?></span></td></tr>
                            <tr><th class="text-muted">Khách hàng</th><td><?= e($order['customer_name'] ?? '-') ?></td></tr>
                            <?php if ($order['customer_phone']): ?><tr><th class="text-muted">SĐT</th><td><?= e($order['customer_phone']) ?></td></tr><?php endif; ?>
                            <tr><th class="text-muted">Sản phẩm</th><td><?= e($order['product_name'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Người tạo</th><td><?= user_avatar($order['created_by_name'] ?? null) ?></td></tr>
                            <tr><th class="text-muted">Ngày tạo</th><td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th class="text-muted" width="130">Tổng kiện</th><td><?= $order['total_packages'] ?></td></tr>
                            <tr><th class="text-muted">Đã gửi</th><td><?= $order['shipped_packages'] ?></td></tr>
                            <tr><th class="text-muted">Đã nhận</th><td class="text-success fw-medium"><?= $order['received_packages'] ?></td></tr>
                            <tr><th class="text-muted">Tổng cân</th><td><?= ($order['total_weight'] ?? 0) > 0 ? rtrim(rtrim(number_format($order['total_weight'], 2), '0'), '.') . ' kg' : '-' ?></td></tr>
                            <tr><th class="text-muted">Số khối</th><td><?= ($order['total_cbm'] ?? 0) > 0 ? rtrim(rtrim(number_format($order['total_cbm'], 4), '0'), '.') . ' m³' : '-' ?></td></tr>
                            <?php if ($order['total_amount'] > 0): ?><tr><th class="text-muted">Tổng tiền</th><td class="fw-semibold"><?= format_money($order['total_amount']) ?></td></tr><?php endif; ?>
                            <?php if ($order['cod_amount'] > 0): ?><tr><th class="text-muted">COD</th><td class="text-danger fw-semibold"><?= format_money($order['cod_amount']) ?></td></tr><?php endif; ?>
                            <?php if ($order['payment_method']): ?><tr><th class="text-muted">Thanh toán</th><td><span class="badge bg-primary-subtle text-primary"><?= $pmLabels[$order['payment_method']] ?? $order['payment_method'] ?></span></td></tr><?php endif; ?>
                        </table>
                        <?php if ($order['note']): ?><div class="border-top pt-3"><strong class="text-muted fs-12">Ghi chú:</strong><p class="mb-0 mt-1"><?= e($order['note']) ?></p></div><?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Ảnh -->
            <?php if (!empty($orderImages)): ?>
            <div class="tab-pane" id="tabImages">
                <div class="d-flex flex-wrap gap-2 mb-3" id="imageList">
                    <?php foreach ($orderImages as $i => $img): ?>
                    <a href="javascript:void(0)" onclick="showImagePopup(<?= htmlspecialchars(json_encode(array_map(fn($im) => url('uploads/logistics/' . $im), $orderImages))) ?>, <?= $i ?>)">
                        <img src="<?= url('uploads/logistics/' . $img) ?>" class="rounded border" style="width:120px;height:120px;object-fit:cover;cursor:pointer">
                    </a>
                    <?php endforeach; ?>
                </div>
                <input type="file" id="orderImageInput" class="d-none" accept="image/*" multiple>
                <button class="btn btn-soft-primary" onclick="document.getElementById('orderImageInput').click()"><i class="ri-camera-line me-1"></i> Thêm ảnh</button>
            </div>
            <?php endif; ?>

            <!-- Lịch sử quét -->
            <?php if (!empty($scanLogs)): ?>
            <div class="tab-pane" id="tabLogs">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Mã quét</th><th>Loại</th><th>Kết quả</th><th>Chi tiết</th><th>Thời gian</th></tr></thead>
                        <tbody>
                        <?php foreach ($scanLogs as $sl): ?>
                        <tr>
                            <td class="fw-medium"><?= e($sl['scan_code']) ?></td>
                            <td><span class="badge bg-<?= $sl['scan_type'] === 'bag' ? 'info' : 'primary' ?>-subtle text-<?= $sl['scan_type'] === 'bag' ? 'info' : 'primary' ?>"><?= $sl['scan_type'] === 'bag' ? 'Bao' : 'Kiện' ?></span></td>
                            <td><span class="badge bg-<?= $sl['result'] === 'success' ? 'success' : ($sl['result'] === 'duplicate' ? 'warning' : 'danger') ?>"><?= $sl['result'] === 'success' ? 'OK' : ($sl['result'] === 'duplicate' ? 'Trùng' : 'Lỗi') ?></span></td>
                            <td class="fs-12"><?= e($sl['message'] ?? '') ?></td>
                            <td class="text-muted fs-12"><?= date('d/m/Y H:i', strtotime($sl['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<?php $pmEdit = [''=>'Chưa chọn','cod'=>'COD','transfer'=>'Chuyển khoản','cash'=>'Tiền mặt','prepaid'=>'Đã thanh toán']; ?>
<div class="modal fade" id="editOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('logistics/orders/' . $order['id'] . '/update') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Sửa <?= e($order['order_code']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Loại</label><select name="type" class="form-select"><option value="retail" <?= $order['type'] === 'retail' ? 'selected' : '' ?>>Hàng lẻ</option><option value="wholesale" <?= $order['type'] === 'wholesale' ? 'selected' : '' ?>>Hàng lô/sỉ</option></select></div>
                        <div class="col-6 mb-3"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><?php foreach ($stLabels as $k => $v): ?><option value="<?= $k ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Tên KH</label><input type="text" class="form-control" name="customer_name" value="<?= e($order['customer_name'] ?? '') ?>"></div>
                        <div class="col-6 mb-3"><label class="form-label">SĐT</label><input type="text" class="form-control" name="customer_phone" value="<?= e($order['customer_phone'] ?? '') ?>"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Sản phẩm</label><input type="text" class="form-control" name="product_name" value="<?= e($order['product_name'] ?? '') ?>"></div>
                    <div class="row">
                        <div class="col-3 mb-3"><label class="form-label">Kiện</label><input type="number" class="form-control" name="total_packages" value="<?= $order['total_packages'] ?>"></div>
                        <div class="col-3 mb-3"><label class="form-label">Cân (kg)</label><input type="number" class="form-control" name="total_weight" value="<?= $order['total_weight'] ?>" step="0.01"></div>
                        <div class="col-3 mb-3"><label class="form-label">Khối</label><input type="number" class="form-control" name="total_cbm" value="<?= $order['total_cbm'] ?>" step="0.0001"></div>
                        <div class="col-3 mb-3"><label class="form-label">Tổng tiền</label><input type="number" class="form-control" name="total_amount" value="<?= $order['total_amount'] ?>" step="1000"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">COD</label><input type="number" class="form-control" name="cod_amount" value="<?= $order['cod_amount'] ?>" step="1000"></div>
                        <div class="col-6 mb-3"><label class="form-label">Thanh toán</label><select name="payment_method" class="form-select"><?php foreach ($pmEdit as $k => $v): ?><option value="<?= $k ?>" <?= ($order['payment_method'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea class="form-control" name="note" rows="2"><?= e($order['note'] ?? '') ?></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Image Popup -->
<div class="modal fade" id="imagePopup" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index:10"></button>
                <button type="button" class="btn btn-light rounded-circle position-absolute start-0 top-50 translate-middle-y ms-2" id="imgPrev" style="z-index:10"><i class="ri-arrow-left-s-line"></i></button>
                <button type="button" class="btn btn-light rounded-circle position-absolute end-0 top-50 translate-middle-y me-2" id="imgNext" style="z-index:10"><i class="ri-arrow-right-s-line"></i></button>
                <img id="popupImage" src="" class="rounded" style="max-height:80vh;max-width:100%">
                <div class="text-white mt-2" id="popupCounter"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Image popup
var popupImages = [], popupIndex = 0;
function showImagePopup(images, startIndex) { popupImages = images; popupIndex = startIndex || 0; updatePopupImage(); new bootstrap.Modal(document.getElementById('imagePopup')).show(); }
function updatePopupImage() { document.getElementById('popupImage').src = popupImages[popupIndex]; document.getElementById('popupCounter').textContent = (popupIndex+1)+' / '+popupImages.length; document.getElementById('imgPrev').style.display = popupImages.length > 1 ? '' : 'none'; document.getElementById('imgNext').style.display = popupImages.length > 1 ? '' : 'none'; }
document.getElementById('imgPrev')?.addEventListener('click', function() { popupIndex = (popupIndex-1+popupImages.length)%popupImages.length; updatePopupImage(); });
document.getElementById('imgNext')?.addEventListener('click', function() { popupIndex = (popupIndex+1)%popupImages.length; updatePopupImage(); });

// Image upload
document.getElementById('orderImageInput')?.addEventListener('change', function() {
    Array.from(this.files).forEach(function(file) {
        var fd = new FormData(); fd.append('image', file); fd.append('_token', '<?= csrf_token() ?>');
        fetch('<?= url("logistics/orders/" . $order["id"] . "/upload") ?>', {method:'POST', body:fd}).then(r=>r.json()).then(function(d) { if(d.success) location.reload(); });
    });
});
</script>
