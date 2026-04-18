<?php
$pageTitle = 'Xuất nhập kho';
$typeLabels = ['import'=>'Nhập kho','export'=>'Xuất kho','transfer'=>'Chuyển kho','adjustment'=>'Điều chỉnh'];
$typeColors = ['import'=>'success','export'=>'danger','transfer'=>'info','adjustment'=>'warning'];
$currentType = $filters['type'] ?? '';
$__tenant = \Core\Database::fetch("SELECT settings FROM tenants WHERE id = ?", [$_SESSION['tenant_id'] ?? 1]);
$__whConfig = json_decode($__tenant['settings'] ?? '{}', true)['warehouse'] ?? [];
$defaultWhId = (int)($__whConfig['default_warehouse_id'] ?? 0);
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Xuất nhập kho</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('warehouses') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Danh sách kho</a>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#movementModal" onclick="document.getElementById('mvType').value='import'"><i class="ri-arrow-down-line me-1"></i> Nhập kho</button>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#movementModal" onclick="document.getElementById('mvType').value='export'"><i class="ri-arrow-up-line me-1"></i> Xuất kho</button>
        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#movementModal" onclick="document.getElementById('mvType').value='transfer'"><i class="ri-arrow-left-right-line me-1"></i> Chuyển kho</button>
    </div>
</div>

<!-- Filter -->
<div class="card mb-2">
    <div class="card-header p-2">
        <div class="d-flex gap-2">
            <a href="<?= url('warehouses/movements') ?>" class="btn <?= !$currentType ? 'btn-primary' : 'btn-soft-primary' ?>">Tất cả</a>
            <?php foreach ($typeLabels as $k => $v): ?>
                <a href="<?= url('warehouses/movements?type=' . $k) ?>" class="btn <?= $currentType === $k ? 'btn-' . $typeColors[$k] : 'btn-soft-' . $typeColors[$k] ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- List -->
<div class="card">
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Mã phiếu</th><th>Loại</th><th>Kho</th><th>Số SP</th><th>Tổng SL</th><th>Người tạo</th><th>Ngày tạo</th><th>Trạng thái</th></tr></thead>
                <tbody>
                <?php foreach ($movements as $m): ?>
                <tr>
                    <td><a href="<?= url('warehouses/movements/' . $m['id']) ?>" class="fw-medium"><?= e($m['code']) ?></a></td>
                    <td><span class="badge bg-<?= $typeColors[$m['type']] ?? 'secondary' ?>"><?= $typeLabels[$m['type']] ?? $m['type'] ?></span></td>
                    <td><?= e($m['warehouse_name']) ?><?= $m['to_warehouse_name'] ? ' → ' . e($m['to_warehouse_name']) : '' ?></td>
                    <td><?= $m['item_count'] ?></td>
                    <td class="fw-medium"><?= number_format($m['total_qty'] ?? 0) ?></td>
                    <td><?= user_avatar($m['created_by_name'] ?? null) ?></td>
                    <td class="text-muted"><?= created_ago($m['created_at']) ?></td>
                    <td><span class="badge bg-<?= $m['status'] === 'confirmed' ? 'success' : ($m['status'] === 'cancelled' ? 'danger' : 'warning') ?>-subtle text-<?= $m['status'] === 'confirmed' ? 'success' : ($m['status'] === 'cancelled' ? 'danger' : 'warning') ?>"><?= $m['status'] === 'confirmed' ? 'Đã xác nhận' : ($m['status'] === 'cancelled' ? 'Đã hủy' : 'Nháp') ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($movements)): ?><tr><td colspan="8" class="text-center text-muted py-4"><i class="ri-inbox-line fs-1 d-block mb-2"></i>Chưa có phiếu nào</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Movement Modal -->
<div class="modal fade" id="movementModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('warehouses/movements/create') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="type" id="mvType" value="import">
                <div class="modal-header"><h5 class="modal-title">Tạo phiếu xuất nhập kho</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Kho <span class="text-danger">*</span></label>
                            <select name="warehouse_id" class="form-select" required>
                                <option value="">Chọn kho...</option>
                                <?php foreach ($warehouses as $wh): ?><option value="<?= $wh['id'] ?>" <?= $wh['id'] == $defaultWhId ? 'selected' : '' ?>><?= e($wh['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4" id="toWhGroup" style="display:none">
                            <label class="form-label">Kho đích</label>
                            <select name="to_warehouse_id" class="form-select">
                                <option value="">Chọn kho đích...</option>
                                <?php foreach ($warehouses as $wh): ?><option value="<?= $wh['id'] ?>"><?= e($wh['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ghi chú</label>
                            <input type="text" class="form-control" name="note" placeholder="Ghi chú...">
                        </div>
                    </div>
                    <h6>Sản phẩm</h6>
                    <table class="table table-bordered" id="mvItemsTable">
                        <thead><tr><th>Sản phẩm</th><th style="width:120px">Số lượng</th><th style="width:140px">Đơn giá</th><th style="width:50px"></th></tr></thead>
                        <tbody>
                            <tr class="mv-row">
                                <td><select name="product_id[]" class="form-select" required><option value="">Chọn...</option><?php foreach ($products as $p): ?><option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>"><?= e($p['name']) ?> (<?= e($p['sku'] ?? '') ?>)</option><?php endforeach; ?></select></td>
                                <td><input type="number" name="quantity[]" class="form-control" min="0.01" step="0.01" required></td>
                                <td><input type="number" name="unit_price[]" class="form-control" min="0" step="1"></td>
                                <td><button type="button" class="btn btn-soft-danger btn-icon" onclick="this.closest('tr').remove()"><i class="ri-close-line"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-soft-primary" id="addMvRow"><i class="ri-add-line me-1"></i> Thêm dòng</button>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Tạo phiếu</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('mvType')?.addEventListener('change', function() {
    document.getElementById('toWhGroup').style.display = this.value === 'transfer' ? '' : 'none';
});
document.getElementById('movementModal')?.addEventListener('show.bs.modal', function() {
    setTimeout(function() {
        document.getElementById('toWhGroup').style.display = document.getElementById('mvType').value === 'transfer' ? '' : 'none';
    }, 100);
});
document.getElementById('addMvRow')?.addEventListener('click', function() {
    var row = document.querySelector('.mv-row').cloneNode(true);
    row.querySelectorAll('input').forEach(function(i) { i.value = ''; });
    row.querySelector('select').value = '';
    document.querySelector('#mvItemsTable tbody').appendChild(row);
});
</script>
