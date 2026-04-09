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
