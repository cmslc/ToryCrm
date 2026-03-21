<?php $pageTitle = 'Khách hàng'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Khách hàng</h4>
                    <div class="page-title-right d-flex gap-2">
                        <a href="<?= url('contacts/trash') ?>" class="btn btn-soft-danger">
                            <i class="ri-delete-bin-line me-1"></i> Thùng rác
                        </a>
                        <a href="<?= url('contacts/create') ?>" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i> Thêm khách hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('contacts') ?>" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="new" <?= ($filters['status'] ?? '') === 'new' ? 'selected' : '' ?>>Mới</option>
                            <option value="contacted" <?= ($filters['status'] ?? '') === 'contacted' ? 'selected' : '' ?>>Đã liên hệ</option>
                            <option value="qualified" <?= ($filters['status'] ?? '') === 'qualified' ? 'selected' : '' ?>>Tiềm năng</option>
                            <option value="converted" <?= ($filters['status'] ?? '') === 'converted' ? 'selected' : '' ?>>Chuyển đổi</option>
                            <option value="lost" <?= ($filters['status'] ?? '') === 'lost' ? 'selected' : '' ?>>Mất</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="source_id" class="form-select">
                            <option value="">Nguồn</option>
                            <?php foreach ($sources ?? [] as $source): ?>
                                <option value="<?= $source['id'] ?>" <?= ($filters['source_id'] ?? '') == $source['id'] ? 'selected' : '' ?>>
                                    <?= e($source['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="owner_id" class="form-select">
                            <option value="">Người phụ trách</option>
                            <?php foreach ($users ?? [] as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($filters['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                    <?= e($u['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('contacts') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status Stats -->
        <div class="row mb-0">
            <?php
            $statusInfo = [
                'new' => ['label' => 'Mới', 'color' => 'info', 'icon' => 'ri-user-add-line'],
                'contacted' => ['label' => 'Đã liên hệ', 'color' => 'primary', 'icon' => 'ri-phone-line'],
                'qualified' => ['label' => 'Tiềm năng', 'color' => 'warning', 'icon' => 'ri-star-line'],
                'converted' => ['label' => 'Chuyển đổi', 'color' => 'success', 'icon' => 'ri-checkbox-circle-line'],
                'lost' => ['label' => 'Mất', 'color' => 'danger', 'icon' => 'ri-close-circle-line'],
            ];
            foreach ($statusInfo as $key => $info):
                $count = 0;
                foreach ($statusCounts ?? [] as $sc) {
                    if ($sc['status'] === $key) $count = $sc['count'];
                }
            ?>
                <div class="col">
                    <div class="card card-animate">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="fw-medium text-muted mb-0"><?= $info['label'] ?></p>
                                    <h2 class="mt-2 mb-0"><?= $count ?></h2>
                                </div>
                                <div class="avatar-sm">
                                    <span class="avatar-title bg-<?= $info['color'] ?>-subtle rounded">
                                        <i class="<?= $info['icon'] ?> text-<?= $info['color'] ?> fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Contact List -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><input type="checkbox" class="form-check-input" id="checkAll"></th>
                                <th>Khách hàng</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                                <th>Công ty</th>
                                <th>Nguồn</th>
                                <th>Trạng thái</th>
                                <th>Người phụ trách</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($contacts['items'])): ?>
                                <?php foreach ($contacts['items'] as $contact): ?>
                                    <tr>
                                        <td><input type="checkbox" class="form-check-input contact-check" value="<?= $contact['id'] ?>"></td>
                                        <td>
                                            <a href="<?= url('contacts/' . $contact['id']) ?>" class="fw-medium text-dark">
                                                <?= e($contact['first_name'] . ' ' . ($contact['last_name'] ?? '')) ?>
                                            </a>
                                            <?php if ($contact['position']): ?>
                                                <br><small class="text-muted"><?= e($contact['position']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($contact['email'] ?? '-') ?></td>
                                        <td><?= e($contact['phone'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($contact['company_id']): ?>
                                                <a href="<?= url('companies/' . $contact['company_id']) ?>"><?= e($contact['company_name']) ?></a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($contact['source_name']): ?>
                                                <span class="badge" style="background-color: <?= safe_color($contact['source_color']) ?>"><?= e($contact['source_name']) ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $sColors = ['new' => 'info', 'contacted' => 'primary', 'qualified' => 'warning', 'converted' => 'success', 'lost' => 'danger'];
                                            $sLabels = ['new' => 'Mới', 'contacted' => 'Đã liên hệ', 'qualified' => 'Tiềm năng', 'converted' => 'Chuyển đổi', 'lost' => 'Mất'];
                                            ?>
                                            <span class="badge bg-<?= $sColors[$contact['status']] ?? 'secondary' ?>-subtle text-<?= $sColors[$contact['status']] ?? 'secondary' ?>">
                                                <?= $sLabels[$contact['status']] ?? $contact['status'] ?>
                                            </span>
                                        </td>
                                        <td><?= e($contact['owner_name'] ?? '-') ?></td>
                                        <td><?= time_ago($contact['created_at']) ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-soft-secondary" data-bs-toggle="dropdown">
                                                    <i class="ri-more-fill"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('contacts/' . $contact['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('contacts/' . $contact['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('contacts/' . $contact['id'] . '/delete') ?>" onsubmit="return confirm('Xác nhận xóa?')">
                                                            <?= csrf_field() ?>
                                                            <button type="submit" class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-contacts-line fs-1 d-block mb-2"></i>
                                            Chưa có khách hàng nào
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (($contacts['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Hiển thị <?= count($contacts['items']) ?> / <?= $contacts['total'] ?> khách hàng
                        </div>
                        <nav>
                            <ul class="pagination mb-0">
                                <?php for ($i = 1; $i <= $contacts['total_pages']; $i++): ?>
                                    <li class="page-item <?= $i === $contacts['page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= url('contacts?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
