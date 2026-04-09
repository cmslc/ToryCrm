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
        <div id="checkinMap" class="rounded border bg-light d-flex align-items-center justify-content-center" style="height:500px">
            <div class="text-center text-muted">
                <i class="ri-map-2-line" style="font-size:48px"></i>
                <p class="mt-2">Tích hợp Google Maps API để hiển thị bản đồ</p>
                <p class="fs-12">Các check-in sẽ được hiển thị dưới dạng marker trên bản đồ</p>
            </div>
        </div>

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
