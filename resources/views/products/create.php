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
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Loại</label>
                                    <select name="type" class="form-select" id="productType">
                                        <option value="product">Sản phẩm</option>
                                        <option value="service">Dịch vụ</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Danh mục</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">-- Chọn --</option>
                                        <?php foreach ($categories ?? [] as $cat): ?>
                                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Nhà sản xuất</label>
                                    <select name="manufacturer_id" class="form-select">
                                        <option value="">-- Chọn --</option>
                                        <?php foreach ($manufacturers ?? [] as $m): ?>
                                            <option value="<?= $m['id'] ?>"><?= e($m['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Xuất xứ</label>
                                    <select name="origin_id" class="form-select">
                                        <option value="">-- Chọn --</option>
                                        <?php foreach ($origins ?? [] as $o): ?>
                                            <option value="<?= $o['id'] ?>"><?= e($o['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Đơn vị tính</label>
                                    <input type="text" class="form-control" name="unit" value="Cái" placeholder="Cái, Bộ, Tháng...">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Khối lượng (kg)</label>
                                    <input type="number" class="form-control" name="weight" step="0.001" min="0" placeholder="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mã vạch (Barcode)</label>
                                    <input type="text" class="form-control" name="barcode" placeholder="VD: 8935039502345">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Kích thước</label>
                                    <input type="text" class="form-control" name="dimensions" placeholder="VD: W1400 x D600 x H750mm" maxlength="255">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Màu sắc</label>
                                    <input type="text" class="form-control" name="color" placeholder="VD: Đen, Nâu" maxlength="100">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Ảnh sản phẩm</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <small class="text-muted">JPG, PNG, GIF, WebP. Giới hạn theo cài đặt chung.</small>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả ngắn</label>
                                    <textarea name="short_description" class="form-control" rows="2" placeholder="Tóm tắt 1-2 câu, hiển thị ở trang danh sách"></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả chi tiết</label>
                                    <textarea name="description" id="productDescription" class="form-control" rows="8"></textarea>
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
                                    <input type="number" class="form-control" name="price" value="0" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá vốn <span class="text-muted">(VNĐ)</span></label>
                                    <input type="number" class="form-control" name="cost_price" value="0" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá sỉ <span class="text-muted">(VNĐ)</span></label>
                                    <input type="number" class="form-control" name="price_wholesale" value="0" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá online <span class="text-muted">(VNĐ)</span></label>
                                    <input type="number" class="form-control" name="price_online" value="0" min="0">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giá khuyến mãi <span class="text-muted">(VNĐ)</span></label>
                                    <input type="number" class="form-control" name="saleoff_price" min="0" placeholder="Để trống nếu không có">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Giảm giá (%)</label>
                                    <input type="number" class="form-control" name="discount_percent" value="0" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Thuế VAT (%)</label>
                                    <input type="number" class="form-control" name="tax_rate" value="0" min="0" max="100" step="0.01">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card" id="stockCard">
                        <div class="card-header"><h5 class="card-title mb-0">Kho</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số lượng tồn kho</label>
                                    <input type="number" class="form-control" name="stock_quantity" value="0" min="0">
                                </div>
                                <div class="col-md-6 mb-3">
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

        <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
        <script>
        document.getElementById('productType')?.addEventListener('change', function() {
            const stockCard = document.getElementById('stockCard');
            if (stockCard) stockCard.style.display = this.value === 'service' ? 'none' : '';
        });

        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.replace('productDescription', { language: 'vi', height: 280, allowedContent: true });
        }
        </script>
