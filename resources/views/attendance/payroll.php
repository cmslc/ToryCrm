<?php
$pageTitle = 'Bảng lương';
$stLabels = ['draft'=>'Nháp','confirmed'=>'Xác nhận','paid'=>'Đã trả'];
$stColors = ['draft'=>'secondary','confirmed'=>'primary','paid'=>'success'];
$monthNames = ['','Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
$totalNet = 0; foreach ($payrolls as $p) $totalNet += $p['net_salary'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-money-dollar-circle-line me-2"></i> Bảng lương</h4>
    <div class="d-flex gap-2">
        <form method="POST" action="<?= url('attendance/payroll/generate') ?>" class="d-flex gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="month" value="<?= $month ?>">
            <input type="hidden" name="year" value="<?= $year ?>">
            <button class="btn btn-primary"><i class="ri-calculator-line me-1"></i> Tạo bảng lương</button>
        </form>
        <a href="<?= url('attendance') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Chấm công</a>
    </div>
</div>

<!-- Month selector -->
<div class="card mb-3">
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
            <?php if ($totalNet > 0): ?>
            <span class="ms-3 fw-medium">Tổng lương: <span class="text-success"><?= number_format($totalNet) ?>đ</span></span>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nhân viên</th>
                        <th class="text-end">Lương CB</th>
                        <th class="text-center">Ngày công</th>
                        <th class="text-center">Nghỉ</th>
                        <th class="text-center">OT (h)</th>
                        <th class="text-end">OT Pay</th>
                        <th class="text-end">Thưởng</th>
                        <th class="text-end">Khấu trừ</th>
                        <th class="text-end">Bảo hiểm</th>
                        <th class="text-end">Thực nhận</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($payrolls as $p): ?>
                <tr>
                    <td class="fw-medium"><?= e($p['user_name']) ?></td>
                    <td class="text-end"><?= number_format($p['base_salary']) ?></td>
                    <td class="text-center"><?= rtrim(rtrim(number_format($p['work_days'], 1), '0'), '.') ?></td>
                    <td class="text-center"><?= $p['leave_days'] > 0 ? rtrim(rtrim(number_format($p['leave_days'], 1), '0'), '.') : '-' ?></td>
                    <td class="text-center"><?= $p['overtime_hours'] > 0 ? rtrim(rtrim(number_format($p['overtime_hours'], 1), '0'), '.') : '-' ?></td>
                    <td class="text-end"><?= $p['overtime_pay'] > 0 ? number_format($p['overtime_pay']) : '-' ?></td>
                    <td class="text-end text-success"><?= $p['bonus'] > 0 ? '+' . number_format($p['bonus']) : '-' ?></td>
                    <td class="text-end text-danger"><?= $p['deductions'] > 0 ? '-' . number_format($p['deductions']) : '-' ?></td>
                    <td class="text-end text-muted"><?= $p['insurance'] > 0 ? '-' . number_format($p['insurance']) : '-' ?></td>
                    <td class="text-end fw-medium text-success"><?= number_format($p['net_salary']) ?></td>
                    <td><span class="badge bg-<?= $stColors[$p['status']] ?>-subtle text-<?= $stColors[$p['status']] ?>"><?= $stLabels[$p['status']] ?></span></td>
                    <td>
                        <?php if ($p['status'] === 'draft'): ?>
                        <div class="d-flex gap-1">
                            <form method="POST" action="<?= url('attendance/payroll/' . $p['id'] . '/confirm') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-soft-primary btn-icon" title="Xác nhận"><i class="ri-check-line"></i></button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($payrolls)): ?>
                <tr><td colspan="12" class="text-center text-muted py-4"><i class="ri-money-dollar-circle-line fs-1 d-block mb-2"></i>Chưa có bảng lương tháng này. Bấm "Tạo bảng lương" để tạo.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
