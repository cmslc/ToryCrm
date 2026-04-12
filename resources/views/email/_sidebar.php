<?php
$_folderLabels = ['inbox'=>'Hộp thư đến','sent'=>'Đã gửi','drafts'=>'Nháp','trash'=>'Thùng rác','spam'=>'Spam'];
$_folderIcons = ['inbox'=>'ri-inbox-line','sent'=>'ri-send-plane-line','drafts'=>'ri-draft-line','trash'=>'ri-delete-bin-line','spam'=>'ri-spam-line'];
$_currentPage = $_currentPage ?? '';
$_accountId = $accountId ?? 0;
$_accounts = $accounts ?? [];
$_folders = $folders ?? [];
$_folderMap = [];
foreach ($_folders as $_f) $_folderMap[$_f['folder']] = $_f;
$_activeFolder = $folder ?? '';
?>
<div class="flex-shrink-0" style="width:220px">
    <a href="<?= url('email/compose') ?>" class="btn btn-primary w-100 mb-3 py-2">
        <i class="ri-edit-line me-1"></i> Soạn thư
    </a>
    <div class="nav flex-column">
        <?php foreach (['inbox','sent','drafts','trash','spam'] as $_f):
            $_cnt = $_folderMap[$_f]['cnt'] ?? 0;
            $_unread = $_folderMap[$_f]['unread'] ?? 0;
            if ($_cnt == 0 && !in_array($_f, ['inbox','sent','trash'])) continue;
            $_isActive = ($_activeFolder === $_f && $_currentPage !== 'settings' && $_currentPage !== 'templates');
        ?>
        <a href="<?= url('email?account=' . $_accountId . '&folder=' . $_f) ?>"
           class="d-flex align-items-center px-3 py-2 rounded text-decoration-none mb-1 <?= $_isActive ? 'bg-primary-subtle text-primary fw-medium' : 'text-body' ?>">
            <i class="<?= $_folderIcons[$_f] ?> me-2 fs-16"></i>
            <span class="flex-grow-1"><?= $_folderLabels[$_f] ?></span>
            <?php if ($_unread > 0): ?><span class="fw-bold fs-12"><?= $_unread ?></span><?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <hr class="my-2">
    <div class="nav flex-column">
        <a href="<?= url('email/templates') ?>" class="d-flex align-items-center px-3 py-2 rounded text-decoration-none mb-1 <?= $_currentPage === 'templates' ? 'bg-primary-subtle text-primary fw-medium' : 'text-body' ?>">
            <i class="ri-file-text-line me-2 fs-16"></i> Mẫu email
        </a>
        <a href="<?= url('email/settings') ?>" class="d-flex align-items-center px-3 py-2 rounded text-decoration-none mb-1 <?= $_currentPage === 'settings' ? 'bg-primary-subtle text-primary fw-medium' : 'text-body' ?>">
            <i class="ri-settings-3-line me-2 fs-16"></i> Cài đặt
        </a>
    </div>
    <hr class="my-2">
    <div class="px-2">
        <?php if (count($_accounts) > 1): ?>
        <select class="form-select mb-2" onchange="location.href='<?= url('email') ?>?account='+this.value">
            <?php foreach ($_accounts as $_acc): ?>
            <option value="<?= $_acc['id'] ?>" <?= $_acc['id'] == $_accountId ? 'selected' : '' ?>><?= e($_acc['email']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php elseif (!empty($_accounts)): ?>
        <small class="text-muted d-block mb-2"><?= e($_accounts[0]['email'] ?? '') ?></small>
        <?php endif; ?>
        <form method="POST" action="<?= url('email/sync') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="account_id" value="<?= $_accountId ?>">
            <button class="btn btn-soft-secondary w-100"><i class="ri-refresh-line me-1"></i> Đồng bộ</button>
        </form>
    </div>
</div>
