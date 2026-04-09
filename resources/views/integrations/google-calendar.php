<?php $pageTitle = 'Google Calendar'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tích hợp Google Calendar</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
                    <li class="breadcrumb-item active">Google Calendar</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php $flashMsg = flash(); if ($flashMsg): ?>
    <div class="alert alert-<?= $flashMsg['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <?= e($flashMsg['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Config Form -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">
                    <i class="ri-google-fill me-1 text-danger"></i> Cấu hình Google Calendar
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-1"></i>
                    Truy cập <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="fw-medium">Google Cloud Console</a>
                    để tạo OAuth 2.0 Client ID. Thêm URI chuyển hướng:
                    <code><?= e(($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/integrations/google-calendar/callback') ?></code>
                </div>
                <form method="POST" action="<?= url('integrations/google-calendar') ?>">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client ID</label>
                            <input type="text" class="form-control" name="client_id"
                                   value="<?= e($config['client_id'] ?? '') ?>"
                                   placeholder="xxxxxxxxx.apps.googleusercontent.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client Secret</label>
                            <input type="password" class="form-control" name="client_secret"
                                   value="<?= e($config['client_secret'] ?? '') ?>"
                                   placeholder="GOCSPX-xxxxxxxxx">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">URI chuyển hướng (Redirect URI)</label>
                            <input type="text" class="form-control" name="redirect_uri"
                                   value="<?= e($config['redirect_uri'] ?? '') ?>"
                                   placeholder="Để trống để dùng mặc định">
                            <small class="text-muted">Để trống sẽ tự động dùng URL mặc định của hệ thống.</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Lưu cấu hình
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Connection Status -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Trạng thái kết nối</h5>
            </div>
            <div class="card-body text-center">
                <?php if ($isConnected): ?>
                    <div class="avatar-md mx-auto mb-3">
                        <div class="avatar-title rounded-circle bg-success-subtle text-success fs-24">
                            <i class="ri-check-double-line"></i>
                        </div>
                    </div>
                    <h6 class="text-success mb-2">Đã kết nối</h6>
                    <?php if ($syncStatus && $syncStatus['last_synced_at']): ?>
                        <p class="text-muted mb-3">
                            <i class="ri-time-line me-1"></i>
                            Đồng bộ lần cuối: <?= date('d/m/Y H:i', strtotime($syncStatus['last_synced_at'])) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-muted mb-3">Chưa đồng bộ lần nào.</p>
                    <?php endif; ?>

                    <div class="d-flex gap-2 justify-content-center">
                        <form method="POST" action="<?= url('integrations/google-calendar/sync') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-refresh-line me-1"></i> Đồng bộ ngay
                            </button>
                        </form>
                        <form method="POST" action="<?= url('integrations/google-calendar/disconnect') ?>"
                              data-confirm="Bạn có chắc chắn muốn ngắt kết nối Google Calendar?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="ri-link-unlink me-1"></i> Ngắt kết nối
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="avatar-md mx-auto mb-3">
                        <div class="avatar-title rounded-circle bg-warning-subtle text-warning fs-24">
                            <i class="ri-link me-0"></i>
                        </div>
                    </div>
                    <h6 class="text-warning mb-2">Chưa kết nối</h6>
                    <p class="text-muted mb-3">Kết nối Google Calendar để đồng bộ lịch hẹn hai chiều.</p>
                    <a href="<?= url('integrations/google-calendar/connect') ?>" class="btn btn-primary">
                        <i class="ri-google-fill me-1"></i> Kết nối Google Calendar
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sync Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thông tin đồng bộ</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 vstack gap-2">
                    <li class="d-flex align-items-center">
                        <i class="ri-arrow-down-circle-line text-info me-2 fs-18"></i>
                        <span class="text-muted">Kéo sự kiện từ Google về ToryCRM</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="ri-arrow-up-circle-line text-success me-2 fs-18"></i>
                        <span class="text-muted">Đẩy sự kiện từ ToryCRM lên Google</span>
                    </li>
                    <li class="d-flex align-items-center">
                        <i class="ri-calendar-check-line text-primary me-2 fs-18"></i>
                        <span class="text-muted">Đồng bộ 30 ngày trước - 90 ngày sau</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
