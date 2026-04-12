<?php
$pageTitle = 'Chấm công';
$stLabels = ['present'=>'Có mặt','absent'=>'Vắng','late'=>'Muộn','half_day'=>'Nửa ngày','leave'=>'Nghỉ phép','holiday'=>'Lễ'];
$stColors = ['present'=>'success','absent'=>'danger','late'=>'warning','half_day'=>'info','leave'=>'primary','holiday'=>'secondary'];
$today = date('Y-m-d');
$myAttendance = $map[$_SESSION['user_id'] ?? 0][$today] ?? null;
$monthNames = ['','Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-calendar-check-line me-2"></i> Chấm công</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('attendance/leaves') ?>" class="btn btn-soft-info"><i class="ri-calendar-event-line me-1"></i> Nghỉ phép</a>
        <a href="<?= url('attendance/payroll') ?>" class="btn btn-soft-success"><i class="ri-money-dollar-circle-line me-1"></i> Bảng lương</a>
    </div>
</div>

<!-- Check-in Card -->
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-1"><?= date('d/m/Y - l') ?></h5>
                <?php if ($myAttendance): ?>
                    <span class="badge bg-<?= $stColors[$myAttendance['status']] ?? 'secondary' ?>-subtle text-<?= $stColors[$myAttendance['status']] ?? 'secondary' ?>"><?= $stLabels[$myAttendance['status']] ?? $myAttendance['status'] ?></span>
                    <span class="text-muted ms-2">In: <?= $myAttendance['check_in'] ? date('H:i', strtotime($myAttendance['check_in'])) : '-' ?></span>
                    <span class="text-muted ms-2">Out: <?= $myAttendance['check_out'] ? date('H:i', strtotime($myAttendance['check_out'])) : '-' ?></span>
                    <?php if ($myAttendance['work_hours']): ?><span class="text-muted ms-2">(<?= $myAttendance['work_hours'] ?>h)</span><?php endif; ?>
                <?php else: ?>
                    <span class="text-muted">Chưa chấm công hôm nay</span>
                <?php endif; ?>
            </div>
            <form method="POST" action="<?= url('attendance/check-in') ?>">
                <?= csrf_field() ?>
                <?php if (!$myAttendance): ?>
                    <button class="btn btn-success"><i class="ri-login-box-line me-1"></i> Check-in</button>
                <?php elseif (!$myAttendance['check_out']): ?>
                    <button class="btn btn-warning"><i class="ri-logout-box-line me-1"></i> Check-out</button>
                <?php else: ?>
                    <span class="badge bg-success-subtle text-success fs-13 px-3 py-2"><i class="ri-check-line me-1"></i> Hoàn thành</span>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<!-- Month selector -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="<?= url('attendance') ?>" class="d-flex align-items-center gap-2">
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

<!-- Attendance Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 fs-12">
                <thead class="table-light">
                    <tr>
                        <th class="sticky-start" style="min-width:150px">Nhân viên</th>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++):
                            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                            $dow = date('N', strtotime($dateStr));
                            $isWeekend = ($dow >= 6);
                        ?>
                        <th class="text-center <?= $isWeekend ? 'bg-light' : '' ?>" style="min-width:35px"><?= $d ?></th>
                        <?php endfor; ?>
                        <th class="text-center" style="min-width:50px">Công</th>
                        <th class="text-center" style="min-width:50px">OT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-medium"><?= e($u['name']) ?></td>
                        <?php
                        $totalWork = 0; $totalOT = 0;
                        for ($d = 1; $d <= $daysInMonth; $d++):
                            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                            $dow = date('N', strtotime($dateStr));
                            $isWeekend = ($dow >= 6);
                            $att = $map[$u['id']][$dateStr] ?? null;
                            if ($att && in_array($att['status'], ['present','late'])) $totalWork++;
                            if ($att && $att['overtime_hours'] > 0) $totalOT += $att['overtime_hours'];
                        ?>
                        <td class="text-center p-1 <?= $isWeekend ? 'bg-light' : '' ?>">
                            <?php if ($att): ?>
                                <?php if ($att['status'] === 'present'): ?><span class="text-success" title="<?= $att['check_in'] ? date('H:i', strtotime($att['check_in'])) : '' ?>">&#10003;</span>
                                <?php elseif ($att['status'] === 'late'): ?><span class="text-warning" title="Muộn <?= $att['check_in'] ? date('H:i', strtotime($att['check_in'])) : '' ?>">M</span>
                                <?php elseif ($att['status'] === 'leave'): ?><span class="text-primary" title="Nghỉ phép">P</span>
                                <?php elseif ($att['status'] === 'absent'): ?><span class="text-danger">X</span>
                                <?php elseif ($att['status'] === 'half_day'): ?><span class="text-info">½</span>
                                <?php elseif ($att['status'] === 'holiday'): ?><span class="text-secondary">L</span>
                                <?php endif; ?>
                            <?php elseif ($isWeekend): ?>
                                <span class="text-muted">-</span>
                            <?php elseif (strtotime($dateStr) < strtotime($today)): ?>
                                <span class="text-danger">X</span>
                            <?php endif; ?>
                        </td>
                        <?php endfor; ?>
                        <td class="text-center fw-medium"><?= $totalWork ?></td>
                        <td class="text-center text-info"><?= $totalOT ? rtrim(rtrim(number_format($totalOT, 1), '0'), '.') : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-2 text-muted fs-12">
    <span class="text-success me-3">&#10003; Có mặt</span>
    <span class="text-warning me-3">M Muộn</span>
    <span class="text-primary me-3">P Nghỉ phép</span>
    <span class="text-danger me-3">X Vắng</span>
    <span class="text-info me-3">½ Nửa ngày</span>
    <span class="text-secondary">L Nghỉ lễ</span>
</div>
