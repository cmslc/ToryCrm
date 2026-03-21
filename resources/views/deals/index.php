<?php $pageTitle = 'Cơ hội kinh doanh'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Cơ hội kinh doanh</h4>
            <div>
                <a href="<?= url('deals/pipeline') ?>" class="btn btn-soft-info me-1"><i class="ri-git-branch-line me-1"></i> Pipeline</a>
                <a href="<?= url('deals/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm cơ hội</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('deals') ?>" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Đang mở</option>
                            <option value="won" <?= ($filters['status'] ?? '') === 'won' ? 'selected' : '' ?>>Thắng</option>
                            <option value="lost" <?= ($filters['status'] ?? '') === 'lost' ? 'selected' : '' ?>>Thua</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="stage_id" class="form-select">
                            <option value="">Giai đoạn</option>
                            <?php foreach ($stages ?? [] as $stage): ?>
                                <option value="<?= $stage['id'] ?>" <?= ($filters['stage_id'] ?? '') == $stage['id'] ? 'selected' : '' ?>><?= e($stage['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('deals') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên cơ hội</th>
                                <th>Giá trị</th>
                                <th>Giai đoạn</th>
                                <th>Khách hàng</th>
                                <th>Công ty</th>
                                <th>Ưu tiên</th>
                                <th>Dự kiến</th>
                                <th>Phụ trách</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($deals['items'])): ?>
                                <?php foreach ($deals['items'] as $deal): ?>
                                    <tr>
                                        <td><a href="<?= url('deals/' . $deal['id']) ?>" class="fw-medium text-dark"><?= e($deal['title']) ?></a></td>
                                        <td class="fw-medium"><?= format_money($deal['value']) ?></td>
                                        <td><span class="badge" style="background-color:<?= safe_color($deal['stage_color'] ?? null) ?>"><?= e($deal['stage_name'] ?? '') ?></span></td>
                                        <td><?= e(($deal['contact_first_name'] ?? '') . ' ' . ($deal['contact_last_name'] ?? '')) ?: '-' ?></td>
                                        <td><?= e($deal['company_name'] ?? '-') ?></td>
                                        <td>
                                            <?php $pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger']; $pl = ['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn']; ?>
                                            <span class="badge bg-<?= $pc[$deal['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$deal['priority']] ?? 'secondary' ?>"><?= $pl[$deal['priority']] ?? '' ?></span>
                                        </td>
                                        <td><?= $deal['expected_close_date'] ? format_date($deal['expected_close_date']) : '-' ?></td>
                                        <td><?= e($deal['owner_name'] ?? '-') ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('deals/' . $deal['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('deals/' . $deal['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('deals/' . $deal['id'] . '/delete') ?>" onsubmit="return confirm('Xác nhận xóa?')">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-hand-coin-line fs-1 d-block mb-2"></i>Chưa có cơ hội</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($deals['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($deals['items']) ?> / <?= $deals['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $deals['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $deals['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('deals?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
