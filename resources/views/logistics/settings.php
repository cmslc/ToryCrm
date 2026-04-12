<?php
$pageTitle = 'Cài đặt Kho Logistics';
$c = $cfg;
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-settings-3-line me-2"></i> Cài đặt Kho Logistics</h4>
</div>

<form method="POST" action="<?= url('logistics/settings') ?>">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-6">
            <!-- Tiền tố mã -->
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ri-hashtag me-2"></i> Tiền tố mã tự động</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Mã kiện</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="prefix_package" value="<?= e($c['prefix_package'] ?? 'K') ?>" placeholder="K">
                                <span class="input-group-text text-muted fs-12">VD: K260411001</span>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Mã bao</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="prefix_bag" value="<?= e($c['prefix_bag'] ?? 'BAO') ?>" placeholder="BAO">
                                <span class="input-group-text text-muted fs-12">VD: BAO-260411</span>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Mã lô hàng</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="prefix_shipment" value="<?= e($c['prefix_shipment'] ?? 'LH') ?>" placeholder="LH">
                                <span class="input-group-text text-muted fs-12">VD: LH260411001</span>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Mã đơn hàng</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="prefix_order" value="<?= e($c['prefix_order'] ?? 'DH') ?>" placeholder="DH">
                                <span class="input-group-text text-muted fs-12">VD: DH260411001</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kho hàng -->
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ri-building-line me-2"></i> Thông tin kho</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tên kho TQ</label>
                            <input type="text" class="form-control" name="warehouse_cn_name" value="<?= e($c['warehouse_cn_name'] ?? 'Kho Trung Quốc') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Tên kho VN</label>
                            <input type="text" class="form-control" name="warehouse_vn_name" value="<?= e($c['warehouse_vn_name'] ?? 'Kho Việt Nam') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Địa chỉ kho TQ</label>
                            <textarea class="form-control" name="warehouse_cn_address" rows="2"><?= e($c['warehouse_cn_address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Địa chỉ kho VN</label>
                            <textarea class="form-control" name="warehouse_vn_address" rows="2"><?= e($c['warehouse_vn_address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Vận chuyển -->
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ri-truck-line me-2"></i> Vận chuyển</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tuyến đường mặc định</label>
                            <input type="text" class="form-control" name="default_route" value="<?= e($c['default_route'] ?? 'Kho TQ - Cửa khẩu') ?>">
                        </div>
                        <div class="col-3 mb-3">
                            <label class="form-label">Điểm đi</label>
                            <input type="text" class="form-control" name="default_origin" value="<?= e($c['default_origin'] ?? 'CN') ?>">
                        </div>
                        <div class="col-3 mb-3">
                            <label class="form-label">Điểm đến</label>
                            <input type="text" class="form-control" name="default_destination" value="<?= e($c['default_destination'] ?? 'VN') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label">Giá cước / kg</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="rate_per_kg" value="<?= $c['rate_per_kg'] ?? 0 ?>" step="1000" min="0">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Giá cước / m³</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="rate_per_cbm" value="<?= $c['rate_per_cbm'] ?? 0 ?>" step="100000" min="0">
                                <span class="input-group-text">đ</span>
                            </div>
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Đơn vị tiền tệ</label>
                            <select name="currency" class="form-select">
                                <option value="VND" <?= ($c['currency'] ?? 'VND') === 'VND' ? 'selected' : '' ?>>VND</option>
                                <option value="CNY" <?= ($c['currency'] ?? '') === 'CNY' ? 'selected' : '' ?>>CNY</option>
                                <option value="USD" <?= ($c['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quét hàng & Đóng bao -->
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0"><i class="ri-qr-scan-2-line me-2"></i> Quét hàng & Đóng bao</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="scan_sound" value="1" id="scanSound" <?= ($c['scan_sound'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="scanSound">Bật âm thanh khi quét</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Tự đóng bao sau (kiện)</label>
                            <input type="number" class="form-control" name="auto_seal_packages" value="<?= $c['auto_seal_packages'] ?? 0 ?>" min="0">
                            <small class="text-muted">0 = tắt</small>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Tự đóng bao sau (kg)</label>
                            <input type="number" class="form-control" name="auto_seal_weight" value="<?= $c['auto_seal_weight'] ?? 0 ?>" min="0" step="0.5">
                            <small class="text-muted">0 = tắt</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cài đặt</button>
                </div>
            </div>
        </div>
    </div>
</form>
