<?php $pageTitle = 'Check-in'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Check-in</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('checkins/map') ?>" class="btn btn-soft-info"><i class="ri-map-2-line me-1"></i> Bản đồ</a>
        <a href="<?= url('checkins/create') ?>" class="btn btn-primary"><i class="ri-map-pin-add-line me-1"></i> Check-in ngay</a>
    </div>
</div>

<!-- Stats Row -->
<div class="row">
    <div class="col-md-4">
        <div class="card card-animate">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2">
                        <span class="avatar-title bg-success-subtle rounded-circle">
                            <i class="ri-map-pin-line text-success"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= $statsToday ?? 0 ?></h5>
                        <p class="text-muted mb-0 fs-12">Hôm nay</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-animate">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2">
                        <span class="avatar-title bg-info-subtle rounded-circle">
                            <i class="ri-calendar-check-line text-info"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= $statsWeek ?? 0 ?></h5>
                        <p class="text-muted mb-0 fs-12">Tuần này</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-animate">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2">
                        <span class="avatar-title bg-primary-subtle rounded-circle">
                            <i class="ri-bar-chart-line text-primary"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= $statsMonth ?? 0 ?></h5>
                        <p class="text-muted mb-0 fs-12">Tháng này</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters + Table -->
<div class="card">
    <div class="card-header border-0">
        <form method="GET" action="<?= url('checkins') ?>" class="row g-2 align-items-center">
            <div class="col-md-2">
                <select name="user_id" class="form-select">
                    <option value="">Nhân viên</option>
                    <?php foreach ($users ?? [] as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($filters['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" placeholder="Từ ngày" value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" placeholder="Đến ngày" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <div class="search-box">
                    <input type="text" class="form-control search" name="contact" placeholder="Tìm KH, doanh nghiệp..." value="<?= e($filters['contact'] ?? '') ?>">
                    <i class="ri-search-line search-icon"></i>
                </div>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary"><i class="ri-equalizer-fill me-1"></i> Lọc</button>
                <a href="<?= url('checkins') ?>" class="btn btn-soft-secondary"><i class="ri-refresh-line"></i></a>
            </div>
        </form>
    </div>

    <!-- Toggle View -->
    <div class="card-header border-0 pt-0">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-soft-primary active" id="btnListView" onclick="toggleView('list')">
                <i class="ri-list-check me-1"></i> Danh sách
            </button>
            <button type="button" class="btn btn-soft-primary" id="btnMapView" onclick="toggleView('map')">
                <i class="ri-map-2-line me-1"></i> Bản đồ
            </button>
        </div>
    </div>

    <!-- List View -->
    <div class="card-body" id="listView">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nhân viên</th>
                        <th>Khách hàng / DN</th>
                        <th>Địa chỉ</th>
                        <th>Ghi chú</th>
                        <th>Loại</th>
                        <th>Thời gian</th>
                        <th>Ảnh</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($checkins)): ?>
                        <?php foreach ($checkins as $ci): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs flex-shrink-0 me-2">
                                            <span class="avatar-title rounded-circle bg-primary text-white">
                                                <?= strtoupper(substr($ci['user_name'] ?? 'U', 0, 1)) ?>
                                            </span>
                                        </div>
                                        <span><?= e($ci['user_name'] ?? '') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($ci['contact_first_name'])): ?>
                                        <a href="<?= url('contacts/' . $ci['contact_id']) ?>"><?= e($ci['contact_first_name'] . ' ' . ($ci['contact_last_name'] ?? '')) ?></a>
                                    <?php endif; ?>
                                    <?php if (!empty($ci['company_name'])): ?>
                                        <div class="text-muted fs-12"><?= e($ci['company_name']) ?></div>
                                    <?php endif; ?>
                                    <?php if (empty($ci['contact_first_name']) && empty($ci['company_name'])): ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width:200px" title="<?= e($ci['address'] ?? '') ?>">
                                        <?= e($ci['address'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width:150px" title="<?= e($ci['note'] ?? '') ?>">
                                        <?= e($ci['note'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $typeBadges = [
                                        'visit' => ['Thăm KH', 'primary'],
                                        'meeting' => ['Họp', 'info'],
                                        'delivery' => ['Giao hàng', 'warning'],
                                        'other' => ['Khác', 'secondary'],
                                    ];
                                    $badge = $typeBadges[$ci['check_type']] ?? ['Khác', 'secondary'];
                                    ?>
                                    <span class="badge bg-<?= $badge[1] ?>-subtle text-<?= $badge[1] ?>"><?= $badge[0] ?></span>
                                </td>
                                <td>
                                    <span class="text-muted"><?= date('d/m/Y H:i', strtotime($ci['created_at'])) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($ci['photo'])): ?>
                                        <img src="<?= e($ci['photo']) ?>" alt="Ảnh check-in" class="rounded" style="width:40px;height:40px;object-fit:cover;cursor:pointer" onclick="window.open(this.src)">
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= url('checkins/' . $ci['id']) ?>" class="btn btn-soft-primary btn-icon" title="Xem chi tiết">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="ri-map-pin-line fs-1 d-block mb-2"></i>
                                Chưa có check-in nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($totalPages ?? 0) > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">Tổng: <?= $total ?> check-in</div>
                <nav>
                    <ul class="pagination mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= url('checkins?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- Map View (hidden by default) -->
    <div class="card-body d-none" id="mapView">
        <div id="checkinMap" class="rounded border bg-light d-flex align-items-center justify-content-center" style="height:500px">
            <div class="text-center text-muted">
                <i class="ri-map-2-line" style="font-size:48px"></i>
                <p class="mt-2">Tích hợp Google Maps API để hiển thị bản đồ</p>
                <p class="fs-12">Các check-in sẽ được hiển thị dưới dạng marker trên bản đồ</p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleView(view) {
    var listView = document.getElementById('listView');
    var mapView = document.getElementById('mapView');
    var btnList = document.getElementById('btnListView');
    var btnMap = document.getElementById('btnMapView');

    if (view === 'map') {
        listView.classList.add('d-none');
        mapView.classList.remove('d-none');
        btnList.classList.remove('active');
        btnMap.classList.add('active');
    } else {
        listView.classList.remove('d-none');
        mapView.classList.add('d-none');
        btnList.classList.add('active');
        btnMap.classList.remove('active');
    }
}
</script>
