<?php $pageTitle = 'Bao hàng';
$stLabels = ['open'=>'Mở','sealed'=>'Đã niêm','shipping'=>'Vận chuyển','arrived'=>'Đã đến','completed'=>'Hoàn thành'];
$stColors = ['open'=>'warning','sealed'=>'primary','shipping'=>'info','arrived'=>'success','completed'=>'success'];
$fStatus = $filters['status'] ?? '';
$fSearch = $filters['search'] ?? '';
$fDateFrom = $filters['dateFrom'] ?? '';
$fDateTo = $filters['dateTo'] ?? '';
$hasFilter = $fStatus || $fSearch || $fDateFrom || $fDateTo;
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Bao hàng</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBagModal"><i class="ri-add-line me-1"></i> Tạo bao</button>
    </div>
</div>

<!-- Bộ lọc -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('logistics/bags') ?>" class="d-flex align-items-center gap-2 flex-wrap">
            <div class="search-box" style="min-width:160px;max-width:240px">
                <input type="text" class="form-control" name="search" placeholder="Mã bao..." value="<?= e($fSearch) ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <select name="status" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <?php foreach ($stLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= $fStatus === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" class="form-control" style="width:auto" value="<?= e($fDateFrom) ?>" title="Từ ngày">
            <input type="date" name="date_to" class="form-control" style="width:auto" value="<?= e($fDateTo) ?>" title="Đến ngày">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
            <?php if ($hasFilter): ?>
            <a href="<?= url('logistics/bags') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Bulk bar for xếp xe -->
<?php
$existingShipments = \Core\Database::fetchAll("SELECT id, shipment_code, origin, destination, total_packages FROM logistics_shipments WHERE tenant_id = ? AND status = 'preparing' ORDER BY created_at DESC", [$_SESSION['tenant_id'] ?? 1]);
?>
<div class="card mb-2 d-none" id="bulkBar" style="position:sticky;top:70px;z-index:100">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="fw-medium"><span id="bulkCount">0</span> bao đã chọn</span>
            <span class="text-muted">|</span>
            <span class="text-muted fs-12"><i class="ri-box-3-line me-1"></i><span id="bulkPkgs">0</span> kiện</span>
            <span class="text-muted fs-12"><i class="ri-scales-line me-1"></i><span id="bulkWeight">0</span> kg</span>
            <span class="text-muted">|</span>
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#shipBagModal"><i class="ri-truck-line me-1"></i> Xếp xe vận chuyển</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th><th>Mã bao</th><th>Số kiện</th><th>Tổng cân</th><th>Trạng thái</th><th>Người tạo</th><th>Ngày tạo</th><th style="width:60px"></th></tr></thead>
                <tbody>
                <?php foreach ($bags as $b): ?>
                <tr>
                    <td><?php if (in_array($b['status'], ['open','sealed'])): ?><input type="checkbox" class="form-check-input row-check" value="<?= $b['id'] ?>" data-pkgs="<?= $b['pkg_count'] ?>" data-weight="<?= $b['total_weight'] ?>"><?php endif; ?></td>
                    <td class="fw-medium"><a href="<?= url('logistics/bags/' . $b['id']) ?>"><?= e($b['bag_code']) ?></a></td>
                    <td><span class="badge bg-primary-subtle text-primary"><?= $b['pkg_count'] ?></span></td>
                    <td><?= $b['total_weight'] ? rtrim(rtrim(number_format($b['total_weight'], 2), '0'), '.') . ' kg' : '-' ?></td>
                    <td><span class="badge bg-<?= $stColors[$b['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$b['status']] ?? 'secondary' ?>"><?= $stLabels[$b['status']] ?? $b['status'] ?></span><?php if ($b['sealed_at']): ?> <small class="text-muted d-block fs-11"><?= created_ago($b['sealed_at']) ?></small><?php endif; ?></td>
                    <td><?= user_avatar($b['created_by_name'] ?? null) ?></td>
                    <td class="text-muted fs-12"><?= created_ago($b['created_at']) ?></td>
                    <td>
                        <?php if (in_array($b['status'], ['open','sealed'])): ?>
                        <div class="d-flex gap-1">
                            <?php if ($b['status'] === 'open'): ?>
                            <button class="btn btn-soft-primary btn-icon" onclick="sealBag(<?= $b['id'] ?>, '<?= e($b['bag_code']) ?>')" title="Đóng bao"><i class="ri-lock-line"></i></button>
                            <?php elseif ($b['status'] === 'sealed'): ?>
                            <button class="btn btn-soft-warning btn-icon" onclick="unsealBag(<?= $b['id'] ?>, '<?= e($b['bag_code']) ?>')" title="Mở lại bao"><i class="ri-lock-unlock-line"></i></button>
                            <?php endif; ?>
                            <button class="btn btn-soft-info btn-icon" onclick="editBag(<?= $b['id'] ?>, '<?= e($b['bag_code']) ?>', '<?= e($b['note'] ?? '') ?>')" title="Sửa"><i class="ri-edit-line"></i></button>
                            <button class="btn btn-soft-danger btn-icon" onclick="deleteBag(<?= $b['id'] ?>, '<?= e($b['bag_code']) ?>')" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bags)): ?><tr><td colspan="8" class="text-center text-muted py-4">Chưa có bao hàng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($totalPages > 1): ?>
    <?php $qs = http_build_query(array_filter(['status' => $fStatus, 'search' => $fSearch, 'date_from' => $fDateFrom, 'date_to' => $fDateTo])); ?>
    <div class="card-footer">
        <div class="d-flex align-items-center justify-content-between">
            <span class="text-muted fs-12">Tổng <?= $total ?> bao</span>
            <ul class="pagination pagination-separated mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('logistics/bags?' . $qs . '&page=' . ($page - 1)) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= url('logistics/bags?' . $qs . '&page=' . $i) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>"><a class="page-link" href="<?= url('logistics/bags?' . $qs . '&page=' . ($page + 1)) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Ship Bags Modal -->
<div class="modal fade" id="shipBagModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning-subtle"><h5 class="modal-title"><i class="ri-truck-line me-2"></i> Xếp bao vào xe</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3"><i class="ri-information-line me-1"></i> <strong id="shipBagSummary"></strong></div>
                <ul class="nav nav-tabs" role="tablist">
                    <?php if (!empty($existingShipments)): ?>
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabBagExisting">Chuyến có sẵn</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBagNew">Tạo chuyến mới</a></li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabBagNew">Tạo chuyến mới</a></li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content pt-3">
                    <?php if (!empty($existingShipments)): ?>
                    <div class="tab-pane active" id="tabBagExisting">
                        <form method="POST" action="" id="existingBagShipForm">
                            <?= csrf_field() ?>
                            <div id="existingBagIds"></div>
                            <table class="table table-hover align-middle mb-3">
                                <thead class="table-light"><tr><th></th><th>Mã chuyến</th><th>Tuyến</th><th>Kiện</th><th>Cân</th></tr></thead>
                                <tbody>
                                <?php foreach ($existingShipments as $es): ?>
                                <tr><td><input type="radio" class="form-check-input" name="existing_shipment" value="<?= $es['id'] ?>"></td><td class="fw-medium"><?= e($es['shipment_code']) ?></td><td><?= $es['origin'] ?> → <?= $es['destination'] ?></td><td><?= $es['total_packages'] ?></td><td><?= $es['total_weight'] ?? '-' ?></td></tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="text-end"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button> <button type="submit" class="btn btn-warning"><i class="ri-truck-line me-1"></i> Xếp xe</button></div>
                        </form>
                    </div>
                    <?php endif; ?>
                    <div class="tab-pane <?= empty($existingShipments) ? 'active' : '' ?>" id="tabBagNew">
                        <form method="POST" action="<?= url('logistics/shipments/create-from-bags') ?>" id="newBagShipForm">
                            <?= csrf_field() ?>
                            <div id="newBagIds"></div>
                            <div class="row">
                                <div class="col-4 mb-3"><label class="form-label">Biển số xe <span class="text-danger">*</span></label><input type="text" class="form-control" name="vehicle_info" placeholder="VD: 29C-12345" required></div>
                                <div class="col-4 mb-3"><label class="form-label">Tên tài xế</label><input type="text" class="form-control" name="driver_name"></div>
                                <div class="col-4 mb-3"><label class="form-label">SĐT tài xế</label><input type="text" class="form-control" name="driver_phone"></div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3"><label class="form-label">Tuyến đường</label><input type="text" class="form-control" name="route_name" value="Kho TQ - Cửa khẩu"></div>
                                <div class="col-6 mb-3"><label class="form-label">Ghi chú</label><input type="text" class="form-control" name="note"></div>
                            </div>
                            <input type="hidden" name="origin" value="CN"><input type="hidden" name="destination" value="VN">
                            <div class="text-end"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button> <button type="submit" class="btn btn-warning"><i class="ri-truck-line me-1"></i> Xếp xe</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addBagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('logistics/bags/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Tạo bao hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Mã bao</label><input type="text" class="form-control" name="bag_code" placeholder="Tự tạo nếu để trống"></div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea class="form-control" name="note" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Tạo</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Unseal Bag Modal -->
<div class="modal fade" id="unsealBagModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="unsealBagForm">
                <?= csrf_field() ?>
                <div class="modal-header bg-warning-subtle"><h5 class="modal-title"><i class="ri-lock-unlock-line me-2"></i> Mở lại bao</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Bạn muốn mở lại bao <strong id="unsealBagCode"></strong>?</p>
                    <div class="alert alert-info py-2 mb-0"><i class="ri-information-line me-1"></i> Sau khi mở, có thể quét thêm kiện vào bao.</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-warning"><i class="ri-lock-unlock-line me-1"></i> Mở lại</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Seal Bag Modal -->
<div class="modal fade" id="sealBagModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="sealBagForm">
                <?= csrf_field() ?>
                <div class="modal-header bg-primary-subtle"><h5 class="modal-title"><i class="ri-lock-line me-2"></i> Đóng bao</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Bạn muốn đóng (niêm phong) bao <strong id="sealBagCode"></strong>?</p>
                    <div class="alert alert-warning py-2 mb-0"><i class="ri-information-line me-1"></i> Sau khi đóng bao, không thể thêm kiện vào bao này nữa.</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-lock-line me-1"></i> Đóng bao</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Bag Modal -->
<div class="modal fade" id="editBagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editBagForm">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title"><i class="ri-edit-line me-2"></i> Sửa bao</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Mã bao</label><input type="text" class="form-control" name="bag_code" id="editBagCode"></div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea class="form-control" name="note" id="editBagNote" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Bag Modal -->
<div class="modal fade" id="deleteBagModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="deleteBagForm">
                <?= csrf_field() ?>
                <div class="modal-header bg-danger-subtle"><h5 class="modal-title text-danger"><i class="ri-delete-bin-line me-2"></i> Xóa bao</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p>Bạn chắc chắn muốn xóa bao <strong id="deleteBagCode"></strong>?</p>
                    <div class="alert alert-danger py-2 mb-0"><i class="ri-error-warning-line me-1"></i> Các kiện trong bao sẽ được gỡ ra khỏi bao.</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function unsealBag(id, code) {
    document.getElementById('unsealBagCode').textContent = code;
    document.getElementById('unsealBagForm').action = '<?= url("logistics/bags") ?>/' + id + '/seal';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('unsealBagModal')).show();
}
function sealBag(id, code) {
    document.getElementById('sealBagCode').textContent = code;
    document.getElementById('sealBagForm').action = '<?= url("logistics/bags") ?>/' + id + '/seal';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('sealBagModal')).show();
}
function editBag(id, code, note) {
    document.getElementById('editBagCode').value = code;
    document.getElementById('editBagNote').value = note;
    document.getElementById('editBagForm').action = '<?= url("logistics/bags") ?>/' + id + '/update';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editBagModal')).show();
}
function deleteBag(id, code) {
    document.getElementById('deleteBagCode').textContent = code;
    document.getElementById('deleteBagForm').action = '<?= url("logistics/bags") ?>/' + id + '/delete';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteBagModal')).show();
}
</script>

<script>
(function() {
    var checkAll = document.getElementById('checkAll');
    var bulkBar = document.getElementById('bulkBar');
    var selectedIds = [];

    function updateBulk() {
        var checked = document.querySelectorAll('.row-check:checked');
        selectedIds = [];
        if (checked.length > 0) {
            bulkBar.classList.remove('d-none');
            document.getElementById('bulkCount').textContent = checked.length;
            var totalPkgs = 0, totalWeight = 0;
            checked.forEach(function(cb) {
                selectedIds.push(cb.value);
                totalPkgs += parseInt(cb.dataset.pkgs || 0);
                totalWeight += parseFloat(cb.dataset.weight || 0);
            });
            document.getElementById('bulkPkgs').textContent = totalPkgs;
            document.getElementById('bulkWeight').textContent = parseFloat(totalWeight.toFixed(2));
            document.getElementById('shipBagSummary').textContent = checked.length + ' bao — ' + totalPkgs + ' kiện — ' + parseFloat(totalWeight.toFixed(2)) + ' kg';
        } else {
            bulkBar.classList.add('d-none');
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = checkAll.checked; });
            updateBulk();
        });
    }
    document.querySelectorAll('.row-check').forEach(function(cb) { cb.addEventListener('change', updateBulk); });

    // Existing shipment form
    document.getElementById('existingBagShipForm')?.addEventListener('submit', function(e) {
        var selected = document.querySelector('input[name="existing_shipment"]:checked');
        if (!selected) { e.preventDefault(); alert('Chọn một chuyến xe'); return; }
        this.action = '<?= url("logistics/shipments") ?>/' + selected.value + '/add-bags';
        var idsDiv = document.getElementById('existingBagIds');
        idsDiv.innerHTML = '';
        selectedIds.forEach(function(id) { var inp = document.createElement('input'); inp.type='hidden'; inp.name='bag_ids[]'; inp.value=id; idsDiv.appendChild(inp); });
    });

    // New shipment form
    document.getElementById('newBagShipForm')?.addEventListener('submit', function() {
        var idsDiv = document.getElementById('newBagIds');
        idsDiv.innerHTML = '';
        selectedIds.forEach(function(id) { var inp = document.createElement('input'); inp.type='hidden'; inp.name='bag_ids[]'; inp.value=id; idsDiv.appendChild(inp); });
    });
})();
</script>
