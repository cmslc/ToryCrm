<?php
$pageTitle = 'Ngân sách: ' . $budget['name'];
$sc = ['draft'=>'secondary','active'=>'success','closed'=>'dark'];
$sl = ['draft'=>'Nháp','active'=>'Đang hoạt động','closed'=>'Đã đóng'];
$tc = ['department'=>'primary','project'=>'info','campaign'=>'warning','general'=>'secondary'];
$tl = ['department'=>'Phòng ban','project'=>'Dự án','campaign'=>'Chiến dịch','general'=>'Chung'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($budget['name']) ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('budgets') ?>">Ngân sách</a></li>
                <li class="breadcrumb-item active"><?= e($budget['name']) ?></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-9">
                <!-- Header card -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= e($budget['name']) ?></h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-<?= $sc[$budget['status']] ?? 'secondary' ?>"><?= $sl[$budget['status']] ?? '' ?></span>
                                <span class="badge bg-<?= $tc[$budget['type']] ?? 'secondary' ?>-subtle text-<?= $tc[$budget['type']] ?? 'secondary' ?>"><?= $tl[$budget['type']] ?? $budget['type'] ?></span>
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="<?= url('budgets/' . $budget['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                            <?php if ($budget['status'] === 'draft'): ?>
                                <form method="POST" action="<?= url('budgets/' . $budget['id'] . '/approve') ?>" class="d-inline" data-confirm="Duyệt ngân sách này?">
                                    <?= csrf_field() ?><button class="btn btn-soft-success"><i class="ri-check-line me-1"></i>Duyệt</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($budget['status'] === 'active'): ?>
                                <form method="POST" action="<?= url('budgets/' . $budget['id'] . '/close') ?>" class="d-inline" data-confirm="Đóng ngân sách?">
                                    <?= csrf_field() ?><button class="btn btn-soft-warning"><i class="ri-lock-line me-1"></i>Đóng</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Overall Progress -->
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <p class="text-muted mb-1">Tổng ngân sách</p>
                                <h4 class="text-primary"><?= format_money($budget['total_budget']) ?></h4>
                            </div>
                            <div class="col-md-4 text-center">
                                <p class="text-muted mb-1">Đã chi</p>
                                <h4 class="text-danger"><?= format_money($budget['total_spent']) ?></h4>
                            </div>
                            <div class="col-md-4 text-center">
                                <p class="text-muted mb-1">Còn lại</p>
                                <h4 class="<?= $budget['remaining'] < 0 ? 'text-danger' : 'text-success' ?>"><?= format_money($budget['remaining']) ?></h4>
                            </div>
                        </div>

                        <?php $util = min(100, $budget['utilization']); ?>
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Tỷ lệ sử dụng</span>
                            <span class="fw-medium"><?= $budget['utilization'] ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-<?= $budget['utilization'] > 100 ? 'danger' : ($budget['utilization'] > 80 ? 'warning' : 'success') ?>"
                                 style="width: <?= $util ?>%"></div>
                        </div>
                    </div>
                </div>

                <!-- Chart: Planned vs Actual -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Biểu đồ: Kế hoạch vs Thực tế</h5></div>
                    <div class="card-body">
                        <canvas id="budgetChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Chi tiết hạng mục</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Hạng mục</th>
                                        <th class="text-end">Kế hoạch</th>
                                        <th class="text-end">Thực tế</th>
                                        <th class="text-end">Chênh lệch</th>
                                        <th class="text-end">% Sử dụng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($item['category']) ?></td>
                                        <td class="text-end"><?= format_money($item['planned_amount']) ?></td>
                                        <td class="text-end"><?= format_money($item['actual_amount']) ?></td>
                                        <td class="text-end <?= $item['variance'] < 0 ? 'text-danger fw-bold' : 'text-success' ?>">
                                            <?= $item['variance'] < 0 ? '-' : '+' ?><?= format_money(abs($item['variance'])) ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <span><?= $item['percent_used'] ?>%</span>
                                                <div class="progress" style="height:6px; width:60px">
                                                    <?php $pct = min(100, $item['percent_used']); ?>
                                                    <div class="progress-bar bg-<?= $item['percent_used'] > 100 ? 'danger' : ($item['percent_used'] > 80 ? 'warning' : 'success') ?>"
                                                         style="width:<?= $pct ?>%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td>Tổng cộng</td>
                                        <td class="text-end"><?= format_money($budget['total_budget']) ?></td>
                                        <td class="text-end"><?= format_money($budget['total_spent']) ?></td>
                                        <td class="text-end <?= $budget['remaining'] < 0 ? 'text-danger' : 'text-success' ?>">
                                            <?= $budget['remaining'] < 0 ? '-' : '+' ?><?= format_money(abs($budget['remaining'])) ?>
                                        </td>
                                        <td class="text-end"><?= $budget['utilization'] ?>%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($budget['notes']): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Ghi chú</h5></div>
                    <div class="card-body"><?= nl2br(e($budget['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Loại</span>
                            <span class="badge bg-<?= $tc[$budget['type']] ?? 'secondary' ?>-subtle text-<?= $tc[$budget['type']] ?? 'secondary' ?>"><?= $tl[$budget['type']] ?? $budget['type'] ?></span>
                        </div>
                        <?php if ($budget['department']): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Phòng ban</span>
                            <span><?= e($budget['department']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Kỳ bắt đầu</span>
                            <span><?= $budget['period_start'] ? format_date($budget['period_start']) : '-' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Kỳ kết thúc</span>
                            <span><?= $budget['period_end'] ? format_date($budget['period_end']) : '-' ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Người tạo</span>
                            <span><?= e($budget['created_by_name'] ?? '-') ?></span>
                        </div>
                        <?php if ($budget['approved_by_name']): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Người duyệt</span>
                            <span><?= e($budget['approved_by_name']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($budget['approved_at'])): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Ngày duyệt</span>
                            <span><?= format_datetime($budget['approved_at']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Ngày tạo</span>
                            <span><?= format_datetime($budget['created_at']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const items = <?= json_encode($items) ?>;
            const labels = items.map(i => i.category);
            const planned = items.map(i => parseFloat(i.planned_amount));
            const actual = items.map(i => parseFloat(i.actual_amount));

            if (typeof Chart !== 'undefined') {
                new Chart(document.getElementById('budgetChart'), {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Kế hoạch',
                                data: planned,
                                backgroundColor: 'rgba(64, 81, 137, 0.6)',
                                borderColor: '#405189',
                                borderWidth: 1,
                            },
                            {
                                label: 'Thực tế',
                                data: actual,
                                backgroundColor: 'rgba(247, 184, 75, 0.6)',
                                borderColor: '#f7b84b',
                                borderWidth: 1,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value) + ' ₫';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
        </script>
