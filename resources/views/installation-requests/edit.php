<?php $pageTitle = 'Sửa yêu cầu thi công'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sửa yêu cầu thi công <span class="text-muted fs-14"><?= e($request['code']) ?></span></h4>
    <a href="<?= url('installation-requests/' . $request['id']) ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<form method="POST" action="<?= url('installation-requests/' . $request['id'] . '/update') ?>">
    <?= csrf_field() ?>
    <?php
    $isEdit = true;
    $code = $request['code'];
    $order = null;
    if (!empty($request['order_id'])) {
        $order = [
            'id' => $request['order_id'],
            'order_number' => $request['order_number'] ?? '',
        ];
    }
    $contact = null;
    if (!empty($request['contact_id'])) {
        $contact = [
            'id' => $request['contact_id'],
            'full_name' => $request['c_full_name'] ?? '',
            'company_name' => $request['c_company_name'] ?? '',
            'account_code' => $request['c_account_code'] ?? '',
        ];
    }
    $me = null;
    include __DIR__ . '/_form.php';
    ?>
</form>
