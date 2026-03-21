<?php $pageTitle = e($task['title']); ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($task['title']) ?></h4>
            <ol class="breadcrumb m-0"><li class="breadcrumb-item"><a href="<?= url('tasks') ?>">Công việc</a></li><li class="breadcrumb-item active">Chi tiết</li></ol>
        </div>

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1"><?= e($task['title']) ?></h5>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="<?= url('tasks/' . $task['id'] . '/edit') ?>" class="btn btn-soft-primary btn"><i class="ri-pencil-line me-1"></i>Sửa</a>
                            <?php if ($task['status'] !== 'done'): ?>
                                <form method="POST" action="<?= url('tasks/' . $task['id'] . '/complete') ?>" data-confirm="Hoàn thành công việc này?"><?= csrf_field() ?><button class="btn btn-soft-success btn"><i class="ri-check-line me-1"></i>Hoàn thành</button></form>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('tasks/' . $task['id'] . '/cancel') ?>" data-confirm="Hủy công việc này?"><?= csrf_field() ?><button class="btn btn-soft-warning btn"><i class="ri-close-circle-line me-1"></i>Hủy</button></form>
                            <form method="POST" action="<?= url('tasks/' . $task['id'] . '/delete') ?>" data-confirm="Xóa công việc?"><?= csrf_field() ?><button class="btn btn-soft-danger btn"><i class="ri-delete-bin-line me-1"></i>Xóa</button></form>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($task['description']): ?>
                            <p><?= nl2br(e($task['description'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted">Không có mô tả</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <?php $sc=['todo'=>'secondary','in_progress'=>'primary','review'=>'warning','done'=>'success']; $sl=['todo'=>'Cần làm','in_progress'=>'Đang làm','review'=>'Review','done'=>'Xong']; ?>
                            <tr><th class="text-muted">Trạng thái</th><td><span class="badge bg-<?= $sc[$task['status']] ?? 'secondary' ?>"><?= $sl[$task['status']] ?? '' ?></span></td></tr>
                            <?php $pc=['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger']; $pl=['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn']; ?>
                            <tr><th class="text-muted">Ưu tiên</th><td><span class="badge bg-<?= $pc[$task['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$task['priority']] ?? 'secondary' ?>"><?= $pl[$task['priority']] ?? '' ?></span></td></tr>
                            <tr><th class="text-muted">Giao cho</th><td><?= e($task['assigned_name'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Tạo bởi</th><td><?= e($task['creator_name'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Hạn</th><td><?= $task['due_date'] ? format_datetime($task['due_date']) : '-' ?></td></tr>
                            <tr><th class="text-muted">Ngày tạo</th><td><?= format_datetime($task['created_at']) ?></td></tr>
                            <?php if ($task['contact_first_name']): ?><tr><th class="text-muted">Khách hàng</th><td><a href="<?= url('contacts/' . $task['contact_id']) ?>"><?= e($task['contact_first_name']) ?></a></td></tr><?php endif; ?>
                            <?php if ($task['deal_title']): ?><tr><th class="text-muted">Cơ hội</th><td><a href="<?= url('deals/' . $task['deal_id']) ?>"><?= e($task['deal_title']) ?></a></td></tr><?php endif; ?>
                        </table>

                        <form method="POST" action="<?= url('tasks/' . $task['id'] . '/status') ?>" class="mt-3">
                            <?= csrf_field() ?>
                            <div class="d-flex gap-2">
                                <select name="status" class="form-select">
                                    <?php foreach ($sl as $v => $l): ?><option value="<?= $v ?>" <?= $task['status'] === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
                                </select>
                                <button class="btn btn-primary btn flex-shrink-0">Cập nhật</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
