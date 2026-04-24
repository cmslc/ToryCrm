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
        <?php
        // Mark new plugins (created within 30 days)
        $newPluginIds = [];
        $hotPluginSlugs = ['attendance-payroll','gamification'];
        foreach ($plugins as $p) {
            if (!empty($p['created_at']) && strtotime($p['created_at']) > strtotime('-30 days')) $newPluginIds[] = $p['id'];
        }
        // Tenant active map
        $activeMap = [];
        try {
            $tpRows = \Core\Database::fetchAll("SELECT plugin_id, is_active FROM tenant_plugins WHERE tenant_id = ?", [\Core\Database::tenantId()]);
            foreach ($tpRows as $tp) $activeMap[$tp['plugin_id']] = $tp['is_active'];
        } catch (\Exception $e) {}
        ?>

        <!-- Search & Filter -->
        <div class="card mb-2">
            <div class="card-header p-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="search-box flex-grow-1" style="max-width:300px">
                        <input type="text" class="form-control" id="pluginSearch" placeholder="Tìm kiếm plugin..." value="<?= e($filters['search'] ?? '') ?>">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                    <form method="GET" action="<?= url('plugins/marketplace') ?>" id="filterForm" class="d-flex gap-2">
                        <select name="category" class="form-select" style="width:auto;min-width:140px" onchange="this.form.submit()">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($filters['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e(ucfirst($cat)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="sort" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                            <option value="">Mặc định</option>
                            <option value="name" <?= ($filters['sort'] ?? '') === 'name' ? 'selected' : '' ?>>Tên A-Z</option>
                            <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="row" id="pluginGrid">
            <?php if (!empty($plugins)): ?>
                <?php foreach ($plugins as $plugin):
                    $isInstalled = in_array($plugin['id'], $installedIds);
                    $isActive = $isInstalled && ($activeMap[$plugin['id']] ?? 0);
                    $isNew = in_array($plugin['id'], $newPluginIds);
                    $isHot = in_array($plugin['slug'], $hotPluginSlugs);
                ?>
                    <div class="col-xl-4 col-md-6 plugin-card" data-name="<?= e(strtolower($plugin['name'])) ?>" data-desc="<?= e(strtolower($plugin['description'])) ?>">
                        <div class="card card-height-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-<?= $isActive ? 'success' : 'primary' ?>-subtle text-<?= $isActive ? 'success' : 'primary' ?> rounded fs-18">
                                                <i class="<?= e($plugin['icon']) ?>"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?= e($plugin['name']) ?></h6>
                                        <div class="d-flex gap-1 align-items-center flex-wrap">
                                            <span class="badge bg-info-subtle text-info"><?= e(ucfirst($plugin['category'])) ?></span>
                                            <span class="text-muted fs-11">v<?= e($plugin['version']) ?></span>
                                            <?php if ($isNew): ?><span class="badge bg-success">Mới</span><?php endif; ?>
                                            <?php if ($isHot): ?><span class="badge bg-danger">Hot</span><?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($isInstalled): ?>
                                        <form method="POST" action="<?= url('plugins/' . $plugin['id'] . '/toggle') ?>" class="ms-2">
                                            <?= csrf_field() ?>
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" <?= $isActive ? 'checked' : '' ?> onchange="this.closest('form').submit()" title="<?= $isActive ? 'Tắt' : 'Bật' ?>">
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted fs-13 mb-3"><?= e($plugin['description']) ?></p>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted fs-12"><i class="ri-user-line me-1"></i><?= e($plugin['author']) ?></span>
                                    <div class="d-flex gap-1">
                                        <?php if ($isInstalled): ?>
                                            <a href="<?= url('plugins/' . $plugin['id'] . '/configure') ?>" class="btn btn-soft-primary">
                                                <i class="ri-settings-3-line me-1"></i> Cấu hình
                                            </a>
                                            <form method="POST" action="<?= url('plugins/' . $plugin['id'] . '/uninstall') ?>" onsubmit="return confirm('Gỡ cài đặt <?= e($plugin['name']) ?>?')">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-soft-danger" title="Gỡ cài đặt"><i class="ri-uninstall-line"></i></button>
                                            </form>
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
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-plug-line fs-1 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">Không tìm thấy plugin nào</h5>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <script>
        document.getElementById('pluginSearch')?.addEventListener('input', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('.plugin-card').forEach(function(card) {
                var name = card.dataset.name || '';
                var desc = card.dataset.desc || '';
                card.style.display = (name.indexOf(q) >= 0 || desc.indexOf(q) >= 0) ? '' : 'none';
            });
        });
        </script>
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
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-<?= $isActive ? 'success' : $card['color'] ?>-subtle text-<?= $isActive ? 'success' : $card['color'] ?> rounded fs-18">
                                            <i class="<?= $card['icon'] ?>"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1"><?= $card['name'] ?></h6>
                                    <div class="d-flex gap-1 align-items-center">
                                        <?php if ($isActive): ?>
                                            <span class="badge bg-success-subtle text-success">Đã kết nối</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">Chưa kết nối</span>
                                        <?php endif; ?>
                                        <?php if ($card['url'] === '#'): ?>
                                            <span class="badge bg-warning-subtle text-warning">Sắp ra mắt</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted fs-13 mb-3"><?= $card['description'] ?></p>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted fs-12"><i class="ri-link me-1"></i>Tích hợp</span>
                                <?php if ($card['url'] !== '#'): ?>
                                    <a href="<?= $card['url'] ?>" class="btn btn-<?= $isActive ? 'soft-success' : 'primary' ?>">
                                        <i class="ri-settings-3-line me-1"></i> <?= $isActive ? 'Cấu hình' : 'Thiết lập' ?>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-soft-secondary" disabled>
                                        <i class="ri-time-line me-1"></i> Sắp ra mắt
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
