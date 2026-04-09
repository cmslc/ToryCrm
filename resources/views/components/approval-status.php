<?php
/**
 * Reusable component showing approval progress.
 * Usage: include with $entityType and $entityId set.
 *
 * Expected variables:
 *   $entityType - string (orders, deals, etc.)
 *   $entityId   - int
 */

use App\Services\ApprovalService;

$_approvalEntityType = $entityType ?? '';
$_approvalEntityId = $entityId ?? 0;

if (!empty($_approvalEntityType) && !empty($_approvalEntityId)) {
    $_approvalStatus = ApprovalService::getStatus($_approvalEntityType, $_approvalEntityId);

    if ($_approvalStatus):
        $_statusBadge = match ($_approvalStatus['status']) {
            'approved' => '<span class="badge bg-success-subtle text-success">Đã duyệt</span>',
            'rejected' => '<span class="badge bg-danger-subtle text-danger">Từ chối</span>',
            default => '<span class="badge bg-warning-subtle text-warning">Đang chờ duyệt</span>',
        };
?>
<div class="card border-top border-primary">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="card-title mb-0">
            <i class="ri-checkbox-circle-line me-1"></i> Phê duyệt: <?= e($_approvalStatus['flow_name']) ?>
        </h6>
        <?= $_statusBadge ?>
    </div>
    <div class="card-body">
        <!-- Horizontal Stepper -->
        <div class="d-flex align-items-start justify-content-between position-relative" style="padding: 0 20px">
            <!-- Connecting Line -->
            <div class="position-absolute" style="top:20px;left:40px;right:40px;height:2px;background:#e9ebec;z-index:0"></div>

            <?php foreach ($_approvalStatus['steps'] as $step):
                $stepStatus = $step['status'];
                $action = $step['action'] ?? null;

                if ($stepStatus === 'approved') {
                    $iconClass = 'ri-check-line';
                    $bgClass = 'bg-success text-white';
                    $textClass = 'text-success';
                } elseif ($stepStatus === 'rejected') {
                    $iconClass = 'ri-close-line';
                    $bgClass = 'bg-danger text-white';
                    $textClass = 'text-danger';
                } elseif ($stepStatus === 'current') {
                    $iconClass = 'ri-time-line';
                    $bgClass = 'bg-warning text-white';
                    $textClass = 'text-warning';
                } else {
                    $iconClass = 'ri-more-line';
                    $bgClass = 'bg-light text-muted';
                    $textClass = 'text-muted';
                }
            ?>
                <div class="text-center position-relative" style="z-index:1;min-width:100px">
                    <div class="avatar-sm mx-auto mb-2">
                        <div class="avatar-title rounded-circle <?= $bgClass ?>" style="width:40px;height:40px">
                            <i class="<?= $iconClass ?>"></i>
                        </div>
                    </div>
                    <h6 class="mb-1 <?= $textClass ?>" style="font-size:13px"><?= e($step['step_label'] ?? ('Bước ' . $step['step_order'])) ?></h6>
                    <p class="text-muted mb-0" style="font-size:12px"><?= e($step['approver_name']) ?></p>
                    <?php if ($action): ?>
                        <small class="text-muted"><?= date('d/m H:i', strtotime($action['created_at'])) ?></small>
                        <?php if (!empty($action['comment'])): ?>
                            <br><small class="fst-italic">"<?= e($action['comment']) ?>"</small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php
    endif;
}
?>
