<?php $pageTitle = 'Báo cáo hoa hồng'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo hoa hồng</h4>
            <a href="<?= url('commissions') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
        </div>

        <!-- Year Selector -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('commissions/report') ?>" class="row g-3 align-items-end">
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

        <!-- Chart -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Hoa hồng theo tháng - nhân viên</h5></div>
            <div class="card-body">
                <canvas id="commissionReportChart" height="100"></canvas>
            </div>
        </div>

        <!-- Summary Table -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Bảng tổng hợp</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nhân viên</th>
                                <?php $monthNames = ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12']; ?>
                                <?php foreach ($monthNames as $mn): ?>
                                    <th class="text-end"><?= $mn ?></th>
                                <?php endforeach; ?>
                                <th class="text-end fw-bold">Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($userData)): ?>
                                <?php $grandTotal = 0; ?>
                                <?php foreach ($userData as $u): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($u['name']) ?></td>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <td class="text-end"><?= $u['months'][$m] > 0 ? format_money($u['months'][$m]) : '-' ?></td>
                                        <?php endfor; ?>
                                        <td class="text-end fw-bold"><?= format_money($u['total']) ?></td>
                                    </tr>
                                    <?php $grandTotal += $u['total']; ?>
                                <?php endforeach; ?>
                                <tr class="table-light fw-bold">
                                    <td>Tổng cộng</td>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <?php $colTotal = array_sum(array_column(array_map(fn($u) => $u['months'], $userData), $m)); ?>
                                        <td class="text-end"><?= $colTotal > 0 ? format_money($colTotal) : '-' ?></td>
                                    <?php endfor; ?>
                                    <td class="text-end"><?= format_money($grandTotal) ?></td>
                                </tr>
                            <?php else: ?>
                                <tr><td colspan="14" class="text-center py-4 text-muted">Chưa có dữ liệu</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userData = <?= json_encode(array_values($userData ?? [])) ?>;
            const labels = ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
            const colors = [
                'rgba(59, 130, 246, 0.7)', 'rgba(16, 185, 129, 0.7)', 'rgba(245, 158, 11, 0.7)',
                'rgba(239, 68, 68, 0.7)', 'rgba(139, 92, 246, 0.7)', 'rgba(236, 72, 153, 0.7)',
                'rgba(20, 184, 166, 0.7)', 'rgba(249, 115, 22, 0.7)', 'rgba(99, 102, 241, 0.7)',
                'rgba(34, 197, 94, 0.7)'
            ];

            const datasets = userData.map(function(user, idx) {
                return {
                    label: user.name,
                    data: Object.values(user.months),
                    backgroundColor: colors[idx % colors.length],
                    borderColor: colors[idx % colors.length].replace('0.7', '1'),
                    borderWidth: 1,
                    borderRadius: 4
                };
            });

            new Chart(document.getElementById('commissionReportChart'), {
                type: 'bar',
                data: { labels: labels, datasets: datasets },
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
