<?php $pageTitle = 'Chờ phê duyệt'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Chờ phê duyệt</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('approvals') ?>">Phê duyệt</a></li>
        <li class="breadcrumb-item active">Chờ xử lý</li>
    </ol>
</div>

<?php
    $moduleLabels = [
        'orders' => 'Đơn hàng',
        'deals' => 'Cơ hội',
        'purchase_orders' => 'Đơn hàng mua',
        'fund_transactions' => 'Giao dịch quỹ',
    ];
    $moduleIcons = [
        'orders' => 'ri-file-list-3-line',
        'deals' => 'ri-hand-coin-line',
        'purchase_orders' => 'ri-shopping-cart-line',
        'fund_transactions' => 'ri-wallet-3-line',
    ];
?>

<!-- Tab: Yêu cầu thêm người liên hệ -->
<?php if (!empty($requests)): ?>
<div class="card mb-3">
    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-user-add-line me-1"></i> Yêu cầu thêm người liên hệ <span class="badge bg-warning ms-1"><?= count($requests) ?></span></h5></div>
    <div class="card-body p-2">
        <?php foreach ($requests as $r):
            $contactName = $r['company_name'] ?: trim($r['first_name'] . ' ' . ($r['last_name'] ?? ''));
        ?>
        <div class="border rounded p-3 mb-2">
            <div class="d-flex align-items-start gap-3">
                <?php if (!empty($r['requester_avatar'])): ?>
                <img src="<?= asset($r['requester_avatar']) ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover">
                <?php else: ?>
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:14px"><?= mb_substr($r['requester_name'], 0, 1) ?></div>
                <?php endif; ?>
                <div class="flex-grow-1">
                    <strong><?= e($r['requester_name']) ?></strong>
                    <small class="text-muted ms-1"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></small>
                    <p class="mb-1 mt-1">Yêu cầu thêm người liên hệ vào <strong><?= e($contactName) ?></strong> (<?= e($r['account_code'] ?? '') ?>)</p>
                    <div class="bg-light rounded p-2" style="font-size:13px">
                        <?php if ($r['cp_title']): ?><span class="badge bg-secondary me-1"><?= e(ucfirst($r['cp_title'])) ?></span><?php endif; ?>
                        <strong><?= e($r['cp_name']) ?></strong>
                        <?php if ($r['cp_phone_masked']): ?> · <i class="ri-phone-line"></i> <?= e($r['cp_phone_masked']) ?><?php endif; ?>
                        <?php if ($r['cp_email']): ?> · <i class="ri-mail-line"></i> <?= e($r['cp_email']) ?><?php endif; ?>
                        <?php if ($r['cp_position']): ?> · <?= e($r['cp_position']) ?><?php endif; ?>
                    </div>
                    <?php if ($r['note']): ?><p class="text-muted mt-1 mb-0" style="font-size:13px"><i class="ri-chat-quote-line me-1"></i><?= e($r['note']) ?></p><?php endif; ?>
                    <div class="d-flex gap-2 mt-2">
                        <form method="POST" action="<?= url('merge-requests/' . $r['id'] . '/approve') ?>">
                            <?= csrf_field() ?>
                            <button class="btn btn-success btn-sm"><i class="ri-check-line me-1"></i> Duyệt</button>
                        </form>
                        <form method="POST" action="<?= url('merge-requests/' . $r['id'] . '/reject') ?>" data-confirm="Từ chối yêu cầu này?">
                            <?= csrf_field() ?>
                            <input type="text" name="reason" class="form-control form-control-sm d-inline-block" style="width:200px" placeholder="Lý do từ chối">
                            <button class="btn btn-danger btn-sm"><i class="ri-close-line me-1"></i> Từ chối</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Tab: Yêu cầu tôi đã gửi -->
<?php if (!empty($myRequests)): ?>
<div class="card mb-3">
    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-send-plane-line me-1"></i> Yêu cầu tôi đã gửi</h5></div>
    <div class="card-body p-2">
        <?php foreach ($myRequests as $r):
            $contactName = $r['company_name'] ?: trim($r['first_name'] . ' ' . ($r['last_name'] ?? ''));
            $statusBadge = ['pending' => 'bg-warning', 'approved' => 'bg-success', 'rejected' => 'bg-danger'];
            $statusLabel = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'];
        ?>
        <div class="d-flex align-items-center gap-3 border rounded p-2 mb-2" style="font-size:13px">
            <span class="badge <?= $statusBadge[$r['status']] ?? 'bg-secondary' ?>"><?= $statusLabel[$r['status']] ?? '' ?></span>
            <span>Thêm <strong><?= e($r['cp_name']) ?></strong> (<?= e($r['cp_phone_masked'] ?? '') ?>) vào <strong><?= e($contactName) ?></strong></span>
            <small class="text-muted ms-auto"><?= date('d/m H:i', strtotime($r['created_at'])) ?></small>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Phê duyệt quy trình (existing) -->
<?php if (!empty($pending)): ?>
    <div class="row">
        <?php foreach ($pending as $item): ?>
            <div class="col-lg-6">
                <div class="card border-warning">
                    <div class="card-header bg-warning-subtle d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="<?= $moduleIcons[$item['module']] ?? 'ri-file-line' ?> fs-5 text-warning"></i>
                            <h6 class="mb-0"><?= $moduleLabels[$item['module']] ?? $item['module'] ?></h6>
                        </div>
                        <span class="badge bg-warning">Bước <?= $item['step_order'] ?></span>
                    </div>
                    <div class="card-body">
                        <h5 class="mb-2"><?= e($item['entity_title'] ?? ($item['entity_type'] . ' #' . $item['entity_id'])) ?></h5>
                        <p class="text-muted mb-1">
                            <i class="ri-flow-chart me-1"></i> Quy trình: <strong><?= e($item['flow_name']) ?></strong>
                        </p>
                        <p class="text-muted mb-1">
                            <i class="ri-user-line me-1"></i> Người yêu cầu: <strong><?= e($item['requested_by_name']) ?></strong>
                        </p>
                        <p class="text-muted mb-3">
                            <i class="ri-time-line me-1"></i> Thời gian: <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                        </p>

                        <div class="mb-3">
                            <textarea class="form-control" id="comment-<?= $item['id'] ?>" rows="2" placeholder="Nhận xét (tùy chọn)"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <form method="POST" action="<?= url('approvals/' . $item['id'] . '/approve') ?>" class="flex-grow-1">
                                <?= csrf_field() ?>
                                <input type="hidden" name="comment" class="approval-comment" data-source="comment-<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="ri-check-line me-1"></i> Phê duyệt
                                </button>
                            </form>
                            <form method="POST" action="<?= url('approvals/' . $item['id'] . '/reject') ?>" class="flex-grow-1" data-confirm="Bạn có chắc muốn từ chối yêu cầu này?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="comment" class="approval-comment" data-source="comment-<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="ri-close-line me-1"></i> Từ chối
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="ri-checkbox-circle-line" style="font-size:48px"></i>
            <p class="mt-3 mb-0">Không có yêu cầu phê duyệt nào đang chờ</p>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy comment text to hidden inputs before form submit
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            var hiddenInput = form.querySelector('.approval-comment');
            if (hiddenInput) {
                var sourceId = hiddenInput.getAttribute('data-source');
                var textarea = document.getElementById(sourceId);
                if (textarea) {
                    hiddenInput.value = textarea.value;
                }
            }
        });
    });
});
</script>
