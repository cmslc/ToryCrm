<?php $pageTitle = 'Thêm sản phẩm'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Thêm sản phẩm / dịch vụ</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('products') ?>">Sản phẩm</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('products/store') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin sản phẩm</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Mã SKU</label>
                                    <input type="text" class="form-control" name="sku" placeholder="VD: SP001">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Loại</label>
                                    <select name="type" class="form-select" id="productType">
                                        <option value="product">Sản phẩm</option>
                                        <option value="service">Dịch vụ</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Danh mục</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Chọn danh mục</option>
                                        <?php foreach ($categories ?? [] as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Đơn vị tính</label>
                                    <input type="text" class="form-control" name="unit" value="Cái" placeholder="VD: Cái, Tháng, Gói">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ảnh sản phẩm</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF. Tối đa 5MB.</small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
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
                                    <input type="number" class="form-control" name="price" value="0" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Giá vốn (VNĐ)</label>
                                    <input type="number" class="form-control" name="cost_price" value="0" min="0">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Thuế (%)</label>
                                    <input type="number" class="form-control" name="tax_rate" value="0" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-md-6 mb-3" id="stockFields">
                                    <label class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control" name="stock_quantity" value="0" min="0">
                                </div>
                                <div class="col-md-6 mb-3" id="minStockField">
                                    <label class="form-label">Tồn kho tối thiểu</label>
                                    <input type="number" class="form-control" name="min_stock" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                            <a href="<?= url('products') ?>" class="btn btn-soft-secondary">Hủy</a>
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
