<?php $pageTitle = 'Báo cáo doanh thu'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo doanh thu</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Báo cáo</a></li>
                <li class="breadcrumb-item active">Doanh thu</li>
            </ol>
        </div>

        <!-- Year Selector -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('reports/revenue') ?>" class="row g-3 align-items-end">
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

        <!-- Revenue Chart -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Doanh thu theo tháng</h5></div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>

        <div class="row">
            <!-- Top 10 Deal -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Top 10 Deal</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tên Deal</th>
                                        <th>Khách hàng</th>
                                        <th>Công ty</th>
                                        <th class="text-end">Giá trị</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topDeals ?? [] as $i => $deal): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td class="fw-medium"><?= e($deal['title']) ?></td>
                                            <td><?= e($deal['customer'] ?? '-') ?></td>
                                            <td><?= e($deal['company'] ?? '-') ?></td>
                                            <td class="text-end fw-medium"><?= format_money($deal['value']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($topDeals)): ?>
                                        <tr><td colspan="5" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doanh thu theo nhân viên -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Doanh thu theo nhân viên</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nhân viên</th>
                                        <th class="text-end">Doanh thu</th>
                                        <th class="text-end">Số deal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($byOwner ?? [] as $owner): ?>
                                        <tr>
                                            <td class="fw-medium"><?= e($owner['name']) ?></td>
                                            <td class="text-end"><?= format_money($owner['revenue']) ?></td>
                                            <td class="text-end"><?= $owner['count'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($byOwner)): ?>
                                        <tr><td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dealData = <?= json_encode($dealData ?? []) ?>;
            const orderData = <?= json_encode($orderData ?? []) ?>;

            const labels = dealData.map(item => item.label || item.month);
            const dealValues = dealData.map(item => item.value || item.revenue || 0);
            const orderValues = orderData.map(item => item.value || item.revenue || 0);

            new Chart(document.getElementById('revenueChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Doanh thu Deal',
                            data: dealValues,
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Doanh thu Đơn hàng',
                            data: orderValues,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' }
                    },
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
