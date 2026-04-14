<?php $pageTitle = 'Báo cáo cơ hội'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Báo cáo cơ hội</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Báo cáo</a></li>
        <li class="breadcrumb-item active">Cơ hội</li>
    </ol>
</div>

<!-- Year filter -->
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

<!-- KPI Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-hand-coin-line text-primary fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">Tổng cơ hội</p>
                        <h4 class="mb-0"><?= number_format($totalDeals) ?></h4>
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
                        <p class="text-muted mb-0">Thắng</p>
                        <h4 class="mb-0"><?= number_format($totalWon) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-warning-subtle rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-percent-line text-warning fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">Tỷ lệ chuyển đổi</p>
                        <h4 class="mb-0"><?= $conversionRate ?>%</h4>
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
                        <p class="text-muted mb-0">TB thời gian close</p>
                        <h4 class="mb-0"><?= $avgCloseDays ?> ngày</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pipeline -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Pipeline hiện tại</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Giai đoạn</th><th class="text-end">Số lượng</th><th class="text-end">Giá trị</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($byStage as $s): ?>
                            <tr>
                                <td><span class="badge" style="background:<?= e($s['color'] ?? '#6c757d') ?>"><?= e($s['stage']) ?></span></td>
                                <td class="text-end"><?= number_format($s['count']) ?></td>
                                <td class="text-end"><?= number_format($s['total_value']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Win/Loss chart -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thắng / Thua theo tháng</h5></div>
            <div class="card-body">
                <canvas id="winLossChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Loss reasons -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Lý do thua</h5></div>
            <div class="card-body">
                <?php if (empty($lossReasons)): ?>
                    <p class="text-muted text-center py-3">Chưa có dữ liệu</p>
                <?php else: ?>
                    <canvas id="lossChart" height="250"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- By owner -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Theo nhân viên</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Nhân viên</th><th class="text-end">Tổng</th><th class="text-end">Thắng</th><th class="text-end">Thua</th><th class="text-end">Giá trị thắng</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($byOwner as $o): ?>
                            <tr>
                                <td><?= e($o['name']) ?></td>
                                <td class="text-end"><?= number_format($o['total']) ?></td>
                                <td class="text-end text-success"><?= number_format($o['won']) ?></td>
                                <td class="text-end text-danger"><?= number_format($o['lost']) ?></td>
                                <td class="text-end fw-semibold"><?= number_format($o['won_value']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
var months = ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'];
new Chart(document.getElementById('winLossChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [
            { label: 'Thắng', data: <?= json_encode($wonData) ?>, backgroundColor: '#0ab39c' },
            { label: 'Thua', data: <?= json_encode($lostData) ?>, backgroundColor: '#f06548' }
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
<?php if (!empty($lossReasons)): ?>
new Chart(document.getElementById('lossChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($lossReasons, 'reason')) ?>,
        datasets: [{ data: <?= json_encode(array_map('intval', array_column($lossReasons, 'count'))) ?>,
            backgroundColor: ['#405189','#0ab39c','#f7b84b','#f06548','#299cdb','#6c757d','#e83e8c','#6610f2','#fd7e14','#20c997'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
<?php endif; ?>
</script>
