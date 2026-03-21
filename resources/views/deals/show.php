<?php $pageTitle = e($deal['title']); ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($deal['title']) ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('deals') ?>">Cơ hội</a></li>
                <li class="breadcrumb-item active"><?= e($deal['title']) ?></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-grow-1">
                                <h5 class="mb-1"><?= e($deal['title']) ?></h5>
                                <?php $dc = ['open'=>'primary','won'=>'success','lost'=>'danger']; $dl = ['open'=>'Đang mở','won'=>'Thắng','lost'=>'Thua']; ?>
                                <span class="badge bg-<?= $dc[$deal['status']] ?? 'secondary' ?>"><?= $dl[$deal['status']] ?? $deal['status'] ?></span>
                            </div>
                        </div>
                        <h3 class="text-primary mb-3"><?= format_money($deal['value']) ?></h3>
                        <div class="d-flex gap-2 mb-4">
                            <a href="<?= url('deals/' . $deal['id'] . '/edit') ?>" class="btn btn-primary btn-sm"><i class="ri-pencil-line me-1"></i> Sửa</a>
                            <form method="POST" action="<?= url('deals/' . $deal['id'] . '/delete') ?>" onsubmit="return confirm('Xác nhận xóa?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-danger btn-sm"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                            </form>
                        </div>
                        <table class="table table-borderless mb-0">
                            <tr><th class="text-muted" width="40%">Giai đoạn</th><td><span class="badge" style="background:<?= safe_color($deal['stage_color'] ?? null) ?>"><?= e($deal['stage_name'] ?? '') ?></span></td></tr>
                            <tr><th class="text-muted">Ưu tiên</th><td><?php $pl=['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn']; echo $pl[$deal['priority']] ?? ''; ?></td></tr>
                            <tr><th class="text-muted">Khách hàng</th><td><?= $deal['contact_id'] ? '<a href="' . url('contacts/' . $deal['contact_id']) . '">' . e($deal['contact_first_name'] . ' ' . ($deal['contact_last_name'] ?? '')) . '</a>' : '-' ?></td></tr>
                            <tr><th class="text-muted">Công ty</th><td><?= $deal['company_id'] ? '<a href="' . url('companies/' . $deal['company_id']) . '">' . e($deal['company_name']) . '</a>' : '-' ?></td></tr>
                            <tr><th class="text-muted">Phụ trách</th><td><?= e($deal['owner_name'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Ngày dự kiến</th><td><?= $deal['expected_close_date'] ? format_date($deal['expected_close_date']) : '-' ?></td></tr>
                            <tr><th class="text-muted">Ngày tạo</th><td><?= format_datetime($deal['created_at']) ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <?php if ($deal['description']): ?>
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Mô tả</h5></div>
                        <div class="card-body"><p><?= nl2br(e($deal['description'])) ?></p></div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Hoạt động</h5></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('activities/store') ?>" class="row g-2 mb-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="deal_id" value="<?= $deal['id'] ?>">
                            <div class="col-md-3">
                                <select name="type" class="form-select">
                                    <option value="note">Ghi chú</option><option value="call">Cuộc gọi</option>
                                    <option value="email">Email</option><option value="meeting">Cuộc họp</option>
                                </select>
                            </div>
                            <div class="col-md-7"><input type="text" class="form-control" name="title" placeholder="Nội dung..." required></div>
                            <div class="col-md-2"><button class="btn btn-primary w-100">Thêm</button></div>
                        </form>
                        <div class="activity-timeline">
                            <?php if (!empty($activities)): ?>
                                <?php foreach ($activities as $act): ?>
                                    <?php $ti=['note'=>'ri-file-text-line','call'=>'ri-phone-line','email'=>'ri-mail-line','meeting'=>'ri-calendar-line']; $tc=['note'=>'primary','call'=>'success','email'=>'info','meeting'=>'warning']; ?>
                                    <div class="activity-item d-flex mb-3">
                                        <div class="avatar-xs"><div class="avatar-title rounded-circle bg-<?= $tc[$act['type']]??'primary' ?>-subtle text-<?= $tc[$act['type']]??'primary' ?>"><i class="<?= $ti[$act['type']]??'ri-file-text-line' ?>"></i></div></div>
                                        <div class="ms-3"><h6 class="mb-0"><?= e($act['title']) ?></h6><small class="text-muted"><?= time_ago($act['created_at']) ?></small></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center">Chưa có hoạt động</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
