<?php $pageTitle = 'Chiến dịch'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Chiến dịch</h4>
            <a href="<?= url('campaigns/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo chiến dịch</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('campaigns') ?>" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">Loại</option>
                            <?php $typeLabels = ['email' => 'Email', 'sms' => 'SMS', 'call' => 'Gọi điện', 'social' => 'Mạng xã hội', 'other' => 'Khác']; ?>
                            <?php foreach ($typeLabels as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($filters['type'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <?php $statusLabels = ['draft' => 'Nháp', 'running' => 'Đang chạy', 'paused' => 'Tạm dừng', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy']; ?>
                            <?php foreach ($statusLabels as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('campaigns') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <?php
                    $typeLabels = ['email' => 'Email', 'sms' => 'SMS', 'call' => 'Gọi điện', 'social' => 'Mạng xã hội', 'other' => 'Khác'];
                    $statusLabels = ['draft' => 'Nháp', 'running' => 'Đang chạy', 'paused' => 'Tạm dừng', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
                    $statusColors = ['draft' => 'secondary', 'running' => 'success', 'paused' => 'warning', 'completed' => 'primary', 'cancelled' => 'danger'];
                ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã</th>
                                <th>Tên chiến dịch</th>
                                <th>Loại</th>
                                <th>Trạng thái</th>
                                <th>Ngân sách</th>
                                <th>Mục tiêu</th>
                                <th>Đã tiếp cận</th>
                                <th>Chuyển đổi</th>
                                <th>Ngày bắt đầu</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($campaigns['items'])): ?>
                                <?php foreach ($campaigns['items'] as $campaign): ?>
                                    <tr>
                                        <td><span class="text-muted"><?= e($campaign['campaign_code'] ?? '') ?></span></td>
                                        <td><a href="<?= url('campaigns/' . $campaign['id']) ?>" class="fw-medium text-dark"><?= e($campaign['name']) ?></a></td>
                                        <td><?= $typeLabels[$campaign['type']] ?? $campaign['type'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $statusColors[$campaign['status']] ?? 'secondary' ?>-subtle text-<?= $statusColors[$campaign['status']] ?? 'secondary' ?>">
                                                <?= $statusLabels[$campaign['status']] ?? $campaign['status'] ?>
                                            </span>
                                        </td>
                                        <td><?= format_money($campaign['budget'] ?? 0) ?></td>
                                        <td><?= number_format($campaign['target'] ?? 0) ?></td>
                                        <td><?= number_format($campaign['reached'] ?? 0) ?></td>
                                        <td><?= number_format($campaign['converted'] ?? 0) ?></td>
                                        <td><?= $campaign['start_date'] ? format_date($campaign['start_date']) : '-' ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('campaigns/' . $campaign['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('campaigns/' . $campaign['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('campaigns/' . $campaign['id'] . '/delete') ?>" data-confirm="Xác nhận xóa chiến dịch này?">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="10" class="text-center py-4 text-muted"><i class="ri-megaphone-line fs-1 d-block mb-2"></i>Chưa có chiến dịch</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($campaigns['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($campaigns['items']) ?> / <?= $campaigns['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $campaigns['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $campaigns['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('campaigns?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
