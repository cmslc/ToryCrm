<?php
$pageTitle = $event['title'];
$typeLabels = ['meeting'=>'Cuộc họp','call'=>'Cuộc gọi','visit'=>'Thăm khách','reminder'=>'Nhắc nhở','other'=>'Khác'];
$typeIcons = ['meeting'=>'ri-team-line','call'=>'ri-phone-line','visit'=>'ri-map-pin-line','reminder'=>'ri-alarm-line','other'=>'ri-calendar-event-line'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($event['title']) ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('calendar') ?>">Lịch</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= e($event['title']) ?></h5>
                            <span class="badge" style="background-color:<?= safe_color($event['color']) ?>">
                                <i class="<?= $typeIcons[$event['type']] ?? 'ri-calendar-event-line' ?> me-1"></i>
                                <?= $typeLabels[$event['type']] ?? '' ?>
                            </span>
                            <?php if ($event['is_completed']): ?>
                                <span class="badge bg-success ms-1">Hoàn thành</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="<?= url('calendar/' . $event['id'] . '/edit') ?>" class="btn btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                            <?php if (!$event['is_completed']): ?>
                                <form method="POST" action="<?= url('calendar/' . $event['id'] . '/complete') ?>" class="d-inline">
                                    <?= csrf_field() ?><button class="btn btn btn-soft-success"><i class="ri-check-line me-1"></i>Hoàn thành</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('calendar/' . $event['id'] . '/delete') ?>" class="d-inline" data-confirm="Xác nhận xóa?">
                                <?= csrf_field() ?><button class="btn btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th width="150"><i class="ri-time-line me-1"></i>Thời gian</th>
                                        <td>
                                            <?php if ($event['all_day']): ?>
                                                <?= format_date($event['start_at']) ?> (Cả ngày)
                                            <?php else: ?>
                                                <?= format_datetime($event['start_at']) ?>
                                                <?php if ($event['end_at']): ?> &mdash; <?= format_datetime($event['end_at']) ?><?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if ($event['location']): ?>
                                    <tr>
                                        <th><i class="ri-map-pin-line me-1"></i>Địa điểm</th>
                                        <td><?= e($event['location']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($event['description']): ?>
                                    <tr>
                                        <th><i class="ri-file-text-line me-1"></i>Mô tả</th>
                                        <td><?= nl2br(e($event['description'])) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <th><i class="ri-user-line me-1"></i>Người thực hiện</th>
                                        <td><?= user_avatar($event['user_name'] ?? null) ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="ri-user-add-line me-1"></i>Người tạo</th>
                                        <td><?= user_avatar($event['created_by_name'] ?? null, 'success') ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Liên kết</h5></div>
                    <div class="card-body">
                        <?php if ($event['contact_first_name']): ?>
                        <div class="d-flex align-items-center mb-3">
                            <i class="ri-contacts-line text-muted me-2 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Khách hàng</small>
                                <a href="<?= url('contacts/' . $event['contact_id']) ?>"><?= e($event['contact_first_name'] . ' ' . ($event['contact_last_name'] ?? '')) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($event['company_name']): ?>
                        <div class="d-flex align-items-center mb-3">
                            <i class="ri-building-line text-muted me-2 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Công ty</small>
                                <a href="<?= url('companies/' . $event['company_id']) ?>"><?= e($event['company_name']) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($event['deal_title']): ?>
                        <div class="d-flex align-items-center mb-3">
                            <i class="ri-hand-coin-line text-muted me-2 fs-5"></i>
                            <div>
                                <small class="text-muted d-block">Cơ hội</small>
                                <a href="<?= url('deals/' . $event['deal_id']) ?>"><?= e($event['deal_title']) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!$event['contact_first_name'] && !$event['company_name'] && !$event['deal_title']): ?>
                            <p class="text-muted text-center mb-0">Không có liên kết</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
