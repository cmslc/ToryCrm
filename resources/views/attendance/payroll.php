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
        <a href="<?= url("attendance/payroll/export?month=$month&year=$year") ?>" class="btn btn-soft-success"><i class="ri-file-excel-line me-1"></i> Xuất Excel</a>
        <?php endif; ?>
        <a href="<?= url('attendance/advances') ?>" class="btn btn-soft-warning"><i class="ri-hand-coin-line me-1"></i> Tạm ứng</a>
        <a href="<?= url('attendance') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Chấm công</a>
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
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 fs-12">
                <thead class="table-light">
                    <tr>
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
                    <td><a href="<?= url('attendance/payroll/' . $p['id']) ?>" class="fw-medium"><?= e($p['user_name']) ?></a></td>
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
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payrolls)): ?>
                <tr><td colspan="14" class="text-center text-muted py-4"><i class="ri-money-dollar-circle-line fs-1 d-block mb-2"></i>Chưa có bảng lương. Bấm "Tạo bảng lương" để tính.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
