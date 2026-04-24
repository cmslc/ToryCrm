<?php $pageTitle = 'Hoa hồng'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Hoa hồng</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('commissions/export?period=' . ($filters['period'] ?? date('Y-m'))) ?>" class="btn btn-soft-success"><i class="ri-file-excel-line me-1"></i> Xuất Excel</a>
                <a href="<?= url('commissions/my') ?>" class="btn btn-soft-info"><i class="ri-user-line me-1"></i> Của tôi</a>
                <a href="<?= url('commissions/report') ?>" class="btn btn-soft-primary"><i class="ri-bar-chart-box-line me-1"></i> Báo cáo</a>
                <a href="<?= url('commissions/rules') ?>" class="btn btn-primary"><i class="ri-settings-3-line me-1"></i> Quy tắc</a>
            </div>
        </div>


        <!-- Summary Cards -->
        <div class="row mb-1">
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-warning-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-time-line text-warning fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Chờ duyệt</p>
                                <h4 class="mb-0 text-warning"><?= format_money($summary['pending'] ?? 0) ?></h4>
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
                                <i class="ri-checkbox-circle-line text-info fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Đã duyệt</p>
                                <h4 class="mb-0 text-info"><?= format_money($summary['approved'] ?? 0) ?></h4>
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
                                <i class="ri-money-dollar-circle-line text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Đã trả</p>
                                <h4 class="mb-0 text-success"><?= format_money($summary['paid'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-percent-line text-primary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Tổng tháng này</p>
                                <h4 class="mb-0 text-primary"><?= format_money($summary['total'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Table -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('commissions') ?>" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <?php $deptGrouped = []; foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                        <select name="user_id" class="form-select searchable-select">
                            <option value="">Tất cả nhân viên</option>
                            <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                            <optgroup label="<?= e($dept) ?>">
                                <?php foreach ($dUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($filters['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                            <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                            <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã trả</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="month" class="form-control" name="period" value="<?= e($filters['period'] ?? date('Y-m')) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="entity_type" class="form-select">
                            <option value="">Tất cả loại</option>
                            <option value="deal" <?= ($filters['entity_type'] ?? '') === 'deal' ? 'selected' : '' ?>>Deal</option>
                            <option value="order" <?= ($filters['entity_type'] ?? '') === 'order' ? 'selected' : '' ?>>Đơn hàng</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Lọc</button>
                        <a href="<?= url('commissions') ?>" class="btn btn-soft-danger btn-icon" title="Xóa lọc"><i class="ri-refresh-line"></i></a>
                    </div>
                </form>

                <!-- Bulk Actions -->
                <form id="bulkForm" method="POST" class="mb-3 d-none">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ids" id="bulkIds">
                    <button type="submit" formaction="<?= url('commissions/bulk-approve') ?>" class="btn btn-info me-1"><i class="ri-checkbox-circle-line me-1"></i> Duyệt đã chọn</button>
                    <button type="submit" formaction="<?= url('commissions/bulk-paid') ?>" class="btn btn-success"><i class="ri-money-dollar-circle-line me-1"></i> Đánh dấu đã trả</button>
                    <span class="ms-2 text-muted" id="selectedCount"></span>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                <th>Nhân viên</th>
                                <th>Loại</th>
                                <th>Mã entity</th>
                                <th class="text-end">Giá trị gốc</th>
                                <th>Tỷ lệ</th>
                                <th class="text-end">Hoa hồng</th>
                                <th>Trạng thái</th>
                                <th>Ngày</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statusColors = ['pending' => 'warning', 'approved' => 'info', 'paid' => 'success'];
                            $statusLabels = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'paid' => 'Đã trả'];
                            $typeLabels = ['deal' => 'Deal', 'order' => 'Đơn hàng'];
                            ?>
                            <?php if (!empty($commissions)): ?>
                                <?php foreach ($commissions as $c): ?>
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input row-check" value="<?= $c['id'] ?>"></td>
                                        <td><?= user_avatar($c['user_name'] ?? null) ?></td>
                                        <td><span class="badge bg-<?= $c['entity_type'] === 'deal' ? 'primary' : 'success' ?>"><?= $typeLabels[$c['entity_type']] ?? $c['entity_type'] ?></span></td>
                                        <td>
                                            <?php if ($c['entity_type'] === 'deal'): ?>
                                                <a href="<?= url('deals/' . $c['entity_id']) ?>">#<?= $c['entity_id'] ?></a>
                                            <?php else: ?>
                                                <a href="<?= url('orders/' . $c['entity_id']) ?>">#<?= $c['entity_id'] ?></a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><?= format_money($c['base_value'] ?? 0) ?></td>
                                        <td><?= $c['rate'] > 0 ? number_format($c['rate'], 1) . '%' : '-' ?></td>
                                        <td class="text-end fw-medium"><?= format_money($c['amount']) ?></td>
                                        <td><span class="badge bg-<?= $statusColors[$c['status']] ?? 'secondary' ?>"><?= $statusLabels[$c['status']] ?? '' ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                                        <td>
                                            <?php if ($c['status'] === 'pending'): ?>
                                                <form method="POST" action="<?= url('commissions/' . $c['id'] . '/approve') ?>" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-soft-info" title="Duyệt"><i class="ri-checkbox-circle-line me-1"></i> Duyệt</button>
                                                </form>
                                            <?php elseif ($c['status'] === 'approved'): ?>
                                                <form method="POST" action="<?= url('commissions/' . $c['id'] . '/paid') ?>" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-soft-success" title="Đánh dấu đã trả"><i class="ri-money-dollar-circle-line me-1"></i> Đã trả</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="10" class="text-center py-4 text-muted"><i class="ri-percent-line fs-1 d-block mb-2"></i>Chưa có dữ liệu hoa hồng</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($pagination['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($commissions) ?> / <?= $pagination['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('commissions?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checks = document.querySelectorAll('.row-check');
            const bulkForm = document.getElementById('bulkForm');
            const bulkIds = document.getElementById('bulkIds');
            const selectedCount = document.getElementById('selectedCount');

            function updateBulk() {
                const selected = [...checks].filter(c => c.checked).map(c => c.value);
                bulkIds.value = selected.join(',');
                bulkForm.classList.toggle('d-none', selected.length === 0);
                selectedCount.textContent = selected.length > 0 ? selected.length + ' mục đã chọn' : '';
            }

            selectAll.addEventListener('change', function() {
                checks.forEach(c => c.checked = this.checked);
                updateBulk();
            });
            checks.forEach(c => c.addEventListener('change', updateBulk));
        });
        </script>
