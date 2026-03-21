<?php $pageTitle = 'Báo cáo khách hàng'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Báo cáo khách hàng</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Báo cáo</a></li>
        <li class="breadcrumb-item active">Khách hàng</li>
    </ol>
</div>

<!-- Date Filter -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="<?= url('reports/customers') ?>" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Khoảng thời gian</label>
                <select name="period" class="form-select" onchange="if(this.value) this.form.submit()">
                    <option value="">Tùy chọn</option>
                    <?php foreach (['today'=>'Hôm nay','yesterday'=>'Hôm qua','this_week'=>'Tuần này','this_month'=>'Tháng này','this_quarter'=>'Quý này','this_year'=>'Năm nay'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($filters['period'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Từ ngày</label>
                <input type="date" class="form-control" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Đến ngày</label>
                <input type="date" class="form-control" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="ri-filter-line me-1"></i> Lọc</button>
                <a href="<?= url('reports/customers') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
            </div>
        </form>
    </div>
</div>

<!-- Chart -->
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Khách hàng mới theo tháng (<?= date('Y') ?>)</h5></div>
    <div class="card-body"><div style="height:280px"><canvas id="monthChart"></canvas></div></div>
</div>

<div class="row">
    <!-- Theo nhóm KH -->
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-group-line me-1"></i> Theo nhóm KH</h5></div>
            <div class="card-body">
                <?php foreach ($byType ?? [] as $row): ?>
                <div class="d-flex align-items-center mb-3">
                    <span class="badge me-2" style="background:<?= safe_color($row['color'] ?? null) ?>;min-width:10px">&nbsp;</span>
                    <span class="flex-grow-1"><?= e($row['name']) ?></span>
                    <span class="fw-semibold"><?= $row['count'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($byType)): ?><p class="text-muted text-center mb-0">Chưa có dữ liệu</p><?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Theo nguồn -->
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-links-line me-1"></i> Theo nguồn</h5></div>
            <div class="card-body">
                <?php foreach ($bySource ?? [] as $row): ?>
                <div class="d-flex align-items-center mb-3">
                    <span class="badge me-2" style="background:<?= safe_color($row['color'] ?? null) ?>;min-width:10px">&nbsp;</span>
                    <span class="flex-grow-1"><?= e($row['name']) ?></span>
                    <span class="fw-semibold"><?= $row['count'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($bySource)): ?><p class="text-muted text-center mb-0">Chưa có dữ liệu</p><?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Theo trạng thái -->
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-bar-chart-line me-1"></i> Theo trạng thái</h5></div>
            <div class="card-body">
                <?php
                $sl = ['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất'];
                $sc = ['new'=>'info','contacted'=>'primary','qualified'=>'warning','converted'=>'success','lost'=>'danger'];
                foreach ($byStatus ?? [] as $row): ?>
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-<?= $sc[$row['status']] ?? 'secondary' ?> me-2"><?= $sl[$row['status']] ?? $row['status'] ?></span>
                    <div class="flex-grow-1"><div class="progress" style="height:6px"><div class="progress-bar bg-<?= $sc[$row['status']] ?? 'secondary' ?>" style="width:<?= min(100, $row['count'] * 5) ?>%"></div></div></div>
                    <span class="fw-semibold ms-2"><?= $row['count'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (empty($byStatus)): ?><p class="text-muted text-center mb-0">Chưa có dữ liệu</p><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Theo User -->
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-user-star-line me-1"></i> Theo người phụ trách</h5></div>
            <div class="card-body">
                <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Nhân viên</th><th class="text-end">Số KH</th></tr></thead>
                    <tbody>
                        <?php foreach ($byOwner ?? [] as $row): ?>
                        <tr><td><?= e($row['name']) ?></td><td class="text-end fw-semibold"><?= $row['count'] ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($byOwner)): ?><tr><td colspan="2" class="text-center text-muted">Chưa có dữ liệu</td></tr><?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <!-- Theo Tỉnh -->
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-map-pin-line me-1"></i> Theo Tỉnh/TP</h5></div>
            <div class="card-body">
                <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Tỉnh/TP</th><th class="text-end">Số KH</th></tr></thead>
                    <tbody>
                        <?php foreach ($byProvince ?? [] as $row): ?>
                        <tr><td><?= e($row['province']) ?></td><td class="text-end fw-semibold"><?= $row['count'] ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($byProvince)): ?><tr><td colspan="2" class="text-center text-muted">Chưa có dữ liệu</td></tr><?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <!-- Theo Ngành -->
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-building-4-line me-1"></i> Theo ngành nghề</h5></div>
            <div class="card-body">
                <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Ngành</th><th class="text-end">Số KH</th></tr></thead>
                    <tbody>
                        <?php foreach ($byIndustry ?? [] as $row): ?>
                        <tr><td><?= e($row['industry']) ?></td><td class="text-end fw-semibold"><?= $row['count'] ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($byIndustry)): ?><tr><td colspan="2" class="text-center text-muted">Chưa có dữ liệu</td></tr><?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Mối quan hệ -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-hearts-line me-1"></i> Trạng thái mối quan hệ</h5></div>
            <div class="card-body">
                <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Mối quan hệ</th><th class="text-end">Số KH</th></tr></thead>
                    <tbody>
                        <?php foreach ($byRelation ?? [] as $row): ?>
                        <tr><td><?= e($row['relation']) ?></td><td class="text-end fw-semibold"><?= $row['count'] ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($byRelation)): ?><tr><td colspan="2" class="text-center text-muted">Chưa có dữ liệu</td></tr><?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
    <!-- Top công ty -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-building-line me-1"></i> Top công ty</h5></div>
            <div class="card-body">
                <div class="table-responsive"><table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Công ty</th><th class="text-end">Số KH</th></tr></thead>
                    <tbody>
                        <?php foreach ($byCompany ?? [] as $row): ?>
                        <tr><td><?= e($row['name']) ?></td><td class="text-end fw-semibold"><?= $row['count'] ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($byCompany)): ?><tr><td colspan="2" class="text-center text-muted">Chưa có dữ liệu</td></tr><?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('monthChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
                datasets: [{ label: 'KH mới', data: <?= json_encode($monthData ?? array_fill(0, 12, 0)) ?>,
                    backgroundColor: 'rgba(64,81,137,0.85)', borderRadius: 4, borderWidth: 0, barPercentage: 0.6 }]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
            }
        });
    }
});
</script>
