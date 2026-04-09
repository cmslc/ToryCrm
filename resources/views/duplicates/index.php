<?php $pageTitle = 'Trùng lặp'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Phát hiện trùng lặp</h4>
            <div>
                <form method="POST" action="<?= url('duplicates/scan') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-search-eye-line me-1"></i> Quét trùng lặp
                    </button>
                </form>
            </div>
        </div>

        <?php if ($flash = getFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= e($flash) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($flash = getFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= e($flash) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-md">
                            <div class="avatar-title bg-warning-subtle text-warning rounded-circle fs-22">
                                <i class="ri-file-copy-line"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-muted mb-0">Tổng nhóm trùng</p>
                            <h4 class="mb-0"><?= $contactCount + $companyCount ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-md">
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-22">
                                <i class="ri-contacts-line"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-muted mb-0">Trùng khách hàng</p>
                            <h4 class="mb-0"><?= $contactCount ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-md">
                            <div class="avatar-title bg-success-subtle text-success rounded-circle fs-22">
                                <i class="ri-building-line"></i>
                            </div>
                        </div>
                        <div>
                            <p class="text-muted mb-0">Trùng doanh nghiệp</p>
                            <h4 class="mb-0"><?= $companyCount ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="mb-3">
            <div class="btn-group">
                <a href="<?= url('duplicates') ?>" class="btn <?= empty($filterType) ? 'btn-primary' : 'btn-light' ?>">Tất cả</a>
                <a href="<?= url('duplicates?type=contact') ?>" class="btn <?= $filterType === 'contact' ? 'btn-primary' : 'btn-light' ?>">Khách hàng</a>
                <a href="<?= url('duplicates?type=company') ?>" class="btn <?= $filterType === 'company' ? 'btn-primary' : 'btn-light' ?>">Doanh nghiệp</a>
            </div>
        </div>

        <!-- Duplicate Groups -->
        <?php if (!empty($groups)): ?>
            <?php foreach ($groups as $group): ?>
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-<?= $group['entity_type'] === 'contact' ? 'primary' : 'success' ?>-subtle text-<?= $group['entity_type'] === 'contact' ? 'primary' : 'success' ?>">
                                <?= $group['entity_type'] === 'contact' ? 'Khách hàng' : 'Doanh nghiệp' ?>
                            </span>
                            <span class="text-muted ms-2">
                                Trùng <strong><?= e($group['match_field']) ?></strong>: <code><?= e($group['match_value']) ?></code>
                            </span>
                        </div>
                        <form method="POST" action="<?= url('duplicates/' . $group['id'] . '/ignore') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-light">
                                <i class="ri-close-line me-1"></i> Bỏ qua
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('duplicates/' . $group['id'] . '/merge') ?>">
                            <?= csrf_field() ?>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-3">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:50px;">Giữ</th>
                                            <th>ID</th>
                                            <?php if ($group['entity_type'] === 'contact'): ?>
                                                <th>Họ tên</th>
                                                <th>Email</th>
                                                <th>Điện thoại</th>
                                                <th>Trạng thái</th>
                                            <?php else: ?>
                                                <th>Tên công ty</th>
                                                <th>Mã số thuế</th>
                                                <th>Điện thoại</th>
                                            <?php endif; ?>
                                            <th>Ngày tạo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($group['items'] as $idx => $item): ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="radio" name="keep_id" value="<?= $item['id'] ?>" class="form-check-input" <?= $idx === 0 ? 'checked' : '' ?>>
                                                </td>
                                                <td>#<?= $item['id'] ?></td>
                                                <?php if ($group['entity_type'] === 'contact'): ?>
                                                    <td>
                                                        <a href="<?= url('contacts/' . $item['id']) ?>"><?= e(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? '')) ?></a>
                                                    </td>
                                                    <td>
                                                        <span class="<?= $group['match_field'] === 'email' ? 'bg-warning-subtle px-1 rounded' : '' ?>">
                                                            <?= e($item['email'] ?? '-') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="<?= $group['match_field'] === 'phone' ? 'bg-warning-subtle px-1 rounded' : '' ?>">
                                                            <?= e($item['phone'] ?? '-') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= e($item['status'] ?? '-') ?></td>
                                                <?php else: ?>
                                                    <td>
                                                        <a href="<?= url('companies/' . $item['id']) ?>">
                                                            <span class="<?= $group['match_field'] === 'name' ? 'bg-warning-subtle px-1 rounded' : '' ?>">
                                                                <?= e($item['name'] ?? '-') ?>
                                                            </span>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="<?= $group['match_field'] === 'tax_code' ? 'bg-warning-subtle px-1 rounded' : '' ?>">
                                                            <?= e($item['tax_code'] ?? '-') ?>
                                                        </span>
                                                    </td>
                                                    <td><?= e($item['phone'] ?? '-') ?></td>
                                                <?php endif; ?>
                                                <td><small class="text-muted"><?= date('d/m/Y', strtotime($item['created_at'] ?? 'now')) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="ri-merge-cells-horizontal me-1"></i> Gộp bản ghi
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="ri-file-copy-line fs-1 text-muted d-block mb-2"></i>
                    <p class="text-muted mb-0">Không có nhóm trùng lặp nào. Nhấn "Quét trùng lặp" để kiểm tra.</p>
                </div>
            </div>
        <?php endif; ?>
