<?php $pageTitle = 'Công nợ'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Công nợ</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('debts/aging') ?>" class="btn btn-soft-warning"><i class="ri-bar-chart-grouped-line me-1"></i> Tuổi nợ</a>
                <a href="<?= url('debts/by-contact') ?>" class="btn btn-soft-info"><i class="ri-group-line me-1"></i> Theo KH</a>
                <a href="<?= url('debts/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo công nợ</a>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?= ($filters['type'] ?? 'receivable') === 'receivable' ? 'active' : '' ?>" href="<?= url('debts?type=receivable') ?>">
                    <i class="ri-arrow-down-circle-line me-1"></i> Phải thu
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($filters['type'] ?? '') === 'payable' ? 'active' : '' ?>" href="<?= url('debts?type=payable') ?>">
                    <i class="ri-arrow-up-circle-line me-1"></i> Phải trả
                </a>
            </li>
        </ul>

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Tổng phải thu</p>
                                <h4 class="mt-2 mb-0 text-success"><?= format_money($summary['total_receivable'] ?? 0) ?></h4>
                            </div>
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-2">
                                    <i class="ri-arrow-down-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Tổng phải trả</p>
                                <h4 class="mt-2 mb-0 text-danger"><?= format_money($summary['total_payable'] ?? 0) ?></h4>
                            </div>
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-2">
                                    <i class="ri-arrow-up-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Quá hạn</p>
                                <h4 class="mt-2 mb-0 text-warning"><?= format_money($summary['total_overdue'] ?? 0) ?></h4>
                            </div>
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-2">
                                    <i class="ri-error-warning-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Đã thu/trả tháng này</p>
                                <h4 class="mt-2 mb-0 text-primary"><?= format_money($summary['collected_this_month'] ?? 0) ?></h4>
                            </div>
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-2">
                                    <i class="ri-checkbox-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Table -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('debts') ?>" class="row g-3 mb-4">
                    <input type="hidden" name="type" value="<?= e($filters['type'] ?? 'receivable') ?>">
                    <div class="col-md-2">
                        <select name="contact_id" class="form-select searchable-select">
                            <option value="">Tất cả KH</option>
                            <?php foreach ($contacts ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($filters['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e(trim($c['first_name'] . ' ' . $c['last_name'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="company_id" class="form-select searchable-select">
                            <option value="">Tất cả công ty</option>
                            <?php foreach ($companies ?? [] as $co): ?>
                                <option value="<?= $co['id'] ?>" <?= ($filters['company_id'] ?? '') == $co['id'] ? 'selected' : '' ?>><?= e($co['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Mở</option>
                            <option value="partial" <?= ($filters['status'] ?? '') === 'partial' ? 'selected' : '' ?>>Một phần</option>
                            <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                            <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Quá hạn</option>
                            <option value="written_off" <?= ($filters['status'] ?? '') === 'written_off' ? 'selected' : '' ?>>Xóa nợ</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" placeholder="Từ ngày">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" placeholder="Đến ngày">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Lọc</button>
                        <a href="<?= url('debts?type=' . e($filters['type'] ?? 'receivable')) ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <?php
                $sc = ['open' => 'info', 'partial' => 'warning', 'paid' => 'success', 'overdue' => 'danger', 'written_off' => 'secondary'];
                $sl = ['open' => 'Mở', 'partial' => 'Một phần', 'paid' => 'Đã TT', 'overdue' => 'Quá hạn', 'written_off' => 'Xóa nợ'];
                ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>KH / Công ty</th>
                                <th>Mã đơn</th>
                                <th>Số tiền</th>
                                <th>Đã <?= ($filters['type'] ?? 'receivable') === 'receivable' ? 'thu' : 'trả' ?></th>
                                <th>Còn lại</th>
                                <th>Ngày đến hạn</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($debts['items'])): ?>
                                <?php foreach ($debts['items'] as $debt): ?>
                                    <?php
                                    $remaining = $debt['amount'] - $debt['paid_amount'];
                                    $isOverdue = !empty($debt['due_date']) && $debt['due_date'] < date('Y-m-d') && $debt['status'] !== 'paid';
                                    $contactName = trim(($debt['contact_first_name'] ?? '') . ' ' . ($debt['contact_last_name'] ?? ''));
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if ($contactName): ?>
                                                <span class="fw-medium"><?= e($contactName) ?></span>
                                            <?php endif; ?>
                                            <?php if ($debt['company_name']): ?>
                                                <br><small class="text-muted"><?= e($debt['company_name']) ?></small>
                                            <?php endif; ?>
                                            <?php if (!$contactName && !$debt['company_name']): ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($debt['order_number']): ?>
                                                <a href="<?= url('orders/' . $debt['order_id']) ?>"><?= e($debt['order_number']) ?></a>
                                            <?php else: ?>
                                                <span class="text-muted">Thủ công</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-medium"><?= format_money($debt['amount']) ?></td>
                                        <td class="text-success"><?= format_money($debt['paid_amount']) ?></td>
                                        <td class="fw-medium <?= $remaining > 0 ? 'text-danger' : 'text-success' ?>"><?= format_money($remaining) ?></td>
                                        <td>
                                            <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                                <?= !empty($debt['due_date']) ? format_date($debt['due_date']) : '-' ?>
                                                <?php if ($isOverdue): ?>
                                                    <i class="ri-error-warning-line ms-1"></i>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php $status = $isOverdue ? 'overdue' : $debt['status']; ?>
                                            <span class="badge bg-<?= $sc[$status] ?? 'secondary' ?>"><?= $sl[$status] ?? '' ?></span>
                                        </td>
                                        <td>
                                            <a href="<?= url('debts/' . $debt['id']) ?>" class="btn btn-soft-primary"><i class="ri-eye-line me-1"></i> Xem</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted"><i class="ri-money-dollar-circle-line fs-1 d-block mb-2"></i>Chưa có công nợ</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($debts['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($debts['items']) ?> / <?= $debts['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $debts['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $debts['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('debts?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
