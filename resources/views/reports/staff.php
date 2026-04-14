<?php $pageTitle = 'Báo cáo nhân viên'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Báo cáo nhân viên</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Báo cáo</a></li>
        <li class="breadcrumb-item active">Nhân viên</li>
    </ol>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Năm</label>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Staff Performance -->
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Hiệu suất nhân viên</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nhân viên</th>
                        <th>Phòng ban</th>
                        <th class="text-end">Deal thắng</th>
                        <th class="text-end">DT Deal</th>
                        <th class="text-end">Đơn hàng</th>
                        <th class="text-end">DT Đơn hàng</th>
                        <th class="text-end">Task HT</th>
                        <th class="text-end">Tổng task</th>
                        <th class="text-end">Hoạt động</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($staffPerf as $s): ?>
                    <tr>
                        <td class="fw-medium"><?= e($s['name']) ?></td>
                        <td><span class="text-muted"><?= e($s['dept_name'] ?? '—') ?></span></td>
                        <td class="text-end"><?= number_format($s['deals_won']) ?></td>
                        <td class="text-end text-success fw-semibold"><?= number_format($s['deal_revenue']) ?></td>
                        <td class="text-end"><?= number_format($s['orders_count']) ?></td>
                        <td class="text-end text-success"><?= number_format($s['order_revenue']) ?></td>
                        <td class="text-end"><?= number_format($s['tasks_done']) ?></td>
                        <td class="text-end"><?= number_format($s['tasks_total']) ?></td>
                        <td class="text-end"><?= number_format($s['activities']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($staffPerf)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Revenue chart by staff -->
<?php if (!empty($staffPerf)): ?>
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Doanh thu theo nhân viên</h5></div>
    <div class="card-body">
        <canvas id="staffRevenueChart" height="300"></canvas>
    </div>
</div>
<?php endif; ?>

<!-- Commissions -->
<?php if (!empty($commissions)): ?>
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Hoa hồng</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nhân viên</th>
                        <th class="text-end">Đã trả</th>
                        <th class="text-end">Đã duyệt</th>
                        <th class="text-end">Chờ duyệt</th>
                        <th class="text-end">Tổng</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($commissions as $c): ?>
                    <tr>
                        <td><?= e($c['name']) ?></td>
                        <td class="text-end text-success"><?= number_format($c['paid']) ?></td>
                        <td class="text-end text-primary"><?= number_format($c['approved']) ?></td>
                        <td class="text-end text-warning"><?= number_format($c['pending']) ?></td>
                        <td class="text-end fw-semibold"><?= number_format($c['paid'] + $c['approved'] + $c['pending']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($staffPerf)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
<?php
$names = []; $dealRev = []; $orderRev = [];
foreach (array_slice($staffPerf, 0, 10) as $s) {
    $names[] = $s['name'];
    $dealRev[] = (float)$s['deal_revenue'];
    $orderRev[] = (float)$s['order_revenue'];
}
?>
new Chart(document.getElementById('staffRevenueChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($names) ?>,
        datasets: [
            { label: 'DT Deal', data: <?= json_encode($dealRev) ?>, backgroundColor: '#405189' },
            { label: 'DT Đơn hàng', data: <?= json_encode($orderRev) ?>, backgroundColor: '#0ab39c' }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { x: { beginAtZero: true, stacked: true }, y: { stacked: true } }
    }
});
</script>
<?php endif; ?>
