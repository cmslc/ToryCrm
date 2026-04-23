<?php $pageTitle = 'Tạo yêu cầu thi công'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Tạo yêu cầu thi công</h4>
    <a href="<?= url('installation-requests') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<form method="POST" action="<?= url('installation-requests/store') ?>">
    <?= csrf_field() ?>
    <?php
    $isEdit = false;
    include __DIR__ . '/_form.php';
    ?>
</form>
