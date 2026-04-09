<?php $pageTitle = 'Bản đồ Check-in'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Bản đồ Check-in</h4>
    <a href="<?= url('checkins') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="card">
    <div class="card-header border-0">
        <form method="GET" action="<?= url('checkins/map') ?>" class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="date_from" class="form-control" value="<?= e($dateFrom ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="date_to" class="form-control" value="<?= e($dateTo ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="ri-equalizer-fill me-1"></i> Lọc</button>
            </div>
        </form>
    </div>

    <div class="card-body">
        <?php $gmapsKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? getenv('GOOGLE_MAPS_API_KEY') ?: ''; ?>
        <?php if (!empty($gmapsKey) && !empty($checkins)): ?>
            <div id="checkinMap" class="rounded border" style="height:500px"></div>
            <script>
            function initMap() {
                var markers = <?= json_encode(array_map(function($c) {
                    return [
                        'lat' => (float)$c['latitude'],
                        'lng' => (float)$c['longitude'],
                        'title' => ($c['user_name'] ?? '') . ' - ' . ($c['contact_first_name'] ?? '') . ' ' . ($c['contact_last_name'] ?? ''),
                        'address' => $c['address'] ?? '',
                        'time' => date('d/m H:i', strtotime($c['created_at'])),
                        'id' => $c['id'],
                    ];
                }, $checkins)) ?>;

                var center = markers.length > 0 ? {lat: markers[0].lat, lng: markers[0].lng} : {lat: 10.7769, lng: 106.7009};
                var map = new google.maps.Map(document.getElementById('checkinMap'), {zoom: 12, center: center});
                var bounds = new google.maps.LatLngBounds();

                markers.forEach(function(m) {
                    var pos = {lat: m.lat, lng: m.lng};
                    var marker = new google.maps.Marker({position: pos, map: map, title: m.title});
                    bounds.extend(pos);
                    var info = new google.maps.InfoWindow({
                        content: '<div style="min-width:200px"><strong>' + m.title + '</strong><br><small>' + m.address + '</small><br><small class="text-muted">' + m.time + '</small><br><a href="/checkins/' + m.id + '">Xem chi tiết</a></div>'
                    });
                    marker.addListener('click', function() { info.open(map, marker); });
                });

                if (markers.length > 1) map.fitBounds(bounds);
            }
            </script>
            <script src="https://maps.googleapis.com/maps/api/js?key=<?= e($gmapsKey) ?>&callback=initMap" async defer></script>
        <?php elseif (empty($gmapsKey)): ?>
            <div id="checkinMap" class="rounded border bg-light d-flex align-items-center justify-content-center" style="height:500px">
                <div class="text-center text-muted">
                    <i class="ri-map-2-line" style="font-size:48px"></i>
                    <p class="mt-2">Thêm Google Maps API key để hiển thị bản đồ</p>
                    <a href="<?= url('settings/api') ?>" class="btn btn-soft-primary"><i class="ri-settings-3-line me-1"></i> Cài đặt API</a>
                </div>
            </div>
        <?php else: ?>
            <div id="checkinMap" class="rounded border bg-light d-flex align-items-center justify-content-center" style="height:500px">
                <div class="text-center text-muted">
                    <i class="ri-map-pin-line" style="font-size:48px"></i>
                    <p class="mt-2">Không có check-in nào trong khoảng thời gian này</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Checkin list under map -->
        <?php if (!empty($checkins)): ?>
        <div class="mt-4">
            <h6 class="mb-3">Danh sách check-in (<?= count($checkins) ?>)</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle table-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nhân viên</th>
                            <th>Khách hàng / DN</th>
                            <th>Tọa độ</th>
                            <th>Địa chỉ</th>
                            <th>Loại</th>
                            <th>Thời gian</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checkins as $ci): ?>
                            <tr>
                                <td><?= e($ci['user_name'] ?? '') ?></td>
                                <td>
                                    <?= e($ci['contact_first_name'] ?? '') ?> <?= e($ci['contact_last_name'] ?? '') ?>
                                    <?php if (!empty($ci['company_name'])): ?>
                                        <div class="text-muted fs-12"><?= e($ci['company_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= $ci['latitude'] ?>, <?= $ci['longitude'] ?></code></td>
                                <td><?= e($ci['address'] ?? '-') ?></td>
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
                                <td><?= date('d/m/Y H:i', strtotime($ci['created_at'])) ?></td>
                                <td>
                                    <a href="<?= url('checkins/' . $ci['id']) ?>" class="btn btn-soft-primary btn-icon"><i class="ri-eye-line"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
            <div class="text-center py-4 text-muted mt-3">
                <p>Không có check-in nào trong khoảng thời gian này</p>
            </div>
        <?php endif; ?>
    </div>
</div>
