<?php $pageTitle = 'Cấu hình VNPay'; ?>

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Cấu hình VNPay</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
                    <li class="breadcrumb-item active">VNPay</li>
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
                    <i class="ri-bank-card-line me-1 text-primary"></i> Cổng thanh toán VNPay
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
                    Truy cập <a href="https://sandbox.vnpayment.vn/merchantv2/" target="_blank" class="fw-medium">VNPay Merchant Portal</a>
                    để lấy TMN Code và Hash Secret. Dùng môi trường Sandbox để kiểm thử.
                </div>
                <form method="POST" action="<?= url('integrations/vnpay') ?>">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">TMN Code</label>
                            <input type="text" class="form-control" name="tmn_code"
                                   value="<?= e($config['tmn_code'] ?? '') ?>"
                                   placeholder="VD: VNPAY001">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hash Secret</label>
                            <input type="password" class="form-control" name="hash_secret"
                                   value="<?= e($config['hash_secret'] ?? '') ?>"
                                   placeholder="Khóa bí mật của VNPay">
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
                    <li class="list-group-item border-0 px-0">Đăng ký tài khoản merchant tại VNPay</li>
                    <li class="list-group-item border-0 px-0">Lấy TMN Code và Hash Secret từ portal</li>
                    <li class="list-group-item border-0 px-0">Nhập thông tin vào form và lưu</li>
                    <li class="list-group-item border-0 px-0">Chọn Sandbox để kiểm thử trước khi go live</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thẻ test (Sandbox)</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Số thẻ:</td>
                        <td><code>9704198526191432198</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tên:</td>
                        <td><code>NGUYEN VAN A</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Ngày phát hành:</td>
                        <td><code>07/15</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">OTP:</td>
                        <td><code>123456</code></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
