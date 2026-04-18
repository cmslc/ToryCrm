<?php $pageTitle = 'Tính phí vận chuyển'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Tính phí vận chuyển</h4>
</div>

<div class="row">
    <div class="col-xl-5">
        <!-- Calculator -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-calculator-line me-1"></i> Tính phí</h5></div>
            <div class="card-body">
                <div class="mb-3"><label class="form-label">Loại hàng</label>
                    <select class="form-select" id="calcType"><option value="easy">Hàng thường</option><option value="difficult">Hàng khó</option></select>
                </div>
                <div class="row">
                    <div class="col-6 mb-3"><label class="form-label">Cân nặng (kg)</label><input type="number" class="form-control" id="calcWeight" step="0.01" min="0"></div>
                    <div class="col-6 mb-3"><label class="form-label">Số khối (m³)</label><input type="number" class="form-control" id="calcCbm" step="0.0001" min="0"></div>
                </div>
                <div class="row">
                    <div class="col-4 mb-3"><label class="form-label">Dài (cm)</label><input type="number" class="form-control" id="calcL" min="0" oninput="autoCalcCbm()"></div>
                    <div class="col-4 mb-3"><label class="form-label">Rộng (cm)</label><input type="number" class="form-control" id="calcW" min="0" oninput="autoCalcCbm()"></div>
                    <div class="col-4 mb-3"><label class="form-label">Cao (cm)</label><input type="number" class="form-control" id="calcH" min="0" oninput="autoCalcCbm()"></div>
                </div>
                <button class="btn btn-primary w-100" onclick="calculate()"><i class="ri-calculator-line me-1"></i> Tính phí</button>
                <div id="calcResult" class="mt-3" style="display:none">
                    <div class="alert alert-success">
                        <h5 class="mb-2">Kết quả:</h5>
                        <div id="calcOutput"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-7">
        <!-- Rate Table -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Bảng giá</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRateModal"><i class="ri-add-line me-1"></i> Thêm</button>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Tên</th><th>Loại</th><th>Giá/kg</th><th>Giá/m³</th><th>Tuyến</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($rates as $r): ?>
                        <tr>
                            <td class="fw-medium"><?= e($r['name']) ?></td>
                            <td><span class="badge bg-<?= $r['cargo_type'] === 'difficult' ? 'danger' : 'success' ?>-subtle text-<?= $r['cargo_type'] === 'difficult' ? 'danger' : 'success' ?>"><?= $r['cargo_type'] === 'difficult' ? 'Khó' : 'Thường' ?></span></td>
                            <td><?= $r['rate_per_kg'] > 0 ? format_money($r['rate_per_kg']) : '-' ?></td>
                            <td><?= $r['rate_per_cbm'] > 0 ? format_money($r['rate_per_cbm']) : '-' ?></td>
                            <td class="text-muted"><?= e($r['origin']) ?> → <?= e($r['destination']) ?></td>
                            <td><form method="POST" action="<?= url('logistics/calculator/' . $r['id'] . '/delete') ?>" data-confirm="Xóa?" class="d-inline"><?= csrf_field() ?><button class="btn btn-soft-danger btn-icon"><i class="ri-delete-bin-line"></i></button></form></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($rates)): ?><tr><td colspan="6" class="text-center text-muted py-3">Chưa có bảng giá</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addRateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('logistics/calculator/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Thêm bảng giá</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Tên</label><input type="text" class="form-control" name="name" required></div>
                    <div class="row">
                        <div class="col-4 mb-3"><label class="form-label">Loại</label><select class="form-select" name="cargo_type"><option value="easy">Thường</option><option value="difficult">Khó</option></select></div>
                        <div class="col-4 mb-3"><label class="form-label">Giá/kg</label><input type="number" class="form-control" name="rate_per_kg" step="1000" min="0" value="0"></div>
                        <div class="col-4 mb-3"><label class="form-label">Giá/m³</label><input type="number" class="form-control" name="rate_per_cbm" step="1000" min="0" value="0"></div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label">Xuất</label><input type="text" class="form-control" name="origin" value="CN"></div>
                        <div class="col-6 mb-3"><label class="form-label">Đến</label><input type="text" class="form-control" name="destination" value="VN"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Lưu</button></div>
            </form>
        </div>
    </div>
</div>

<script>
var ratesData = <?= json_encode($rates) ?>;
function autoCalcCbm() {
    var l = parseFloat(document.getElementById('calcL').value) || 0;
    var w = parseFloat(document.getElementById('calcW').value) || 0;
    var h = parseFloat(document.getElementById('calcH').value) || 0;
    if (l > 0 && w > 0 && h > 0) document.getElementById('calcCbm').value = parseFloat((l * w * h / 1000000).toFixed(4));
}
function calculate() {
    var type = document.getElementById('calcType').value;
    var weight = parseFloat(document.getElementById('calcWeight').value) || 0;
    var cbm = parseFloat(document.getElementById('calcCbm').value) || 0;
    if (!weight && !cbm) { alert('Nhập cân nặng hoặc số khối'); return; }

    var matching = ratesData.filter(function(r) { return r.cargo_type === type && r.is_active; });
    var html = '';
    matching.forEach(function(r) {
        var cost = 0;
        if (r.rate_per_kg > 0 && weight > 0) cost = weight * r.rate_per_kg;
        if (r.rate_per_cbm > 0 && cbm > 0) { var cbmCost = cbm * r.rate_per_cbm; if (cbmCost > cost) cost = cbmCost; }
        if (cost > 0) html += '<div class="d-flex justify-content-between mb-1"><span>' + r.name + '</span><strong>' + new Intl.NumberFormat('vi-VN').format(Math.round(cost)) + ' đ</strong></div>';
    });
    if (!html) html = '<p class="text-muted mb-0">Không có bảng giá phù hợp</p>';
    document.getElementById('calcOutput').innerHTML = html;
    document.getElementById('calcResult').style.display = '';
}
</script>
