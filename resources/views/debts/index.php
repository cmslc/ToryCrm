<?php $pageTitle = 'Công nợ'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Công nợ</h4>
            <a href="<?= url('debts/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo công nợ</a>
        </div>

        <!-- Summary + Aging -->
        <div class="row mb-1">
            <div class="col-6 col-md-3"><div class="card card-animate mb-2"><div class="card-body py-3"><p class="text-muted mb-1 fs-12">Phải thu</p><h5 class="mb-0 text-success"><?= format_money($summary['total_receivable'] ?? 0) ?></h5></div></div></div>
            <div class="col-6 col-md-3"><div class="card card-animate mb-2"><div class="card-body py-3"><p class="text-muted mb-1 fs-12">Phải trả</p><h5 class="mb-0 text-danger"><?= format_money($summary['total_payable'] ?? 0) ?></h5></div></div></div>
            <div class="col-6 col-md-3"><div class="card card-animate mb-2"><div class="card-body py-3"><p class="text-muted mb-1 fs-12">Quá hạn <?php if (($overdueCount ?? 0) > 0): ?><span class="badge bg-danger ms-1"><?= $overdueCount ?></span><?php endif; ?></p><h5 class="mb-0 text-warning"><?= format_money($summary['total_overdue'] ?? 0) ?></h5></div></div></div>
            <div class="col-6 col-md-3"><div class="card card-animate mb-2"><div class="card-body py-3"><p class="text-muted mb-1 fs-12">Thu/trả tháng này</p><h5 class="mb-0 text-primary"><?= format_money($summary['collected_this_month'] ?? 0) ?></h5></div></div></div>
        </div>

        <?php if (!empty($aging) && (($aging['overdue_30'] ?? 0) + ($aging['overdue_60'] ?? 0) + ($aging['overdue_90'] ?? 0) + ($aging['overdue_90plus'] ?? 0)) > 0): ?>
        <div class="card mb-2">
            <div class="card-body py-2">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="text-muted fw-medium fs-13"><i class="ri-timer-line me-1"></i> Tuổi nợ:</span>
                    <?php
                    $agingItems = [
                        ['Chưa hạn', $aging['current_due'], 'success'],
                        ['1-30 ngày', $aging['overdue_30'], 'warning'],
                        ['31-60', $aging['overdue_60'], 'warning'],
                        ['61-90', $aging['overdue_90'], 'danger'],
                        ['90+', $aging['overdue_90plus'], 'dark'],
                    ];
                    foreach ($agingItems as $ai):
                        if ($ai[1] <= 0) continue;
                    ?>
                    <span class="badge bg-<?= $ai[2] ?>-subtle text-<?= $ai[2] ?> px-3 py-2 fs-13"><?= $ai[0] ?>: <?= format_money($ai[1]) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav nav-pills mb-2">
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

        <!-- PLACEHOLDER to match old code continuation -->
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
