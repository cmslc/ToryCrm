<?php $pageTitle = 'Dòng tiền'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo dòng tiền</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('finance-reports') ?>">Báo cáo tài chính</a></li>
                <li class="breadcrumb-item active">Dòng tiền</li>
            </ol>
        </div>

        <!-- Period Selector -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('finance-reports/cash-flow') ?>" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">Năm</label>
                        <select name="year" class="form-select">
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?= $y ?>" <?= ($year ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Xem</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-arrow-down-circle-line text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Tổng thu</p>
                                <h4 class="mb-0 text-success"><?= format_money($totalIn ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-danger-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-arrow-up-circle-line text-danger fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Tổng chi</p>
                                <h4 class="mb-0 text-danger"><?= format_money($totalOut ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-exchange-funds-line text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Dòng tiền ròng</p>
                                <h4 class="mb-0 <?= ($netFlow ?? 0) >= 0 ? 'text-primary' : 'text-danger' ?>"><?= format_money($netFlow ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thu chi theo tháng (<?= $year ?? date('Y') ?>)</h5></div>
            <div class="card-body">
                <canvas id="cashFlowChart" height="100"></canvas>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Bảng dòng tiền</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tháng</th>
                                <th class="text-end">Số dư đầu kỳ</th>
                                <th class="text-end text-success">Thu</th>
                                <th class="text-end text-danger">Chi</th>
                                <th class="text-end">Số dư cuối kỳ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $balance = $openingBalance ?? 0;
                            $monthNames = ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
                            for ($i = 0; $i < 12; $i++):
                                $opening = $balance;
                                $receipt = $monthlyReceipts[$i] ?? 0;
                                $payment = $monthlyPayments[$i] ?? 0;
                                $balance = $opening + $receipt - $payment;
                            ?>
                                <tr>
                                    <td class="fw-medium"><?= $monthNames[$i] ?></td>
                                    <td class="text-end"><?= format_money($opening) ?></td>
                                    <td class="text-end text-success"><?= $receipt > 0 ? '+' . format_money($receipt) : '-' ?></td>
                                    <td class="text-end text-danger"><?= $payment > 0 ? '-' . format_money($payment) : '-' ?></td>
                                    <td class="text-end fw-medium <?= $balance >= 0 ? '' : 'text-danger' ?>"><?= format_money($balance) ?></td>
                                </tr>
                            <?php endfor; ?>
                            <tr class="table-light fw-bold">
                                <td>Tổng cộng</td>
                                <td class="text-end"><?= format_money($openingBalance ?? 0) ?></td>
                                <td class="text-end text-success">+<?= format_money($totalIn ?? 0) ?></td>
                                <td class="text-end text-danger">-<?= format_money($totalOut ?? 0) ?></td>
                                <td class="text-end"><?= format_money($balance) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const receipts = <?= json_encode($monthlyReceipts ?? array_fill(0, 12, 0)) ?>;
            const payments = <?= json_encode($monthlyPayments ?? array_fill(0, 12, 0)) ?>;
            const labels = ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];

            new Chart(document.getElementById('cashFlowChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Thu',
                            data: receipts,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            stack: 'stack0'
                        },
                        {
                            label: 'Chi',
                            data: payments,
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            stack: 'stack1'
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
