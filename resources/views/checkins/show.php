<?php $pageTitle = 'Chi tiết Check-in'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Chi tiết Check-in #<?= $checkin['id'] ?></h4>
    <a href="<?= url('checkins') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th style="width:180px" class="text-muted">Nhân viên</th>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs flex-shrink-0 me-2">
                                            <span class="avatar-title rounded-circle bg-primary text-white">
                                                <?= strtoupper(substr($checkin['user_name'] ?? 'U', 0, 1)) ?>
                                            </span>
                                        </div>
                                        <span><?= e($checkin['user_name'] ?? '') ?></span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Khách hàng</th>
                                <td>
                                    <?php if (!empty($checkin['contact_first_name'])): ?>
                                        <a href="<?= url('contacts/' . $checkin['contact_id']) ?>"><?= e($checkin['contact_first_name'] . ' ' . ($checkin['contact_last_name'] ?? '')) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Doanh nghiệp</th>
                                <td>
                                    <?php if (!empty($checkin['company_name'])): ?>
                                        <a href="<?= url('companies/' . $checkin['company_id']) ?>"><?= e($checkin['company_name']) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Loại</th>
                                <td>
                                    <?php
                                    $typeBadges = [
                                        'visit' => ['Thăm KH', 'primary'],
                                        'meeting' => ['Họp', 'info'],
                                        'delivery' => ['Giao hàng', 'warning'],
                                        'other' => ['Khác', 'secondary'],
                                    ];
                                    $badge = $typeBadges[$checkin['check_type']] ?? ['Khác', 'secondary'];
                                    ?>
                                    <span class="badge bg-<?= $badge[1] ?>-subtle text-<?= $badge[1] ?>"><?= $badge[0] ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Địa chỉ</th>
                                <td><?= e($checkin['address'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Tọa độ</th>
                                <td>
                                    <code><?= $checkin['latitude'] ?>, <?= $checkin['longitude'] ?></code>
                                    <a href="https://www.google.com/maps?q=<?= $checkin['latitude'] ?>,<?= $checkin['longitude'] ?>" target="_blank" class="ms-2 text-primary">
                                        <i class="ri-external-link-line"></i> Mở Google Maps
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Ghi chú</th>
                                <td><?= nl2br(e($checkin['note'] ?? '-')) ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Thời gian</th>
                                <td><?= date('d/m/Y H:i:s', strtotime($checkin['created_at'])) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Photo -->
        <?php if (!empty($checkin['photo'])): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="ri-image-line me-1"></i> Ảnh check-in</h6>
            </div>
            <div class="card-body text-center">
                <img src="<?= e($checkin['photo']) ?>" alt="Ảnh check-in" class="img-fluid rounded" style="max-height:400px;cursor:pointer" onclick="window.open(this.src)">
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <!-- Map placeholder -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="ri-map-pin-line me-1"></i> Vị trí check-in</h6>
            </div>
            <div class="card-body p-0">
                <div class="rounded-bottom bg-light d-flex align-items-center justify-content-center" style="height:300px">
                    <div class="text-center text-muted">
                        <i class="ri-map-pin-2-fill text-danger" style="font-size:48px"></i>
                        <p class="mt-2 mb-1"><?= $checkin['latitude'] ?>, <?= $checkin['longitude'] ?></p>
                        <a href="https://www.google.com/maps?q=<?= $checkin['latitude'] ?>,<?= $checkin['longitude'] ?>" target="_blank" class="btn btn-soft-primary mt-2">
                            <i class="ri-map-2-line me-1"></i> Xem trên Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
