<?php $pageTitle = 'Bao hàng';
$stLabels = ['open'=>'Mở','sealed'=>'Đã niêm','shipping'=>'Vận chuyển','arrived'=>'Đã đến','completed'=>'Hoàn thành'];
$stColors = ['open'=>'warning','sealed'=>'primary','shipping'=>'info','arrived'=>'success','completed'=>'success'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Bao hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('logistics') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Dashboard</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBagModal"><i class="ri-add-line me-1"></i> Tạo bao</button>
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
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th><th>Mã bao</th><th>Số kiện</th><th>Tổng cân</th><th>Trạng thái</th><th>Người tạo</th><th>Ngày tạo</th></tr></thead>
                <tbody>
                <?php foreach ($bags as $b): ?>
                <tr>
                    <td><?php if (in_array($b['status'], ['open','sealed'])): ?><input type="checkbox" class="form-check-input row-check" value="<?= $b['id'] ?>" data-pkgs="<?= $b['pkg_count'] ?>" data-weight="<?= $b['total_weight'] ?>"><?php endif; ?></td>
                    <td class="fw-medium"><?= e($b['bag_code']) ?></td>
                    <td><span class="badge bg-primary-subtle text-primary"><?= $b['pkg_count'] ?></span></td>
                    <td><?= $b['total_weight'] ? rtrim(rtrim(number_format($b['total_weight'], 2), '0'), '.') . ' kg' : '-' ?></td>
                    <td><span class="badge bg-<?= $stColors[$b['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$b['status']] ?? 'secondary' ?>"><?= $stLabels[$b['status']] ?? $b['status'] ?></span></td>
                    <td><?= user_avatar($b['created_by_name'] ?? null) ?></td>
                    <td class="text-muted fs-12"><?= created_ago($b['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bags)): ?><tr><td colspan="7" class="text-center text-muted py-4">Chưa có bao hàng</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
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
