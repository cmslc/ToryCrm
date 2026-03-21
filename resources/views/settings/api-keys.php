<?php $pageTitle = 'API Keys'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Quản lý API Keys</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
                <li class="breadcrumb-item active">API Keys</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">API Keys hiện có</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Tên</th><th>API Key</th><th>Rate Limit</th><th>Lần dùng cuối</th><th>Requests</th><th></th></tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($keys)): ?>
                                        <?php foreach ($keys as $k): ?>
                                        <tr>
                                            <td class="fw-medium"><?= e($k['name']) ?></td>
                                            <td><code class="small user-select-all"><?= e(substr($k['api_key'], 0, 12)) ?>...<?= e(substr($k['api_key'], -4)) ?></code></td>
                                            <td><?= $k['rate_limit'] ?>/h</td>
                                            <td><?= $k['last_used_at'] ? time_ago($k['last_used_at']) : '-' ?></td>
                                            <td><?= number_format($k['request_count']) ?></td>
                                            <td>
                                                <form method="POST" action="<?= url('settings/api-keys/' . $k['id'] . '/delete') ?>" data-confirm="Xóa API key?">
                                                    <?= csrf_field() ?><button class="btn btn btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-3">Chưa có API key</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Tạo API Key mới</h5></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('settings/api-keys/create') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label">Tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required placeholder="VD: Mobile App">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rate Limit (requests/giờ)</label>
                                <input type="number" class="form-control" name="rate_limit" value="100" min="1">
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="ri-key-line me-1"></i> Tạo API Key</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h6>Cách sử dụng</h6>
                        <p class="text-muted small mb-1">Thêm header vào mọi API request:</p>
                        <code class="d-block bg-light p-2 rounded small">X-API-KEY: your_api_key_here</code>
                        <p class="text-muted small mt-2 mb-0">Base URL: <code><?= url('api/v1') ?></code></p>
                    </div>
                </div>
            </div>
        </div>
