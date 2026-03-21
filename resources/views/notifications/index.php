<?php $pageTitle = 'Thông báo'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Thông báo</h4>
            <form method="POST" action="<?= url('notifications/mark-all-read') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect" value="1">
                <button class="btn btn-soft-primary"><i class="ri-check-double-line me-1"></i> Đánh dấu tất cả đã đọc</button>
            </form>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if (!empty($notifications['items'])): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications['items'] as $notif): ?>
                            <div class="list-group-item d-flex align-items-start gap-3 py-3 <?= !$notif['is_read'] ? 'bg-light' : '' ?>">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs">
                                        <?php
                                        $colors = ['info'=>'info','success'=>'success','warning'=>'warning','danger'=>'danger','task'=>'primary','deal'=>'success','order'=>'info','calendar'=>'warning','system'=>'secondary'];
                                        $color = $colors[$notif['type']] ?? 'primary';
                                        ?>
                                        <span class="avatar-title rounded-circle bg-<?= $color ?>-subtle text-<?= $color ?>">
                                            <i class="<?= e($notif['icon'] ?? 'ri-notification-3-line') ?>"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1 <?= !$notif['is_read'] ? 'fw-bold' : '' ?>">
                                            <?php if ($notif['link']): ?>
                                                <a href="<?= url('notifications/' . $notif['id'] . '/read') ?>" class="text-dark"><?= e($notif['title']) ?></a>
                                            <?php else: ?>
                                                <?= e($notif['title']) ?>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!$notif['is_read']): ?>
                                                <span class="badge bg-primary rounded-pill">Mới</span>
                                            <?php endif; ?>
                                            <form method="POST" action="<?= url('notifications/' . $notif['id'] . '/delete') ?>" data-confirm="Xóa thông báo?">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-sm btn-link text-muted p-0"><i class="ri-close-line"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                    <?php if ($notif['message']): ?>
                                        <p class="text-muted mb-1 small"><?= e($notif['message']) ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted"><?= time_ago($notif['created_at']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (($notifications['total_pages'] ?? 0) > 1): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">Trang <?= $notifications['page'] ?> / <?= $notifications['total_pages'] ?></div>
                            <nav><ul class="pagination mb-0">
                                <?php for ($i = 1; $i <= $notifications['total_pages']; $i++): ?>
                                    <li class="page-item <?= $i === $notifications['page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= url('notifications?page=' . $i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul></nav>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ri-notification-off-line fs-1 d-block mb-2"></i>
                        <p>Chưa có thông báo nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
