<?php $pageTitle = 'VoIP / Stringee'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Cấu hình VoIP / Stringee</h4>
            <a href="<?= url('integrations') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
        </div>

        <?php $flashMsg = flash(); if ($flashMsg): ?>
            <div class="alert alert-<?= $flashMsg['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= e($flashMsg['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-xl-8">
                <!-- API Config -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Thông tin API</h5>
                        <?php if (!empty($config['api_key_sid'])): ?>
                            <span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>Đã cấu hình</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Chưa cấu hình</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('integrations/voip') ?>" id="voipForm">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">API Key SID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="api_key_sid" value="<?= e($config['api_key_sid'] ?? '') ?>" placeholder="Nhập API Key SID" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">API Key Secret <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="api_key_secret" value="<?= e($config['api_key_secret'] ?? '') ?>" placeholder="Nhập API Key Secret" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại gọi đi</label>
                                    <input type="text" class="form-control" name="phone_from" value="<?= e($config['phone_from'] ?? '') ?>" placeholder="VD: 19001234">
                                    <small class="text-muted">Số hotline đã đăng ký trên Stringee</small>
                                </div>
                            </div>

                            <!-- Extension Mapping -->
                            <h6 class="mt-4 mb-3">Ánh xạ Extension</h6>
                            <div id="extensionList">
                                <?php
                                $extensions = $config['extensions'] ?? [];
                                if (empty($extensions)) {
                                    $extensions = [['user_id' => '', 'number' => '']];
                                }
                                ?>
                                <?php foreach ($extensions as $i => $ext): ?>
                                <div class="row mb-2 extension-row">
                                    <div class="col-5">
                                        <select class="form-select" name="ext_user_id[]">
                                            <option value="">-- Chọn nhân viên --</option>
                                            <?php foreach ($users as $u): ?>
                                                <option value="<?= $u['id'] ?>" <?= ($ext['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" class="form-control" name="ext_number[]" value="<?= e($ext['number'] ?? '') ?>" placeholder="Số extension (VD: 101)">
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-soft-danger w-100" onclick="this.closest('.extension-row').remove()"><i class="ri-delete-bin-line"></i></button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-soft-info mb-4" onclick="addExtensionRow()"><i class="ri-add-line me-1"></i> Thêm extension</button>

                            <div>
                                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cấu hình</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recent Call Logs -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Lịch sử cuộc gọi gần đây</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($callLogs)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="ri-phone-line fs-36 d-block mb-2"></i>
                                Chưa có cuộc gọi nào
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nhân viên</th>
                                            <th>Từ số</th>
                                            <th>Đến số</th>
                                            <th>Chiều</th>
                                            <th>Trạng thái</th>
                                            <th>Thời lượng</th>
                                            <th>Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($callLogs as $log): ?>
                                        <tr>
                                            <td><?= e($log['user_name'] ?? 'N/A') ?></td>
                                            <td><code><?= e($log['caller_number'] ?? '') ?></code></td>
                                            <td><code><?= e($log['callee_number'] ?? '') ?></code></td>
                                            <td>
                                                <?php if (($log['call_type'] ?? '') === 'inbound'): ?>
                                                    <span class="badge bg-info-subtle text-info"><i class="ri-phone-fill"></i> Gọi vào</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary-subtle text-primary"><i class="ri-phone-line"></i> Gọi ra</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $statusColors = [
                                                        'answered' => 'success',
                                                        'missed' => 'warning',
                                                        'busy' => 'danger',
                                                        'failed' => 'danger',
                                                        'voicemail' => 'info',
                                                    ];
                                                    $statusLabels = [
                                                        'answered' => 'Đã nghe',
                                                        'missed' => 'Nhỡ',
                                                        'busy' => 'Bận',
                                                        'failed' => 'Thất bại',
                                                        'voicemail' => 'Hộp thư thoại',
                                                    ];
                                                    $st = $log['status'] ?? 'unknown';
                                                    $stColor = $statusColors[$st] ?? 'secondary';
                                                    $stLabel = $statusLabels[$st] ?? $st;
                                                ?>
                                                <span class="badge bg-<?= $stColor ?>-subtle text-<?= $stColor ?>"><?= $stLabel ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                    $dur = (int) ($log['duration'] ?? 0);
                                                    echo $dur > 0 ? gmdate('i:s', $dur) : '-';
                                                ?>
                                            </td>
                                            <td><small><?= date('d/m/Y H:i', strtotime($log['created_at'] ?? 'now')) ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <!-- Webhook Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Webhook URL</h5>
                    </div>
                    <div class="card-body">
                        <?php
                            $voipWebhookUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                                . '://' . ($_SERVER['HTTP_HOST'] ?? 'your-domain.com') . '/webhooks/voip';
                        ?>
                        <p class="text-muted mb-2">Sao chép URL bên dưới và dán vào phần cấu hình Webhook trên trang quản trị Stringee.</p>
                        <div class="input-group">
                            <input type="text" class="form-control" id="voipWebhookUrl" value="<?= e($voipWebhookUrl) ?>" readonly>
                            <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('voipWebhookUrl').value); this.innerHTML='<i class=\'ri-check-line\'></i> Đã copy'; setTimeout(() => this.innerHTML='<i class=\'ri-file-copy-line\'></i> Copy', 2000);">
                                <i class="ri-file-copy-line"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Guide -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Hướng dẫn</h5>
                    </div>
                    <div class="card-body">
                        <ol class="mb-0">
                            <li class="mb-2">Đăng ký tài khoản tại <a href="https://stringee.com" target="_blank">Stringee.com</a></li>
                            <li class="mb-2">Tạo Project và lấy API Key SID, Secret</li>
                            <li class="mb-2">Mua và đăng ký số hotline</li>
                            <li class="mb-2">Cấu hình Answer URL với Webhook URL ở trên</li>
                            <li class="mb-2">Ánh xạ extension cho từng nhân viên</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function addExtensionRow() {
            const list = document.getElementById('extensionList');
            const row = document.createElement('div');
            row.className = 'row mb-2 extension-row';
            row.innerHTML = `
                <div class="col-5">
                    <select class="form-select" name="ext_user_id[]">
                        <option value="">-- Chọn nhân viên --</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-5">
                    <input type="text" class="form-control" name="ext_number[]" placeholder="Số extension (VD: 101)">
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-soft-danger w-100" onclick="this.closest('.extension-row').remove()"><i class="ri-delete-bin-line"></i></button>
                </div>
            `;
            list.appendChild(row);
        }
        </script>
