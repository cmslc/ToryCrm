<?php $pageTitle = 'Doanh nghiệp'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Doanh nghiệp</h4>
                    <a href="<?= url('companies/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm doanh nghiệp</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('companies') ?>" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="industry" class="form-select">
                            <option value="">Ngành nghề</option>
                            <?php $industries = ['Công nghệ', 'Tài chính', 'Giáo dục', 'Y tế', 'Bất động sản', 'Thương mại', 'Sản xuất', 'Dịch vụ', 'Khác']; ?>
                            <?php foreach ($industries as $ind): ?>
                                <option value="<?= $ind ?>" <?= ($filters['industry'] ?? '') === $ind ? 'selected' : '' ?>><?= $ind ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('companies') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Doanh nghiệp</th>
                                <th>Email</th>
                                <th>Điện thoại</th>
                                <th>Ngành nghề</th>
                                <th>Liên hệ</th>
                                <th>Cơ hội</th>
                                <th>Người phụ trách</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($companies['items'])): ?>
                                <?php foreach ($companies['items'] as $company): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= url('companies/' . $company['id']) ?>" class="fw-medium text-dark">
                                                <?= e($company['name']) ?>
                                            </a>
                                            <?php if ($company['website']): ?>
                                                <br><small class="text-muted"><?= e($company['website']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($company['email'] ?? '-') ?></td>
                                        <td><?= e($company['phone'] ?? '-') ?></td>
                                        <td><?= e($company['industry'] ?? '-') ?></td>
                                        <td><span class="badge bg-primary-subtle text-primary"><?= $company['contact_count'] ?? 0 ?></span></td>
                                        <td><span class="badge bg-warning-subtle text-warning"><?= $company['deal_count'] ?? 0 ?></span></td>
                                        <td><?= e($company['owner_name'] ?? '-') ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('companies/' . $company['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('companies/' . $company['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('companies/' . $company['id'] . '/delete') ?>" data-confirm="Xác nhận xóa?">
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
                                <tr><td colspan="8" class="text-center py-4 text-muted"><i class="ri-building-line fs-1 d-block mb-2"></i>Chưa có doanh nghiệp</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($companies['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($companies['items']) ?> / <?= $companies['total'] ?></div>
                        <nav>
                            <ul class="pagination mb-0">
                                <?php for ($i = 1; $i <= $companies['total_pages']; $i++): ?>
                                    <li class="page-item <?= $i === $companies['page'] ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= url('companies?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
