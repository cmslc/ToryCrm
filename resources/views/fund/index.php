<?php $pageTitle = 'Quỹ'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Quỹ</h4>
            <div>
                <a href="<?= url('fund/create?type=receipt') ?>" class="btn btn-success me-1"><i class="ri-add-line me-1"></i> Tạo phiếu thu</a>
                <a href="<?= url('fund/create?type=payment') ?>" class="btn btn-danger"><i class="ri-add-line me-1"></i> Tạo phiếu chi</a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-arrow-down-circle-line text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Tổng thu</p>
                                <h4 class="mb-0 text-success"><?= format_money($summary['total_receipt'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-danger-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-arrow-up-circle-line text-danger fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Tổng chi</p>
                                <h4 class="mb-0 text-danger"><?= format_money($summary['total_payment'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-wallet-3-line text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Số dư</p>
                                <h4 class="mb-0 text-primary"><?= format_money($summary['balance'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <?php if (!empty($monthlyChart)): ?>
        <div class="row mb-3">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-bar-chart-line me-2"></i> Thu chi theo tháng</h5></div>
                    <div class="card-body"><canvas id="fundChart" height="250"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-height-100">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-pie-chart-line me-2"></i> Phân loại chi phí</h5></div>
                    <div class="card-body">
                        <?php foreach ($categories as $cat): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><?= e($cat['category'] ?: 'Khác') ?></span>
                            <span class="fw-medium <?= $cat['type'] === 'receipt' ? 'text-success' : 'text-danger' ?>"><?= format_money($cat['total']) ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?><p class="text-muted text-center mb-0">Chưa có dữ liệu</p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') return;
            new Chart(document.getElementById('fundChart'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_column($monthlyChart, 'month')) ?>,
                    datasets: [
                        {label: 'Thu', data: <?= json_encode(array_column($monthlyChart, 'receipt')) ?>, backgroundColor: 'rgba(10,179,156,0.7)'},
                        {label: 'Chi', data: <?= json_encode(array_column($monthlyChart, 'payment')) ?>, backgroundColor: 'rgba(240,101,72,0.7)'}
                    ]
                },
                options: {responsive:true, plugins:{legend:{position:'top'}}, scales:{y:{beginAtZero:true, ticks:{callback:function(v){return (v/1000000)+'tr'}}}}}
            });
        });
        </script>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('fund') ?>" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search" placeholder="Tìm mã phiếu..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">Tất cả loại</option>
                            <option value="receipt" <?= ($filters['type'] ?? '') === 'receipt' ? 'selected' : '' ?>>Phiếu thu</option>
                            <option value="payment" <?= ($filters['type'] ?? '') === 'payment' ? 'selected' : '' ?>>Phiếu chi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                            <option value="confirmed" <?= ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="fund_account_id" class="form-select">
                            <option value="">Tất cả quỹ</option>
                            <?php foreach ($accounts ?? [] as $account): ?>
                                <option value="<?= $account['id'] ?>" <?= ($filters['fund_account_id'] ?? '') == $account['id'] ? 'selected' : '' ?>><?= e($account['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <input type="date" class="form-control" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" placeholder="Từ ngày">
                    </div>
                    <div class="col-md-1">
                        <input type="date" class="form-control" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" placeholder="Đến ngày">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('fund') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Loại</th>
                                <th>Quỹ</th>
                                <th>Số tiền</th>
                                <th>Đối tượng</th>
                                <th>Ngày</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transactions['items'])): ?>
                                <?php
                                $statusColors = ['draft'=>'secondary','confirmed'=>'success','cancelled'=>'danger'];
                                $statusLabels = ['draft'=>'Nháp','confirmed'=>'Đã xác nhận','cancelled'=>'Đã hủy'];
                                ?>
                                <?php foreach ($transactions['items'] as $txn): ?>
                                    <tr>
                                        <td><a href="<?= url('fund/' . $txn['id']) ?>" class="fw-medium"><?= e($txn['transaction_code']) ?></a></td>
                                        <td>
                                            <?= $txn['type'] === 'receipt'
                                                ? '<span class="badge bg-success">Thu</span>'
                                                : '<span class="badge bg-danger">Chi</span>' ?>
                                        </td>
                                        <td><?= e($txn['account_name'] ?? '-') ?></td>
                                        <td class="fw-medium"><?= format_money($txn['amount']) ?></td>
                                        <td><?= e($txn['contact_name'] ?? $txn['company_name'] ?? '-') ?></td>
                                        <td><?= format_date($txn['transaction_date']) ?></td>
                                        <td><span class="badge bg-<?= $statusColors[$txn['status']] ?? 'secondary' ?>"><?= $statusLabels[$txn['status']] ?? '' ?></span></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('fund/' . $txn['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('fund/' . $txn['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('fund/' . $txn['id'] . '/delete') ?>" data-confirm="Xác nhận xóa?">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted"><i class="ri-wallet-3-line fs-1 d-block mb-2"></i>Chưa có giao dịch</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($transactions['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($transactions['items']) ?> / <?= $transactions['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $transactions['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $transactions['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('fund?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
