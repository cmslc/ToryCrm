<?php $pageTitle = 'Sửa sản phẩm'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa sản phẩm</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('products') ?>">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('products/' . $product['id'] . '/update') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin sản phẩm</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= e($product['name']) ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Mã SKU</label>
                                    <input type="text" class="form-control" name="sku" value="<?= e($product['sku'] ?? '') ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Loại</label>
                                    <select name="type" class="form-select" id="productType">
                                        <option value="product" <?= $product['type'] === 'product' ? 'selected' : '' ?>>Sản phẩm</option>
                                        <option value="service" <?= $product['type'] === 'service' ? 'selected' : '' ?>>Dịch vụ</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Danh mục</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">-- Chọn --</option>
                                        <?php foreach ($categories ?? [] as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Nhà sản xuất</label>
                                    <select name="manufacturer_id" class="form-select">
                                        <option value="">-- Chọn --</option>
                                        <?php foreach ($manufacturers ?? [] as $m): ?>
                                            <option value="<?= $m['id'] ?>" <?= ($product['manufacturer_id'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Xuất xứ</label>
                                    <select name="origin_id" class="form-select">
                                        <option value="">-- Chọn --</option>
                                        <?php foreach ($origins ?? [] as $o): ?>
                                            <option value="<?= $o['id'] ?>" <?= ($product['origin_id'] ?? '') == $o['id'] ? 'selected' : '' ?>><?= e($o['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Đơn vị tính</label>
                                    <input type="text" class="form-control" name="unit" value="<?= e($product['unit'] ?? 'Cái') ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Khối lượng (kg)</label>
                                    <input type="number" class="form-control" name="weight" step="0.001" min="0" value="<?= $product['weight'] !== null ? e((string)$product['weight']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mã vạch (Barcode)</label>
                                    <input type="text" class="form-control" name="barcode" value="<?= e($product['barcode'] ?? '') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ảnh sản phẩm</label>
                                    <?php if (!empty($product['image'])): ?>
                                        <div class="mb-2">
                                            <img src="<?= e(product_image_url($product['image'])) ?>" class="rounded" style="max-height:100px" alt="">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <small class="text-muted">Để trống nếu không đổi ảnh.</small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả ngắn</label>
                                    <textarea name="short_description" class="form-control" rows="2"><?= e($product['short_description'] ?? '') ?></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả chi tiết</label>
                                    <textarea name="description" class="form-control" rows="4"><?= e($product['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Giá bán</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Đơn giá bán <span class="text-muted">(VNĐ)</span></label>
                                    <input type="number" class="form-control" name="price" value="<?= (float)($product['price'] ?? 0) ?>" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá vốn <span class="text-muted">(VNĐ)</span></label>
                                    <input type="number" class="form-control" name="cost_price" value="<?= (float)($product['cost_price'] ?? 0) ?>" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá sỉ <span class="text-muted">(VNĐ)</span></label>
                                    <input type="number" class="form-control" name="price_wholesale" value="<?= (float)($product['price_wholesale'] ?? 0) ?>" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá online <span class="text-muted">(VNĐ)</span></label>
                                    <input type="number" class="form-control" name="price_online" value="<?= (float)($product['price_online'] ?? 0) ?>" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá khuyến mãi <span class="text-muted">(VNĐ)</span></label>
                                    <input type="number" class="form-control" name="saleoff_price" value="<?= $product['saleoff_price'] !== null ? (float)$product['saleoff_price'] : '' ?>" min="0" placeholder="Để trống nếu không có">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giảm giá (%)</label>
                                    <input type="number" class="form-control" name="discount_percent" value="<?= (float)($product['discount_percent'] ?? 0) ?>" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Thuế VAT (%)</label>
                                    <input type="number" class="form-control" name="tax_rate" value="<?= (float)($product['tax_rate'] ?? 0) ?>" min="0" max="100" step="0.01">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="stockCard" style="<?= $product['type'] === 'service' ? 'display:none' : '' ?>">
                        <div class="card-header"><h5 class="card-title mb-0">Kho</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control" name="stock_quantity" value="<?= (int)($product['stock_quantity'] ?? 0) ?>" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tồn kho tối thiểu</label>
                                    <input type="number" class="form-control" name="min_stock" value="<?= (int)($product['min_stock'] ?? 0) ?>" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Trạng thái</h5></div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= $product['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">Đang hoạt động</label>
                            </div>
                            <?php if (!empty($product['getfly_id'])): ?>
                            <div class="alert alert-info py-2 mb-0">
                                <i class="ri-refresh-line me-1"></i> Sản phẩm này đồng bộ từ Getfly (ID: <?= (int)$product['getfly_id'] ?>). Thay đổi có thể bị ghi đè ở lần sync tiếp theo.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Cập nhật</button>
                            <a href="<?= url('products/' . $product['id']) ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        document.getElementById('productType')?.addEventListener('change', function() {
            const stockCard = document.getElementById('stockCard');
            if (stockCard) stockCard.style.display = this.value === 'service' ? 'none' : '';
        });
        </script>
