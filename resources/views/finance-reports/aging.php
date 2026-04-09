<?php $pageTitle = 'Tuổi nợ'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Phân tích tuổi nợ phải thu</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('finance-reports') ?>">Báo cáo tài chính</a></li>
                <li class="breadcrumb-item active">Tuổi nợ</li>
            </ol>
        </div>

        <div class="row">
            <!-- Donut Chart -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Phân bổ công nợ</h5></div>
                    <div class="card-body">
                        <?php if (($totals['total'] ?? 0) > 0): ?>
                            <canvas id="agingDonut" height="250"></canvas>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="ri-check-double-line fs-1 d-block mb-2"></i>
                                <p>Không có công nợ phải thu</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Tổng hợp</h5></div>
                    <div class="card-body">
                        <table class="table mb-0">
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-success">Hiện tại</span></td>
                                    <td class="text-end fw-medium"><?= format_money($totals['current'] ?? 0) ?></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">1 - 30 ngày</span></td>
                                    <td class="text-end fw-medium"><?= format_money($totals['d1_30'] ?? 0) ?></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">31 - 60 ngày</span></td>
                                    <td class="text-end fw-medium"><?= format_money($totals['d31_60'] ?? 0) ?></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger">61 - 90 ngày</span></td>
                                    <td class="text-end fw-medium"><?= format_money($totals['d61_90'] ?? 0) ?></td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-dark">Trên 90 ngày</span></td>
                                    <td class="text-end fw-medium"><?= format_money($totals['d90_plus'] ?? 0) ?></td>
                                </tr>
                                <tr class="table-light fw-bold">
                                    <td>Tổng cộng</td>
                                    <td class="text-end"><?= format_money($totals['total'] ?? 0) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Table by Customer -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Chi tiết theo khách hàng</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Khách hàng</th>
                                        <th class="text-end">Hiện tại</th>
                                        <th class="text-end">1-30 ngày</th>
                                        <th class="text-end">31-60 ngày</th>
                                        <th class="text-end">61-90 ngày</th>
                                        <th class="text-end">Trên 90</th>
                                        <th class="text-end fw-bold">Tổng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($agingData)): ?>
                                        <?php foreach ($agingData as $customer): ?>
                                            <tr>
                                                <td class="fw-medium"><?= e($customer['name']) ?></td>
                                                <td class="text-end"><?= $customer['current'] > 0 ? format_money($customer['current']) : '-' ?></td>
                                                <td class="text-end"><?= $customer['d1_30'] > 0 ? format_money($customer['d1_30']) : '-' ?></td>
                                                <td class="text-end"><?= $customer['d31_60'] > 0 ? format_money($customer['d31_60']) : '-' ?></td>
                                                <td class="text-end"><?= $customer['d61_90'] > 0 ? format_money($customer['d61_90']) : '-' ?></td>
                                                <td class="text-end"><?= $customer['d90_plus'] > 0 ? format_money($customer['d90_plus']) : '-' ?></td>
                                                <td class="text-end fw-bold"><?= format_money($customer['total']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-light fw-bold">
                                            <td>Tổng cộng</td>
                                            <td class="text-end"><?= format_money($totals['current'] ?? 0) ?></td>
                                            <td class="text-end"><?= format_money($totals['d1_30'] ?? 0) ?></td>
                                            <td class="text-end"><?= format_money($totals['d31_60'] ?? 0) ?></td>
                                            <td class="text-end"><?= format_money($totals['d61_90'] ?? 0) ?></td>
                                            <td class="text-end"><?= format_money($totals['d90_plus'] ?? 0) ?></td>
                                            <td class="text-end"><?= format_money($totals['total'] ?? 0) ?></td>
                                        </tr>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-check-double-line fs-1 d-block mb-2"></i>Không có công nợ phải thu</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (($totals['total'] ?? 0) > 0): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totals = <?= json_encode($totals ?? []) ?>;

            new Chart(document.getElementById('agingDonut'), {
                type: 'doughnut',
                data: {
                    labels: ['Hiện tại', '1-30 ngày', '31-60 ngày', '61-90 ngày', 'Trên 90 ngày'],
                    datasets: [{
                        data: [
                            totals.current || 0,
                            totals.d1_30 || 0,
                            totals.d31_60 || 0,
                            totals.d61_90 || 0,
                            totals.d90_plus || 0
                        ],
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(55, 65, 81, 0.8)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + new Intl.NumberFormat('vi-VN').format(context.raw) + ' VNĐ';
                                }
                            }
                        }
                    }
                }
            });
        });
        </script>
        <?php endif; ?>
