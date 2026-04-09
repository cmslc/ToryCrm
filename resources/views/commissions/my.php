<?php $pageTitle = 'Hoa hồng của tôi'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Hoa hồng của tôi</h4>
            <a href="<?= url('commissions') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
        </div>

        <!-- Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-warning-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-time-line text-warning fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Chờ duyệt</p>
                                <h4 class="mb-0 text-warning"><?= format_money($totalPending ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-info-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-checkbox-circle-line text-info fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Đã duyệt</p>
                                <h4 class="mb-0 text-info"><?= format_money($totalApproved ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-money-dollar-circle-line text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Đã nhận</p>
                                <h4 class="mb-0 text-success"><?= format_money($totalPaid ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Chart -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="card-title mb-0">Hoa hồng theo tháng (12 tháng gần nhất)</h5></div>
            <div class="card-body">
                <canvas id="myCommissionChart" height="80"></canvas>
            </div>
        </div>

        <!-- Table -->
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Chi tiết hoa hồng</h5>
                <form method="GET" action="<?= url('commissions/my') ?>" class="d-flex gap-2">
                    <input type="month" class="form-control" name="period" value="<?= e($filters['period'] ?? '') ?>" placeholder="Lọc theo tháng">
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i></button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Loại</th>
                                <th>Mã entity</th>
                                <th class="text-end">Giá trị gốc</th>
                                <th>Tỷ lệ</th>
                                <th class="text-end">Hoa hồng</th>
                                <th>Trạng thái</th>
                                <th>Ngày</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statusColors = ['pending' => 'warning', 'approved' => 'info', 'paid' => 'success'];
                            $statusLabels = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'paid' => 'Đã trả'];
                            ?>
                            <?php if (!empty($commissions)): ?>
                                <?php foreach ($commissions as $c): ?>
                                    <tr>
                                        <td><span class="badge bg-<?= $c['entity_type'] === 'deal' ? 'primary' : 'success' ?>"><?= $c['entity_type'] === 'deal' ? 'Deal' : 'Đơn hàng' ?></span></td>
                                        <td>
                                            <?php if ($c['entity_type'] === 'deal'): ?>
                                                <a href="<?= url('deals/' . $c['entity_id']) ?>">#<?= $c['entity_id'] ?></a>
                                            <?php else: ?>
                                                <a href="<?= url('orders/' . $c['entity_id']) ?>">#<?= $c['entity_id'] ?></a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><?= format_money($c['base_amount'] ?? 0) ?></td>
                                        <td><?= $c['rate_type'] === 'percent' ? number_format($c['rate'], 1) . '%' : format_money($c['rate']) ?></td>
                                        <td class="text-end fw-medium"><?= format_money($c['amount']) ?></td>
                                        <td><span class="badge bg-<?= $statusColors[$c['status']] ?? 'secondary' ?>"><?= $statusLabels[$c['status']] ?? '' ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-percent-line fs-1 d-block mb-2"></i>Chưa có hoa hồng</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyData = <?= json_encode($monthlyData ?? []) ?>;
            const labels = monthlyData.map(d => d.month);
            const values = monthlyData.map(d => parseFloat(d.total));

            new Chart(document.getElementById('myCommissionChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Hoa hồng',
                        data: values,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
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
