<?php
$pageTitle = 'Sửa đơn ' . e($order['order_code']);
$pmLabels = [''=>'Chưa chọn','cod'=>'COD','transfer'=>'Chuyển khoản','cash'=>'Tiền mặt','prepaid'=>'Đã thanh toán'];
$stLabels = ['pending'=>'Chờ','processing'=>'Đang xử lý','partial'=>'Nhận 1 phần','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sửa đơn <?= e($order['order_code']) ?></h4>
    <a href="<?= url('logistics/orders/' . $order['id']) ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<form method="POST" action="<?= url('logistics/orders/' . $order['id'] . '/update') ?>">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Thông tin đơn hàng</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Mã đơn</label>
                            <input type="text" class="form-control" value="<?= e($order['order_code']) ?>" disabled>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Loại</label>
                            <select name="type" class="form-select">
                                <option value="retail" <?= $order['type'] === 'retail' ? 'selected' : '' ?>>Hàng lẻ</option>
                                <option value="wholesale" <?= $order['type'] === 'wholesale' ? 'selected' : '' ?>>Hàng lô/sỉ</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <?php foreach ($stLabels as $k => $v): ?><option value="<?= $k ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khách hàng</label>
                            <input type="text" class="form-control" name="customer_name" value="<?= e($order['customer_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SĐT</label>
                            <input type="text" class="form-control" name="customer_phone" value="<?= e($order['customer_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sản phẩm</label>
                        <input type="text" class="form-control" name="product_name" value="<?= e($order['product_name'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tổng kiện</label>
                            <input type="number" class="form-control" name="total_packages" value="<?= $order['total_packages'] ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Cân nặng (kg)</label>
                            <input type="number" class="form-control" name="total_weight" value="<?= $order['total_weight'] ?>" step="0.01">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Số khối (m³)</label>
                            <input type="number" class="form-control" name="total_cbm" value="<?= $order['total_cbm'] ?>" step="0.0001">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tổng tiền</label>
                            <input type="number" class="form-control" name="total_amount" value="<?= $order['total_amount'] ?>" step="1000">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">COD thu hộ</label>
                            <input type="number" class="form-control" name="cod_amount" value="<?= $order['cod_amount'] ?>" step="1000">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Thanh toán</label>
                            <select name="payment_method" class="form-select">
                                <?php foreach ($pmLabels as $k => $v): ?><option value="<?= $k ?>" <?= ($order['payment_method'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" rows="3"><?= e($order['note'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                    <a href="<?= url('logistics/orders/' . $order['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                </div>
            </div>
        </div>
    </div>
</form>
