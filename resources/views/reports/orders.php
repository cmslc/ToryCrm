<?php $pageTitle = 'Báo cáo đơn hàng'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Báo cáo đơn hàng</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Báo cáo</a></li>
        <li class="breadcrumb-item active">Đơn hàng</li>
    </ol>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Năm</label>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- KPI -->
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-info-subtle rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-shopping-bag-line text-info fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">Tổng đơn hàng</p>
                        <h4 class="mb-0"><?= number_format($totalOrders) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-money-dollar-circle-line text-success fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">Tổng doanh thu</p>
                        <h4 class="mb-0"><?= number_format($totalRevenue) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-warning-subtle rounded me-3 d-flex align-items-center justify-content-center">
                        <i class="ri-calculator-line text-warning fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0">TB giá trị đơn</p>
                        <h4 class="mb-0"><?= number_format($avgOrderValue) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Orders by month -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Đơn hàng theo tháng</h5></div>
            <div class="card-body">
                <canvas id="orderChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- By status -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Theo trạng thái</h5></div>
            <div class="card-body">
                <?php
                $statusLabels = ['pending'=>'Chờ xử lý','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý','completed'=>'Hoàn thành','cancelled'=>'Đã hủy','returned'=>'Hoàn trả'];
                $statusColors = ['pending'=>'warning','confirmed'=>'info','processing'=>'primary','completed'=>'success','cancelled'=>'danger','returned'=>'secondary'];
                ?>
                <?php foreach ($byStatus as $s): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-<?= $statusColors[$s['status']] ?? 'secondary' ?>-subtle text-<?= $statusColors[$s['status']] ?? 'secondary' ?>"><?= $statusLabels[$s['status']] ?? $s['status'] ?></span>
                    <span><?= number_format($s['count']) ?> đơn — <?= number_format($s['total_value']) ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($byStatus)): ?>
                    <p class="text-muted text-center py-3">Chưa có dữ liệu</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top products -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Sản phẩm bán chạy</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Sản phẩm</th><th class="text-end">SL bán</th><th class="text-end">Doanh thu</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topProducts as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= e($p['name']) ?></td>
                                <td class="text-end"><?= number_format($p['qty']) ?></td>
                                <td class="text-end fw-semibold"><?= number_format($p['revenue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topProducts)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- By salesperson -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Theo nhân viên bán</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Nhân viên</th><th class="text-end">Số đơn</th><th class="text-end">Doanh thu</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bySales as $s): ?>
                            <tr>
                                <td><?= e($s['name']) ?></td>
                                <td class="text-end"><?= number_format($s['count']) ?></td>
                                <td class="text-end fw-semibold"><?= number_format($s['revenue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bySales)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
var months = ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'];
new Chart(document.getElementById('orderChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [
            { label: 'Số đơn', data: <?= json_encode($monthCount) ?>, backgroundColor: '#405189', yAxisID: 'y' },
            { label: 'Doanh thu', data: <?= json_encode($monthRevenue) ?>, type: 'line', borderColor: '#0ab39c', yAxisID: 'y1', fill: false }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, position: 'left', ticks: { stepSize: 1 } },
            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
        }
    }
});
</script>
