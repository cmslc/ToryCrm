<?php $pageTitle = 'Hợp đồng'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Hợp đồng</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('contracts/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo hợp đồng</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="fw-medium text-muted mb-0">Đang hoạt động</p>
                                <h4 class="mt-2 mb-0 text-success"><?= (int) ($stats['active_count'] ?? 0) ?></h4>
                            </div>
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-2">
                                    <i class="ri-file-shield-2-line"></i>
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
                                <p class="fw-medium text-muted mb-0">Sắp hết hạn (30 ngày)</p>
                                <h4 class="mt-2 mb-0 text-warning"><?= (int) ($stats['expiring_soon'] ?? 0) ?></h4>
                            </div>
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-2">
                                    <i class="ri-time-line"></i>
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
                                <p class="fw-medium text-muted mb-0">Đã hết hạn</p>
                                <h4 class="mt-2 mb-0 text-danger"><?= (int) ($stats['expired_count'] ?? 0) ?></h4>
                            </div>
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-2">
                                    <i class="ri-close-circle-line"></i>
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
                                <p class="fw-medium text-muted mb-0">Tổng giá trị</p>
                                <h4 class="mt-2 mb-0 text-primary"><?= format_money($stats['total_value'] ?? 0) ?></h4>
                            </div>
                            <div class="avatar-md flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-2">
                                    <i class="ri-money-dollar-circle-line"></i>
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
                <form method="GET" action="<?= url('contracts') ?>" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                            <option value="sent" <?= ($filters['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Đã gửi</option>
                            <option value="signed" <?= ($filters['status'] ?? '') === 'signed' ? 'selected' : '' ?>>Đã ký</option>
                            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Hết hạn</option>
                            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">Loại HĐ</option>
                            <option value="service" <?= ($filters['type'] ?? '') === 'service' ? 'selected' : '' ?>>Dịch vụ</option>
                            <option value="product" <?= ($filters['type'] ?? '') === 'product' ? 'selected' : '' ?>>Sản phẩm</option>
                            <option value="rental" <?= ($filters['type'] ?? '') === 'rental' ? 'selected' : '' ?>>Cho thuê</option>
                            <option value="maintenance" <?= ($filters['type'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Bảo trì</option>
                            <option value="other" <?= ($filters['type'] ?? '') === 'other' ? 'selected' : '' ?>>Khác</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="contact_id" class="form-select searchable-select">
                            <option value="">Tất cả KH</option>
                            <?php foreach ($contacts ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($filters['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e(trim($c['first_name'] . ' ' . $c['last_name'])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" placeholder="Từ ngày">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" placeholder="Đến ngày">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('contracts') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <?php
                $sc = ['draft' => 'secondary', 'sent' => 'info', 'signed' => 'primary', 'active' => 'success', 'expired' => 'danger', 'cancelled' => 'dark'];
                $sl = ['draft' => 'Nháp', 'sent' => 'Đã gửi', 'signed' => 'Đã ký', 'active' => 'Hoạt động', 'expired' => 'Hết hạn', 'cancelled' => 'Đã hủy'];
                $tc = ['service' => 'primary', 'product' => 'success', 'rental' => 'warning', 'maintenance' => 'info', 'other' => 'secondary'];
                $tl = ['service' => 'Dịch vụ', 'product' => 'Sản phẩm', 'rental' => 'Cho thuê', 'maintenance' => 'Bảo trì', 'other' => 'Khác'];
                ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Số HĐ</th>
                                <th>Tiêu đề</th>
                                <th>Khách hàng</th>
                                <th>Loại</th>
                                <th>Giá trị</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($contracts['items'])): ?>
                                <?php foreach ($contracts['items'] as $ct): ?>
                                    <?php
                                    $endingSoon = !empty($ct['end_date']) && $ct['end_date'] >= date('Y-m-d') && $ct['end_date'] <= date('Y-m-d', strtotime('+30 days'));
                                    $isExpired = !empty($ct['end_date']) && $ct['end_date'] < date('Y-m-d') && $ct['status'] !== 'expired' && $ct['status'] !== 'cancelled';
                                    $contactName = trim(($ct['contact_first_name'] ?? '') . ' ' . ($ct['contact_last_name'] ?? ''));
                                    ?>
                                    <tr>
                                        <td><a href="<?= url('contracts/' . $ct['id']) ?>" class="fw-medium"><?= e($ct['contract_number']) ?></a></td>
                                        <td><?= e($ct['title']) ?></td>
                                        <td>
                                            <?= $contactName ? e($contactName) : '' ?>
                                            <?php if (!empty($ct['company_name'])): ?>
                                                <?= $contactName ? '<br>' : '' ?><small class="text-muted"><?= e($ct['company_name']) ?></small>
                                            <?php endif; ?>
                                            <?php if (!$contactName && empty($ct['company_name'])): ?>-<?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-<?= $tc[$ct['type']] ?? 'secondary' ?>-subtle text-<?= $tc[$ct['type']] ?? 'secondary' ?>"><?= $tl[$ct['type']] ?? $ct['type'] ?></span></td>
                                        <td class="fw-medium"><?= format_money($ct['value']) ?></td>
                                        <td><?= !empty($ct['start_date']) ? format_date($ct['start_date']) : '-' ?></td>
                                        <td>
                                            <?php if (!empty($ct['end_date'])): ?>
                                                <span class="<?= $endingSoon ? 'text-warning fw-bold' : '' ?><?= $isExpired ? 'text-danger fw-bold' : '' ?>">
                                                    <?= format_date($ct['end_date']) ?>
                                                    <?php if ($endingSoon): ?><i class="ri-error-warning-line ms-1"></i><?php endif; ?>
                                                    <?php if ($isExpired): ?><i class="ri-close-circle-line ms-1"></i><?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Không xác định</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-<?= $sc[$ct['status']] ?? 'secondary' ?>"><?= $sl[$ct['status']] ?? '' ?></span></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('contracts/' . $ct['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('contracts/' . $ct['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('contracts/' . $ct['id'] . '/delete') ?>" data-confirm="Xác nhận xóa hợp đồng?">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-file-shield-2-line fs-1 d-block mb-2"></i>Chưa có hợp đồng</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($contracts['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($contracts['items']) ?> / <?= $contracts['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $contracts['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $contracts['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('contracts?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
