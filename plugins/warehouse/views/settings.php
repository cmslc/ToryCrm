<?php
$pageTitle = 'Cài đặt kho';
$warehouses = \Core\Database::fetchAll("SELECT id, name FROM warehouses WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [$_SESSION['tenant_id'] ?? 1]);
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Cài đặt kho</h4>
    <a href="<?= url('warehouses') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Liên kết với module khác</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('warehouses/settings') ?>">
                    <?= csrf_field() ?>

                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div>
                            <h6 class="mb-1"><i class="ri-shopping-cart-line me-2 text-success"></i>Đơn hàng bán → Tự xuất kho</h6>
                            <p class="text-muted mb-0 fs-12">Khi đơn hàng bán hoàn thành, tự động xuất kho sản phẩm tương ứng từ kho mặc định</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_export_on_order" value="1" <?= ($config['auto_export_on_order'] ?? false) ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div>
                            <h6 class="mb-1"><i class="ri-truck-line me-2 text-primary"></i>Đơn hàng mua → Tự nhập kho</h6>
                            <p class="text-muted mb-0 fs-12">Khi đơn hàng mua hoàn thành, tự động nhập kho sản phẩm vào kho mặc định</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_import_on_purchase" value="1" <?= ($config['auto_import_on_purchase'] ?? false) ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div>
                            <h6 class="mb-1"><i class="ri-box-3-line me-2 text-info"></i>Hiện tồn kho trên trang sản phẩm</h6>
                            <p class="text-muted mb-0 fs-12">Hiển thị số lượng tồn kho bên cạnh mỗi sản phẩm trong danh sách và chi tiết</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="show_stock_on_product" value="1" <?= ($config['show_stock_on_product'] ?? false) ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div>
                            <h6 class="mb-1"><i class="ri-alarm-warning-line me-2 text-danger"></i>Cảnh báo tồn kho thấp</h6>
                            <p class="text-muted mb-0 fs-12">Thông báo khi sản phẩm dưới mức tồn kho tối thiểu (cài đặt trong từng kho)</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="low_stock_notification" value="1" <?= ($config['low_stock_notification'] ?? false) ? 'checked' : '' ?>>
                        </div>
                    </div>

                    <div class="py-3">
                        <h6 class="mb-2"><i class="ri-store-2-line me-2"></i>Kho mặc định</h6>
                        <p class="text-muted mb-2 fs-12">Kho được sử dụng khi tự động xuất/nhập từ đơn hàng</p>
                        <select name="default_warehouse_id" class="form-select" style="max-width:300px">
                            <option value="">Chọn kho mặc định...</option>
                            <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>" <?= ($config['default_warehouse_id'] ?? 0) == $wh['id'] ? 'selected' : '' ?>><?= e($wh['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pt-3 border-top">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cài đặt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Trạng thái liên kết</h5></div>
            <div class="card-body">
                <?php
                $links = [
                    ['name' => 'Đơn hàng bán → Xuất kho', 'key' => 'auto_export_on_order', 'icon' => 'ri-shopping-cart-line'],
                    ['name' => 'Đơn hàng mua → Nhập kho', 'key' => 'auto_import_on_purchase', 'icon' => 'ri-truck-line'],
                    ['name' => 'Tồn kho trên sản phẩm', 'key' => 'show_stock_on_product', 'icon' => 'ri-box-3-line'],
                    ['name' => 'Cảnh báo tồn thấp', 'key' => 'low_stock_notification', 'icon' => 'ri-alarm-warning-line'],
                ];
                foreach ($links as $l):
                    $on = ($config[$l['key']] ?? false);
                ?>
                <div class="d-flex align-items-center mb-3">
                    <i class="<?= $l['icon'] ?> fs-18 me-2 text-<?= $on ? 'success' : 'muted' ?>"></i>
                    <span class="flex-grow-1"><?= $l['name'] ?></span>
                    <span class="badge bg-<?= $on ? 'success' : 'secondary' ?>-subtle text-<?= $on ? 'success' : 'secondary' ?>"><?= $on ? 'Bật' : 'Tắt' ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
