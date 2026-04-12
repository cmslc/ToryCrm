<?php $pageTitle = 'Báo cáo tài chính'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo tài chính</h4>
        </div>

        <!-- Top Metrics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-arrow-up-circle-line text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Doanh thu tháng</p>
                                <h4 class="mb-0 text-success"><?= format_money($totalRevenue ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-danger-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-arrow-down-circle-line text-danger fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Chi phí tháng</p>
                                <h4 class="mb-0 text-danger"><?= format_money($totalExpense ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-line-chart-line text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Lợi nhuận ròng</p>
                                <h4 class="mb-0 <?= ($netProfit ?? 0) >= 0 ? 'text-primary' : 'text-danger' ?>"><?= format_money($netProfit ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-warning-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-money-dollar-circle-line text-warning fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Công nợ phải thu</p>
                                <h4 class="mb-0 text-warning"><?= format_money($receivables ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-3">
            <div class="col-lg-8">
                <div class="card card-height-100">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-line-chart-line me-2"></i> Xu hướng thu chi 6 tháng</h5></div>
                    <div class="card-body">
                        <?php if (!empty($monthlyTrend)): ?>
                        <canvas id="trendChart" height="180"></canvas>
                        <?php else: ?>
                        <p class="text-muted text-center py-4">Chưa có dữ liệu</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-height-100">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-funds-line me-2"></i> Công nợ hiện tại</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Phải thu</span>
                            <span class="fw-medium text-success"><?= format_money($debtSummary['receivable'] ?? 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Phải trả</span>
                            <span class="fw-medium text-danger"><?= format_money($debtSummary['payable'] ?? 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                            <span class="text-muted">Quá hạn</span>
                            <span class="fw-medium text-warning"><?= format_money($debtSummary['overdue'] ?? 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-medium">Ròng (thu - trả)</span>
                            <?php $net = ($debtSummary['receivable'] ?? 0) - ($debtSummary['payable'] ?? 0); ?>
                            <span class="fw-medium text-<?= $net >= 0 ? 'primary' : 'danger' ?>"><?= format_money($net) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($monthlyTrend)): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') return;
            var labels = <?= json_encode(array_column($monthlyTrend, 'month')) ?>;
            var receipts = <?= json_encode(array_map(function($r){return (float)$r['receipt'];}, $monthlyTrend)) ?>;
            var payments = <?= json_encode(array_map(function($r){return (float)$r['payment'];}, $monthlyTrend)) ?>;
            var profits = receipts.map(function(r,i){ return r - payments[i]; });
            new Chart(document.getElementById('trendChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {label:'Thu', data:receipts, backgroundColor:'rgba(10,179,156,0.7)', order:2},
                        {label:'Chi', data:payments, backgroundColor:'rgba(240,101,72,0.7)', order:2},
                        {label:'Lợi nhuận', data:profits, type:'line', borderColor:'#405189', backgroundColor:'transparent', borderWidth:2, pointRadius:4, order:1}
                    ]
                },
                options:{responsive:true, plugins:{legend:{position:'top'}}, scales:{y:{beginAtZero:true, ticks:{callback:function(v){return (v/1000000)+'tr'}}}}}
            });
        });
        </script>
        <?php endif; ?>

        <!-- Quick Links -->
        <div class="row">
            <div class="col-md-4">
                <a href="<?= url('finance-reports/profit-loss') ?>" class="card card-body text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                            <i class="ri-file-chart-line text-primary fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Lãi / Lỗ (P&L)</h5>
                            <p class="text-muted mb-0">Doanh thu, chi phí, lợi nhuận theo kỳ</p>
                        </div>
                        <i class="ri-arrow-right-s-line fs-4 ms-auto text-muted"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= url('finance-reports/cash-flow') ?>" class="card card-body text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                            <i class="ri-exchange-funds-line text-success fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Dòng tiền</h5>
                            <p class="text-muted mb-0">Thu chi theo tháng, số dư luỹ kế</p>
                        </div>
                        <i class="ri-arrow-right-s-line fs-4 ms-auto text-muted"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?= url('finance-reports/aging') ?>" class="card card-body text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-warning-subtle rounded me-3 d-flex align-items-center justify-content-center">
                            <i class="ri-timer-line text-warning fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Tuổi nợ</h5>
                            <p class="text-muted mb-0">Phân tích công nợ phải thu theo độ tuổi</p>
                        </div>
                        <i class="ri-arrow-right-s-line fs-4 ms-auto text-muted"></i>
                    </div>
                </a>
            </div>
        </div>
