<?php $pageTitle = 'Quản lý danh mục sản phẩm'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Danh mục & Phân loại</h4>
    <a href="<?= url('products') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Sản phẩm</a>
</div>

<div class="row">
    <!-- Categories -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"><i class="ri-folder-line me-1"></i> Danh mục (<?= count($categories) ?>)</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#catModal" onclick="document.getElementById('catForm').reset();document.getElementById('catId').value=''"><i class="ri-add-line"></i> Thêm</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:500px;overflow-y:auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th>Tên danh mục</th><th class="text-center" style="width:80px">Thứ tự</th><th style="width:100px"></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><?= e($c['name']) ?></td>
                                <td class="text-center"><?= $c['sort_order'] ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-soft-primary btn-sm btn-icon" onclick="editCat(<?= $c['id'] ?>, '<?= e(addslashes($c['name'])) ?>', <?= $c['sort_order'] ?>)"><i class="ri-pencil-line"></i></button>
                                        <form method="POST" action="<?= url('products/settings/category/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('Xóa danh mục này?')">
                                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                            <button class="btn btn-soft-danger btn-sm btn-icon"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Chưa có danh mục</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <!-- Manufacturers -->
        <div class="card" id="manufacturers">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"><i class="ri-building-line me-1"></i> Nhà sản xuất (<?= count($manufacturers) ?>)</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#mfrModal" onclick="document.getElementById('mfrForm').reset();document.getElementById('mfrId').value=''"><i class="ri-add-line"></i> Thêm</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:250px;overflow-y:auto">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr><th>Tên nhà sản xuất</th><th style="width:100px"></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($manufacturers as $m): ?>
                            <tr>
                                <td><?= e($m['name']) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-soft-primary btn-sm btn-icon" onclick="editMfr(<?= $m['id'] ?>, '<?= e(addslashes($m['name'])) ?>')"><i class="ri-pencil-line"></i></button>
                                        <form method="POST" action="<?= url('products/settings/manufacturer/' . $m['id'] . '/delete') ?>" onsubmit="return confirm('Xóa nhà sản xuất này?')">
                                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                            <button class="btn btn-soft-danger btn-sm btn-icon"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($manufacturers)): ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">Chưa có nhà sản xuất</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Origins -->
        <div class="card" id="origins">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0"><i class="ri-map-pin-line me-1"></i> Xuất xứ (<?= count($origins) ?>)</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#oriModal" onclick="document.getElementById('oriForm').reset();document.getElementById('oriId').value=''"><i class="ri-add-line"></i> Thêm</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Tên xuất xứ</th><th style="width:100px"></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($origins as $o): ?>
                            <tr>
                                <td><?= e($o['name']) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-soft-primary btn-sm btn-icon" onclick="editOri(<?= $o['id'] ?>, '<?= e(addslashes($o['name'])) ?>')"><i class="ri-pencil-line"></i></button>
                                        <form method="POST" action="<?= url('products/settings/origin/' . $o['id'] . '/delete') ?>" onsubmit="return confirm('Xóa xuất xứ này?')">
                                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                            <button class="btn btn-soft-danger btn-sm btn-icon"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($origins)): ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">Chưa có xuất xứ</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="catModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="catForm" method="POST" action="<?= url('products/settings/category') ?>">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" id="catId" value="">
        <div class="modal-header"><h5 class="modal-title" id="catModalTitle">Thêm danh mục</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" id="catName" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Thứ tự sắp xếp</label>
                <input type="number" class="form-control" name="sort_order" id="catSort" value="0">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary">Lưu</button>
        </div>
    </form>
</div></div></div>

<!-- Manufacturer Modal -->
<div class="modal fade" id="mfrModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="mfrForm" method="POST" action="<?= url('products/settings/manufacturer') ?>">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" id="mfrId" value="">
        <div class="modal-header"><h5 class="modal-title" id="mfrModalTitle">Thêm nhà sản xuất</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Tên nhà sản xuất <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" id="mfrName" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary">Lưu</button>
        </div>
    </form>
</div></div></div>

<!-- Origin Modal -->
<div class="modal fade" id="oriModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form id="oriForm" method="POST" action="<?= url('products/settings/origin') ?>">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="id" id="oriId" value="">
        <div class="modal-header"><h5 class="modal-title" id="oriModalTitle">Thêm xuất xứ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Tên xuất xứ <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" id="oriName" required>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary">Lưu</button>
        </div>
    </form>
</div></div></div>

<script>
function editCat(id, name, sort) {
    document.getElementById('catId').value = id;
    document.getElementById('catName').value = name;
    document.getElementById('catSort').value = sort;
    document.getElementById('catModalTitle').textContent = 'Sửa danh mục';
    new bootstrap.Modal(document.getElementById('catModal')).show();
}
function editMfr(id, name) {
    document.getElementById('mfrId').value = id;
    document.getElementById('mfrName').value = name;
    document.getElementById('mfrModalTitle').textContent = 'Sửa nhà sản xuất';
    new bootstrap.Modal(document.getElementById('mfrModal')).show();
}
function editOri(id, name) {
    document.getElementById('oriId').value = id;
    document.getElementById('oriName').value = name;
    document.getElementById('oriModalTitle').textContent = 'Sửa xuất xứ';
    new bootstrap.Modal(document.getElementById('oriModal')).show();
}
</script>
