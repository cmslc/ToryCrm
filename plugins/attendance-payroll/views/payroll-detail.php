<?php
$pageTitle = 'Phiếu lương - ' . e($payroll['user_name']);
$stLabels = ['draft'=>'Nháp','confirmed'=>'Xác nhận','paid'=>'Đã trả'];
$stColors = ['draft'=>'secondary','confirmed'=>'primary','paid'=>'success'];
$monthNames = ['','Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
$p = $payroll;
$fmt = function($v) { return number_format((float)$v); };
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Phiếu lương <?= $monthNames[$p['month']] ?>/<?= $p['year'] ?></h4>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-soft-info"><i class="ri-printer-line me-1"></i> In</button>
        <a href="<?= url("attendance/payroll?month={$p['month']}&year={$p['year']}") ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card" id="payslip">
            <div class="card-header bg-primary-subtle text-center">
                <h5 class="mb-1">PHIẾU LƯƠNG</h5>
                <p class="mb-0 text-muted"><?= $monthNames[$p['month']] ?> / <?= $p['year'] ?></p>
            </div>
            <div class="card-body">
                <!-- Info -->
                <div class="row mb-4">
                    <div class="col-6">
                        <p class="mb-1"><strong>Nhân viên:</strong> <?= e($p['user_name']) ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?= e($p['user_email'] ?? '') ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1"><strong>Trạng thái:</strong> <span class="badge bg-<?= $stColors[$p['status']] ?>-subtle text-<?= $stColors[$p['status']] ?>"><?= $stLabels[$p['status']] ?></span></p>
                        <p class="mb-1"><strong>Người phụ thuộc:</strong> <?= $p['dependents'] ?></p>
                    </div>
                </div>

                <!-- Thu nhập -->
                <h6 class="text-primary border-bottom pb-2 mb-3"><i class="ri-add-circle-line me-1"></i> Thu nhập</h6>
                <table class="table table-borderless mb-4">
                    <tr><td>Lương cơ bản</td><td class="text-end"><?= $fmt($p['base_salary']) ?></td></tr>
                    <tr><td>Ngày công: <?= rtrim(rtrim(number_format($p['work_days'], 1), '0'), '.') ?>/22 ngày</td><td class="text-end"><?= $fmt(round($p['base_salary'] / 22 * $p['work_days'])) ?></td></tr>
                    <?php if ($p['overtime_pay'] > 0): ?><tr><td>Tăng ca (<?= rtrim(rtrim(number_format($p['overtime_hours'], 1), '0'), '.') ?>h x 1.5)</td><td class="text-end"><?= $fmt($p['overtime_pay']) ?></td></tr><?php endif; ?>
                    <?php if ($p['allowance_lunch'] > 0): ?><tr><td>Phụ cấp ăn trưa</td><td class="text-end"><?= $fmt($p['allowance_lunch']) ?></td></tr><?php endif; ?>
                    <?php if ($p['allowance_transport'] > 0): ?><tr><td>Phụ cấp xăng xe</td><td class="text-end"><?= $fmt($p['allowance_transport']) ?></td></tr><?php endif; ?>
                    <?php if ($p['allowance_phone'] > 0): ?><tr><td>Phụ cấp điện thoại</td><td class="text-end"><?= $fmt($p['allowance_phone']) ?></td></tr><?php endif; ?>
                    <?php if ($p['allowance_other'] > 0): ?><tr><td>Phụ cấp khác</td><td class="text-end"><?= $fmt($p['allowance_other']) ?></td></tr><?php endif; ?>
                    <?php if ($p['bonus'] > 0): ?><tr><td class="text-success">Thưởng</td><td class="text-end text-success">+<?= $fmt($p['bonus']) ?></td></tr><?php endif; ?>
                    <tr class="border-top fw-medium"><td>Tổng thu nhập (Gross)</td><td class="text-end"><?= $fmt($p['gross_salary'] + $p['bonus']) ?></td></tr>
                </table>

                <!-- Khấu trừ -->
                <h6 class="text-danger border-bottom pb-2 mb-3"><i class="ri-subtract-line me-1"></i> Khấu trừ</h6>
                <table class="table table-borderless mb-4">
                    <tr><td>BHXH (8%)</td><td class="text-end">-<?= $fmt($p['bhxh']) ?></td></tr>
                    <tr><td>BHYT (1.5%)</td><td class="text-end">-<?= $fmt($p['bhyt']) ?></td></tr>
                    <tr><td>BHTN (1%)</td><td class="text-end">-<?= $fmt($p['bhtn']) ?></td></tr>
                    <tr><td>Thuế TNCN</td><td class="text-end">-<?= $fmt($p['tax']) ?></td></tr>
                    <tr><td class="text-muted fs-12" colspan="2">Giảm trừ bản thân: 11,000,000 | Người phụ thuộc: <?= $p['dependents'] ?> x 4,400,000 = <?= $fmt($p['dependents'] * 4400000) ?> | Thu nhập chịu thuế: <?= $fmt($p['tax_income']) ?></td></tr>
                    <?php if ($p['advance'] > 0): ?><tr><td>Tạm ứng</td><td class="text-end text-warning">-<?= $fmt($p['advance']) ?></td></tr><?php endif; ?>
                    <?php if ($p['deductions'] > 0): ?><tr><td>Khấu trừ khác</td><td class="text-end">-<?= $fmt($p['deductions']) ?></td></tr><?php endif; ?>
                    <tr class="border-top fw-medium"><td>Tổng khấu trừ</td><td class="text-end text-danger">-<?= $fmt($p['insurance'] + $p['tax'] + $p['advance'] + $p['deductions']) ?></td></tr>
                </table>

                <!-- Thực nhận -->
                <div class="bg-success-subtle rounded p-3 text-center">
                    <span class="text-muted">THỰC NHẬN</span>
                    <h3 class="text-success mb-0"><?= $fmt($p['net_salary']) ?> VNĐ</h3>
                </div>

                <?php if ($p['note']): ?>
                <div class="mt-3 text-muted"><i class="ri-chat-3-line me-1"></i> <?= e($p['note']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($p['status'] === 'draft'): ?>
        <!-- Edit form -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Điều chỉnh</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('attendance/payroll/' . $p['id'] . '/update') ?>">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Thưởng</label><input type="number" class="form-control" name="bonus" value="<?= $p['bonus'] ?>" step="1000" min="0"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Khấu trừ</label><input type="number" class="form-control" name="deductions" value="<?= $p['deductions'] ?>" step="1000" min="0"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Ghi chú</label><input type="text" class="form-control" name="note" value="<?= e($p['note'] ?? '') ?>"></div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Cập nhật</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .page-title-box, .card:not(#payslip), nav, .sidebar, header, footer, .btn { display: none !important; }
    #payslip { border: none !important; box-shadow: none !important; }
}
</style>
