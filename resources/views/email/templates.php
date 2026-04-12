<?php $pageTitle = 'Mẫu email'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-file-text-line me-2"></i> Mẫu email</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tplModal"><i class="ri-add-line me-1"></i> Tạo mẫu</button>
        <a href="<?= url('email') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Email</a>
    </div>
</div>

<div class="row">
    <?php foreach ($templates as $t): ?>
    <div class="col-md-4">
        <div class="card card-height-100">
            <div class="card-body">
                <h6 class="mb-1"><?= e($t['name']) ?></h6>
                <p class="text-muted fs-12 mb-2">Tiêu đề: <?= e($t['subject'] ?: '-') ?></p>
                <p class="text-muted fs-13 mb-0"><?= e(mb_substr(strip_tags($t['body']), 0, 100)) ?>...</p>
            </div>
            <div class="card-footer border-top d-flex gap-1">
                <a href="<?= url('email/compose?template=' . $t['id']) ?>" class="btn btn-soft-primary flex-grow-1"><i class="ri-mail-send-line me-1"></i> Dùng</a>
                <form method="POST" action="<?= url('email/templates/' . $t['id'] . '/delete') ?>" onsubmit="return confirm('Xóa mẫu này?')">
                    <?= csrf_field() ?>
                    <button class="btn btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($templates)): ?>
    <div class="col-12"><div class="card"><div class="card-body text-center py-5 text-muted">
        <i class="ri-file-text-line fs-1 d-block mb-2"></i>Chưa có mẫu email
    </div></div></div>
    <?php endif; ?>
</div>

<!-- Create Template Modal -->
<div class="modal fade" id="tplModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= url('email/templates/save') ?>">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title">Tạo mẫu email</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Tên mẫu <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" required placeholder="VD: Chào mừng KH mới"></div>
                    <div class="mb-3"><label class="form-label">Tiêu đề email</label><input type="text" class="form-control" name="subject" placeholder="VD: Chào mừng bạn đến với..."></div>
                    <div class="mb-3"><label class="form-label">Nội dung</label><textarea class="form-control" name="body" rows="8" placeholder="Nội dung mẫu..."></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button></div>
            </form>
        </div>
    </div>
</div>
