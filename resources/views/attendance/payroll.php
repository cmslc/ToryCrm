<?php
$pageTitle = 'Bảng lương';
$stLabels = ['draft'=>'Nháp','confirmed'=>'Xác nhận','paid'=>'Đã trả'];
$stColors = ['draft'=>'secondary','confirmed'=>'primary','paid'=>'success'];
$monthNames = ['','Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
$totalNet = 0; $totalGross = 0;
foreach ($payrolls as $p) { $totalNet += $p['net_salary']; $totalGross += $p['gross_salary']; }
$fmt = function($v) { return $v > 0 ? number_format($v) : '-'; };
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-money-dollar-circle-line me-2"></i> Bảng lương</h4>
    <div class="d-flex gap-2">
        <form method="POST" action="<?= url('attendance/payroll/generate') ?>" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="month" value="<?= $month ?>">
            <input type="hidden" name="year" value="<?= $year ?>">
            <button class="btn btn-primary"><i class="ri-calculator-line me-1"></i> Tạo bảng lương</button>
        </form>
        <?php if (!empty($payrolls)): ?>
        <form method="POST" action="<?= url('attendance/payroll/generate') ?>" class="d-inline" onsubmit="return confirm('Tạo lại sẽ xóa bảng lương nháp và tính lại. Tiếp tục?')">
            <?= csrf_field() ?>
            <input type="hidden" name="month" value="<?= $month ?>">
            <input type="hidden" name="year" value="<?= $year ?>">
            <input type="hidden" name="regenerate" value="1">
            <button class="btn btn-soft-warning"><i class="ri-refresh-line me-1"></i> Tạo lại</button>
        </form>
        <?php endif; ?>
        <?php if (!empty($payrolls)): ?>
        <a href="<?= url("attendance/payroll/export?month=$month&year=$year") ?>" class="btn btn-soft-success"><i class="ri-file-excel-line me-1"></i> Xuất Excel</a>
        <?php endif; ?>
    </div>
</div>

<!-- Month selector + summary -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card mb-0">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('attendance/payroll') ?>" class="d-flex align-items-center gap-2">
                    <select name="month" class="form-select" style="width:auto" onchange="this.form.submit()">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="form-select" style="width:auto" onchange="this.form.submit()">
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                        <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
    <?php if (!empty($payrolls)): ?>
    <div class="col-md-8">
        <div class="row g-2">
            <div class="col-4"><div class="card card-animate mb-0"><div class="card-body py-2 text-center"><h5 class="mb-0"><?= count($payrolls) ?></h5><span class="text-muted fs-12">Nhân viên</span></div></div></div>
            <div class="col-4"><div class="card card-animate mb-0"><div class="card-body py-2 text-center"><h5 class="mb-0 text-info"><?= number_format($totalGross) ?></h5><span class="text-muted fs-12">Tổng Gross</span></div></div></div>
            <div class="col-4"><div class="card card-animate mb-0"><div class="card-body py-2 text-center"><h5 class="mb-0 text-success"><?= number_format($totalNet) ?></h5><span class="text-muted fs-12">Tổng thực nhận</span></div></div></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-12">
                <thead class="table-light">
                    <tr>
                        <th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                        <th>Nhân viên</th>
                        <th class="text-end">Lương CB</th>
                        <th class="text-center">Công</th>
                        <th class="text-end">OT</th>
                        <th class="text-end">Phụ cấp</th>
                        <th class="text-end">Gross</th>
                        <th class="text-end">BH (10.5%)</th>
                        <th class="text-end">Thuế</th>
                        <th class="text-end">Tạm ứng</th>
                        <th class="text-end">Thưởng</th>
                        <th class="text-end">Khấu trừ</th>
                        <th class="text-end fw-medium">Thực nhận</th>
                        <th>TT</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($payrolls as $p): ?>
                <tr>
                    <td><input type="checkbox" class="form-check-input row-check" value="<?= $p['id'] ?>"></td>
                    <td><a href="<?= url('attendance/payroll/' . $p['id']) ?>" class="fw-medium"><?= e($p['user_name']) ?></a><br><a href="<?= url('attendance/payroll/history/' . $p['user_id']) ?>" class="text-muted fs-11">Lịch sử</a></td>
                    <td class="text-end"><?= number_format($p['base_salary']) ?></td>
                    <td class="text-center"><?= rtrim(rtrim(number_format($p['work_days'], 1), '0'), '.') ?></td>
                    <td class="text-end"><?= $fmt($p['overtime_pay']) ?></td>
                    <td class="text-end"><?= $fmt($p['total_allowance']) ?></td>
                    <td class="text-end"><?= number_format($p['gross_salary']) ?></td>
                    <td class="text-end text-muted"><?= $fmt($p['insurance']) ?></td>
                    <td class="text-end text-muted"><?= $fmt($p['tax']) ?></td>
                    <td class="text-end text-warning"><?= $fmt($p['advance']) ?></td>
                    <td class="text-end text-success"><?= $fmt($p['bonus']) ?></td>
                    <td class="text-end text-danger"><?= $fmt($p['deductions']) ?></td>
                    <td class="text-end fw-medium text-success"><?= number_format($p['net_salary']) ?></td>
                    <td><span class="badge bg-<?= $stColors[$p['status']] ?>-subtle text-<?= $stColors[$p['status']] ?>"><?= $stLabels[$p['status']] ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= url('attendance/payroll/' . $p['id']) ?>" class="btn btn-soft-info btn-icon" title="Chi tiết"><i class="ri-eye-line"></i></a>
                            <?php if ($p['status'] === 'draft'): ?>
                            <form method="POST" action="<?= url('attendance/payroll/' . $p['id'] . '/confirm') ?>"><?= csrf_field() ?><button class="btn btn-soft-primary btn-icon" title="Xác nhận"><i class="ri-check-line"></i></button></form>
                            <?php elseif ($p['status'] === 'confirmed'): ?>
                            <form method="POST" action="<?= url('attendance/payroll/' . $p['id'] . '/paid') ?>"><?= csrf_field() ?><button class="btn btn-soft-success btn-icon" title="Đã trả"><i class="ri-money-dollar-circle-line"></i></button></form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payrolls)): ?>
                <tr><td colspan="15" class="text-center text-muted py-4"><i class="ri-money-dollar-circle-line fs-1 d-block mb-2"></i>Chưa có bảng lương. Bấm "Tạo bảng lương" để tính.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bulk bar -->
<div class="card mb-2 d-none" id="bulkBar" style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);z-index:100;min-width:400px">
    <div class="card-body py-2">
        <form method="POST" action="<?= url('attendance/payroll/bulk') ?>" class="d-flex align-items-center gap-3" id="bulkForm">
            <?= csrf_field() ?>
            <div id="bulkIds"></div>
            <span class="fw-medium"><span id="bulkCount">0</span> đã chọn</span>
            <button type="submit" name="action" value="confirm" class="btn btn-primary"><i class="ri-check-line me-1"></i> Xác nhận</button>
            <button type="submit" name="action" value="paid" class="btn btn-success"><i class="ri-money-dollar-circle-line me-1"></i> Đã trả</button>
        </form>
    </div>
</div>

<script>
(function(){
    var checkAll = document.getElementById('checkAll');
    var bulkBar = document.getElementById('bulkBar');
    function updateBulk() {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length > 0) {
            bulkBar.classList.remove('d-none');
            document.getElementById('bulkCount').textContent = checked.length;
            var div = document.getElementById('bulkIds'); div.innerHTML = '';
            checked.forEach(function(cb) { var inp = document.createElement('input'); inp.type='hidden'; inp.name='payroll_ids[]'; inp.value=cb.value; div.appendChild(inp); });
        } else { bulkBar.classList.add('d-none'); }
    }
    if (checkAll) { checkAll.addEventListener('change', function() { document.querySelectorAll('.row-check').forEach(function(cb){cb.checked=checkAll.checked}); updateBulk(); }); }
    document.querySelectorAll('.row-check').forEach(function(cb){cb.addEventListener('change', updateBulk);});
})();
</script>
