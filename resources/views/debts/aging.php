<?php $pageTitle = 'Báo cáo tuổi nợ'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo tuổi nợ</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('debts') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?= ($type ?? 'receivable') === 'receivable' ? 'active' : '' ?>" href="<?= url('debts/aging?type=receivable') ?>">Phải thu</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($type ?? '') === 'payable' ? 'active' : '' ?>" href="<?= url('debts/aging?type=payable') ?>">Phải trả</a>
            </li>
        </ul>

        <!-- Summary Totals -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">0 - 30 ngày</p>
                        <h5 class="mb-0 text-success"><?= format_money($totals['0_30'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">31 - 60 ngày</p>
                        <h5 class="mb-0 text-warning"><?= format_money($totals['31_60'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">61 - 90 ngày</p>
                        <h5 class="mb-0 text-danger"><?= format_money($totals['61_90'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Trên 90 ngày</p>
                        <h5 class="mb-0 text-dark"><?= format_money($totals['90_plus'] ?? 0) ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Biểu đồ tuổi nợ</h5>
            </div>
            <div class="card-body">
                <canvas id="agingChart" height="80"></canvas>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Chi tiết theo khách hàng / công ty</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Khách hàng / Công ty</th>
                                <th class="text-end">0 - 30 ngày</th>
                                <th class="text-end">31 - 60 ngày</th>
                                <th class="text-end">61 - 90 ngày</th>
                                <th class="text-end">Trên 90 ngày</th>
                                <th class="text-end">Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($agingData)): ?>
                                <?php foreach ($agingData as $row): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($row['name']) ?></td>
                                        <td class="text-end"><?= format_money($row['0_30']) ?></td>
                                        <td class="text-end"><?= format_money($row['31_60']) ?></td>
                                        <td class="text-end"><?= format_money($row['61_90']) ?></td>
                                        <td class="text-end"><?= format_money($row['90_plus']) ?></td>
                                        <td class="text-end fw-bold"><?= format_money($row['total']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($agingData)): ?>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td>Tổng cộng</td>
                                    <td class="text-end"><?= format_money($totals['0_30'] ?? 0) ?></td>
                                    <td class="text-end"><?= format_money($totals['31_60'] ?? 0) ?></td>
                                    <td class="text-end"><?= format_money($totals['61_90'] ?? 0) ?></td>
                                    <td class="text-end"><?= format_money($totals['90_plus'] ?? 0) ?></td>
                                    <td class="text-end"><?= format_money($totals['total'] ?? 0) ?></td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var agingData = <?= json_encode($agingData ?? []) ?>;
            var labels = agingData.map(function(r) { return r.name; });
            var ctx = document.getElementById('agingChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: '0-30 ngày', data: agingData.map(function(r) { return r['0_30']; }), backgroundColor: 'rgba(37, 160, 96, 0.7)' },
                        { label: '31-60 ngày', data: agingData.map(function(r) { return r['31_60']; }), backgroundColor: 'rgba(247, 184, 75, 0.7)' },
                        { label: '61-90 ngày', data: agingData.map(function(r) { return r['61_90']; }), backgroundColor: 'rgba(239, 68, 68, 0.7)' },
                        { label: '90+ ngày', data: agingData.map(function(r) { return r['90_plus']; }), backgroundColor: 'rgba(108, 117, 125, 0.7)' },
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, ticks: { callback: function(v) { return new Intl.NumberFormat('vi-VN').format(v) + ' đ'; } } }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return ctx.dataset.label + ': ' + new Intl.NumberFormat('vi-VN').format(ctx.raw) + ' đ'; }
                            }
                        }
                    }
                }
            });
        });
        </script>
