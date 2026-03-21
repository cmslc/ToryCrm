<?php $pageTitle = 'Webhook: ' . e($webhook['name']); ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($webhook['name']) ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('webhooks') ?>">Webhook</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">URL</small>
                            <code class="small"><?= e($webhook['url']) ?></code>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Secret Key</small>
                            <code class="small"><?= e($webhook['secret_key'] ?? '-') ?></code>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Trạng thái</small>
                            <?= $webhook['is_active'] ? '<span class="badge bg-success">Bật</span>' : '<span class="badge bg-secondary">Tắt</span>' ?>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Events</small>
                            <?php foreach (json_decode($webhook['events'] ?? '[]', true) as $evt): ?>
                                <span class="badge bg-primary-subtle text-primary me-1 mb-1"><code><?= e($evt) ?></code></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Người tạo</small>
                            <?= e($webhook['created_by_name'] ?? '-') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Lịch sử gọi (<?= count($logs) ?> gần nhất)</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Event</th>
                                        <th>Status</th>
                                        <th>HTTP</th>
                                        <th>Duration</th>
                                        <th>Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($logs)): ?>
                                        <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><code class="small"><?= e($log['event']) ?></code></td>
                                            <td>
                                                <?php $sc = ['success'=>'success','failed'=>'danger','pending'=>'warning']; ?>
                                                <span class="badge bg-<?= $sc[$log['status']] ?? 'secondary' ?>"><?= $log['status'] ?></span>
                                            </td>
                                            <td><?= $log['response_code'] ?: '-' ?></td>
                                            <td><?= $log['duration_ms'] ? $log['duration_ms'] . 'ms' : '-' ?></td>
                                            <td class="small"><?= time_ago($log['created_at']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center text-muted py-3">Chưa có log</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
