<?php $pageTitle = 'Hóa đơn'; ?>

<!-- Page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Hóa đơn</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('billing') ?>">Thanh toán</a></li>
                    <li class="breadcrumb-item active">Hóa đơn</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-file-list-3-line me-1"></i> Danh sách hóa đơn
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sticky mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã hóa đơn</th>
                                <th>Mô tả</th>
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
                                        <td class="text-muted"><?= e($invoice['description'] ?? '-') ?></td>
                                        <td class="fw-medium"><?= number_format($invoice['amount'] ?? 0, 0, ',', '.') ?> d</td>
                                        <td>
                                            <?php
                                            $statusColors = ['paid' => 'success', 'sent' => 'info', 'overdue' => 'danger', 'draft' => 'secondary'];
                                            $statusLabels = ['paid' => 'Đã thanh toán', 'sent' => 'Đã gửi', 'overdue' => 'Quá hạn', 'draft' => 'Nháp'];
                                            ?>
                                            <span class="badge bg-<?= $statusColors[$invoice['status']] ?? 'secondary' ?>-subtle text-<?= $statusColors[$invoice['status']] ?? 'secondary' ?>">
                                                <?= $statusLabels[$invoice['status']] ?? $invoice['status'] ?>
                                            </span>
                                        </td>
                                        <td><?= !empty($invoice['due_date']) ? date('d/m/Y', strtotime($invoice['due_date'])) : '-' ?></td>
                                        <td><?= !empty($invoice['paid_at']) ? date('d/m/Y', strtotime($invoice['paid_at'])) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="ri-file-list-3-line fs-2 d-block mb-2"></i>
                                        Chưa có hóa đơn nào
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
