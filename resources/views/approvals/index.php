<?php $pageTitle = 'Quy trình phê duyệt'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Quy trình phê duyệt</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('approvals/pending') ?>" class="btn btn-soft-warning">
            <i class="ri-time-line me-1"></i> Chờ phê duyệt
        </a>
        <a href="<?= url('approvals/create') ?>" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> Tạo quy trình
        </a>
    </div>
</div>

<?php
    $moduleLabels = [
        'orders' => 'Đơn hàng',
        'deals' => 'Cơ hội',
        'purchase_orders' => 'Đơn hàng mua',
        'fund_transactions' => 'Giao dịch quỹ',
    ];
?>

<div class="card">
    <div class="card-body">
        <?php if (!empty($flows)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tên quy trình</th>
                            <th>Module</th>
                            <th>Các bước duyệt</th>
                            <th>Số yêu cầu</th>
                            <th>Trạng thái</th>
                            <th>Người tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flows as $flow): ?>
                            <tr>
                                <td class="fw-medium"><?= e($flow['name']) ?></td>
                                <td>
                                    <span class="badge bg-info-subtle text-info">
                                        <?= $moduleLabels[$flow['module']] ?? $flow['module'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <?php foreach ($flow['steps'] ?? [] as $step): ?>
                                            <span class="badge bg-primary-subtle text-primary">
                                                <?= $step['step_order'] ?>. <?= e($step['approver_name']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= $flow['request_count'] ?? 0 ?></span>
                                </td>
                                <td>
                                    <?php if ($flow['is_active']): ?>
                                        <span class="badge bg-success-subtle text-success">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Tắt</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($flow['created_by_name'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="ri-checkbox-circle-line" style="font-size:48px"></i>
                <p class="mt-3 mb-0">Chưa có quy trình phê duyệt nào</p>
                <a href="<?= url('approvals/create') ?>" class="btn btn-primary mt-3">
                    <i class="ri-add-line me-1"></i> Tạo quy trình đầu tiên
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
