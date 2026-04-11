<?php
$pageTitle = 'Bao ' . e($bag['bag_code']);
$stLabels = ['open'=>'Mở','sealed'=>'Đã niêm','shipping'=>'Vận chuyển','arrived'=>'Đã đến','completed'=>'Hoàn thành'];
$stColors = ['open'=>'warning','sealed'=>'primary','shipping'=>'info','arrived'=>'success','completed'=>'success'];
$pkgStLabels = ['pending'=>'Chờ','warehouse_cn'=>'Kho TQ','packed'=>'Đóng gói','shipping'=>'Vận chuyển','warehouse_vn'=>'Kho VN','delivering'=>'Đang giao','delivered'=>'Đã giao'];
$pkgStColors = ['pending'=>'secondary','warehouse_cn'=>'info','packed'=>'primary','shipping'=>'warning','warehouse_vn'=>'success','delivering'=>'info','delivered'=>'success'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= e($bag['bag_code']) ?></h4>
    <div class="d-flex gap-2">
        <?php if ($bag['status'] === 'open'): ?>
        <form method="POST" action="<?= url('logistics/bags/' . $bag['id'] . '/seal') ?>" onsubmit="return confirm('Đóng bao? Sẽ không thể thêm kiện nữa.')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary"><i class="ri-lock-line me-1"></i> Đóng bao</button>
        </form>
        <?php endif; ?>
        <a href="<?= url('logistics/bags') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> DS Bao</a>
    </div>
</div>

<!-- Info Cards -->
<div class="row mb-3">
    <div class="col-6 col-md-3">
        <div class="card card-animate mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-<?= $stColors[$bag['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$bag['status']] ?? 'secondary' ?> rounded-2 fs-20"><i class="ri-inbox-line"></i></span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <span class="badge bg-<?= $stColors[$bag['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$bag['status']] ?? 'secondary' ?>"><?= $stLabels[$bag['status']] ?? $bag['status'] ?></span>
                        <p class="text-muted mb-0 fs-12 mt-1">Trạng thái</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-20"><i class="ri-box-3-line"></i></span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-0"><?= count($packages) ?></h4>
                        <p class="text-muted mb-0 fs-12">Kiện hàng</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle text-info rounded-2 fs-20"><i class="ri-scales-line"></i></span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h4 class="mb-0"><?= $bag['total_weight'] ? rtrim(rtrim(number_format($bag['total_weight'], 2), '0'), '.') : '0' ?> <small class="fs-12 fw-normal text-muted">kg</small></h4>
                        <p class="text-muted mb-0 fs-12">Tổng cân</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-animate mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0">
                        <?= user_avatar($bag['created_by_name'] ?? null) ?>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <span class="fw-medium"><?= e($bag['created_by_name'] ?? 'N/A') ?></span>
                        <p class="text-muted mb-0 fs-12"><?= created_ago($bag['created_at']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($bag['note'] || $bag['sealed_at']): ?>
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-3 flex-wrap text-muted fs-13">
            <?php if ($bag['note']): ?><span><i class="ri-chat-3-line me-1"></i> <?= e($bag['note']) ?></span><?php endif; ?>
            <?php if ($bag['sealed_at']): ?><span><i class="ri-lock-line me-1"></i> Niêm phong: <?= user_avatar($bag['sealed_by_name'] ?? null) ?> <?= created_ago($bag['sealed_at']) ?></span><?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($bag['status'] === 'open'): ?>
<!-- Scan Input -->
<div class="card">
    <div class="card-header bg-success-subtle">
        <h5 class="card-title mb-0"><i class="ri-qr-scan-2-line me-2"></i> Quét kiện vào bao</h5>
    </div>
    <div class="card-body">
        <form id="scanForm" class="d-flex gap-2">
            <div class="flex-grow-1">
                <input type="text" class="form-control form-control-lg" id="scanInput" name="barcode"
                    placeholder="Quét mã kiện (tracking / mã kiện)..." autofocus autocomplete="off">
            </div>
            <button type="submit" class="btn btn-success btn-lg"><i class="ri-qr-scan-2-line me-1"></i> Quét</button>
        </form>
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
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Bỏ qua</button><button type="button" class="btn btn-primary" id="btnSaveWeight"><i class="ri-save-line me-1"></i> Lưu</button></div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabPkgs"><i class="ri-box-3-line me-1"></i> Kiện hàng (<?= count($packages) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabLogs"><i class="ri-history-line me-1"></i> Lịch sử quét</a></li>
</ul>

<div class="tab-content">
    <!-- Packages Tab -->
    <div class="tab-pane active" id="tabPkgs">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="pkgTable">
                        <thead class="table-light"><tr><th>Mã kiện</th><th>Tracking</th><th>Khách hàng</th><th>Cân nặng</th><th>Trạng thái</th><th>Ngày nhập</th><?php if ($bag['status'] === 'open'): ?><th style="width:50px"></th><?php endif; ?></tr></thead>
                        <tbody>
                        <?php foreach ($packages as $p): ?>
                        <tr id="pkg-row-<?= $p['id'] ?>">
                            <td class="fw-medium"><?= e($p['package_code']) ?></td>
                            <td><?= e($p['tracking_code'] ?? '-') ?></td>
                            <td><?= e($p['customer_name'] ?? '-') ?></td>
                            <td><?= $p['weight_actual'] ? rtrim(rtrim(number_format($p['weight_actual'], 2), '0'), '.') . ' kg' : '-' ?></td>
                            <td><span class="badge bg-<?= $pkgStColors[$p['status']] ?? 'secondary' ?>-subtle text-<?= $pkgStColors[$p['status']] ?? 'secondary' ?>"><?= $pkgStLabels[$p['status']] ?? $p['status'] ?></span></td>
                            <td class="text-muted fs-12"><?= $p['received_at'] ? created_ago($p['received_at']) : created_ago($p['created_at']) ?></td>
                            <?php if ($bag['status'] === 'open'): ?>
                            <td><button class="btn btn-soft-danger btn-icon" onclick="removePkg(<?= $p['id'] ?>, '<?= e($p['package_code']) ?>')" title="Gỡ khỏi bao"><i class="ri-close-line"></i></button></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($packages)): ?><tr id="emptyRow"><td colspan="<?= $bag['status'] === 'open' ? 7 : 6 ?>" class="text-center text-muted py-4">Chưa có kiện - quét mã để thêm</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scan Logs Tab -->
    <div class="tab-pane" id="tabLogs">
        <div class="card border-top-0 rounded-top-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Mã quét</th><th>Kết quả</th><th>Ghi chú</th><th>Người quét</th><th>Thời gian</th></tr></thead>
                        <tbody>
                        <?php foreach ($scanLogs as $sl): ?>
                        <tr>
                            <td class="fw-medium"><?= e($sl['scan_code']) ?></td>
                            <td>
                                <?php if ($sl['result'] === 'success'): ?><span class="badge bg-success-subtle text-success">OK</span>
                                <?php elseif ($sl['result'] === 'duplicate'): ?><span class="badge bg-warning-subtle text-warning">Trùng</span>
                                <?php else: ?><span class="badge bg-danger-subtle text-danger">Lỗi</span><?php endif; ?>
                            </td>
                            <td class="text-muted"><?= e($sl['message'] ?? '') ?></td>
                            <td><?= user_avatar($sl['scanned_by_name'] ?? null) ?></td>
                            <td class="text-muted fs-12"><?= created_ago($sl['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($scanLogs)): ?><tr><td colspan="5" class="text-center text-muted py-4">Chưa có lịch sử quét</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sounds -->
<audio id="soundSuccess" preload="auto"><source src="<?= asset('sounds/scan-success.mp3') ?>" type="audio/mpeg"></audio>
<audio id="soundError" preload="auto"><source src="<?= asset('sounds/scan-error.mp3') ?>" type="audio/mpeg"></audio>
<audio id="soundDup" preload="auto"><source src="<?= asset('sounds/scan-dup.mp3') ?>" type="audio/mpeg"></audio>

<?php if ($bag['status'] === 'open'): ?>
<script>
(function() {
    var scanForm = document.getElementById('scanForm');
    var scanInput = document.getElementById('scanInput');
    var scanResult = document.getElementById('scanResult');
    var scanAlert = document.getElementById('scanAlert');
    var pkgTable = document.querySelector('#pkgTable tbody');
    var scanning = false;

    function playSound(type) {
        try {
            var el = document.getElementById('sound' + type.charAt(0).toUpperCase() + type.slice(1));
            if (el) { el.currentTime = 0; el.play(); }
        } catch(e) {}
    }

    scanForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (scanning) return;
        var barcode = scanInput.value.trim();
        if (!barcode) return;
        scanning = true;

        fetch('<?= url("logistics/bags/" . $bag['id'] . "/scan") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: '_token=<?= csrf_token() ?>&barcode=' + encodeURIComponent(barcode)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            scanResult.style.display = 'block';
            if (data.success) {
                playSound('success');
                scanAlert.className = 'alert alert-success';
                scanAlert.innerHTML = '<i class="ri-checkbox-circle-line me-2"></i>' + data.message
                    + (data.package ? ' — <strong>' + (data.package.code || data.package.package_code) + '</strong>' : '');

                // Add row to table
                var emptyRow = document.getElementById('emptyRow');
                if (emptyRow) emptyRow.remove();

                var tr = document.createElement('tr');
                tr.id = 'pkg-row-' + data.package.id;
                tr.innerHTML = '<td class="fw-medium">' + (data.package.code || data.package.package_code || '') + '</td>'
                    + '<td>' + (data.package.tracking || data.package.tracking_code || '-') + '</td>'
                    + '<td>' + (data.package.customer_name || '-') + '</td>'
                    + '<td>' + (data.package.weight_actual ? parseFloat(Number(data.package.weight_actual).toFixed(2)) + ' kg' : '-') + '</td>'
                    + '<td><span class="badge bg-info-subtle text-info">Kho TQ</span></td>'
                    + '<td class="text-muted fs-12">Vừa xong</td>'
                    + '<td><button class="btn btn-soft-danger btn-icon" onclick="removePkg(' + data.package.id + ', \'' + (data.package.code || data.package.package_code || '') + '\')" title="Gỡ khỏi bao"><i class="ri-close-line"></i></button></td>';
                pkgTable.insertBefore(tr, pkgTable.firstChild);

                // Weight modal
                if (data.need_weight) {
                    document.getElementById('weightPkgId').value = data.package.id;
                    document.getElementById('weightInput').value = '';
                    document.getElementById('lengthInput').value = '';
                    document.getElementById('widthInput').value = '';
                    document.getElementById('heightInput').value = '';
                    new bootstrap.Modal(document.getElementById('weightModal')).show();
                }
            } else {
                if (data.type === 'duplicate') {
                    playSound('dup');
                    scanAlert.className = 'alert alert-warning';
                } else {
                    playSound('error');
                    scanAlert.className = 'alert alert-danger';
                }
                scanAlert.innerHTML = '<i class="ri-error-warning-line me-2"></i>' + (data.error || data.message || 'Lỗi');
            }

            scanInput.value = '';
            scanInput.focus();
            scanning = false;
        })
        .catch(function(err) {
            playSound('error');
            scanResult.style.display = 'block';
            scanAlert.className = 'alert alert-danger';
            scanAlert.innerHTML = '<i class="ri-error-warning-line me-2"></i> Lỗi kết nối';
            scanning = false;
            scanInput.focus();
        });
    });

    // Save weight
    document.getElementById('btnSaveWeight')?.addEventListener('click', function() {
        var pkgId = document.getElementById('weightPkgId').value;
        var weight = document.getElementById('weightInput').value;
        if (!weight || parseFloat(weight) <= 0) { alert('Nhập cân nặng'); return; }

        fetch('<?= url("logistics/update-weight") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: '_token=<?= csrf_token() ?>&package_id=' + pkgId
                + '&weight=' + weight
                + '&length=' + (document.getElementById('lengthInput').value || 0)
                + '&width=' + (document.getElementById('widthInput').value || 0)
                + '&height=' + (document.getElementById('heightInput').value || 0)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('weightModal')).hide();
                // Update weight cell
                var row = document.getElementById('pkg-row-' + pkgId);
                if (row) {
                    var cells = row.querySelectorAll('td');
                    cells[3].textContent = parseFloat(Number(weight).toFixed(2)) + ' kg';
                }
            }
            scanInput.focus();
        });
    });
})();

function removePkg(pkgId, code) {
    if (!confirm('Gỡ kiện ' + code + ' khỏi bao?')) return;
    fetch('<?= url("logistics/bags/" . $bag['id'] . "/remove-package") ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: '_token=<?= csrf_token() ?>&package_id=' + pkgId
    })
    .then(function(r) {
        if (!r.ok && r.status !== 422) throw new Error('HTTP ' + r.status);
        var ct = r.headers.get('content-type') || '';
        if (ct.indexOf('json') < 0) throw new Error('Không phải JSON');
        return r.json();
    })
    .then(function(data) {
        if (data.success) {
            var row = document.getElementById('pkg-row-' + pkgId);
            if (row) row.remove();
        } else {
            alert(data.error || 'Lỗi');
        }
    })
    .catch(function(err) { alert('Lỗi: ' + err.message); });
}
</script>
<?php endif; ?>
