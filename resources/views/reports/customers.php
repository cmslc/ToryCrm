<?php $pageTitle = 'Báo cáo khách hàng'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo cáo khách hàng</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Báo cáo</a></li>
                <li class="breadcrumb-item active">Khách hàng</li>
            </ol>
        </div>

        <!-- Chart: KH theo tháng -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Khách hàng mới theo tháng (<?= date('Y') ?>)</h5></div>
                    <div class="card-body">
                        <canvas id="monthChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Theo nguồn -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Theo nguồn</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>Nguồn</th><th class="text-end">Số lượng</th></tr></thead>
                                <tbody>
                                    <?php foreach ($bySource ?? [] as $row): ?>
                                    <tr>
                                        <td><span class="badge" style="background:<?= $row['color'] ?>">&nbsp;</span> <?= e($row['name']) ?></td>
                                        <td class="text-end fw-medium"><?= $row['count'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($bySource)): ?><tr><td colspan="2" class="text-center text-muted">Chưa có dữ liệu</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theo trạng thái -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Theo trạng thái</h5></div>
                    <div class="card-body">
                        <?php
                        $statusLabels = ['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất'];
                        $statusColors = ['new'=>'info','contacted'=>'primary','qualified'=>'warning','converted'=>'success','lost'=>'danger'];
                        ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>Trạng thái</th><th class="text-end">Số lượng</th></tr></thead>
                                <tbody>
                                    <?php foreach ($byStatus ?? [] as $row): ?>
                                    <tr>
                                        <td><span class="badge bg-<?= $statusColors[$row['status']] ?? 'secondary' ?>"><?= $statusLabels[$row['status']] ?? $row['status'] ?></span></td>
                                        <td class="text-end fw-medium"><?= $row['count'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Theo người phụ trách -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Theo người phụ trách</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>Nhân viên</th><th class="text-end">KH phụ trách</th></tr></thead>
                                <tbody>
                                    <?php foreach ($byOwner ?? [] as $row): ?>
                                    <tr>
                                        <td><?= e($row['name']) ?></td>
                                        <td class="text-end fw-medium"><?= $row['count'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Theo công ty -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Top công ty</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>Công ty</th><th class="text-end">Số KH</th></tr></thead>
                                <tbody>
                                    <?php foreach ($byCompany ?? [] as $row): ?>
                                    <tr>
                                        <td><?= e($row['name']) ?></td>
                                        <td class="text-end fw-medium"><?= $row['count'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('monthChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
                        datasets: [{
                            label: 'Khách hàng mới',
                            data: <?= json_encode($monthData ?? array_fill(0, 12, 0)) ?>,
                            backgroundColor: 'rgba(64, 81, 137, 0.85)',
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            }
        });
        </script>
