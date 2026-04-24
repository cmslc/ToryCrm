<?php $pageTitle = 'Cài đặt Email'; $_currentPage = 'settings'; ?>

<div class="d-flex" style="min-height:calc(100vh - 140px)">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <div class="flex-grow-1 ms-3">

<!-- Existing accounts -->
<?php if (!empty($accounts)): ?>
<div class="card mb-3">
    <div class="card-header"><h5 class="card-title mb-0">Tài khoản đã cấu hình</h5></div>
    <div class="card-body p-2">
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
                            <button class="btn btn-soft-primary btn-icon edit-acc-btn" title="Sửa"
                                data-id="<?= $acc['id'] ?>" data-email="<?= e($acc['email']) ?>"
                                data-token="<?= e($acc['api_token'] ?? '') ?>" data-display="<?= e($acc['display_name'] ?? '') ?>"
                                data-user="<?= $acc['user_id'] ?? '' ?>" data-default="<?= $acc['is_default'] ?>"><i class="ri-pencil-line"></i></button>
                            <form method="POST" action="<?= url('email/settings/test') ?>"><?= csrf_field() ?><input type="hidden" name="account_id" value="<?= $acc['id'] ?>"><button class="btn btn-soft-info btn-icon" title="Test"><i class="ri-wifi-line"></i></button></form>
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
                <textarea name="signature" id="signatureEditor"><?= e($firstAcc['signature'] ?? '') ?></textarea>
                <small class="text-muted">Chữ ký sẽ tự động thêm vào cuối mỗi email gửi đi.</small>
            </div>
            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu chữ ký</button>
        </form>
        <script src="<?= asset('libs/ckeditor/ckeditor.js') ?>"></script>
        <script>CKEDITOR.replace('signatureEditor',{height:150,removeButtons:'About',toolbar:[['Bold','Italic','Underline'],['Link','Unlink'],['TextColor'],['Font','FontSize'],['Source']]});</script>
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
                    <?php $allUsers = \Core\Database::fetchAll("SELECT u.id, u.name, u.avatar, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [\Core\Database::tenantId()]); ?>
                    <?php $deptGroupedEmail = []; foreach ($allUsers as $u) { $deptGroupedEmail[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                    <select name="user_id" class="form-select searchable-select">
                        <option value="">Tất cả</option>
                        <?php foreach ($deptGroupedEmail as $dept => $dUsers): ?>
                        <optgroup label="<?= e($dept) ?>">
                            <?php foreach ($dUsers as $u): ?>
                            <option value="<?= $u['id'] ?>" data-avatar="<?= e($u['avatar'] ?? '') ?>"><?= e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
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

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('email/settings/save') ?>" id="editAccForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editAccId">
                <div class="modal-header"><h5 class="modal-title">Sửa tài khoản email</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editAccEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">API Token</label>
                        <input type="text" class="form-control" name="api_token" id="editAccToken" placeholder="Để trống nếu không đổi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tên hiển thị</label>
                        <input type="text" class="form-control" name="display_name" id="editAccDisplay">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gán cho</label>
                        <?php $allUsersEdit = \Core\Database::fetchAll("SELECT u.id, u.name, u.avatar, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.tenant_id = ? AND u.is_active = 1 ORDER BY d.name, u.name", [\Core\Database::tenantId()]); ?>
                        <?php $deptGroupedEdit = []; foreach ($allUsersEdit as $u) { $deptGroupedEdit[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                        <select name="user_id" class="form-select searchable-select" id="editAccUser">
                            <option value="">Tất cả</option>
                            <?php foreach ($deptGroupedEdit as $dept => $dUsers): ?>
                            <optgroup label="<?= e($dept) ?>">
                                <?php foreach ($dUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" data-avatar="<?= e($u['avatar'] ?? '') ?>"><?= e($u['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="editAccDefault">
                        <label class="form-check-label" for="editAccDefault">Mặc định</label>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button></div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.edit-acc-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('editAccId').value = this.dataset.id;
        document.getElementById('editAccEmail').value = this.dataset.email;
        document.getElementById('editAccToken').value = '';
        document.getElementById('editAccDisplay').value = this.dataset.display;
        var userSel = document.getElementById('editAccUser');
        userSel.value = this.dataset.user;
        userSel.dispatchEvent(new Event('change'));
        document.getElementById('editAccDefault').checked = this.dataset.default === '1';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('editAccModal')).show();
    });
});
</script>

    </div>
</div>
