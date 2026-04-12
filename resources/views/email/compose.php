<?php
$pageTitle = 'Soạn email';
$isReply = !empty($replyMsg);
$defaultTo = $contactEmail ?? ($isReply ? $replyMsg['from_email'] : '');
$defaultSubject = $isReply ? 'Re: ' . $replyMsg['subject'] : '';
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-edit-line me-2"></i> <?= $isReply ? 'Trả lời email' : 'Soạn email mới' ?></h4>
    <a href="<?= url('email') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<?php if (empty($accounts)): ?>
<div class="card"><div class="card-body text-center py-5">
    <p class="text-muted">Chưa cấu hình tài khoản email.</p>
    <a href="<?= url('email/settings') ?>" class="btn btn-primary">Cấu hình ngay</a>
</div></div>
<?php else: ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url('email/send') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Từ</label>
                <select name="account_id" class="form-select">
                    <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>"><?= e($acc['display_name'] ? $acc['display_name'] . ' <' . $acc['email'] . '>' : $acc['email']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Đến <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="to" value="<?= e($defaultTo) ?>" required placeholder="email@example.com">
            </div>
            <div class="mb-3">
                <label class="form-label">CC</label>
                <input type="text" class="form-control" name="cc" placeholder="Phân cách bằng dấu phẩy">
            </div>
            <div class="mb-3">
                <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="subject" value="<?= e($defaultSubject) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nội dung</label>
                <textarea class="form-control" name="body" rows="12" id="emailBody"><?php
                    if ($isReply) {
                        echo "\n\n<br><hr><p><strong>" . e($replyMsg['from_name'] ?: $replyMsg['from_email']) . "</strong> - " . date('d/m/Y H:i', strtotime($replyMsg['sent_at'])) . ":</p>";
                        echo $replyMsg['body_html'] ?: nl2br(e($replyMsg['body_text']));
                    }
                ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="ri-send-plane-line me-1"></i> Gửi</button>
                <a href="<?= url('email') ?>" class="btn btn-soft-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
