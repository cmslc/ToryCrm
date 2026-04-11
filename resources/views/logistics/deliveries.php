<?php $pageTitle = 'Giao hàng';
$stLabels = ['pending'=>'Chờ giao','delivering'=>'Đang giao','delivered'=>'Đã giao','failed'=>'Thất bại'];
$stColors = ['pending'=>'secondary','delivering'=>'warning','delivered'=>'success','failed'=>'danger'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Giao hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('logistics') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Dashboard</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDelModal"><i class="ri-add-line me-1"></i> Tạo phiếu giao</button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Mã phiếu</th><th>Khách hàng</th><th>Kiện</th><th>Loại</th><th>COD</th><th>Đã thu</th><th>PT thu</th><th>Trạng thái</th><th>Người giao</th><th>Ngày giao</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($deliveries as $d): ?>
                <tr>
                    <td class="fw-medium"><?= e($d['delivery_code']) ?></td>
                    <td><?= e($d['customer_name'] ?? '-') ?><?= $d['customer_phone'] ? '<div class="text-muted fs-11">' . e($d['customer_phone']) . '</div>' : '' ?></td>
                    <td><?= $d['delivered_packages'] ?>/<?= $d['total_packages'] ?></td>
                    <td><span class="badge bg-<?= $d['delivery_type'] === 'partial' ? 'warning' : 'success' ?>-subtle text-<?= $d['delivery_type'] === 'partial' ? 'warning' : 'success' ?>"><?= $d['delivery_type'] === 'partial' ? 'Giao 1 phần' : 'Giao đủ' ?></span></td>
                    <td><?= $d['cod_amount'] > 0 ? format_money($d['cod_amount']) : '-' ?></td>
                    <td class="<?= $d['cod_collected'] > 0 ? 'text-success fw-medium' : '' ?>"><?= $d['cod_collected'] > 0 ? format_money($d['cod_collected']) : '-' ?></td>
                    <td><?php $pmL = ['cash'=>'Tiền mặt','transfer'=>'CK','balance'=>'Số dư']; echo $d['cod_method'] ? ($pmL[$d['cod_method']] ?? $d['cod_method']) : '-'; ?></td>
                    <td><span class="badge bg-<?= $stColors[$d['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$d['status']] ?? 'secondary' ?>"><?= $stLabels[$d['status']] ?? $d['status'] ?></span></td>
                    <td><?= user_avatar($d['delivered_by_name'] ?? null) ?></td>
                    <td class="text-muted fs-12"><?= $d['delivered_at'] ? date('d/m H:i', strtotime($d['delivered_at'])) : '-' ?></td>
                    <td>
                        <?php if ($d['status'] !== 'delivered'): ?>
                        <button class="btn btn-soft-success" onclick="markDelivered(<?= $d['id'] ?>, <?= $d['cod_amount'] ?>, <?= $d['total_packages'] ?>)"><i class="ri-check-line me-1"></i> Giao</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($deliveries)): ?><tr><td colspan="11" class="text-center text-muted py-4">Chưa có phiếu giao</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Mark Delivered Modal -->
<div class="modal fade" id="deliverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success-subtle"><h5 class="modal-title"><i class="ri-checkbox-circle-line me-2"></i>Xác nhận giao hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="delId">
                <div class="mb-3"><label class="form-label">Số kiện giao</label><input type="number" class="form-control" id="delPkgCount" min="1"></div>
                <div class="mb-3"><label class="form-label">Số tiền COD thu</label><input type="number" class="form-control" id="delCodCollected" step="1000"></div>
                <div class="mb-3"><label class="form-label">Hình thức thu</label>
                    <select class="form-select" id="delCodMethod"><option value="">Không thu COD</option><option value="cash">Tiền mặt</option><option value="transfer">Chuyển khoản</option></select>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="button" class="btn btn-success" id="confirmDeliver"><i class="ri-check-double-line me-1"></i> Xác nhận giao</button></div>
        </div>
    </div>
</div>

<!-- Create Delivery Modal -->
<div class="modal fade" id="addDelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('logistics/deliveries/create') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Tạo phiếu giao hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Tên KH</label><input type="text" class="form-control" name="customer_name"></div>
                        <div class="col-6 mb-3"><label class="form-label">SĐT</label><input type="text" class="form-control" name="customer_phone"></div>
                    </div>
                    <div class="row">
                        <div class="col-4 mb-3"><label class="form-label">Số kiện</label><input type="number" class="form-control" name="total_packages" value="1" min="1"></div>
                        <div class="col-4 mb-3"><label class="form-label">COD</label><input type="number" class="form-control" name="cod_amount" step="1000" min="0" value="0"></div>
                        <div class="col-4 mb-3"><label class="form-label">Loại</label><select class="form-select" name="delivery_type"><option value="full">Giao đủ</option><option value="partial">Giao 1 phần</option></select></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Ghi chú</label><textarea class="form-control" name="note" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Tạo</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function markDelivered(id, codAmount, totalPkgs) {
    document.getElementById('delId').value = id;
    document.getElementById('delPkgCount').value = totalPkgs;
    document.getElementById('delCodCollected').value = codAmount;
    document.getElementById('delCodMethod').value = codAmount > 0 ? 'cash' : '';
    new bootstrap.Modal(document.getElementById('deliverModal')).show();
}
document.getElementById('confirmDeliver')?.addEventListener('click', function() {
    var id = document.getElementById('delId').value;
    fetch('<?= url("logistics/deliveries") ?>/' + id + '/mark', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_token=<?= csrf_token() ?>&delivered_packages=' + document.getElementById('delPkgCount').value
            + '&cod_collected=' + document.getElementById('delCodCollected').value
            + '&cod_method=' + document.getElementById('delCodMethod').value
    }).then(r => r.json()).then(function(d) {
        if (d.success) location.reload();
    });
});
</script>
