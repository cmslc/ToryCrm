<?php $pageTitle = 'Cài đặt Email'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-mail-settings-line me-2"></i> Cài đặt Email</h4>
    <a href="<?= url('email') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<!-- Existing accounts -->
<?php if (!empty($accounts)): ?>
<div class="card mb-3">
    <div class="card-header"><h5 class="card-title mb-0">Tài khoản đã cấu hình</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Email</th><th>IMAP</th><th>SMTP</th><th>Đồng bộ cuối</th><th>Mặc định</th><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($accounts as $acc): ?>
                <tr>
                    <td>
                        <span class="fw-medium"><?= e($acc['email']) ?></span>
                        <?php if ($acc['display_name']): ?><br><small class="text-muted"><?= e($acc['display_name']) ?></small><?php endif; ?>
                    </td>
                    <td class="fs-12"><?= e($acc['imap_host']) ?>:<?= $acc['imap_port'] ?></td>
                    <td class="fs-12"><?= e($acc['smtp_host']) ?>:<?= $acc['smtp_port'] ?></td>
                    <td class="fs-12 text-muted"><?= $acc['last_sync'] ? created_ago($acc['last_sync']) : 'Chưa' ?></td>
                    <td><?= $acc['is_default'] ? '<span class="badge bg-success">Mặc định</span>' : '' ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <form method="POST" action="<?= url('email/settings/test') ?>"><?= csrf_field() ?><input type="hidden" name="account_id" value="<?= $acc['id'] ?>"><button class="btn btn-soft-info btn-icon" title="Test kết nối"><i class="ri-wifi-line"></i></button></form>
                            <form method="POST" action="<?= url('email/settings/' . $acc['id'] . '/delete') ?>" onsubmit="return confirm('Xóa tài khoản này?')"><?= csrf_field() ?><button class="btn btn-soft-danger btn-icon" title="Xóa"><i class="ri-delete-bin-line"></i></button></form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Add new account -->
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-add-line me-2"></i> Thêm tài khoản email</h5></div>
    <div class="card-body">
        <form method="POST" action="<?= url('email/settings/save') ?>">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" required placeholder="sales@congty.com">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tên hiển thị</label>
                    <input type="text" class="form-control" name="display_name" placeholder="VD: Phòng Kinh doanh">
                </div>
            </div>

            <div class="mb-3">
                <a class="text-muted fs-13" data-bs-toggle="collapse" href="#advancedSettings"><i class="ri-settings-3-line me-1"></i> Cài đặt nâng cao (IMAP/SMTP) <i class="ri-arrow-down-s-line"></i></a>
            </div>
            <div class="collapse" id="advancedSettings">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">IMAP Host</label>
                        <input type="text" class="form-control" name="imap_host" value="mail.getcodemail.com">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Port</label>
                        <input type="number" class="form-control" name="imap_port" value="993">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Mã hóa</label>
                        <select name="imap_encryption" class="form-select">
                            <option value="ssl" selected>SSL</option>
                            <option value="tls">TLS</option>
                            <option value="none">Không</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">SMTP Host</label>
                        <input type="text" class="form-control" name="smtp_host" value="mail.getcodemail.com">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Port</label>
                        <input type="number" class="form-control" name="smtp_port" value="587">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Mã hóa</label>
                        <select name="smtp_encryption" class="form-select">
                            <option value="tls" selected>TLS</option>
                            <option value="ssl">SSL</option>
                            <option value="none">Không</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault">
                    <label class="form-check-label" for="isDefault">Đặt làm tài khoản mặc định</label>
                </div>
            </div>

            <div class="alert alert-info py-2 mb-3">
                <i class="ri-information-line me-1"></i> Sử dụng thông tin IMAP/SMTP từ hệ thống <a href="https://getcodemail.com" target="_blank">GetcodeMail</a> hoặc nhà cung cấp email của bạn.
            </div>

            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu tài khoản</button>
        </form>
    </div>
</div>

<!-- Hướng dẫn -->
<div class="card">
    <div class="card-header bg-light"><h5 class="card-title mb-0"><i class="ri-question-line me-2"></i> Hướng dẫn cấu hình</h5></div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-6">
                <h6 class="fw-medium mb-3">1. Tạo tài khoản email trên GetcodeMail</h6>
                <ol class="text-muted mb-4">
                    <li>Truy cập <a href="https://getcodemail.com" target="_blank">getcodemail.com</a> và đăng nhập</li>
                    <li>Vào <strong>Tên miền</strong> > Thêm tên miền công ty (VD: <code>congty.com</code>)</li>
                    <li>Cấu hình DNS theo hướng dẫn (MX, SPF, DKIM)</li>
                    <li>Vào <strong>Hộp thư</strong> > Tạo mailbox (VD: <code>sales@congty.com</code>)</li>
                    <li>Ghi nhớ email và mật khẩu đã tạo</li>
                </ol>

                <h6 class="fw-medium mb-3">2. Cấu hình trong ToryCRM</h6>
                <ol class="text-muted mb-0">
                    <li>Điền email và mật khẩu vào form bên trên</li>
                    <li>IMAP/SMTP Host giữ mặc định <code>mail.getcodemail.com</code></li>
                    <li>Bấm <strong>Lưu tài khoản</strong></li>
                    <li>Bấm nút <strong>Test kết nối</strong> để kiểm tra</li>
                    <li>Vào <strong>Email > Đồng bộ</strong> để pull email về CRM</li>
                </ol>
            </div>
            <div class="col-lg-6">
                <h6 class="fw-medium mb-3">Thông số kết nối</h6>
                <div class="table-responsive">
                    <table class="table table-bordered mb-4">
                        <thead class="table-light"><tr><th>Mục</th><th>GetcodeMail</th><th>Gmail</th></tr></thead>
                        <tbody>
                            <tr><td>IMAP Host</td><td><code>mail.getcodemail.com</code></td><td><code>imap.gmail.com</code></td></tr>
                            <tr><td>IMAP Port</td><td><code>993</code></td><td><code>993</code></td></tr>
                            <tr><td>IMAP Mã hóa</td><td>SSL</td><td>SSL</td></tr>
                            <tr><td>SMTP Host</td><td><code>mail.getcodemail.com</code></td><td><code>smtp.gmail.com</code></td></tr>
                            <tr><td>SMTP Port</td><td><code>587</code></td><td><code>587</code></td></tr>
                            <tr><td>SMTP Mã hóa</td><td>TLS</td><td>TLS</td></tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-medium mb-3">Nhà cung cấp khác</h6>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light"><tr><th>Nhà cung cấp</th><th>IMAP</th><th>SMTP</th></tr></thead>
                        <tbody>
                            <tr><td>Zoho Mail</td><td><code>imap.zoho.com:993</code></td><td><code>smtp.zoho.com:587</code></td></tr>
                            <tr><td>Outlook/Office 365</td><td><code>outlook.office365.com:993</code></td><td><code>smtp.office365.com:587</code></td></tr>
                            <tr><td>Yahoo Mail</td><td><code>imap.mail.yahoo.com:993</code></td><td><code>smtp.mail.yahoo.com:587</code></td></tr>
                            <tr><td>Yandex Mail</td><td><code>imap.yandex.com:993</code></td><td><code>smtp.yandex.com:587</code></td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning py-2 mt-3 mb-0">
                    <i class="ri-error-warning-line me-1"></i> <strong>Gmail:</strong> Cần bật "Mật khẩu ứng dụng" (App Password) trong cài đặt bảo mật Google, không dùng mật khẩu thường.
                </div>
            </div>
        </div>
    </div>
</div>
