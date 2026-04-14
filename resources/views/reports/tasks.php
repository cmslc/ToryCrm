<?php $pageTitle = 'Báo cáo công việc'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Báo cáo công việc</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Báo cáo</a></li>
        <li class="breadcrumb-item active">Công việc</li>
    </ol>
</div>

<!-- KPI -->
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-task-line text-primary fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">Tổng công việc</p>
                        <h4 class="mb-0"><?= number_format($total) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-check-double-line text-success fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">Hoàn thành</p>
                        <h4 class="mb-0"><?= number_format($done) ?> <small class="text-success">(<?= $completionRate ?>%)</small></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-danger-subtle rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-alarm-warning-line text-danger fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">Quá hạn</p>
                        <h4 class="mb-0 text-danger"><?= number_format($overdue) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-info-subtle rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-time-line text-info fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">TB hoàn thành</p>
                        <h4 class="mb-0"><?= $avgCompletionDays ?> ngày</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Status distribution -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Theo trạng thái</h5></div>
            <div class="card-body">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Priority distribution -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Theo mức ưu tiên</h5></div>
            <div class="card-body">
                <canvas id="priorityChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Completed by month -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Hoàn thành theo tháng (<?= date('Y') ?>)</h5></div>
            <div class="card-body">
                <canvas id="monthChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- By assignee -->
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Năng suất theo nhân viên</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Nhân viên</th><th class="text-end">Tổng</th><th class="text-end">Hoàn thành</th><th class="text-end">Quá hạn</th><th class="text-end">Tỷ lệ HT</th><th>Tiến độ</th></tr>
                </thead>
                <tbody>
                <?php foreach ($byAssignee as $a):
                    $rate = $a['total'] > 0 ? round($a['done'] / $a['total'] * 100) : 0;
                    $barColor = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                ?>
                    <tr>
                        <td><?= e($a['name']) ?></td>
                        <td class="text-end"><?= number_format($a['total']) ?></td>
                        <td class="text-end text-success"><?= number_format($a['done']) ?></td>
                        <td class="text-end text-danger"><?= number_format($a['overdue']) ?></td>
                        <td class="text-end"><?= $rate ?>%</td>
                        <td style="min-width:120px">
                            <div class="progress" style="height:6px">
                                <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= $rate ?>%"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($byAssignee)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
<?php
$statusLabels = ['todo'=>'Cần làm','in_progress'=>'Đang làm','review'=>'Review','done'=>'Hoàn thành'];
$statusColors = ['todo'=>'#405189','in_progress'=>'#f7b84b','review'=>'#299cdb','done'=>'#0ab39c'];
$sLabels = []; $sData = []; $sColors = [];
foreach ($byStatus as $s) {
    $sLabels[] = $statusLabels[$s['status']] ?? $s['status'];
    $sData[] = (int)$s['count'];
    $sColors[] = $statusColors[$s['status']] ?? '#6c757d';
}
$prioLabels = ['low'=>'Thấp','medium'=>'Trung bình','high'=>'Cao','urgent'=>'Khẩn cấp'];
$prioColors = ['low'=>'#299cdb','medium'=>'#f7b84b','high'=>'#f06548','urgent'=>'#dc3545'];
$pLabels = []; $pData = []; $pColors = [];
foreach ($byPriority as $p) {
    $pLabels[] = $prioLabels[$p['priority']] ?? $p['priority'];
    $pData[] = (int)$p['count'];
    $pColors[] = $prioColors[$p['priority']] ?? '#6c757d';
}
?>
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($sLabels) ?>, datasets: [{ data: <?= json_encode($sData) ?>, backgroundColor: <?= json_encode($sColors) ?> }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('priorityChart'), {
    type: 'doughnut',
    data: { labels: <?= json_encode($pLabels) ?>, datasets: [{ data: <?= json_encode($pData) ?>, backgroundColor: <?= json_encode($pColors) ?> }] },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('monthChart'), {
    type: 'bar',
    data: { labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'], datasets: [{ label: 'Hoàn thành', data: <?= json_encode($monthDone) ?>, backgroundColor: '#0ab39c' }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
