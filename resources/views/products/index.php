<?php $pageTitle = 'Sản phẩm & Dịch vụ'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sản phẩm & Dịch vụ</h4>
            <div class="d-flex gap-2">
                <button class="btn btn-soft-info" data-bs-toggle="modal" data-bs-target="#importExportProdModal"><i class="ri-upload-2-line me-1"></i> Import / Export</button>
                <a href="<?= url('products/trash') ?>" class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Đã xóa</a>
                <a href="<?= url('products/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm sản phẩm</a>
            </div>
        </div>

<!-- Import/Export Modal -->
<div class="modal fade" id="importExportProdModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="ri-upload-2-line me-2"></i> Import / Export Sản phẩm</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabImportP">Import</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabExportP">Export</a></li>
                </ul>
                <div class="tab-content pt-3">
                    <div class="tab-pane active" id="tabImportP">
                        <form method="POST" action="<?= url('import-export/import-products') ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label">Chọn file CSV</label>
                                <input type="file" class="form-control" name="file" accept=".csv" required>
                            </div>
                            <div class="alert alert-light border py-2 mb-3">
                                <i class="ri-information-line me-1"></i> File CSV UTF-8, phân cách dấu phẩy. Cột bắt buộc: <code>name</code>. Khác: <code>sku, type, unit, price, cost_price, category, description</code>.
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="ri-upload-2-line me-1"></i> Import</button>
                                <a href="<?= url('import-export/template/products') ?>" class="btn btn-soft-info"><i class="ri-download-line me-1"></i> Tải template</a>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="tabExportP">
                        <div class="mb-3">
                            <label class="form-label">Khoảng thời gian (tùy chọn)</label>
                            <div class="row g-2">
                                <div class="col-6"><input type="date" class="form-control" id="expPDateFrom"></div>
                                <div class="col-6"><input type="date" class="form-control" id="expPDateTo"></div>
                            </div>
                        </div>
                        <a href="<?= url('import-export/export-products') ?>" class="btn btn-success" id="btnExpProducts"><i class="ri-download-2-line me-1"></i> Export Sản phẩm</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var df=document.getElementById('expPDateFrom'), dt=document.getElementById('expPDateTo'), btn=document.getElementById('btnExpProducts');
    if(!df||!dt||!btn) return;
    var base='<?= url("import-export/export-products") ?>';
    function upd(){ var p=[]; if(df.value)p.push('date_from='+df.value); if(dt.value)p.push('date_to='+dt.value); btn.href=base+(p.length?'?'+p.join('&'):''); }
    df.addEventListener('change',upd); dt.addEventListener('change',upd);
})();
</script>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('products') ?>" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm tên, SKU..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="category_id" class="form-select">
                            <option value="">Danh mục</option>
                            <?php foreach ($categories ?? [] as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">Loại</option>
                            <option value="product" <?= ($filters['type'] ?? '') === 'product' ? 'selected' : '' ?>>Sản phẩm</option>
                            <option value="service" <?= ($filters['type'] ?? '') === 'service' ? 'selected' : '' ?>>Dịch vụ</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('products') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th>SKU</th>
                                <th>Loại</th>
                                <th>Danh mục</th>
                                <th>Đơn giá</th>
                                <th>Giá vốn</th>
                                <th>Tồn kho</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products['items'])): ?>
                                <?php foreach ($products['items'] as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($product['image'])): ?>
                                                    <img src="<?= url('uploads/products/' . $product['image']) ?>" class="rounded me-2" style="width:40px;height:40px;object-fit:cover">
                                                <?php else: ?>
                                                    <div class="avatar-sm me-2 flex-shrink-0"><span class="avatar-title bg-light rounded"><i class="ri-image-line text-muted"></i></span></div>
                                                <?php endif; ?>
                                                <a href="<?= url('products/' . $product['id']) ?>" class="fw-medium text-dark">
                                                    <?= e($product['name']) ?>
                                                </a>
                                            </div>
                                        </td>
                                        <td><code><?= e($product['sku'] ?? '-') ?></code></td>
                                        <td>
                                            <?php if ($product['type'] === 'service'): ?>
                                                <span class="badge bg-info-subtle text-info">Dịch vụ</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary-subtle text-primary">Sản phẩm</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($product['category_name'] ?? '-') ?></td>
                                        <td class="fw-medium"><?= format_money($product['price']) ?></td>
                                        <td><?= format_money($product['cost_price']) ?></td>
                                        <td>
                                            <?php if ($product['type'] === 'product'): ?>
                                                <?php if ($product['stock_quantity'] <= $product['min_stock']): ?>
                                                    <span class="text-danger fw-medium"><?= $product['stock_quantity'] ?></span>
                                                    <i class="ri-error-warning-line text-danger" title="Tồn kho thấp"></i>
                                                <?php else: ?>
                                                    <?= $product['stock_quantity'] ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($product['is_active']): ?>
                                                <span class="badge bg-success-subtle text-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger">Ngừng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('products/' . $product['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('products/' . $product['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('products/' . $product['id'] . '/delete') ?>" data-confirm="Xác nhận xóa sản phẩm này?">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-shopping-bag-line fs-1 d-block mb-2"></i>Chưa có sản phẩm</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($products['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($products['items']) ?> / <?= $products['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $products['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $products['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('products?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
