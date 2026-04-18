<?php $pageTitle = 'Báo cáo doanh thu'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Báo cáo doanh thu</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Báo cáo</a></li>
        <li class="breadcrumb-item active">Doanh thu</li>
    </ol>
</div>

<!-- Year + Summary -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?= url('reports/revenue') ?>" class="d-flex align-items-end gap-3 mb-3">
            <div>
                <label class="form-label">Năm</label>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>
        <?php
        $totalDealRev = array_sum($dealData ?? []);
        $totalOrderRev = array_sum($orderData ?? []);
        $totalDeals = 0; $totalOrders = 0;
        ?>
        <div class="row text-center">
            <div class="col-md-3">
                <h5 class="text-success mb-1"><?= format_money($totalDealRev) ?></h5>
                <p class="text-muted mb-0">Doanh thu Deal</p>
            </div>
            <div class="col-md-3">
                <h5 class="text-primary mb-1"><?= format_money($totalOrderRev) ?></h5>
                <p class="text-muted mb-0">Doanh thu Đơn hàng</p>
            </div>
            <div class="col-md-3">
                <h5 class="mb-1"><?= format_money($totalDealRev + $totalOrderRev) ?></h5>
                <p class="text-muted mb-0">Tổng cộng</p>
            </div>
            <div class="col-md-3">
                <h5 class="mb-1"><?= count($topDeals ?? []) ?></h5>
                <p class="text-muted mb-0">Deal thắng</p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Doanh thu theo tháng - <?= $year ?></h5></div>
    <div class="card-body">
        <div style="height:350px"><canvas id="revenueChart"></canvas></div>
    </div>
</div>

<div class="row">
    <!-- Top 10 Deal -->
    <div class="col-lg-6">
        <div class="card card-height-100">
            <div class="card-header"><h5 class="card-title mb-0">Top 10 Deal thắng</h5></div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Deal</th><th>Khách hàng</th><th class="text-end">Giá trị</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topDeals ?? [] as $i => $deal): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="fw-medium"><?= e($deal['title']) ?></td>
                                <td>
                                    <?= e(trim(($deal['first_name'] ?? '') . ' ' . ($deal['last_name'] ?? ''))) ?: '-' ?>
                                    <?php if (!empty($deal['company_name'])): ?>
                                        <div class="text-muted fs-12"><?= e($deal['company_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-semibold text-success"><?= format_money($deal['value']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topDeals)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Owner -->
    <div class="col-lg-6">
        <div class="card card-height-100">
            <div class="card-header"><h5 class="card-title mb-0">Doanh thu theo nhân viên</h5></div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Nhân viên</th><th class="text-end">Doanh thu</th><th class="text-end">Deal</th><th>Tỷ trọng</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $maxOwnerRev = max(array_column($byOwner ?? [], 'revenue') ?: [1]) ?: 1;
                            foreach ($byOwner ?? [] as $owner):
                                $pct = $totalDealRev > 0 ? round($owner['revenue'] / $totalDealRev * 100) : 0;
                            ?>
                            <tr>
                                <td><?= user_avatar($owner['name'] ?? null) ?></td>
                                <td class="text-end fw-semibold"><?= format_money($owner['revenue']) ?></td>
                                <td class="text-end"><?= $owner['count'] ?></td>
                                <td style="min-width:100px">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px"><div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div></div>
                                        <span class="fs-12 text-muted"><?= $pct ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($byOwner)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    new Chart(ctx, {
        data: {
            labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
            datasets: [
                {
                    type: 'bar',
                    label: 'Doanh thu Deal',
                    data: <?= json_encode($dealData) ?>,
                    backgroundColor: 'rgba(10, 179, 156, 0.8)',
                    borderRadius: 4,
                    barPercentage: 0.5,
                    order: 2,
                },
                {
                    type: 'bar',
                    label: 'Doanh thu Đơn hàng',
                    data: <?= json_encode($orderData) ?>,
                    backgroundColor: 'rgba(64, 81, 137, 0.8)',
                    borderRadius: 4,
                    barPercentage: 0.5,
                    order: 1,
                },
                {
                    type: 'line',
                    label: 'Tổng cộng',
                    data: <?= json_encode(array_map(function($i) use ($dealData, $orderData) { return ($dealData[$i] ?? 0) + ($orderData[$i] ?? 0); }, range(0, 11))) ?>,
                    borderColor: '#f06548',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#f06548',
                    fill: false,
                    tension: 0.4,
                    order: 0,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { callback: function(v) { return v >= 1000000 ? (v/1000000).toFixed(0)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'k' : v; } } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
