<?php
$pageTitle = 'Thêm plugin';
$tab = $_GET['tab'] ?? 'plugins';
$integrationCards = [
    ['type'=>'zalo_oa','name'=>'Zalo OA','description'=>'Kết nối Zalo Official Account để gửi/nhận tin nhắn, quản lý follower.','icon'=>'ri-message-3-line','color'=>'primary','url'=>url('integrations/zalo')],
    ['type'=>'voip_stringee','name'=>'VoIP / Stringee','description'=>'Tổng đài ảo Click-to-Call, ghi nhận cuộc gọi, quản lý extension.','icon'=>'ri-phone-line','color'=>'success','url'=>url('integrations/voip')],
    ['type'=>'google_calendar','name'=>'Google Calendar','description'=>'Đồng bộ lịch hẹn CRM với Google Calendar.','icon'=>'ri-calendar-2-line','color'=>'warning','url'=>'#'],
    ['type'=>'vnpay','name'=>'VNPay','description'=>'Cổng thanh toán VNPay, nhận thanh toán trực tuyến.','icon'=>'ri-bank-card-line','color'=>'info','url'=>'#'],
    ['type'=>'momo','name'=>'MoMo','description'=>'Kết nối ví MoMo, thanh toán QR code.','icon'=>'ri-wallet-line','color'=>'danger','url'=>'#'],
    ['type'=>'webhook','name'=>'Webhook','description'=>'Gửi dữ liệu tự động đến hệ thống bên ngoài khi có sự kiện.','icon'=>'ri-links-line','color'=>'secondary','url'=>url('webhooks')],
];
// Integration status
$intStatusMap = [];
try {
    $intRows = \Core\Database::fetchAll("SELECT type, is_active, updated_at FROM integrations WHERE tenant_id = ?", [\Core\Database::tenantId()]);
    foreach ($intRows as $r) $intStatusMap[$r['type']] = $r;
} catch (\Exception $e) {}
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Thêm plugin</h4>
    <a href="<?= url('plugins') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Plugin đã cài</a>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'plugins' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tabPlugins" role="tab">
            <i class="ri-apps-line me-1"></i> Plugin <span class="badge bg-primary-subtle text-primary ms-1"><?= count($plugins ?? []) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'integrations' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tabIntegrations" role="tab">
            <i class="ri-link me-1"></i> Tích hợp <span class="badge bg-info-subtle text-info ms-1"><?= count($integrationCards) ?></span>
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- Plugins Tab -->
    <div class="tab-pane <?= $tab === 'plugins' ? 'active' : '' ?>" id="tabPlugins">
        <!-- Search & Filter -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('plugins/marketplace') ?>" class="row g-3">
                    <div class="col-md-6">
                        <div class="search-box">
                            <input type="text" class="form-control" name="search" placeholder="Tìm kiếm plugin..." value="<?= e($filters['search'] ?? '') ?>">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= ($filters['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e(ucfirst($cat)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="ri-search-line me-1"></i> Lọc</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($plugins)): ?>
                <?php foreach ($plugins as $plugin): ?>
                    <?php $isInstalled = in_array($plugin['id'], $installedIds); ?>
                    <div class="col-xl-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-primary-subtle text-primary rounded fs-18">
                                                <i class="<?= e($plugin['icon']) ?>"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-1"><?= e($plugin['name']) ?></h5>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge bg-info-subtle text-info"><?= e(ucfirst($plugin['category'])) ?></span>
                                            <span class="text-muted">v<?= e($plugin['version']) ?></span>
                                        </div>
                                    </div>
                                    <?php if ($isInstalled): ?>
                                        <span class="badge bg-success-subtle text-success">Đã cài</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted mb-3"><?= e($plugin['description']) ?></p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted"><i class="ri-user-line me-1"></i><?= e($plugin['author']) ?></span>
                                    <?php if ($isInstalled): ?>
                                        <a href="<?= url('plugins/' . $plugin['id'] . '/configure') ?>" class="btn btn-soft-primary">
                                            <i class="ri-settings-3-line me-1"></i> Cấu hình
                                        </a>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('plugins/' . $plugin['id'] . '/install') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ri-download-line me-1"></i> Cài đặt
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-store-2-line fs-1 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">Không tìm thấy plugin nào</h5>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Integrations Tab -->
    <div class="tab-pane <?= $tab === 'integrations' ? 'active' : '' ?>" id="tabIntegrations">
        <div class="row">
            <?php foreach ($integrationCards as $card): ?>
                <?php
                    $intData = $intStatusMap[$card['type']] ?? null;
                    $isActive = $intData && ($intData['is_active'] ?? 0);
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
    </div>
</div>
