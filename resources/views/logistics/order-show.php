<?php
$pageTitle = 'Đơn ' . e($order['order_code']);
$stLabels = ['pending'=>'Chờ','processing'=>'Đang xử lý','partial'=>'Nhận 1 phần','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$stColors = ['pending'=>'secondary','processing'=>'primary','partial'=>'warning','completed'=>'success','cancelled'=>'danger'];
$pkgLabels = ['pending'=>'Chờ','warehouse_cn'=>'Kho TQ','packed'=>'Đóng gói','shipping'=>'Vận chuyển','warehouse_vn'=>'Kho VN','delivering'=>'Đang giao','delivered'=>'Đã giao','returned'=>'Hoàn','damaged'=>'Hư hỏng'];
$pkgColors = ['pending'=>'secondary','warehouse_cn'=>'info','packed'=>'primary','shipping'=>'warning','warehouse_vn'=>'success','delivering'=>'info','delivered'=>'success','returned'=>'danger','damaged'=>'danger'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= e($order['order_code']) ?>
        <span class="badge bg-<?= $order['type'] === 'wholesale' ? 'success' : 'info' ?>"><?= $order['type'] === 'wholesale' ? 'Hàng lô' : 'Hàng lẻ' ?></span>
        <span class="badge bg-<?= $stColors[$order['status']] ?? 'secondary' ?>"><?= $stLabels[$order['status']] ?? $order['status'] ?></span>
    </h4>
    <a href="<?= url('logistics/orders') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-xl-8">
        <!-- Packages -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Kiện hàng <span class="badge bg-primary ms-1"><?= count($packages) ?></span></h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Mã kiện</th><th>Tracking</th><th>Sản phẩm</th><th>Cân nặng</th><th>Số khối</th><th>Kích thước</th><th>SL</th><th>Trạng thái</th><th>Người nhận</th><th>Ngày nhận</th></tr></thead>
                        <tbody>
                        <?php foreach ($packages as $p): ?>
                        <tr>
                            <td><a href="<?= url('logistics/packages/' . $p['id']) ?>" class="fw-medium"><?= e($p['package_code']) ?></a></td>
                            <td class="text-muted fs-12"><?= e($p['tracking_code'] ?? '-') ?></td>
                            <td class="fs-12"><?= e(mb_substr($p['product_name'] ?? '-', 0, 30)) ?></td>
                            <td><?= $p['weight_actual'] ? rtrim(rtrim(number_format($p['weight_actual'], 2), '0'), '.') . ' kg' : '-' ?></td>
                            <td class="fs-12"><?= ($p['length_cm'] && $p['width_cm'] && $p['height_cm']) ? number_format($p['length_cm'] * $p['width_cm'] * $p['height_cm'] / 1000000, 4) . ' m³' : '-' ?></td>
                            <td class="fs-12 text-muted"><?= ($p['length_cm'] && $p['width_cm'] && $p['height_cm']) ? $p['length_cm'] . '×' . $p['width_cm'] . '×' . $p['height_cm'] . 'cm' : '-' ?></td>
                            <td><?= $p['quantity'] ?></td>
                            <td><span class="badge bg-<?= $pkgColors[$p['status']] ?? 'secondary' ?>-subtle text-<?= $pkgColors[$p['status']] ?? 'secondary' ?>"><?= $pkgLabels[$p['status']] ?? $p['status'] ?></span></td>
                            <td><?= user_avatar($p['received_by_name'] ?? null) ?></td>
                            <td class="text-muted fs-12"><?= $p['received_at'] ? date('d/m/Y H:i', strtotime($p['received_at'])) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($packages)): ?><tr><td colspan="10" class="text-center text-muted py-4">Chưa có kiện hàng</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Scan History -->
        <?php if (!empty($scanLogs)): ?>
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Lịch sử quét</h5></div>
            <div class="card-body p-0">
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
        </div>
        <?php endif; ?>
    </div>

    <div class="col-xl-4">
        <!-- Order Info -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thông tin đơn hàng</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted" width="130">Mã đơn</th><td class="fw-medium"><?= e($order['order_code']) ?></td></tr>
                    <tr><th class="text-muted">Loại</th><td><span class="badge bg-<?= $order['type'] === 'wholesale' ? 'success' : 'info' ?>"><?= $order['type'] === 'wholesale' ? 'Hàng lô/sỉ' : 'Hàng lẻ' ?></span></td></tr>
                    <tr><th class="text-muted">Trạng thái</th><td><span class="badge bg-<?= $stColors[$order['status']] ?? 'secondary' ?> fs-12"><?= $stLabels[$order['status']] ?? $order['status'] ?></span></td></tr>
                    <tr><th class="text-muted">Khách hàng</th><td><?= e($order['customer_name'] ?? '-') ?></td></tr>
                    <?php if ($order['customer_phone']): ?><tr><th class="text-muted">SĐT</th><td><?= e($order['customer_phone']) ?></td></tr><?php endif; ?>
                    <tr><th class="text-muted">Sản phẩm</th><td><?= e($order['product_name'] ?? '-') ?></td></tr>
                    <tr><th class="text-muted">Tổng cân</th><td><?= ($order['total_weight'] ?? 0) > 0 ? rtrim(rtrim(number_format($order['total_weight'], 2), '0'), '.') . ' kg' : '-' ?></td></tr>
                    <tr><th class="text-muted">Số khối</th><td><?= ($order['total_cbm'] ?? 0) > 0 ? rtrim(rtrim(number_format($order['total_cbm'], 4), '0'), '.') . ' m³' : '-' ?></td></tr>
                    <tr><th class="text-muted">Người tạo</th><td><?= user_avatar($order['created_by_name'] ?? null) ?></td></tr>
                    <tr><th class="text-muted">Ngày tạo</th><td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td></tr>
                </table>
            </div>
        </div>

        <!-- Receiving Progress -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Tiến độ nhận hàng</h5></div>
            <div class="card-body">
                <?php
                $pct = $order['total_packages'] > 0 ? round($order['received_packages'] / $order['total_packages'] * 100) : 0;
                ?>
                <div class="text-center mb-3">
                    <h2 class="mb-1 <?= $pct >= 100 ? 'text-success' : ($pct > 0 ? 'text-warning' : 'text-muted') ?>"><?= $pct ?>%</h2>
                    <p class="text-muted mb-0"><?= $order['received_packages'] ?> / <?= $order['total_packages'] ?> kiện</p>
                </div>
                <div class="progress mb-3" style="height:10px">
                    <div class="progress-bar bg-<?= $pct >= 100 ? 'success' : ($pct > 0 ? 'warning' : 'secondary') ?>" style="width:<?= $pct ?>%"></div>
                </div>
                <div class="row text-center">
                    <div class="col-4">
                        <p class="text-muted mb-1 fs-12">Tổng</p>
                        <h5 class="mb-0"><?= $order['total_packages'] ?></h5>
                    </div>
                    <div class="col-4">
                        <p class="text-muted mb-1 fs-12">Đã gửi</p>
                        <h5 class="mb-0"><?= $order['shipped_packages'] ?></h5>
                    </div>
                    <div class="col-4">
                        <p class="text-muted mb-1 fs-12">Đã nhận</p>
                        <h5 class="mb-0 text-success"><?= $order['received_packages'] ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial -->
        <?php if ($order['total_amount'] > 0 || $order['cod_amount'] > 0): ?>
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thanh toán</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">Tổng tiền</th><td class="fw-semibold text-end"><?= format_money($order['total_amount']) ?></td></tr>
                    <?php if ($order['cod_amount'] > 0): ?><tr><th class="text-muted">COD thu hộ</th><td class="fw-semibold text-end text-danger"><?= format_money($order['cod_amount']) ?></td></tr><?php endif; ?>
                    <?php if ($order['payment_method']): ?>
                    <?php $pmLabels = ['cod'=>'COD','transfer'=>'Chuyển khoản','cash'=>'Tiền mặt','prepaid'=>'Đã thanh toán']; ?>
                    <tr><th class="text-muted">Phương thức</th><td class="text-end"><span class="badge bg-primary-subtle text-primary"><?= $pmLabels[$order['payment_method']] ?? $order['payment_method'] ?></span></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Images -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Ảnh đơn hàng</h5>
            </div>
            <div class="card-body">
                <?php $orderImages = json_decode($order['images'] ?? '[]', true) ?: []; ?>
                <div class="d-flex flex-wrap gap-2 mb-3" id="imageList">
                    <?php foreach ($orderImages as $i => $img): ?>
                    <a href="javascript:void(0)" onclick="showImagePopup(<?= htmlspecialchars(json_encode(array_map(fn($im) => url('uploads/logistics/' . $im), $orderImages))) ?>, <?= $i ?>)">
                        <img src="<?= url('uploads/logistics/' . $img) ?>" class="rounded border" style="width:80px;height:80px;object-fit:cover;cursor:pointer">
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($orderImages)): ?><p class="text-muted mb-0 fs-12" id="noImages">Chưa có ảnh</p><?php endif; ?>
                </div>
                <div>
                    <input type="file" id="orderImageInput" class="d-none" accept="image/*" multiple>
                    <button class="btn btn-soft-primary" onclick="document.getElementById('orderImageInput').click()"><i class="ri-camera-line me-1"></i> Tải ảnh</button>
                </div>
            </div>
        </div>
        <script>
        document.getElementById('orderImageInput')?.addEventListener('change', function() {
            Array.from(this.files).forEach(function(file) {
                var fd = new FormData();
                fd.append('image', file);
                fd.append('_token', '<?= csrf_token() ?>');
                fetch('<?= url("logistics/orders/" . $order["id"] . "/upload") ?>', {method:'POST', body:fd})
                    .then(function(r){return r.json()})
                    .then(function(d) {
                        if (d.success) {
                            document.getElementById('noImages')?.remove();
                            var img = document.createElement('a');
                            img.href = d.url; img.target = '_blank';
                            img.innerHTML = '<img src="' + d.url + '" class="rounded border" style="width:80px;height:80px;object-fit:cover">';
                            document.getElementById('imageList').appendChild(img);
                        }
                    });
            });
        });
        </script>

        <?php if ($order['note']): ?>
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Ghi chú</h5></div>
            <div class="card-body"><p class="mb-0"><?= e($order['note']) ?></p></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Image Popup Modal -->
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
var popupImages = [], popupIndex = 0;
function showImagePopup(images, startIndex) {
    popupImages = images;
    popupIndex = startIndex || 0;
    updatePopupImage();
    new bootstrap.Modal(document.getElementById('imagePopup')).show();
}
function updatePopupImage() {
    document.getElementById('popupImage').src = popupImages[popupIndex];
    document.getElementById('popupCounter').textContent = (popupIndex + 1) + ' / ' + popupImages.length;
    document.getElementById('imgPrev').style.display = popupImages.length > 1 ? '' : 'none';
    document.getElementById('imgNext').style.display = popupImages.length > 1 ? '' : 'none';
}
document.getElementById('imgPrev')?.addEventListener('click', function() { popupIndex = (popupIndex - 1 + popupImages.length) % popupImages.length; updatePopupImage(); });
document.getElementById('imgNext')?.addEventListener('click', function() { popupIndex = (popupIndex + 1) % popupImages.length; updatePopupImage(); });
</script>
