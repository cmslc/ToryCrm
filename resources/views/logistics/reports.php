<?php $pageTitle = 'Báo cáo Logistics'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Báo cáo Logistics</h4>
</div>

<!-- Date Filter -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('logistics/reports') ?>" class="d-flex align-items-center gap-2">
            <input type="date" name="date_from" class="form-control" style="width:auto" value="<?= e($filters['date_from']) ?>">
            <span class="text-muted">đến</span>
            <input type="date" name="date_to" class="form-control" style="width:auto" value="<?= e($filters['date_to']) ?>">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Xem</button>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="row">
    <div class="col-md-3"><div class="card card-animate"><div class="card-body text-center"><h4 class="mb-1"><?= $receiveStats['total'] ?? 0 ?></h4><p class="text-muted mb-0">Tổng kiện</p></div></div></div>
    <div class="col-md-3"><div class="card card-animate"><div class="card-body text-center"><h4 class="mb-1 text-success"><?= $receiveStats['received'] ?? 0 ?></h4><p class="text-muted mb-0">Đã nhập kho</p></div></div></div>
    <div class="col-md-3"><div class="card card-animate"><div class="card-body text-center"><h4 class="mb-1"><?= ($receiveStats['total_weight'] ?? 0) > 0 ? rtrim(rtrim(number_format($receiveStats['total_weight'], 2), '0'), '.') . ' kg' : '-' ?></h4><p class="text-muted mb-0">Tổng cân nặng</p></div></div></div>
    <div class="col-md-3"><div class="card card-animate"><div class="card-body text-center"><h4 class="mb-1 text-primary"><?= $deliveryStats['total'] ?? 0 ?></h4><p class="text-muted mb-0">Đã giao</p></div></div></div>
</div>

<div class="row">
    <!-- Receive Chart -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Nhập kho theo ngày</h5></div>
            <div class="card-body"><div style="height:300px"><canvas id="receiveChart"></canvas></div></div>
        </div>
    </div>
    <!-- COD + Finance -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Tài chính</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3"><span class="text-muted">Đơn hàng</span><span class="fw-medium"><?= $orderStats['total'] ?? 0 ?> (<?= $orderStats['completed'] ?? 0 ?> hoàn thành)</span></div>
                <div class="d-flex justify-content-between mb-3"><span class="text-muted">Tổng giá trị đơn</span><span class="fw-semibold"><?= format_money($orderStats['total_amount'] ?? 0) ?></span></div>
                <hr>
                <div class="d-flex justify-content-between mb-3"><span class="text-muted">Tổng COD đã thu</span><span class="fw-semibold text-success"><?= format_money($deliveryStats['total_cod'] ?? 0) ?></span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-muted fs-12">Tiền mặt</span><span><?= format_money($deliveryStats['cod_cash'] ?? 0) ?></span></div>
                <div class="d-flex justify-content-between"><span class="text-muted fs-12">Chuyển khoản</span><span><?= format_money($deliveryStats['cod_transfer'] ?? 0) ?></span></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('receiveChart');
    if (!ctx) return;
    var data = <?= json_encode($dailyReceive) ?>;
    new Chart(ctx, {
        data: {
            labels: data.map(function(d) { return d.day; }),
            datasets: [
                { type: 'bar', label: 'Kiện nhập', data: data.map(function(d) { return d.count; }), backgroundColor: 'rgba(10,179,156,0.8)', borderRadius: 4, order: 1 },
                { type: 'line', label: 'Cân nặng (kg)', data: data.map(function(d) { return d.weight; }), borderColor: '#405189', borderWidth: 2, pointRadius: 3, fill: false, tension: 0.4, yAxisID: 'y1', order: 0 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                y1: { beginAtZero: true, position: 'right', grid: { display: false } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
