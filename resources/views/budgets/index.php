<?php $pageTitle = 'Ngân sách'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Ngân sách</h4>
            <div>
                <a href="<?= url('budgets/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo ngân sách</a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-1">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-money-dollar-circle-line text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Tổng ngân sách</p>
                                <h4 class="mb-0 text-primary"><?= format_money($summary['total_planned'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-danger-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-arrow-up-circle-line text-danger fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Đã chi</p>
                                <h4 class="mb-0 text-danger"><?= format_money($summary['total_spent'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-wallet-line text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Còn lại</p>
                                <h4 class="mb-0 text-success"><?= format_money($summary['remaining'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-info-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-percent-line text-info fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Tỷ lệ sử dụng</p>
                                <h4 class="mb-0 text-info"><?= $summary['utilization'] ?? 0 ?>%</h4>
                                <div class="progress mt-1" style="height:4px; width:100px">
                                    <?php $util = min(100, $summary['utilization'] ?? 0); ?>
                                    <div class="progress-bar bg-<?= $util > 100 ? 'danger' : ($util > 80 ? 'warning' : 'info') ?>" style="width:<?= $util ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('budgets') ?>" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Tìm tên ngân sách..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">Tất cả loại</option>
                            <option value="department" <?= ($filters['type'] ?? '') === 'department' ? 'selected' : '' ?>>Phòng ban</option>
                            <option value="project" <?= ($filters['type'] ?? '') === 'project' ? 'selected' : '' ?>>Dự án</option>
                            <option value="campaign" <?= ($filters['type'] ?? '') === 'campaign' ? 'selected' : '' ?>>Chiến dịch</option>
                            <option value="general" <?= ($filters['type'] ?? '') === 'general' ? 'selected' : '' ?>>Chung</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                            <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Đã đóng</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('budgets') ?>" class="btn btn-soft-danger btn-icon" title="Xóa lọc"><i class="ri-refresh-line"></i></a>
                    </div>
                </form>

                <?php
                $sc = ['draft'=>'secondary','active'=>'success','closed'=>'dark'];
                $sl = ['draft'=>'Nháp','active'=>'Đang hoạt động','closed'=>'Đã đóng'];
                $tc = ['department'=>'primary','project'=>'info','campaign'=>'warning','general'=>'secondary'];
                $tl = ['department'=>'Phòng ban','project'=>'Dự án','campaign'=>'Chiến dịch','general'=>'Chung'];
                ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên</th>
                                <th>Loại</th>
                                <th>Kỳ</th>
                                <th class="text-end">Ngân sách</th>
                                <th class="text-end">Đã chi</th>
                                <th class="text-end">Còn lại</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($budgets['items'])): ?>
                                <?php foreach ($budgets['items'] as $b): ?>
                                    <tr>
                                        <td><a href="<?= url('budgets/' . $b['id']) ?>" class="fw-medium"><?= e($b['name']) ?></a></td>
                                        <td><span class="badge bg-<?= $tc[$b['type']] ?? 'secondary' ?>-subtle text-<?= $tc[$b['type']] ?? 'secondary' ?>"><?= $tl[$b['type']] ?? $b['type'] ?></span></td>
                                        <td>
                                            <?php if ($b['period_start'] && $b['period_end']): ?>
                                                <?= format_date($b['period_start']) ?> - <?= format_date($b['period_end']) ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-medium"><?= format_money($b['total_budget']) ?></td>
                                        <td class="text-end">
                                            <?= format_money($b['total_spent'] ?? 0) ?>
                                            <?php $bPct = $b['total_budget'] > 0 ? round(($b['total_spent'] ?? 0) / $b['total_budget'] * 100) : 0; ?>
                                            <div class="progress mt-1" style="height:4px"><div class="progress-bar bg-<?= $bPct > 100 ? 'danger' : ($bPct > 80 ? 'warning' : 'success') ?>" style="width:<?= min($bPct, 100) ?>%"></div></div>
                                            <?php if ($bPct > 100): ?><small class="text-danger fw-medium">Vượt <?= $bPct - 100 ?>%</small><?php endif; ?>
                                        </td>
                                        <td class="text-end <?= ($b['remaining'] ?? 0) < 0 ? 'text-danger fw-bold' : 'text-success' ?>">
                                            <?= format_money($b['remaining'] ?? 0) ?>
                                        </td>
                                        <td><span class="badge bg-<?= $sc[$b['status']] ?? 'secondary' ?>"><?= $sl[$b['status']] ?? '' ?></span></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('budgets/' . $b['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('budgets/' . $b['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <?php if ($b['status'] === 'draft'): ?>
                                                    <li>
                                                        <form method="POST" action="<?= url('budgets/' . $b['id'] . '/approve') ?>" data-confirm="Duyệt ngân sách này?">
                                                            <?= csrf_field() ?><button class="dropdown-item"><i class="ri-check-line me-2"></i>Duyệt</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if ($b['status'] === 'active'): ?>
                                                    <li>
                                                        <form method="POST" action="<?= url('budgets/' . $b['id'] . '/close') ?>" data-confirm="Đóng ngân sách?">
                                                            <?= csrf_field() ?><button class="dropdown-item"><i class="ri-lock-line me-2"></i>Đóng</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted"><i class="ri-wallet-line fs-1 d-block mb-2"></i>Chưa có ngân sách</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($budgets['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($budgets['items']) ?> / <?= $budgets['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $budgets['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $budgets['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('budgets?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
