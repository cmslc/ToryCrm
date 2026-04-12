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
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" required placeholder="sales@congty.com">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên hiển thị</label>
                    <input type="text" class="form-control" name="display_name" placeholder="VD: Phòng Kinh doanh">
                </div>
            </div>

            <h6 class="text-muted border-bottom pb-2 mb-3"><i class="ri-inbox-line me-1"></i> IMAP (Nhận email)</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">IMAP Host</label>
                    <input type="text" class="form-control" name="imap_host" placeholder="mail.getcodemail.com" value="mail.getcodemail.com">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Port</label>
                    <input type="number" class="form-control" name="imap_port" value="993">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mã hóa</label>
                    <select name="imap_encryption" class="form-select">
                        <option value="ssl" selected>SSL</option>
                        <option value="tls">TLS</option>
                        <option value="none">Không</option>
                    </select>
                </div>
            </div>

            <h6 class="text-muted border-bottom pb-2 mb-3"><i class="ri-send-plane-line me-1"></i> SMTP (Gửi email)</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" class="form-control" name="smtp_host" placeholder="mail.getcodemail.com" value="mail.getcodemail.com">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Port</label>
                    <input type="number" class="form-control" name="smtp_port" value="587">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mã hóa</label>
                    <select name="smtp_encryption" class="form-select">
                        <option value="tls" selected>TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="none">Không</option>
                    </select>
                </div>
            </div>

            <h6 class="text-muted border-bottom pb-2 mb-3"><i class="ri-lock-line me-1"></i> Xác thực</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="username" required placeholder="sales@congty.com">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password" required>
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
