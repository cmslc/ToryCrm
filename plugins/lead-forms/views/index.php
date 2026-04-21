<?php $pageTitle = 'Lead Forms'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-survey-line me-2"></i> Lead Forms</h4>
    <a href="<?= url('lead-forms/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo form</a>
</div>

<div class="row">
    <?php foreach ($forms as $f): ?>
    <div class="col-xl-4 col-md-6">
        <div class="card card-height-100">
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <div class="avatar-sm flex-shrink-0">
                        <div class="avatar-title bg-<?= $f['is_active'] ? 'success' : 'secondary' ?>-subtle text-<?= $f['is_active'] ? 'success' : 'secondary' ?> rounded fs-18">
                            <i class="ri-survey-line"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1"><?= e($f['name']) ?></h6>
                        <div class="d-flex gap-1">
                            <span class="badge bg-<?= $f['is_active'] ? 'success' : 'secondary' ?>-subtle text-<?= $f['is_active'] ? 'success' : 'secondary' ?>"><?= $f['is_active'] ? 'Hoạt động' : 'Tắt' ?></span>
                            <span class="badge bg-primary-subtle text-primary"><?= $f['submission_count'] ?> leads</span>
                        </div>
                    </div>
                </div>
                <?php if ($f['description']): ?><p class="text-muted fs-13 mb-3"><?= e($f['description']) ?></p><?php endif; ?>
                <div class="d-flex align-items-center justify-content-between">
                    <small class="text-muted"><?= created_ago($f['created_at']) ?></small>
                    <div class="d-flex gap-1">
                        <a href="<?= url('lead-forms/' . $f['id'] . '/submissions') ?>" class="btn btn-soft-info btn-icon" title="Xem leads"><i class="ri-list-check"></i></a>
                        <a href="<?= url('lead-forms/' . $f['id'] . '/embed') ?>" class="btn btn-soft-success btn-icon" title="Mã nhúng"><i class="ri-code-line"></i></a>
                        <a href="<?= url('lead-forms/' . $f['id'] . '/edit') ?>" class="btn btn-soft-primary btn-icon" title="Sửa"><i class="ri-pencil-line"></i></a>
                        <form method="POST" action="<?= url('lead-forms/' . $f['id'] . '/delete') ?>" onsubmit="return confirm('Xóa form này?')" class="d-inline">
                            <?= csrf_field() ?>
                            <button class="btn btn-soft-danger btn-icon" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($forms)): ?>
    <div class="col-12"><div class="card"><div class="card-body text-center py-5 text-muted">
        <i class="ri-survey-line fs-1 d-block mb-2"></i>
        <h5>Chưa có form nào</h5>
        <p>Tạo form để thu thập thông tin khách hàng từ website.</p>
        <a href="<?= url('lead-forms/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo form đầu tiên</a>
    </div></div></div>
    <?php endif; ?>
</div>
