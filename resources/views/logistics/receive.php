<?php
$pageTitle = 'Nhập kho - Quét mã';
$statusLabels = ['pending'=>'Chờ','warehouse_cn'=>'Kho TQ','packed'=>'Đóng gói','shipping'=>'Vận chuyển','warehouse_vn'=>'Kho VN','delivering'=>'Đang giao','delivered'=>'Đã giao','returned'=>'Hoàn','damaged'=>'Hư hỏng'];
$statusColors = ['pending'=>'secondary','warehouse_cn'=>'info','packed'=>'primary','shipping'=>'warning','warehouse_vn'=>'success','delivering'=>'info','delivered'=>'success','returned'=>'danger','damaged'=>'danger'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-qr-scan-2-line me-2"></i>Nhập kho - Quét mã</h4>
    <a href="<?= url('logistics') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Dashboard</a>
</div>

<!-- Stats Bar -->
<div class="row mb-3">
    <div class="col-md-3"><div class="card card-animate mb-0"><div class="card-body py-3 text-center"><h4 class="mb-0" id="statTotal"><?= $todayStats['total'] ?? 0 ?></h4><span class="text-muted fs-12">Tổng quét</span></div></div></div>
    <div class="col-md-3"><div class="card card-animate mb-0"><div class="card-body py-3 text-center"><h4 class="mb-0 text-success" id="statSuccess"><?= $todayStats['success'] ?? 0 ?></h4><span class="text-muted fs-12">Thành công</span></div></div></div>
    <div class="col-md-3"><div class="card card-animate mb-0"><div class="card-body py-3 text-center"><h4 class="mb-0 text-danger" id="statError"><?= $todayStats['error'] ?? 0 ?></h4><span class="text-muted fs-12">Lỗi</span></div></div></div>
    <div class="col-md-3"><div class="card card-animate mb-0"><div class="card-body py-3 text-center"><h4 class="mb-0 text-warning" id="statDup"><?= $todayStats['duplicate'] ?? 0 ?></h4><span class="text-muted fs-12">Trùng / Bao</span></div></div></div>
</div>

<!-- Scan Input -->
<div class="card">
    <div class="card-body">
        <form id="scanForm" class="d-flex gap-2">
            <div class="flex-grow-1">
                <input type="text" class="form-control form-control-lg" id="scanInput" name="barcode"
                    placeholder="Quét mã kiện hàng (tracking/mã kiện) hoặc mã bao (BAO-xxx)..." autofocus autocomplete="off">
            </div>
            <button type="submit" class="btn btn-primary btn-lg" id="btnScan"><i class="ri-qr-scan-2-line me-1"></i> Quét</button>
        </form>

        <!-- Result area -->
        <div id="scanResult" class="mt-3" style="display:none">
            <div id="scanAlert" class="alert"></div>
        </div>
    </div>
</div>

<!-- Weight Modal -->
<div class="modal fade" id="weightModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Cân nặng & Kích thước</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="weightPkgId">
                <div class="mb-3"><label class="form-label">Cân nặng (kg) <span class="text-danger">*</span></label><input type="number" class="form-control" id="weightInput" min="0.01" step="0.01" autofocus></div>
                <div class="row">
                    <div class="col-4 mb-3"><label class="form-label">Dài (cm)</label><input type="number" class="form-control" id="lengthInput" min="0" step="0.1"></div>
                    <div class="col-4 mb-3"><label class="form-label">Rộng (cm)</label><input type="number" class="form-control" id="widthInput" min="0" step="0.1"></div>
                    <div class="col-4 mb-3"><label class="form-label">Cao (cm)</label><input type="number" class="form-control" id="heightInput" min="0" step="0.1"></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Bỏ qua</button><button type="button" class="btn btn-primary" id="saveWeight"><i class="ri-save-line me-1"></i> Lưu</button></div>
        </div>
    </div>
</div>

<!-- Scan History -->
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Lịch sử quét hôm nay</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>#</th><th>Mã quét</th><th>Loại</th><th>Sản phẩm</th><th>Khách hàng</th><th>Kết quả</th><th>Chi tiết</th><th>Thời gian</th></tr></thead>
                <tbody id="scanHistoryBody">
                    <?php foreach ($scanHistory as $i => $sl): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td class="fw-medium"><?= e($sl['scan_code']) ?></td>
                        <td><span class="badge bg-<?= $sl['scan_type'] === 'bag' ? 'info' : 'primary' ?>-subtle text-<?= $sl['scan_type'] === 'bag' ? 'info' : 'primary' ?>"><?= $sl['scan_type'] === 'bag' ? 'Bao' : 'Kiện' ?></span></td>
                        <td class="text-muted fs-12"><?= e($sl['product_name'] ?? '-') ?></td>
                        <td class="text-muted fs-12"><?= e($sl['customer_name'] ?? '-') ?></td>
                        <td><span class="badge bg-<?= $sl['result'] === 'success' ? 'success' : ($sl['result'] === 'duplicate' ? 'warning' : 'danger') ?>"><?= $sl['result'] === 'success' ? 'OK' : ($sl['result'] === 'duplicate' ? 'Trùng' : 'Lỗi') ?></span></td>
                        <td class="fs-12"><?= e($sl['message'] ?? '') ?></td>
                        <td class="text-muted fs-12"><?= date('H:i:s', strtotime($sl['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($scanHistory)): ?><tr id="noScanRow"><td colspan="8" class="text-center text-muted py-4">Chưa có lượt quét nào hôm nay</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    var token = '<?= csrf_token() ?>';
    var scanCount = 0;

    document.getElementById('scanForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var input = document.getElementById('scanInput');
        var barcode = input.value.trim();
        if (!barcode) return;

        var btn = document.getElementById('btnScan');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang quét...';

        fetch('<?= url("logistics/scan") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=' + token + '&barcode=' + encodeURIComponent(barcode)
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-qr-scan-2-line me-1"></i> Quét';

            var resultDiv = document.getElementById('scanResult');
            var alertDiv = document.getElementById('scanAlert');
            resultDiv.style.display = '';

            if (d.success) {
                alertDiv.className = 'alert alert-success d-flex align-items-center';
                alertDiv.innerHTML = '<i class="ri-check-circle-line fs-20 me-2"></i><div><strong>' + (d.package?.code || d.package?.package_code || barcode) + '</strong> — ' + d.message + '</div>';
                updateStat('statSuccess');

                if (d.need_weight) {
                    document.getElementById('weightPkgId').value = d.package.id;
                    new bootstrap.Modal(document.getElementById('weightModal')).show();
                }
            } else if (d.type === 'duplicate') {
                alertDiv.className = 'alert alert-warning d-flex align-items-center';
                alertDiv.innerHTML = '<i class="ri-repeat-line fs-20 me-2"></i><div>' + d.message + '</div>';
                updateStat('statDup');
            } else {
                alertDiv.className = 'alert alert-danger d-flex align-items-center';
                alertDiv.innerHTML = '<i class="ri-error-warning-line fs-20 me-2"></i><div>' + (d.message || d.error || 'Lỗi') + '</div>';
                updateStat('statError');
            }
            updateStat('statTotal');

            // Add to history table
            addHistoryRow(barcode, d);

            input.value = '';
            input.focus();
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-qr-scan-2-line me-1"></i> Quét';
            input.focus();
        });
    });

    // Save weight
    document.getElementById('saveWeight')?.addEventListener('click', function() {
        var pkgId = document.getElementById('weightPkgId').value;
        var weight = document.getElementById('weightInput').value;
        if (!weight) return;

        fetch('<?= url("logistics/update-weight") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=' + token + '&package_id=' + pkgId + '&weight=' + weight
                + '&length=' + (document.getElementById('lengthInput').value || 0)
                + '&width=' + (document.getElementById('widthInput').value || 0)
                + '&height=' + (document.getElementById('heightInput').value || 0)
        }).then(function() {
            bootstrap.Modal.getInstance(document.getElementById('weightModal')).hide();
            document.getElementById('weightInput').value = '';
            document.getElementById('lengthInput').value = '';
            document.getElementById('widthInput').value = '';
            document.getElementById('heightInput').value = '';
            document.getElementById('scanInput').focus();
        });
    });

    function updateStat(id) {
        var el = document.getElementById(id);
        el.textContent = parseInt(el.textContent) + 1;
    }

    function addHistoryRow(barcode, d) {
        document.getElementById('noScanRow')?.remove();
        var tbody = document.getElementById('scanHistoryBody');
        scanCount++;
        var resultBadge = d.success ? '<span class="badge bg-success">OK</span>' : (d.type === 'duplicate' ? '<span class="badge bg-warning">Trùng</span>' : '<span class="badge bg-danger">Lỗi</span>');
        var typeBadge = d.type === 'bag' ? '<span class="badge bg-info-subtle text-info">Bao</span>' : '<span class="badge bg-primary-subtle text-primary">Kiện</span>';
        var row = '<tr><td>' + scanCount + '</td><td class="fw-medium">' + barcode + '</td><td>' + typeBadge + '</td><td class="text-muted fs-12">' + (d.package?.product_name || '-') + '</td><td class="text-muted fs-12">' + (d.package?.customer_name || '-') + '</td><td>' + resultBadge + '</td><td class="fs-12">' + (d.message || '') + '</td><td class="text-muted fs-12">' + new Date().toLocaleTimeString('vi-VN') + '</td></tr>';
        tbody.insertAdjacentHTML('afterbegin', row);
    }

    // Auto focus scan input
    document.getElementById('scanInput').focus();
})();
</script>
