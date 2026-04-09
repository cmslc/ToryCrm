<?php $pageTitle = 'Tích hợp'; ?>

        <div class="page-title-box">
            <h4 class="mb-0">Trung tâm tích hợp</h4>
        </div>

        <?php $flashMsg = flash(); if ($flashMsg): ?>
            <div class="alert alert-<?= $flashMsg['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= e($flashMsg['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php
            $cards = [
                [
                    'type' => 'zalo_oa',
                    'name' => 'Zalo OA',
                    'description' => 'Kết nối Zalo Official Account để gửi/nhận tin nhắn, quản lý follower và chăm sóc khách hàng.',
                    'icon' => 'ri-message-3-line',
                    'color' => 'primary',
                    'url' => url('integrations/zalo'),
                ],
                [
                    'type' => 'voip_stringee',
                    'name' => 'VoIP / Stringee',
                    'description' => 'Tổng đài ảo với tính năng Click-to-Call, ghi nhận lịch sử cuộc gọi và quản lý extension.',
                    'icon' => 'ri-phone-line',
                    'color' => 'success',
                    'url' => url('integrations/voip'),
                ],
                [
                    'type' => 'google_calendar',
                    'name' => 'Google Calendar',
                    'description' => 'Đồng bộ lịch hẹn CRM với Google Calendar, tự động tạo sự kiện khi đặt lịch.',
                    'icon' => 'ri-calendar-2-line',
                    'color' => 'warning',
                    'url' => '#',
                ],
                [
                    'type' => 'vnpay',
                    'name' => 'VNPay',
                    'description' => 'Tích hợp cổng thanh toán VNPay để nhận thanh toán trực tuyến từ khách hàng.',
                    'icon' => 'ri-bank-card-line',
                    'color' => 'info',
                    'url' => '#',
                ],
                [
                    'type' => 'momo',
                    'name' => 'MoMo',
                    'description' => 'Kết nối ví MoMo để nhận thanh toán qua QR code và ví điện tử.',
                    'icon' => 'ri-wallet-line',
                    'color' => 'danger',
                    'url' => '#',
                ],
            ];
            ?>

            <?php foreach ($cards as $card): ?>
                <?php
                    $intData = $statusMap[$card['type']] ?? null;
                    $isActive = $intData && ($intData['is_active'] ?? 0);
                    $lastSync = $intData['updated_at'] ?? null;
                ?>
                <div class="col-xl-4 col-md-6">
                    <div class="card card-height-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-md flex-shrink-0">
                                    <div class="avatar-title bg-<?= $card['color'] ?>-subtle text-<?= $card['color'] ?> rounded fs-24">
                                        <i class="<?= $card['icon'] ?>"></i>
                                    </div>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <h5 class="mb-1"><?= $card['name'] ?></h5>
                                    <?php if ($isActive): ?>
                                        <span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-line me-1"></i>Đã kết nối</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Chưa kết nối</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p class="text-muted mb-3"><?= $card['description'] ?></p>
                            <?php if ($isActive && $lastSync): ?>
                                <p class="text-muted mb-3"><small><i class="ri-time-line me-1"></i>Cập nhật: <?= date('d/m/Y H:i', strtotime($lastSync)) ?></small></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer border-top">
                            <?php if ($card['url'] !== '#'): ?>
                                <a href="<?= $card['url'] ?>" class="btn btn-<?= $isActive ? 'soft-' . $card['color'] : 'primary' ?> w-100">
                                    <i class="ri-settings-3-line me-1"></i> <?= $isActive ? 'Cấu hình' : 'Thiết lập' ?>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-soft-secondary w-100" disabled>
                                    <i class="ri-time-line me-1"></i> Sắp ra mắt
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
