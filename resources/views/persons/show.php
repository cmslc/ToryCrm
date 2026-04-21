<?php
$pageTitle = $person['full_name'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= e($person['full_name']) ?></h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('contacts') ?>">Khách hàng</a></li>
        <li class="breadcrumb-item active">Người liên hệ</li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <?php if (!empty($person['avatar'])): ?>
                <img src="<?= asset($person['avatar']) ?>" class="rounded-circle mb-3" style="width:100px;height:100px;object-fit:cover">
                <?php else: ?>
                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:100px;height:100px;font-size:36px">
                    <?= mb_strtoupper(mb_substr($person['full_name'], 0, 1)) ?>
                </div>
                <?php endif; ?>
                <h5 class="mb-1"><?= e($person['full_name']) ?></h5>
                <div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">
                    <a href="<?= url('persons/' . $person['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                    <form method="POST" action="<?= url('persons/' . $person['id'] . '/delete') ?>" class="d-inline" data-confirm="Xoá vĩnh viễn người này? (Phải xoá hết contact_persons trước)">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xoá</button>
                    </form>
                </div>
            </div>
            <div class="card-body border-top">
                <?php if ($person['phone']): ?>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="ri-phone-line text-muted"></i>
                    <a href="tel:<?= e($person['phone']) ?>"><?= e($person['phone']) ?></a>
                </div>
                <?php endif; ?>
                <?php if ($person['email']): ?>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="ri-mail-line text-muted"></i>
                    <a href="mailto:<?= e($person['email']) ?>"><?= e($person['email']) ?></a>
                </div>
                <?php endif; ?>
                <?php if ($person['gender']): ?>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="ri-user-line text-muted"></i>
                    <?= ['male'=>'Nam','female'=>'Nữ','other'=>'Khác'][$person['gender']] ?? '-' ?>
                </div>
                <?php endif; ?>
                <?php if ($person['date_of_birth']): ?>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="ri-cake-2-line text-muted"></i>
                    <?= format_date($person['date_of_birth']) ?>
                </div>
                <?php endif; ?>
                <?php if ($person['note']): ?>
                <div class="text-muted fs-13 mt-2 border-top pt-2"><?= nl2br(e($person['note'])) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-briefcase-line me-1"></i> Lịch sử làm việc <span class="badge bg-primary ms-1"><?= count($employments) ?></span></h5>
            </div>
            <div class="card-body">
                <?php if (empty($employments)): ?>
                    <div class="text-center text-muted py-3">Chưa có thông tin công ty.</div>
                <?php else: ?>
                    <?php foreach ($employments as $emp): ?>
                    <div class="d-flex align-items-start gap-3 py-3 <?= $emp !== end($employments) ? 'border-bottom' : '' ?>">
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-<?= ($emp['is_active'] ?? 1) ? 'primary' : 'secondary' ?>-subtle text-<?= ($emp['is_active'] ?? 1) ? 'primary' : 'secondary' ?> rounded-circle">
                                <i class="ri-building-line"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <?php if ($emp['can_access']): ?>
                                <a href="<?= url('contacts/' . $emp['contact_id']) ?>" class="fw-semibold"><?= e($emp['company_name'] ?: $emp['contact_full_name'] ?: '?') ?></a>
                                <?php else: ?>
                                <span class="fw-semibold text-muted"><?= e($emp['company_name'] ?: $emp['contact_full_name'] ?: '?') ?></span>
                                <i class="ri-lock-line text-muted fs-12" title="Không có quyền truy cập"></i>
                                <?php endif; ?>
                                <?php if ($emp['is_active'] ?? 1): ?>
                                <span class="badge bg-success-subtle text-success">Đang làm</span>
                                <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary">Đã nghỉ</span>
                                <?php endif; ?>
                                <?php if ($emp['is_primary']): ?>
                                <span class="badge bg-primary-subtle text-primary">Chính</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($emp['position']): ?>
                            <div class="text-muted fs-13"><i class="ri-user-star-line me-1"></i><?= e($emp['position']) ?></div>
                            <?php endif; ?>
                            <?php if ($emp['phone'] || $emp['email']): ?>
                            <div class="d-flex gap-3 fs-12 mt-1">
                                <?php if ($emp['phone']): ?><span><i class="ri-phone-line me-1"></i><?= e($emp['phone']) ?></span><?php endif; ?>
                                <?php if ($emp['email']): ?><span><i class="ri-mail-line me-1"></i><?= e($emp['email']) ?></span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($emp['start_date'] || $emp['end_date']): ?>
                            <div class="text-muted fs-12 mt-1">
                                <i class="ri-calendar-line me-1"></i>
                                <?= $emp['start_date'] ? format_date($emp['start_date']) : '?' ?>
                                →
                                <?= $emp['end_date'] ? format_date($emp['end_date']) : 'nay' ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($emp['owner_name']): ?>
                            <div class="fs-12 mt-1 text-muted">Phụ trách công ty: <?= e($emp['owner_name']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
