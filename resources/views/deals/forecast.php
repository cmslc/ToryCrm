<?php $pageTitle = 'Dự báo doanh thu'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Dự báo doanh thu</h4>
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="<?= url('deals') ?>">Cơ hội</a></li>
            <li class="breadcrumb-item active">Dự báo</li>
        </ol>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-md"><div class="avatar-title bg-primary-subtle text-primary rounded fs-24"><i class="ri-hand-coin-line"></i></div></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1">Tổng cơ hội mở</p>
                        <h4 class="mb-0"><?= $totalDeals ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-md"><div class="avatar-title bg-info-subtle text-info rounded fs-24"><i class="ri-money-dollar-circle-line"></i></div></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1">Tổng giá trị Pipeline</p>
                        <h4 class="mb-0"><?= format_money($totalValue) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-md"><div class="avatar-title bg-success-subtle text-success rounded fs-24"><i class="ri-line-chart-line"></i></div></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1">Dự báo (có trọng số)</p>
                        <h4 class="mb-0 text-success"><?= format_money($totalWeighted) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-md"><div class="avatar-title bg-warning-subtle text-warning rounded fs-24"><i class="ri-trophy-line"></i></div></div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-1">Thắng tháng này</p>
                        <h4 class="mb-0"><?= format_money($thisMonthWon['total'] ?? 0) ?></h4>
                        <small class="text-muted"><?= $thisMonthWon['cnt'] ?? 0 ?> cơ hội</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pipeline Funnel Chart -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Pipeline theo giai đoạn</h5>
            </div>
            <div class="card-body">
                <canvas id="pipelineChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Comparison -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">So sánh với tháng trước</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 border rounded text-center">
                            <p class="text-muted mb-1">Tháng trước (Thắng)</p>
                            <h3 class="text-primary mb-0"><?= format_money($lastMonthWon['total'] ?? 0) ?></h3>
                            <small class="text-muted"><?= $lastMonthWon['cnt'] ?? 0 ?> cơ hội</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded text-center">
                            <p class="text-muted mb-1">Tháng này (Thắng)</p>
                            <h3 class="text-success mb-0"><?= format_money($thisMonthWon['total'] ?? 0) ?></h3>
                            <small class="text-muted"><?= $thisMonthWon['cnt'] ?? 0 ?> cơ hội</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 border rounded text-center">
                            <p class="text-muted mb-1">Dự báo có trọng số</p>
                            <h2 class="text-success mb-0"><?= format_money($totalWeighted) ?></h2>
                            <small class="text-muted">Dựa trên xác suất từng giai đoạn</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weighted Forecast Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Chi tiết dự báo theo giai đoạn</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Giai đoạn</th>
                        <th class="text-center">Số cơ hội</th>
                        <th class="text-end">Tổng giá trị</th>
                        <th class="text-center">Xác suất (%)</th>
                        <th class="text-end">Giá trị có trọng số</th>
                        <th>Tỷ trọng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forecastData as $fd): ?>
                        <?php $pct = $totalValue > 0 ? round(($fd['total_value'] / $totalValue) * 100) : 0; ?>
                        <tr>
                            <td>
                                <span class="badge px-3 py-2" style="background-color: <?= safe_color($fd['stage']['color'] ?? null) ?>">
                                    <?= e($fd['stage']['name']) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $fd['deal_count'] ?></td>
                            <td class="text-end"><?= format_money($fd['total_value']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info"><?= $fd['probability'] ?>%</span>
                            </td>
                            <td class="text-end fw-medium text-success"><?= format_money($fd['weighted_value']) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar" style="width: <?= $pct ?>%; background-color: <?= safe_color($fd['stage']['color'] ?? null) ?>"></div>
                                    </div>
                                    <small class="text-muted"><?= $pct ?>%</small>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td>Tổng cộng</td>
                        <td class="text-center"><?= $totalDeals ?></td>
                        <td class="text-end"><?= format_money($totalValue) ?></td>
                        <td></td>
                        <td class="text-end text-success"><?= format_money($totalWeighted) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('pipelineChart').getContext('2d');

    const labels = <?= json_encode(array_map(function($fd) { return $fd['stage']['name']; }, $forecastData)) ?>;
    const values = <?= json_encode(array_map(function($fd) { return $fd['total_value']; }, $forecastData)) ?>;
    const counts = <?= json_encode(array_map(function($fd) { return $fd['deal_count']; }, $forecastData)) ?>;
    const colors = <?= json_encode(array_map(function($fd) { return $fd['stage']['color'] ?? '#6c757d'; }, $forecastData)) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Giá trị (VNĐ)',
                data: values,
                backgroundColor: colors.map(c => c + 'cc'),
                borderColor: colors,
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            const val = new Intl.NumberFormat('vi-VN').format(ctx.parsed.x);
                            return val + 'đ (' + counts[ctx.dataIndex] + ' cơ hội)';
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value);
                        }
                    }
                }
            }
        }
    });
});
</script>
