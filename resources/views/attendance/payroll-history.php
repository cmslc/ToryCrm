<?php
$pageTitle = 'Lịch sử lương - ' . e($user['name']);
$stLabels = ['draft'=>'Nháp','confirmed'=>'Xác nhận','paid'=>'Đã trả'];
$stColors = ['draft'=>'secondary','confirmed'=>'primary','paid'=>'success'];
$monthNames = ['','T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0">Lịch sử lương</h4>
        <p class="text-muted mb-0"><?= e($user['name']) ?> — <?= e($user['email']) ?></p>
    </div>
    <a href="<?= url('attendance/payroll') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Tháng</th><th class="text-end">Lương CB</th><th class="text-center">Công</th><th class="text-end">Gross</th><th class="text-end">BH</th><th class="text-end">Thuế</th><th class="text-end">Tạm ứng</th><th class="text-end fw-medium">Thực nhận</th><th>TT</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($history as $p): ?>
                <tr>
                    <td class="fw-medium"><?= $monthNames[$p['month']] ?>/<?= $p['year'] ?></td>
                    <td class="text-end"><?= number_format($p['base_salary']) ?></td>
                    <td class="text-center"><?= rtrim(rtrim(number_format($p['work_days'], 1), '0'), '.') ?></td>
                    <td class="text-end"><?= number_format($p['gross_salary']) ?></td>
                    <td class="text-end text-muted"><?= number_format($p['insurance']) ?></td>
                    <td class="text-end text-muted"><?= number_format($p['tax']) ?></td>
                    <td class="text-end text-warning"><?= $p['advance'] > 0 ? number_format($p['advance']) : '-' ?></td>
                    <td class="text-end fw-medium text-success"><?= number_format($p['net_salary']) ?></td>
                    <td><span class="badge bg-<?= $stColors[$p['status']] ?>-subtle text-<?= $stColors[$p['status']] ?>"><?= $stLabels[$p['status']] ?></span></td>
                    <td><a href="<?= url('attendance/payroll/' . $p['id']) ?>" class="btn btn-soft-info btn-icon" title="Chi tiết"><i class="ri-eye-line"></i></a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($history)): ?><tr><td colspan="10" class="text-center text-muted py-4">Chưa có lịch sử lương</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
