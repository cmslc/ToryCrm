<?php $pageTitle = 'Định nghĩa dữ liệu'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Định nghĩa dữ liệu</h4>
</div>

<div class="row">
    <?php foreach ($modules as $key => $mod): ?>
    <div class="col-lg-6 col-xl-4">
        <a href="<?= url('settings/data-definition/' . $key) ?>" class="card card-body text-decoration-none">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-primary-subtle text-primary rounded">
                        <i class="<?= $mod['icon'] ?> fs-20"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <h5 class="mb-1"><?= e($mod['label']) ?></h5>
                    <p class="text-muted mb-0 fs-13"><?= e($mod['desc']) ?></p>
                </div>
                <i class="ri-arrow-right-s-line fs-20 text-muted"></i>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
