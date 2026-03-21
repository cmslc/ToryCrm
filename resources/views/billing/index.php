<?php $pageTitle = 'Gói dịch vụ & Thanh toán'; ?>

<!-- Page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Gói dịch vụ & Thanh toán</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item active">Thanh toán</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php if ($subscription): ?>
<!-- Current Plan -->
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">
                    <i class="ri-vip-crown-2-line me-1"></i> Gói hiện tại
                </h5>
                <?php
                    $statusColors = ['active' => 'success', 'trialing' => 'info', 'cancelled' => 'danger', 'expired' => 'warning'];
                    $statusLabels = ['active' => 'Đang hoạt động', 'trialing' => 'Dùng thử', 'cancelled' => 'Đã hủy', 'expired' => 'Hết hạn'];
                ?>
                <span class="badge bg-<?= $statusColors[$subscription['status']] ?? 'secondary' ?>">
                    <?= $statusLabels[$subscription['status']] ?? $subscription['status'] ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="text-primary"><?= e($subscription['plan_name']) ?></h3>
                        <div class="mb-3">
                            <span class="fs-4 fw-bold text-dark">
                                <?= number_format($subscription['amount'] ?? 0, 0, ',', '.') ?> d
                            </span>
                            <span class="text-muted">
                                / <?= $subscription['billing_cycle'] === 'yearly' ? 'năm' : 'tháng' ?>
                            </span>
                        </div>
                        <p class="text-muted mb-1">
                            <i class="ri-calendar-line me-1"></i>
                            Bắt đầu: <?= date('d/m/Y', strtotime($subscription['start_date'])) ?>
                        </p>
                        <p class="text-muted mb-1">
                            <i class="ri-calendar-check-line me-1"></i>
                            Thanh toán tiếp theo: <?= date('d/m/Y', strtotime($subscription['end_date'])) ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <a href="<?= url('billing/plans') ?>" class="btn btn-primary me-2">
                            <i class="ri-arrow-up-circle-line me-1"></i> Nâng cấp gói
                        </a>
                        <form method="POST" action="<?= url('billing/cancel-subscription') ?>" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy gói dịch vụ?')">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="ri-close-circle-line me-1"></i> Hủy gói
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-pie-chart-line me-1"></i> Mức sử dụng</h5>
            </div>
            <div class="card-body">
                <?php
                    $maxUsers = $subscription['max_users'] ?? 0;
                    $maxContacts = $subscription['max_contacts'] ?? 0;
                    $maxDeals = $subscription['max_deals'] ?? 0;
                    $maxStorage = $subscription['max_storage_mb'] ?? 0;

                    $usersPercent = $maxUsers > 0 ? min(100, round(($usage['users'] / $maxUsers) * 100)) : 0;
                    $contactsPercent = $maxContacts > 0 ? min(100, round(($usage['contacts'] / $maxContacts) * 100)) : 0;
                    $dealsPercent = $maxDeals > 0 ? min(100, round(($usage['deals'] / $maxDeals) * 100)) : 0;
                ?>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Người dùng</span>
                        <span class="fw-medium"><?= $usage['users'] ?>/<?= $maxUsers > 0 ? $maxUsers : 'KGH' ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-<?= $usersPercent >= 90 ? 'danger' : ($usersPercent >= 70 ? 'warning' : 'primary') ?>" style="width: <?= $usersPercent ?>%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Liên hệ</span>
                        <span class="fw-medium"><?= number_format($usage['contacts']) ?>/<?= $maxContacts > 0 ? number_format($maxContacts) : 'KGH' ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-<?= $contactsPercent >= 90 ? 'danger' : ($contactsPercent >= 70 ? 'warning' : 'success') ?>" style="width: <?= $contactsPercent ?>%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Cơ hội</span>
                        <span class="fw-medium"><?= number_format($usage['deals']) ?>/<?= $maxDeals > 0 ? number_format($maxDeals) : 'KGH' ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-<?= $dealsPercent >= 90 ? 'danger' : ($dealsPercent >= 70 ? 'warning' : 'info') ?>" style="width: <?= $dealsPercent ?>%"></div>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Lưu trữ</span>
                        <span class="fw-medium"><?= $maxStorage > 0 ? round($maxStorage/1024,1) . ' GB' : 'KGH' ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 15%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="avatar-lg mx-auto mb-3">
                    <span class="avatar-title bg-warning-subtle rounded-circle fs-1">
                        <i class="ri-vip-crown-2-line text-warning"></i>
                    </span>
                </div>
                <h4>Bạn chưa đăng ký gói dịch vụ nào</h4>
                <p class="text-muted mb-4">Chọn một gói dịch vụ phù hợp để bắt đầu sử dụng đầy đủ tính năng của ToryCRM.</p>
                <a href="<?= url('billing/plans') ?>" class="btn btn-primary btn-lg">
                    <i class="ri-shopping-cart-line me-1"></i> Xem các gói dịch vụ
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Invoices -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">
                    <i class="ri-file-list-3-line me-1"></i> Hóa đơn gần đây
                </h5>
                <a href="<?= url('billing/invoices') ?>" class="btn btn-sm btn-soft-primary">Xem tất cả</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã hóa đơn</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Hạn thanh toán</th>
                                <th>Ngày thanh toán</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($invoices)): ?>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($invoice['invoice_number']) ?></td>
                                        <td><?= number_format($invoice['amount'] ?? 0, 0, ',', '.') ?> d</td>
                                        <td>
                                            <?php
                                            $invStatusColors = ['paid' => 'success', 'sent' => 'info', 'overdue' => 'danger', 'draft' => 'secondary'];
                                            $invStatusLabels = ['paid' => 'Đã thanh toán', 'sent' => 'Đã gửi', 'overdue' => 'Quá hạn', 'draft' => 'Nháp'];
                                            ?>
                                            <span class="badge bg-<?= $invStatusColors[$invoice['status']] ?? 'secondary' ?>-subtle text-<?= $invStatusColors[$invoice['status']] ?? 'secondary' ?>">
                                                <?= $invStatusLabels[$invoice['status']] ?? $invoice['status'] ?>
                                            </span>
                                        </td>
                                        <td><?= !empty($invoice['due_date']) ? date('d/m/Y', strtotime($invoice['due_date'])) : '-' ?></td>
                                        <td><?= !empty($invoice['paid_at']) ? date('d/m/Y', strtotime($invoice['paid_at'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Chưa có hóa đơn nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
