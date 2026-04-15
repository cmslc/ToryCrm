<?php
$pageTitle = 'Sản phẩm & Dịch vụ';
$colKeys = array_column($displayColumns ?? [], 'key');
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Sản phẩm & Dịch vụ</h4>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-soft-secondary" id="toggleColumnPanel">Hiển thị cột <i class="ri-arrow-down-s-line ms-1"></i></button>
                <a href="<?= url('products/settings') ?>" class="btn btn-soft-secondary"><i class="ri-folder-settings-line me-1"></i> Danh mục</a>
                <button class="btn btn-soft-info" data-bs-toggle="modal" data-bs-target="#importExportProdModal"><i class="ri-upload-2-line me-1"></i> Import / Export</button>
                <a href="<?= url('products/trash') ?>" class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Đã xóa</a>
                <a href="<?= url('products/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm sản phẩm</a>
            </div>
        </div>

        <!-- Column Options Panel -->
        <div class="card mb-2 d-none" id="columnPanel">
            <div class="card-body py-3">
                <h6 class="mb-2">Cột hiển thị</h6>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <?php foreach ($displayColumns as $dc): ?>
                    <div class="form-check">
                        <input class="form-check-input column-toggle" type="checkbox" id="<?= $dc['key'] ?>" data-column="<?= $dc['key'] ?>" checked>
                        <label class="form-check-label" for="<?= $dc['key'] ?>"><?= e($dc['label']) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-soft-secondary py-1 px-2" id="resetColumns"><i class="ri-refresh-line me-1"></i>Đặt lại</button>
                </div>
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
            <div class="card-header p-2">
                <form method="GET" action="<?= url('products') ?>" class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="search-box" style="min-width:200px;max-width:300px">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm tên, SKU..." value="<?= e($filters['search'] ?? '') ?>">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                    <select name="category_id" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                        <option value="">Danh mục</option>
                        <?php foreach ($categories ?? [] as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="type" class="form-select" style="width:auto;min-width:100px" onchange="this.form.submit()">
                        <option value="">Loại</option>
                        <option value="product" <?= ($filters['type'] ?? '') === 'product' ? 'selected' : '' ?>>Sản phẩm</option>
                        <option value="service" <?= ($filters['type'] ?? '') === 'service' ? 'selected' : '' ?>>Dịch vụ</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                    <?php if (!empty(array_filter($filters ?? []))): ?>
                        <a href="<?= url('products') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
                    <?php endif; ?>
                    <select name="per_page" class="form-select ms-auto" style="width:auto;min-width:90px" onchange="this.form.submit()">
                        <?php foreach ([10,20,50,100] as $pp): ?>
                        <option value="<?= $pp ?>" <?= ($filters['per_page'] ?? 10) == $pp ? 'selected' : '' ?>><?= $pp ?> dòng</option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="card-body p-0">

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <?php foreach ($displayColumns as $dc): ?>
                                <th class="<?= $dc['key'] ?>"><?= e($dc['label']) ?></th>
                                <?php endforeach; ?>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products['items'])): ?>
                                <?php foreach ($products['items'] as $product): ?>
                                    <tr>
                                        <?php foreach ($displayColumns as $dc):
                                            $field = $dc['field'];
                                            $key = $dc['key'];
                                            $val = $product[$field] ?? '';
                                        ?>
                                        <td class="<?= $key ?>">
                                        <?php switch ($field):
                                            case 'name': ?>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($product['image'])): ?>
                                                        <img src="<?= url('uploads/products/' . $product['image']) ?>" class="rounded me-2" style="width:40px;height:40px;object-fit:cover">
                                                    <?php else: ?>
                                                        <div class="avatar-sm me-2 flex-shrink-0"><span class="avatar-title bg-light rounded"><i class="ri-image-line text-muted"></i></span></div>
                                                    <?php endif; ?>
                                                    <a href="<?= url('products/' . $product['id']) ?>" class="fw-medium text-dark"><?= e($product['name']) ?></a>
                                                </div>
                                            <?php break; case 'sku': ?>
                                                <code><?= e($val ?: '-') ?></code>
                                            <?php break; case 'type': ?>
                                                <?= $val === 'service' ? '<span class="badge bg-info-subtle text-info">Dịch vụ</span>' : '<span class="badge bg-primary-subtle text-primary">Sản phẩm</span>' ?>
                                            <?php break; case 'category_id': ?>
                                                <?= e($product['category_name'] ?? '-') ?>
                                            <?php break; case 'price': case 'cost_price': ?>
                                                <?= format_money($val) ?>
                                            <?php break; case 'stock_quantity': ?>
                                                <?php if ($product['type'] === 'product'): ?>
                                                    <?php if ($val <= ($product['min_stock'] ?? 0)): ?>
                                                        <span class="text-danger fw-medium"><?= $val ?></span> <i class="ri-error-warning-line text-danger"></i>
                                                    <?php else: echo $val; endif; ?>
                                                <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                                            <?php break; case 'is_active': ?>
                                                <?= $val ? '<span class="badge bg-success-subtle text-success">Hoạt động</span>' : '<span class="badge bg-danger-subtle text-danger">Ngừng</span>' ?>
                                            <?php break; default: ?>
                                                <?= e($val ?: '-') ?>
                                        <?php endswitch; ?>
                                        </td>
                                        <?php endforeach; ?>
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
                    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                        <div class="text-muted fs-13">Hiển thị <strong><?= (($products['page'] - 1) * ($filters['per_page'] ?? 10)) + 1 ?> - <?= min($products['page'] * ($filters['per_page'] ?? 10), $products['total']) ?></strong> / <strong><?= number_format($products['total']) ?></strong></div>
                        <nav><ul class="pagination mb-0">
                            <?php
                            $curPage = $products['page'];
                            $totalPages = $products['total_pages'];
                            $qs = http_build_query(array_filter($filters ?? []));
                            $pageUrl = function($p) use ($qs) { return url('products?page=' . $p . ($qs ? '&' . $qs : '')); };

                            if ($curPage > 1): ?>
                                <li class="page-item"><a class="page-link" href="<?= $pageUrl($curPage - 1) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
                            <?php endif;

                            if ($curPage > 3): ?>
                                <li class="page-item"><a class="page-link" href="<?= $pageUrl(1) ?>">1</a></li>
                                <?php if ($curPage > 4): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif;
                            endif;

                            for ($i = max(1, $curPage - 2); $i <= min($totalPages, $curPage + 2); $i++): ?>
                                <li class="page-item <?= $i === $curPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $pageUrl($i) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor;

                            if ($curPage < $totalPages - 2): ?>
                                <?php if ($curPage < $totalPages - 3): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                                <li class="page-item"><a class="page-link" href="<?= $pageUrl($totalPages) ?>"><?= $totalPages ?></a></li>
                            <?php endif;

                            if ($curPage < $totalPages): ?>
                                <li class="page-item"><a class="page-link" href="<?= $pageUrl($curPage + 1) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
                            <?php endif; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<script>
document.getElementById('toggleColumnPanel')?.addEventListener('click', function() {
    var panel = document.getElementById('columnPanel');
    panel.classList.toggle('d-none');
    var isOpen = !panel.classList.contains('d-none');
    this.innerHTML = 'Hiển thị cột <i class="ri-arrow-' + (isOpen ? 'up' : 'down') + '-s-line ms-1"></i>';
});

(function() {
    var STORAGE_KEY = 'torycrm_products_columns';
    var allColumns = <?= json_encode($colKeys) ?>;
    var defaultVisible = ['col-name','col-sku','col-type','col-categoryid','col-price','col-costprice','col-stockquantity','col-isactive'];

    function getVisible() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || defaultVisible; }
        catch(e) { return defaultVisible; }
    }

    function applyColumns(visible) {
        allColumns.forEach(function(col) {
            var show = visible.includes(col);
            document.querySelectorAll('.' + col).forEach(function(el) { el.style.display = show ? '' : 'none'; });
            var cb = document.getElementById(col);
            if (cb) cb.checked = show;
        });
    }

    document.querySelectorAll('.column-toggle').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var visible = getVisible();
            if (this.checked) { if (!visible.includes(this.dataset.column)) visible.push(this.dataset.column); }
            else { visible = visible.filter(function(c) { return c !== cb.dataset.column; }); }
            localStorage.setItem(STORAGE_KEY, JSON.stringify(visible));
            applyColumns(visible);
        });
    });

    document.getElementById('resetColumns')?.addEventListener('click', function() {
        localStorage.removeItem(STORAGE_KEY);
        applyColumns(defaultVisible);
    });

    applyColumns(getVisible());
})();
</script>
