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
                <thead class="table-light"><tr><th>Email</th><th>Nhân viên</th><th>API Token</th><th>Đồng bộ cuối</th><th>MĐ</th><th>Thao tác</th></tr></thead>
                <tbody>
                <?php foreach ($accounts as $acc): ?>
                <tr>
                    <td>
                        <span class="fw-medium"><?= e($acc['email']) ?></span>
                        <?php if ($acc['display_name']): ?><br><small class="text-muted"><?= e($acc['display_name']) ?></small><?php endif; ?>
                    </td>
                    <td><?= e($acc['user_name'] ?? 'Tất cả') ?></td>
                    <td class="fs-12"><code><?= e(substr($acc['api_token'] ?? '', 0, 12)) ?>...</code></td>
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
<?php
// Signature form for first account
$firstAcc = $accounts[0] ?? null;
if ($firstAcc): ?>
<div class="card mb-3">
    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-quill-pen-line me-2"></i> Chữ ký email</h5></div>
    <div class="card-body">
        <form method="POST" action="<?= url('email/settings/signature') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="account_id" value="<?= $firstAcc['id'] ?>">
            <div class="mb-3">
                <textarea class="form-control" name="signature" rows="4" placeholder="VD: Trân trọng,&#10;Nguyễn Văn A&#10;Phòng Kinh doanh | Công ty ABC&#10;SĐT: 0123 456 789"><?= e($firstAcc['signature'] ?? '') ?></textarea>
                <small class="text-muted">Chữ ký sẽ tự động thêm vào cuối mỗi email gửi đi.</small>
            </div>
            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu chữ ký</button>
        </form>
    </div>
</div>
<?php endif; endif; ?>

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
                    <label class="form-label">API Token <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="api_token" required placeholder="Token từ GetcodeMail">
                    <small class="text-muted">Lấy token tại GetcodeMail > Mailbox > API Token</small>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Tên hiển thị</label>
                    <input type="text" class="form-control" name="display_name" placeholder="VD: Phòng KD">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Gán cho</label>
                    <select name="user_id" class="form-select">
                        <option value="">Tất cả</option>
                        <?php $allUsers = \Core\Database::fetchAll("SELECT id, name FROM users WHERE tenant_id = ? AND is_active = 1 ORDER BY name", [\Core\Database::tenantId()]); ?>
                        <?php foreach ($allUsers as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault">
                    <label class="form-check-label" for="isDefault">Đặt làm tài khoản mặc định</label>
                </div>
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
                </ol>

                <h6 class="fw-medium mb-3">2. Lấy API Token</h6>
                <ol class="text-muted mb-4">
                    <li>Trên GetcodeMail, vào mailbox vừa tạo</li>
                    <li>Tìm mục <strong>API Token</strong> hoặc tạo token mới</li>
                    <li>Copy token (chuỗi dài ~64 ký tự)</li>
                </ol>
            </div>
            <div class="col-lg-6">
                <h6 class="fw-medium mb-3">3. Cấu hình trong ToryCRM</h6>
                <ol class="text-muted mb-4">
                    <li>Điền <strong>Email</strong> và <strong>API Token</strong> vào form bên trên</li>
                    <li>Bấm <strong>Lưu tài khoản</strong></li>
                    <li>Bấm nút <strong>Test kết nối</strong> để kiểm tra</li>
                    <li>Vào <strong>Email > Đồng bộ</strong> để pull email về CRM</li>
                </ol>

                <div class="alert alert-info py-2 mb-3">
                    <i class="ri-information-line me-1"></i> API Token dùng để ToryCRM kết nối với GetcodeMail một cách an toàn. Không cần nhập mật khẩu email.
                </div>

                <div class="alert alert-light border py-2 mb-0">
                    <strong>API Endpoints:</strong><br>
                    <code class="fs-12">GET /api/v1/mailbox/inbox</code> — Đọc inbox<br>
                    <code class="fs-12">GET /api/v1/mailbox/read/{id}</code> — Đọc email<br>
                    <code class="fs-12">POST /api/v1/send</code> — Gửi email<br>
                    <small class="text-muted">Base URL: https://getcodemail.com</small>
                </div>
            </div>
        </div>
    </div>
</div>
