<?php
$pageTitle = 'So sánh KPI phòng ban';
$monthNames = ['','Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
$pMonth = (int)substr($period, 5, 2);
$pYear = (int)substr($period, 0, 4);
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-bar-chart-grouped-line me-2"></i> So sánh KPI phòng ban</h4>
    <a href="<?= url('departments') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<!-- Period selector -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="<?= url('departments/kpi-comparison') ?>" class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 me-2">Kỳ:</label>
            <select name="period" class="form-select" style="width:auto" onchange="this.form.submit()">
                <?php for ($m = 1; $m <= 12; $m++):
                    $val = $pYear . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                ?>
                <option value="<?= $val ?>" <?= $val === $period ? 'selected' : '' ?>><?= $monthNames[$m] ?> <?= $pYear ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<!-- Comparison Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Phòng ban</th>
                        <th class="text-center">NV</th>
                        <th class="text-end">Doanh thu</th>
                        <th class="text-center">%</th>
                        <th class="text-center">Deal</th>
                        <th class="text-center">%</th>
                        <th class="text-center">Task</th>
                        <th class="text-center">%</th>
                        <th class="text-center">KH</th>
                        <th class="text-center">%</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($comparison as $c):
                    $kpi = $c['kpi'];
                    $s = $c['stats'];
                    $revTarget = (float)($kpi['target_revenue'] ?? 0);
                    $dealTarget = (int)($kpi['target_deals'] ?? 0);
                    $taskTarget = (int)($kpi['target_tasks'] ?? 0);
                    $contactTarget = (int)($kpi['target_contacts'] ?? 0);
                    $revPct = $revTarget > 0 ? min(200, round($s['revenue'] / $revTarget * 100)) : 0;
                    $dealPct = $dealTarget > 0 ? min(200, round($s['deals'] / $dealTarget * 100)) : 0;
                    $taskPct = $taskTarget > 0 ? min(200, round($s['tasks_done'] / $taskTarget * 100)) : 0;
                    $contactPct = $contactTarget > 0 ? min(200, round($s['contacts'] / $contactTarget * 100)) : 0;
                    $pctColor = function($p) { if ($p >= 100) return 'success'; if ($p >= 70) return 'warning'; return 'danger'; };
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:<?= e($c['color']) ?>"></span>
                            <a href="<?= url('departments/' . $c['id']) ?>" class="fw-medium"><?= e($c['name']) ?></a>
                        </div>
                    </td>
                    <td class="text-center"><?= $c['member_count'] ?></td>
                    <td class="text-end"><?= format_money($s['revenue']) ?><br><small class="text-muted">/ <?= format_money($revTarget) ?></small></td>
                    <td class="text-center"><span class="badge bg-<?= $pctColor($revPct) ?>-subtle text-<?= $pctColor($revPct) ?>"><?= $revPct ?>%</span></td>
                    <td class="text-center"><?= $s['deals'] ?><br><small class="text-muted">/ <?= $dealTarget ?></small></td>
                    <td class="text-center"><span class="badge bg-<?= $pctColor($dealPct) ?>-subtle text-<?= $pctColor($dealPct) ?>"><?= $dealPct ?>%</span></td>
                    <td class="text-center"><?= $s['tasks_done'] ?><br><small class="text-muted">/ <?= $taskTarget ?></small></td>
                    <td class="text-center"><span class="badge bg-<?= $pctColor($taskPct) ?>-subtle text-<?= $pctColor($taskPct) ?>"><?= $taskPct ?>%</span></td>
                    <td class="text-center"><?= $s['contacts'] ?><br><small class="text-muted">/ <?= $contactTarget ?></small></td>
                    <td class="text-center"><span class="badge bg-<?= $pctColor($contactPct) ?>-subtle text-<?= $pctColor($contactPct) ?>"><?= $contactPct ?>%</span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($comparison)): ?>
                <tr><td colspan="10" class="text-center text-muted py-4">Chưa có phòng ban</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chart -->
<?php if (!empty($comparison)): ?>
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Biểu đồ doanh thu</h5></div>
    <div class="card-body">
        <canvas id="kpiChart" height="300"></canvas>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;
    var ctx = document.getElementById('kpiChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($comparison, 'name')) ?>,
            datasets: [
                {
                    label: 'Thực tế',
                    data: <?= json_encode(array_map(function($c) { return $c['stats']['revenue']; }, $comparison)) ?>,
                    backgroundColor: <?= json_encode(array_map(function($c) { return $c['color'] . '99'; }, $comparison)) ?>,
                    borderColor: <?= json_encode(array_column($comparison, 'color')) ?>,
                    borderWidth: 1,
                },
                {
                    label: 'Mục tiêu',
                    data: <?= json_encode(array_map(function($c) { return (float)($c['kpi']['target_revenue'] ?? 0); }, $comparison)) ?>,
                    backgroundColor: 'rgba(200,200,200,0.3)',
                    borderColor: '#ccc',
                    borderWidth: 1,
                    borderDash: [5,5],
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { callback: function(v) { return (v/1000000) + 'tr'; } } } }
        }
    });
});
</script>
<?php endif; ?>
