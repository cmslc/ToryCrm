<?php $pageTitle = 'Hoạt động'; ?>

        <div class="page-title-box"><h4 class="mb-0">Lịch sử hoạt động</h4></div>

        <div class="card">
            <div class="card-body">
                <div class="activity-timeline">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $act): ?>
                            <?php
                            $typeIcons = ['note'=>'ri-file-text-line','call'=>'ri-phone-line','email'=>'ri-mail-line','meeting'=>'ri-calendar-line','task'=>'ri-task-line','deal'=>'ri-hand-coin-line','system'=>'ri-settings-3-line'];
                            $typeColors = ['note'=>'primary','call'=>'success','email'=>'info','meeting'=>'warning','task'=>'danger','deal'=>'success','system'=>'secondary'];
                            ?>
                            <div class="activity-item d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs">
                                        <div class="avatar-title rounded-circle bg-<?= $typeColors[$act['type']] ?? 'primary' ?>-subtle text-<?= $typeColors[$act['type']] ?? 'primary' ?>">
                                            <i class="<?= $typeIcons[$act['type']] ?? 'ri-file-text-line' ?>"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1"><?= e($act['title']) ?></h6>
                                    <?php if (!empty($act['description'])): ?>
                                        <p class="text-muted mb-1"><?= e($act['description']) ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted"><?= time_ago($act['created_at']) ?></small>
                                        <?php if (!empty($act['user_name'])): ?>
                                            <small class="text-muted">- <?= e($act['user_name']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($act['contact_first_name'])): ?>
                                            <a href="<?= url('contacts/' . $act['contact_id']) ?>" class="badge bg-primary-subtle text-primary"><?= e($act['contact_first_name'] . ' ' . ($act['contact_last_name'] ?? '')) ?></a>
                                        <?php endif; ?>
                                        <?php if (!empty($act['deal_title'])): ?>
                                            <a href="<?= url('deals/' . $act['deal_id']) ?>" class="badge bg-warning-subtle text-warning"><?= e($act['deal_title']) ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4"><i class="ri-history-line fs-1 d-block mb-2"></i>Chưa có hoạt động nào</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
