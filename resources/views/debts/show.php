<?php
$pageTitle = 'Chi tiết công nợ';
$remaining = $debt['amount'] - $debt['paid_amount'];
$isOverdue = !empty($debt['due_date']) && $debt['due_date'] < date('Y-m-d') && $debt['status'] !== 'paid';
$sc = ['open' => 'info', 'partial' => 'warning', 'paid' => 'success', 'overdue' => 'danger', 'written_off' => 'secondary'];
$sl = ['open' => 'Mở', 'partial' => 'Một phần', 'paid' => 'Đã thanh toán', 'overdue' => 'Quá hạn', 'written_off' => 'Xóa nợ'];
$statusKey = $isOverdue ? 'overdue' : $debt['status'];
$typeLabel = $debt['type'] === 'receivable' ? 'Phải thu' : 'Phải trả';
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $typeLabel ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('debts') ?>">Công nợ</a></li>
                <li class="breadcrumb-item active">Chi tiết</li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-9">
                <!-- Debt Info -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= $typeLabel ?></h5>
                            <span class="badge bg-<?= $sc[$statusKey] ?? 'secondary' ?>"><?= $sl[$statusKey] ?? '' ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Khách hàng</h6>
                                <?php $contactName = trim(($debt['contact_first_name'] ?? '') . ' ' . ($debt['contact_last_name'] ?? '')); ?>
                                <?php if ($contactName): ?>
                                    <p class="mb-1 fw-medium"><?= e($contactName) ?></p>
                                    <?php if (!empty($debt['contact_email'])): ?><p class="mb-1 text-muted"><i class="ri-mail-line me-1"></i><?= e($debt['contact_email']) ?></p><?php endif; ?>
                                    <?php if (!empty($debt['contact_phone'])): ?><p class="mb-0 text-muted"><i class="ri-phone-line me-1"></i><?= e($debt['contact_phone']) ?></p><?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">-</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Công ty</h6>
                                <p class="mb-0 fw-medium"><?= e($debt['company_name'] ?? '-') ?></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <p class="text-muted mb-1">Tổng nợ</p>
                                    <h5 class="mb-0"><?= format_money($debt['amount']) ?></h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <p class="text-muted mb-1">Đã <?= $debt['type'] === 'receivable' ? 'thu' : 'trả' ?></p>
                                    <h5 class="mb-0 text-success"><?= format_money($debt['paid_amount']) ?></h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <p class="text-muted mb-1">Còn lại</p>
                                    <h5 class="mb-0 text-danger"><?= format_money($remaining) ?></h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center">
                                    <p class="text-muted mb-1">Ngày đến hạn</p>
                                    <h5 class="mb-0 <?= $isOverdue ? 'text-danger' : '' ?>"><?= !empty($debt['due_date']) ? format_date($debt['due_date']) : '-' ?></h5>
                                </div>
                            </div>
                        </div>

                        <?php if ($debt['amount'] > 0): ?>
                            <div class="mt-3">
                                <div class="progress" style="height: 8px;">
                                    <?php $pct = min(100, round(($debt['paid_amount'] / $debt['amount']) * 100)); ?>
                                    <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
                                </div>
                                <small class="text-muted"><?= $pct ?>% đã thanh toán</small>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($debt['order_number'])): ?>
                            <div class="mt-3">
                                <h6 class="text-muted mb-1">Đơn hàng liên quan</h6>
                                <a href="<?= url('orders/' . $debt['order_id']) ?>" class="fw-medium"><?= e($debt['order_number']) ?></a>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($debt['note'])): ?>
                            <div class="mt-3">
                                <h6 class="text-muted mb-1">Ghi chú</h6>
                                <p class="mb-0"><?= nl2br(e($debt['note'])) ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="mt-3 text-muted">
                            <small>Người tạo: <?= e($debt['created_by_name'] ?? '-') ?> | Ngày tạo: <?= !empty($debt['created_at']) ? format_datetime($debt['created_at']) : '-' ?></small>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Lịch sử thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($payments)): ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ngày</th>
                                            <th>Số tiền</th>
                                            <th>Phương thức</th>
                                            <th>Ghi chú</th>
                                            <th>Người ghi nhận</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $methodLabels = ['cash' => 'Tiền mặt', 'bank_transfer' => 'Chuyển khoản', 'card' => 'Thẻ', 'other' => 'Khác'];
                                        foreach ($payments as $p):
                                        ?>
                                            <tr>
                                                <td><?= !empty($p['paid_at']) ? format_date($p['paid_at']) : '-' ?></td>
                                                <td class="fw-medium text-success"><?= format_money($p['amount']) ?></td>
                                                <td><?= $methodLabels[$p['payment_method'] ?? ''] ?? ($p['payment_method'] ?? '-') ?></td>
                                                <td><?= e($p['note'] ?? '-') ?></td>
                                                <td><?= user_avatar($p['created_by_name'] ?? null) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3 mb-0">Chưa có thanh toán</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <!-- Add Payment -->
                <?php if ($remaining > 0): ?>
                    <div class="card border-primary">
                        <div class="card-header bg-primary-subtle">
                            <h5 class="card-title mb-0 text-primary"><i class="ri-add-circle-line me-1"></i> Ghi nhận thanh toán</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= url('debts/' . $debt['id'] . '/payment') ?>">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label class="form-label">Số tiền <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="amount" required min="1" max="<?= $remaining ?>" step="any" placeholder="Tối đa <?= format_money($remaining) ?>">
                                    <small class="text-muted">Còn lại: <?= format_money($remaining) ?></small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Phương thức thanh toán</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="cash">Tiền mặt</option>
                                        <option value="bank_transfer">Chuyển khoản</option>
                                        <option value="card">Thẻ</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ngày thanh toán</label>
                                    <input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Ghi chú thanh toán..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="create_fund_transaction" id="createFund" value="1">
                                        <label class="form-check-label" for="createFund">Tạo phiếu thu/chi tương ứng</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100"><i class="ri-check-line me-1"></i> Ghi nhận</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
