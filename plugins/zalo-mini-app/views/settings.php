<?php $pageTitle = 'Zalo OA'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Cấu hình Zalo OA</h4>
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
                <!-- Connection Config -->
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Thông tin kết nối</h5>
                        <?php if (!empty($config['access_token'])): ?>
                            <span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>Đã kết nối</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Chưa kết nối</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('integrations/zalo') ?>">
                            <?= csrf_field() ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">App ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="app_id" value="<?= e($config['app_id'] ?? '') ?>" placeholder="Nhập Zalo App ID" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Secret Key <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="secret_key" value="<?= e($config['secret_key'] ?? '') ?>" placeholder="Nhập Secret Key" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">OA ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="oa_id" value="<?= e($config['oa_id'] ?? '') ?>" placeholder="Nhập OA ID" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Refresh Token</label>
                                    <input type="text" class="form-control" name="refresh_token" value="<?= e($config['refresh_token'] ?? '') ?>" placeholder="Nhập Refresh Token">
                                    <small class="text-muted">Để trống nếu không thay đổi</small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cấu hình</button>
                        </form>
                    </div>
                </div>

                <!-- Recent Messages -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Tin nhắn gần đây</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($messages)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="ri-chat-3-line fs-36 d-block mb-2"></i>
                                Chưa có tin nhắn nào
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Zalo User</th>
                                            <th>Chiều</th>
                                            <th>Loại</th>
                                            <th>Nội dung</th>
                                            <th>Trạng thái</th>
                                            <th>Thời gian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($messages as $msg): ?>
                                        <tr>
                                            <td><code class="small"><?= e(substr($msg['zalo_user_id'] ?? '', 0, 16)) ?>...</code></td>
                                            <td>
                                                <?php if (($msg['direction'] ?? '') === 'inbound'): ?>
                                                    <span class="badge bg-info-subtle text-info"><i class="ri-arrow-down-line"></i> Nhận</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary-subtle text-primary"><i class="ri-arrow-up-line"></i> Gửi</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= e($msg['message_type'] ?? 'text') ?></td>
                                            <td><?= e(mb_substr($msg['content'] ?? '', 0, 60)) ?><?= mb_strlen($msg['content'] ?? '') > 60 ? '...' : '' ?></td>
                                            <td>
                                                <?php
                                                    $statusColors = ['sent' => 'success', 'delivered' => 'info', 'read' => 'primary', 'failed' => 'danger'];
                                                    $sc = $statusColors[$msg['status'] ?? ''] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $sc ?>-subtle text-<?= $sc ?>"><?= e($msg['status'] ?? '') ?></span>
                                            </td>
                                            <td><small><?= date('d/m/Y H:i', strtotime($msg['created_at'] ?? 'now')) ?></small></td>
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
                <!-- Webhook URL -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Webhook URL</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">Sao chép URL bên dưới và dán vào phần cấu hình Webhook trên trang quản trị Zalo OA.</p>
                        <div class="input-group">
                            <input type="text" class="form-control" id="webhookUrl" value="<?= e($webhookUrl) ?>" readonly>
                            <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('webhookUrl').value); this.innerHTML='<i class=\'ri-check-line\'></i> Đã copy'; setTimeout(() => this.innerHTML='<i class=\'ri-file-copy-line\'></i> Copy', 2000);">
                                <i class="ri-file-copy-line"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Connection Test -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Kiểm tra kết nối</h5>
                    </div>
                    <div class="card-body">
                        <div id="connectionStatus" class="text-center py-3">
                            <?php if (!empty($config['access_token']) && ($config['token_expires_at'] ?? 0) > time()): ?>
                                <div class="avatar-md mx-auto mb-3">
                                    <div class="avatar-title bg-success-subtle text-success rounded-circle fs-24">
                                        <i class="ri-checkbox-circle-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-success">Kết nối hoạt động</h6>
                                <p class="text-muted mb-0"><small>Token hết hạn: <?= date('d/m/Y H:i', $config['token_expires_at']) ?></small></p>
                            <?php elseif (!empty($config['app_id'])): ?>
                                <div class="avatar-md mx-auto mb-3">
                                    <div class="avatar-title bg-warning-subtle text-warning rounded-circle fs-24">
                                        <i class="ri-error-warning-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-warning">Token cần làm mới</h6>
                                <p class="text-muted mb-0"><small>Hệ thống sẽ tự động refresh khi gửi tin nhắn</small></p>
                            <?php else: ?>
                                <div class="avatar-md mx-auto mb-3">
                                    <div class="avatar-title bg-secondary-subtle text-secondary rounded-circle fs-24">
                                        <i class="ri-plug-line"></i>
                                    </div>
                                </div>
                                <h6 class="text-secondary">Chưa cấu hình</h6>
                                <p class="text-muted mb-0"><small>Điền thông tin kết nối bên trái để bắt đầu</small></p>
                            <?php endif; ?>
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
                            <li class="mb-2">Đăng nhập <a href="https://developers.zalo.me" target="_blank">Zalo Developers</a></li>
                            <li class="mb-2">Tạo ứng dụng mới hoặc chọn ứng dụng có sẵn</li>
                            <li class="mb-2">Lấy App ID, Secret Key từ trang cấu hình</li>
                            <li class="mb-2">Kích hoạt OA và lấy OA ID</li>
                            <li class="mb-2">Tạo Refresh Token từ trang OAuth</li>
                            <li class="mb-2">Dán Webhook URL vào cấu hình webhook của Zalo</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
