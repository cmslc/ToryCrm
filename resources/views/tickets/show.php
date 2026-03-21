<?php $pageTitle = e($ticket['title']); ?>

        <?php
            $sc = ['open'=>'info','in_progress'=>'primary','waiting'=>'warning','resolved'=>'success','closed'=>'secondary'];
            $sl = ['open'=>'Mở','in_progress'=>'Đang xử lý','waiting'=>'Chờ phản hồi','resolved'=>'Đã xử lý','closed'=>'Đóng'];
            $pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
            $pl = ['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn'];
        ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1"><?= e($ticket['title']) ?></h4>
                <span class="text-muted me-2"><?= e($ticket['ticket_code']) ?></span>
                <span class="badge bg-<?= $sc[$ticket['status']] ?? 'secondary' ?>"><?= $sl[$ticket['status']] ?? $ticket['status'] ?></span>
                <span class="badge bg-<?= $pc[$ticket['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$ticket['priority']] ?? 'secondary' ?>"><?= $pl[$ticket['priority']] ?? '' ?></span>
            </div>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('tickets') ?>">Ticket</a></li>
                <li class="breadcrumb-item active"><?= e($ticket['ticket_code']) ?></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Nội dung</h5>
                        <div class="d-flex gap-2">
                            <a href="<?= url('tickets/' . $ticket['id'] . '/edit') ?>" class="btn btn-primary btn-sm"><i class="ri-pencil-line me-1"></i> Sửa</a>
                            <form method="POST" action="<?= url('tickets/' . $ticket['id'] . '/delete') ?>" onsubmit="return confirm('Xác nhận xóa?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-danger btn-sm"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-0"><?= nl2br(e($ticket['content'] ?? '')) ?></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Bình luận</h5></div>
                    <div class="card-body">
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="d-flex mb-4">
                                    <div class="avatar-xs me-3">
                                        <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                            <i class="ri-user-line"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <h6 class="mb-0 me-2"><?= e($comment['user_name']) ?></h6>
                                            <?php if (!empty($comment['is_internal'])): ?>
                                                <span class="badge bg-warning-subtle text-warning">Nội bộ</span>
                                            <?php endif; ?>
                                            <small class="text-muted ms-auto"><?= time_ago($comment['created_at']) ?></small>
                                        </div>
                                        <p class="text-muted mb-0"><?= nl2br(e($comment['content'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">Chưa có bình luận</p>
                        <?php endif; ?>

                        <hr>
                        <form method="POST" action="<?= url('tickets/' . $ticket['id'] . '/comment') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <textarea name="content" class="form-control" rows="3" placeholder="Viết bình luận..." required></textarea>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_internal" id="is_internal" value="1">
                                    <label class="form-check-label" for="is_internal">Ghi chú nội bộ</label>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="ri-send-plane-line me-1"></i> Gửi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr><th class="text-muted" width="40%">Danh mục</th><td>
                                <?php if (!empty($ticket['category_name'])): ?>
                                    <span class="badge" style="background-color:<?= safe_color($ticket['category_color'] ?? null) ?>"><?= e($ticket['category_name']) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td></tr>
                            <tr><th class="text-muted">Khách hàng</th><td><?= $ticket['contact_id'] ? '<a href="' . url('contacts/' . $ticket['contact_id']) . '">' . e($ticket['contact_name'] ?? '') . '</a>' : '-' ?></td></tr>
                            <tr><th class="text-muted">Công ty</th><td><?= $ticket['company_id'] ? '<a href="' . url('companies/' . $ticket['company_id']) . '">' . e($ticket['company_name'] ?? '') . '</a>' : '-' ?></td></tr>
                            <tr><th class="text-muted">Phụ trách</th><td><?= e($ticket['assigned_name'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Hạn xử lý</th><td><?= !empty($ticket['due_date']) ? format_datetime($ticket['due_date']) : '-' ?></td></tr>
                            <tr><th class="text-muted">Người tạo</th><td><?= e($ticket['created_by_name'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Ngày tạo</th><td><?= format_datetime($ticket['created_at']) ?></td></tr>
                            <tr><th class="text-muted">Cập nhật</th><td><?= format_datetime($ticket['updated_at']) ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
