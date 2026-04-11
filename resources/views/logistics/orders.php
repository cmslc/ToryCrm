<?php
$pageTitle = 'Đơn hàng Logistics';
$stLabels = ['pending'=>'Chờ','processing'=>'Đang xử lý','partial'=>'Nhận 1 phần','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$stColors = ['pending'=>'secondary','processing'=>'primary','partial'=>'warning','completed'=>'success','cancelled'=>'danger'];
$currentType = $filters['type'] ?? '';
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Đơn hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('logistics') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Dashboard</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal"><i class="ri-add-line me-1"></i> Tạo đơn</button>
    </div>
</div>

<!-- Filter -->
<div class="card mb-2">
    <div class="card-header p-2">
        <div class="d-flex gap-2">
            <a href="<?= url('logistics/orders') ?>" class="btn <?= !$currentType ? 'btn-primary' : 'btn-soft-primary' ?>">Tất cả</a>
            <a href="<?= url('logistics/orders?type=retail') ?>" class="btn <?= $currentType === 'retail' ? 'btn-info' : 'btn-soft-info' ?>">Hàng lẻ</a>
            <a href="<?= url('logistics/orders?type=wholesale') ?>" class="btn <?= $currentType === 'wholesale' ? 'btn-success' : 'btn-soft-success' ?>">Hàng lô/sỉ</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Mã đơn</th><th>Ảnh</th><th>Loại</th><th>Khách hàng</th><th>Sản phẩm</th><th>Kiện</th><th>Đã nhận</th><th>Tổng tiền</th><th>COD</th><th>Trạng thái</th><th>Ngày tạo</th></tr></thead>
                <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><a href="<?= url('logistics/orders/' . $o['id']) ?>" class="fw-medium"><?= e($o['order_code']) ?></a></td>
                    <td>
                        <?php
                        $oImgs = json_decode($o['images'] ?? '[]', true) ?: [];
                        if (!empty($oImgs)):
                            $firstImg = $oImgs[0];
                        ?>
                            <a href="javascript:void(0)" onclick="showImagePopup(<?= htmlspecialchars(json_encode(array_map(fn($img) => url('uploads/logistics/' . $img), $oImgs))) ?>)">
                                <img src="<?= url('uploads/logistics/' . $firstImg) ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;cursor:pointer">
                                <?php if (count($oImgs) > 1): ?><span class="text-muted fs-11 ms-1">+<?= count($oImgs) - 1 ?></span><?php endif; ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge bg-<?= $o['type'] === 'wholesale' ? 'success' : 'info' ?>-subtle text-<?= $o['type'] === 'wholesale' ? 'success' : 'info' ?>"><?= $o['type'] === 'wholesale' ? 'Sỉ' : 'Lẻ' ?></span></td>
                    <td><?= e($o['customer_name'] ?? '-') ?><?= $o['customer_phone'] ? '<div class="text-muted fs-11">' . e($o['customer_phone']) . '</div>' : '' ?></td>
                    <td class="fs-12"><?= e(mb_substr($o['product_name'] ?? '-', 0, 30)) ?></td>
                    <td class="fw-medium"><?= $o['total_packages'] ?></td>
                    <td>
                        <?php if ($o['type'] === 'wholesale' && $o['total_packages'] > 0): ?>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:6px;min-width:50px"><div class="progress-bar bg-success" style="width:<?= min(100, round(($o['received_packages'] / $o['total_packages']) * 100)) ?>%"></div></div>
                                <span class="fs-12"><?= $o['received_packages'] ?>/<?= $o['total_packages'] ?></span>
                            </div>
                        <?php else: ?>
                            <?= $o['received_packages'] ?>
                        <?php endif; ?>
                    </td>
                    <td><?= $o['total_amount'] > 0 ? format_money($o['total_amount']) : '-' ?></td>
                    <td><?= $o['cod_amount'] > 0 ? format_money($o['cod_amount']) : '-' ?></td>
                    <td><span class="badge bg-<?= $stColors[$o['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$o['status']] ?? 'secondary' ?>"><?= $stLabels[$o['status']] ?? $o['status'] ?></span></td>
                    <td class="text-muted fs-12"><?= created_ago($o['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?><tr><td colspan="11" class="text-center text-muted py-4">Chưa có đơn hàng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('logistics/orders/create') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Tạo đơn hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Mã đơn</label><input type="text" class="form-control" name="order_code" placeholder="Tự tạo"></div>
                        <div class="col-6 mb-3"><label class="form-label">Loại <span class="text-danger">*</span></label>
                            <select name="type" class="form-select"><option value="retail">Hàng lẻ</option><option value="wholesale">Hàng lô/sỉ</option></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Tên KH</label><input type="text" class="form-control" name="customer_name"></div>
                        <div class="col-6 mb-3"><label class="form-label">SĐT</label><input type="text" class="form-control" name="customer_phone"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Sản phẩm</label><input type="text" class="form-control" name="product_name"></div>
                    <div class="row">
                        <div class="col-4 mb-3"><label class="form-label">Tổng kiện</label><input type="number" class="form-control" name="total_packages" min="0" value="1"></div>
                        <div class="col-4 mb-3"><label class="form-label">Tổng tiền</label><input type="number" class="form-control" name="total_amount" min="0" step="1000"></div>
                        <div class="col-4 mb-3"><label class="form-label">COD thu</label><input type="number" class="form-control" name="cod_amount" min="0" step="1000"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Thanh toán</label>
                            <select name="payment_method" class="form-select"><option value="">Chưa chọn</option><option value="cod">COD</option><option value="transfer">Chuyển khoản</option><option value="cash">Tiền mặt</option><option value="prepaid">Đã thanh toán</option></select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea class="form-control" name="note" rows="2"></textarea></div>
                    <div class="mb-3"><label class="form-label">Ảnh đơn hàng</label><input type="file" name="images[]" class="form-control" accept="image/*" multiple></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Tạo</button></div>
            </form>
        </div>
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
document.getElementById('imagePopup')?.addEventListener('keydown', function(e) { if (e.key === 'ArrowLeft') document.getElementById('imgPrev').click(); if (e.key === 'ArrowRight') document.getElementById('imgNext').click(); });
</script>
