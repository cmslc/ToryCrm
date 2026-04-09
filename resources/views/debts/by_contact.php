<?php $pageTitle = 'Công nợ theo khách hàng'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Công nợ theo khách hàng</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('debts') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?= ($type ?? 'receivable') === 'receivable' ? 'active' : '' ?>" href="<?= url('debts/by-contact?type=receivable') ?>">Phải thu</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($type ?? '') === 'payable' ? 'active' : '' ?>" href="<?= url('debts/by-contact?type=payable') ?>">Phải trả</a>
            </li>
        </ul>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Khách hàng / Công ty</th>
                                <th class="text-end">Số công nợ</th>
                                <th class="text-end">Tổng nợ</th>
                                <th class="text-end">Đã <?= ($type ?? 'receivable') === 'receivable' ? 'thu' : 'trả' ?></th>
                                <th class="text-end">Còn lại</th>
                                <th class="text-end">Quá hạn</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data)): ?>
                                <?php foreach ($data as $row): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($row['name']) ?></td>
                                        <td class="text-end"><?= (int) $row['debt_count'] ?></td>
                                        <td class="text-end"><?= format_money($row['total_amount']) ?></td>
                                        <td class="text-end text-success"><?= format_money($row['total_paid']) ?></td>
                                        <td class="text-end fw-bold text-danger"><?= format_money($row['total_remaining']) ?></td>
                                        <td class="text-end">
                                            <?php if ($row['overdue_amount'] > 0): ?>
                                                <span class="text-danger fw-bold"><?= format_money($row['overdue_amount']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">0 đ</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
