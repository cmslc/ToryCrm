<?php $pageTitle = 'Báo cáo Lãi / Lỗ'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo Lãi / Lỗ (P&L)</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('finance-reports') ?>">Báo cáo tài chính</a></li>
                <li class="breadcrumb-item active">Lãi / Lỗ</li>
            </ol>
        </div>

        <!-- Period Selector -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('finance-reports/profit-loss') ?>" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">Loại kỳ</label>
                        <select name="period_type" class="form-select" id="periodType" onchange="togglePeriodFields()">
                            <option value="month" <?= ($periodType ?? 'month') === 'month' ? 'selected' : '' ?>>Tháng</option>
                            <option value="quarter" <?= ($periodType ?? '') === 'quarter' ? 'selected' : '' ?>>Quý</option>
                            <option value="year" <?= ($periodType ?? '') === 'year' ? 'selected' : '' ?>>Năm</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Năm</label>
                        <select name="year" class="form-select">
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto" id="monthField">
                        <label class="form-label">Tháng</label>
                        <select name="month" class="form-select">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= ($month ?? date('n')) == $m ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto" id="quarterField" style="display:none">
                        <label class="form-label">Quý</label>
                        <select name="quarter" class="form-select">
                            <?php for ($q = 1; $q <= 4; $q++): ?>
                                <option value="<?= $q ?>" <?= ($quarter ?? ceil(date('n') / 3)) == $q ? 'selected' : '' ?>>Quý <?= $q ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Xem</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- P&L Summary -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-success-subtle"><h5 class="card-title mb-0 text-success">Doanh thu</h5></div>
                    <div class="card-body">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td>Doanh thu Deal (Cơ hội thắng)</td>
                                    <td class="text-end fw-medium"><?= format_money($dealRevenue ?? 0) ?></td>
                                </tr>
                                <tr>
                                    <td>Doanh thu Đơn hàng</td>
                                    <td class="text-end fw-medium"><?= format_money($orderRevenue ?? 0) ?></td>
                                </tr>
                                <tr class="table-success fw-bold">
                                    <td>Tổng doanh thu</td>
                                    <td class="text-end"><?= format_money($totalRevenue ?? 0) ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php if (($prevRevenue ?? 0) > 0): ?>
                            <div class="mt-2">
                                <span class="badge bg-<?= ($revenueChange ?? 0) >= 0 ? 'success' : 'danger' ?>">
                                    <?= ($revenueChange ?? 0) >= 0 ? '+' : '' ?><?= number_format($revenueChange ?? 0, 1) ?>%
                                </span>
                                <span class="text-muted ms-1">so với kỳ trước (<?= format_money($prevRevenue ?? 0) ?>)</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header bg-danger-subtle"><h5 class="card-title mb-0 text-danger">Chi phí</h5></div>
                    <div class="card-body">
                        <table class="table mb-0">
                            <tbody>
                                <?php if (!empty($expenses)): ?>
                                    <?php foreach ($expenses as $exp): ?>
                                        <tr>
                                            <td><?= e($exp['category']) ?></td>
                                            <td class="text-end fw-medium"><?= format_money($exp['total']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="2" class="text-muted text-center">Chưa có chi phí</td></tr>
                                <?php endif; ?>
                                <tr class="table-danger fw-bold">
                                    <td>Tổng chi phí</td>
                                    <td class="text-end"><?= format_money($totalExpense ?? 0) ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php if (($prevExpense ?? 0) > 0): ?>
                            <div class="mt-2">
                                <span class="badge bg-<?= ($expenseChange ?? 0) <= 0 ? 'success' : 'danger' ?>">
                                    <?= ($expenseChange ?? 0) >= 0 ? '+' : '' ?><?= number_format($expenseChange ?? 0, 1) ?>%
                                </span>
                                <span class="text-muted ms-1">so với kỳ trước (<?= format_money($prevExpense ?? 0) ?>)</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="card border-<?= ($netProfit ?? 0) >= 0 ? 'primary' : 'danger' ?>">
            <div class="card-body text-center py-4">
                <h5 class="text-muted mb-2">Lợi nhuận ròng</h5>
                <h2 class="mb-0 <?= ($netProfit ?? 0) >= 0 ? 'text-primary' : 'text-danger' ?>"><?= format_money($netProfit ?? 0) ?></h2>
                <p class="text-muted mt-1 mb-0">
                    Từ <?= date('d/m/Y', strtotime($dateFrom ?? date('Y-m-01'))) ?> đến <?= date('d/m/Y', strtotime($dateTo ?? date('Y-m-t'))) ?>
                </p>
            </div>
        </div>

        <!-- Monthly Chart -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Doanh thu vs Chi phí theo tháng (<?= $year ?? date('Y') ?>)</h5></div>
            <div class="card-body">
                <canvas id="plChart" height="100"></canvas>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        function togglePeriodFields() {
            var pt = document.getElementById('periodType').value;
            document.getElementById('monthField').style.display = pt === 'month' ? '' : 'none';
            document.getElementById('quarterField').style.display = pt === 'quarter' ? '' : 'none';
        }
        togglePeriodFields();

        document.addEventListener('DOMContentLoaded', function() {
            const monthlyRevenue = <?= json_encode($monthlyRevenue ?? array_fill(0, 12, 0)) ?>;
            const monthlyExpense = <?= json_encode($monthlyExpense ?? array_fill(0, 12, 0)) ?>;
            const labels = ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];

            new Chart(document.getElementById('plChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Doanh thu',
                            data: monthlyRevenue,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Chi phí',
                            data: monthlyExpense,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN').format(value);
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>
