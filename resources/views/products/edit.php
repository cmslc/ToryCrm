<?php $pageTitle = 'Sửa sản phẩm'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sửa sản phẩm</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('products') ?>">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Sửa</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('products/' . $product['id'] . '/update') ?>">
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
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Loại</label>
                                    <select name="type" class="form-select" id="productType">
                                        <option value="product" <?= $product['type'] === 'product' ? 'selected' : '' ?>>Sản phẩm</option>
                                        <option value="service" <?= $product['type'] === 'service' ? 'selected' : '' ?>>Dịch vụ</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Danh mục</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Chọn danh mục</option>
                                        <?php foreach ($categories ?? [] as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Đơn vị tính</label>
                                    <input type="text" class="form-control" name="unit" value="<?= e($product['unit'] ?? 'Cái') ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3"><?= e($product['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Giá & Kho</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Đơn giá bán (VNĐ)</label>
                                    <input type="number" class="form-control" name="price" value="<?= $product['price'] ?>" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Giá vốn (VNĐ)</label>
                                    <input type="number" class="form-control" name="cost_price" value="<?= $product['cost_price'] ?>" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Thuế (%)</label>
                                    <input type="number" class="form-control" name="tax_rate" value="<?= $product['tax_rate'] ?>" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-md-6 mb-3" id="stockFields" style="<?= $product['type'] === 'service' ? 'display:none' : '' ?>">
                                    <label class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control" name="stock_quantity" value="<?= $product['stock_quantity'] ?>" min="0">
                                </div>
                                <div class="col-md-6 mb-3" id="minStockField" style="<?= $product['type'] === 'service' ? 'display:none' : '' ?>">
                                    <label class="form-label">Tồn kho tối thiểu</label>
                                    <input type="number" class="form-control" name="min_stock" value="<?= $product['min_stock'] ?>" min="0">
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
            const isService = this.value === 'service';
            document.getElementById('stockFields').style.display = isService ? 'none' : '';
            document.getElementById('minStockField').style.display = isService ? 'none' : '';
        });
        </script>
