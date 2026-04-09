<?php $pageTitle = 'Cấu hình MoMo'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Cấu hình MoMo</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
                    <li class="breadcrumb-item active">MoMo</li>
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
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">
                    <i class="ri-wallet-3-line me-1 text-danger"></i> Cổng thanh toán MoMo
                </h5>
                <?php if ($integration && $integration['is_active']): ?>
                    <span class="badge bg-success">Đang hoạt động</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Chưa kích hoạt</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-1"></i>
                    Truy cập <a href="https://business.momo.vn" target="_blank" class="fw-medium">MoMo Business</a>
                    để đăng ký merchant và lấy thông tin API. Dùng môi trường Sandbox để kiểm thử.
                </div>
                <form method="POST" action="<?= url('integrations/momo') ?>">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Partner Code</label>
                            <input type="text" class="form-control" name="partner_code"
                                   value="<?= e($config['partner_code'] ?? '') ?>"
                                   placeholder="VD: MOMO001">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Access Key</label>
                            <input type="text" class="form-control" name="access_key"
                                   value="<?= e($config['access_key'] ?? '') ?>"
                                   placeholder="Access Key từ MoMo">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Secret Key</label>
                            <input type="password" class="form-control" name="secret_key"
                                   value="<?= e($config['secret_key'] ?? '') ?>"
                                   placeholder="Secret Key từ MoMo">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Môi trường</label>
                            <select class="form-select" name="environment">
                                <option value="sandbox" <?= ($config['environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>
                                    Sandbox (Kiểm thử)
                                </option>
                                <option value="production" <?= ($config['environment'] ?? '') === 'production' ? 'selected' : '' ?>>
                                    Production (Thật)
                                </option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Lưu cấu hình
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Hướng dẫn</h5>
            </div>
            <div class="card-body">
                <ol class="list-group list-group-numbered list-group-flush">
                    <li class="list-group-item border-0 px-0">Đăng ký tài khoản doanh nghiệp tại MoMo</li>
                    <li class="list-group-item border-0 px-0">Lấy Partner Code, Access Key, Secret Key</li>
                    <li class="list-group-item border-0 px-0">Nhập thông tin vào form và lưu</li>
                    <li class="list-group-item border-0 px-0">Kiểm thử với Sandbox trước khi go live</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thông tin Sandbox</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">Sử dụng app MoMo Test để quét mã QR thanh toán trong môi trường Sandbox.</p>
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Số điện thoại:</td>
                        <td><code>9704 0000 0000 0018</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">OTP:</td>
                        <td><code>000000</code></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
