<?php $pageTitle = e($message['subject'] ?? 'Email'); $m = $message; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0 text-truncate" style="max-width:70%"><?= e($m['subject'] ?: '(Không tiêu đề)') ?></h4>
    <div class="d-flex gap-2">
        <a href="<?= url('email/compose?reply_to=' . $m['id']) ?>" class="btn btn-primary"><i class="ri-reply-line me-1"></i> Trả lời</a>
        <form method="POST" action="<?= url('email/' . $m['id'] . '/trash') ?>"><?= csrf_field() ?><button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button></form>
        <a href="<?= url('email?folder=' . ($m['folder'] ?? 'inbox')) ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width:40px;height:40px">
                <?= strtoupper(mb_substr($m['from_name'] ?: $m['from_email'], 0, 1)) ?>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center">
                    <span class="fw-medium"><?= e($m['from_name'] ?: $m['from_email']) ?></span>
                    <span class="text-muted ms-2 fs-12">&lt;<?= e($m['from_email']) ?>&gt;</span>
                    <?php if ($m['contact_id']): ?>
                    <a href="<?= url('contacts/' . $m['contact_id']) ?>" class="badge bg-success-subtle text-success ms-2">Khách hàng</a>
                    <?php endif; ?>
                </div>
                <div class="text-muted fs-12">
                    Đến: <?= e($m['to_emails'] ?? $m['account_email']) ?>
                    <?php if ($m['cc_emails']): ?> | CC: <?= e($m['cc_emails']) ?><?php endif; ?>
                    | <?= $m['sent_at'] ? date('d/m/Y H:i', strtotime($m['sent_at'])) : '' ?>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if ($m['body_html']): ?>
        <div class="email-body"><?= $m['body_html'] ?></div>
        <?php elseif ($m['body_text']): ?>
        <pre class="mb-0" style="white-space:pre-wrap;font-family:inherit"><?= e($m['body_text']) ?></pre>
        <?php else: ?>
        <p class="text-muted">(Không có nội dung)</p>
        <?php endif; ?>
    </div>
</div>

<style>
.email-body img { max-width: 100%; height: auto; }
.email-body table { max-width: 100%; }
.email-body { word-break: break-word; }
</style>
