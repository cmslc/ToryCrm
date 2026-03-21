<?php $pageTitle = 'Webhook'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Webhook</h4>
            <a href="<?= url('webhooks/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm webhook</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên</th>
                                <th>URL</th>
                                <th>Events</th>
                                <th>Trạng thái</th>
                                <th>Lần gọi cuối</th>
                                <th>Response</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($webhooks)): ?>
                                <?php foreach ($webhooks as $wh): ?>
                                <tr>
                                    <td><a href="<?= url('webhooks/' . $wh['id']) ?>" class="fw-medium text-dark"><?= e($wh['name']) ?></a></td>
                                    <td><code class="small"><?= e(strlen($wh['url']) > 40 ? substr($wh['url'], 0, 40) . '...' : $wh['url']) ?></code></td>
                                    <td>
                                        <?php $events = json_decode($wh['events'] ?? '[]', true); ?>
                                        <span class="badge bg-primary-subtle text-primary"><?= count($events) ?> events</span>
                                    </td>
                                    <td>
                                        <?= $wh['is_active']
                                            ? '<span class="badge bg-success">Bật</span>'
                                            : '<span class="badge bg-secondary">Tắt</span>' ?>
                                        <?php if ($wh['fail_count'] > 0): ?>
                                            <span class="badge bg-danger-subtle text-danger"><?= $wh['fail_count'] ?> lỗi</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $wh['last_triggered_at'] ? time_ago($wh['last_triggered_at']) : '-' ?></td>
                                    <td>
                                        <?php if ($wh['last_response_code']): ?>
                                            <span class="badge bg-<?= $wh['last_response_code'] < 300 ? 'success' : 'danger' ?>"><?= $wh['last_response_code'] ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="<?= url('webhooks/' . $wh['id']) ?>" class="btn btn-sm btn-soft-primary"><i class="ri-eye-line"></i></a>
                                            <form method="POST" action="<?= url('webhooks/' . $wh['id'] . '/toggle') ?>">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-soft-<?= $wh['is_active'] ? 'warning' : 'success' ?>" title="<?= $wh['is_active'] ? 'Tắt' : 'Bật' ?>">
                                                    <i class="ri-<?= $wh['is_active'] ? 'pause-line' : 'play-line' ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= url('webhooks/' . $wh['id'] . '/delete') ?>" data-confirm="Xóa webhook?">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-links-line fs-1 d-block mb-2"></i>Chưa có webhook</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
