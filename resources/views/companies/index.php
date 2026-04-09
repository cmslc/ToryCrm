<?php
$pageTitle = 'Doanh nghiệp';
$industries = ['Công nghệ', 'Tài chính', 'Bất động sản', 'Sản xuất', 'Thương mại', 'Y tế', 'Giáo dục', 'Truyền thông', 'Vận tải', 'F&B', 'Du lịch', 'Nông nghiệp', 'Khác'];
$sizes = ['1-10', '10-20', '20-50', '50-100', '100-500', '200-500', '500+'];
?>

<!-- Toolbar -->
<div class="card mb-2">
    <div class="card-body py-1 px-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="d-flex align-items-center me-2">
                <div class="avatar-xs flex-shrink-0 me-2">
                    <span class="avatar-title bg-info-subtle text-info rounded"><i class="ri-building-line"></i></span>
                </div>
                <h5 class="mb-0 text-nowrap">Doanh nghiệp</h5>
            </div>

            <form method="GET" action="<?= url('companies') ?>" class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap">
                <div class="search-box" style="min-width:180px;max-width:280px">
                    <input type="text" class="form-control" name="search" placeholder="Tên, email, SĐT, MST..." value="<?= e($filters['search'] ?? '') ?>">
                    <i class="ri-search-line search-icon"></i>
                </div>

                <select name="industry" class="form-select" style="width:auto;min-width:140px" onchange="this.form.submit()">
                    <option value="">Ngành nghề</option>
                    <?php foreach ($industries as $ind): ?>
                        <option value="<?= $ind ?>" <?= ($filters['industry'] ?? '') === $ind ? 'selected' : '' ?>><?= $ind ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="company_size" class="form-select" style="width:auto;min-width:120px" onchange="this.form.submit()">
                    <option value="">Quy mô</option>
                    <?php foreach ($sizes as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['company_size'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?> người</option>
                    <?php endforeach; ?>
                </select>

                <select name="city" class="form-select" style="width:auto;min-width:140px" onchange="this.form.submit()">
                    <option value="">Thành phố</option>
                    <?php foreach ($cities ?? [] as $c): ?>
                        <option value="<?= e($c['city']) ?>" <?= ($filters['city'] ?? '') === $c['city'] ? 'selected' : '' ?>><?= e($c['city']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="owner_id" class="form-select" style="width:auto;min-width:150px" onchange="this.form.submit()">
                    <option value="">Phụ trách</option>
                    <?php foreach ($users ?? [] as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($filters['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                <?php if (!empty(array_filter($filters ?? []))): ?>
                    <a href="<?= url('companies') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
                <?php endif; ?>
            </form>

            <div class="d-flex gap-2 ms-auto">
                <a href="<?= url('companies/trash') ?>" class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Thùng rác</a>
                <a href="<?= url('companies/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm DN</a>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="text-muted table-light">
                    <tr>
                        <th class="ps-3" style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                        <th>Doanh nghiệp</th>
                        <th>Liên hệ</th>
                        <th>Ngành nghề</th>
                        <th>Quy mô</th>
                        <th>KH</th>
                        <th>Cơ hội</th>
                        <th>Doanh thu</th>
                        <th>Phụ trách</th>
                        <th>Liên hệ cuối</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($companies['items'])): ?>
                        <?php foreach ($companies['items'] as $c): ?>
                        <tr>
                            <td class="ps-3"><input type="checkbox" class="form-check-input row-check" value="<?= $c['id'] ?>"></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs flex-shrink-0 me-2">
                                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-13"><?= strtoupper(substr($c['name'], 0, 1)) ?></span>
                                    </div>
                                    <div>
                                        <a href="<?= url('companies/' . $c['id']) ?>" class="fw-medium text-dark"><?= e($c['name']) ?></a>
                                        <?php if ($c['city']): ?>
                                            <div class="text-muted fs-12"><i class="ri-map-pin-line me-1"></i><?= e($c['city']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($c['email']): ?><div class="fs-12"><i class="ri-mail-line me-1 text-muted"></i><?= e($c['email']) ?></div><?php endif; ?>
                                <?php if ($c['phone']): ?><div class="fs-12"><i class="ri-phone-line me-1 text-muted"></i><?= e($c['phone']) ?></div><?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['industry']): ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= e($c['industry']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted fs-13"><?= e($c['company_size'] ?? '-') ?></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= $c['contact_count'] ?? 0 ?></span></td>
                            <td><span class="badge bg-warning-subtle text-warning"><?= $c['deal_count'] ?? 0 ?></span></td>
                            <td class="fw-medium"><?= ($c['total_revenue'] ?? 0) > 0 ? format_money($c['total_revenue']) : '-' ?></td>
                            <td class="fs-13"><?= e($c['owner_name'] ?? '-') ?></td>
                            <td class="text-muted fs-12"><?= !empty($c['last_activity_at']) ? time_ago($c['last_activity_at']) : '-' ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= url('companies/' . $c['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                        <li><a class="dropdown-item" href="<?= url('companies/' . $c['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="<?= url('companies/' . $c['id'] . '/delete') ?>" data-confirm="Xóa <?= e($c['name']) ?>?">
                                                <?= csrf_field() ?>
                                                <button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="avatar-md mx-auto mb-3">
                                    <span class="avatar-title bg-info-subtle rounded-circle"><i class="ri-building-line text-info fs-24"></i></span>
                                </div>
                                <h5 class="text-muted">Chưa có doanh nghiệp nào</h5>
                                <a href="<?= url('companies/create') ?>" class="btn btn-primary mt-2"><i class="ri-add-line me-1"></i> Thêm doanh nghiệp</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($companies['total_pages'] ?? 0) > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
            <div class="text-muted fs-13">
                Hiển thị <strong><?= (($companies['page'] - 1) * 20) + 1 ?> - <?= min($companies['page'] * 20, $companies['total']) ?></strong> / <strong><?= number_format($companies['total']) ?></strong>
            </div>
            <nav>
                <ul class="pagination mb-0">
                    <?php if ($companies['page'] > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= url('companies?page=' . ($companies['page']-1) . '&' . http_build_query(array_filter($filters ?? []))) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $companies['page']-2); $i <= min($companies['total_pages'], $companies['page']+2); $i++): ?>
                        <li class="page-item <?= $i === $companies['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('companies?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($companies['page'] < $companies['total_pages']): ?>
                        <li class="page-item"><a class="page-link" href="<?= url('companies?page=' . ($companies['page']+1) . '&' . http_build_query(array_filter($filters ?? []))) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
