<?php $pageTitle = 'Khách hàng'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Khách hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('contacts/trash') ?>" class="btn btn-soft-danger btn-sm"><i class="ri-delete-bin-line me-1"></i> Thùng rác</a>
        <a href="<?= url('contacts/create') ?>" class="btn btn-primary btn-sm"><i class="ri-add-line me-1"></i> Thêm KH</a>
    </div>
</div>

<!-- Stat Cards (compact) -->
<div class="row">
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
        foreach ($statusCounts ?? [] as $sc) { if ($sc['status'] === $key) $count = $sc['count']; }
    ?>
    <div class="col">
        <div class="card card-animate">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-xs flex-shrink-0 me-2">
                        <span class="avatar-title bg-<?= $info['color'] ?>-subtle rounded-circle">
                            <i class="<?= $info['icon'] ?> text-<?= $info['color'] ?>"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= $count ?></h5>
                        <p class="text-muted mb-0 fs-12"><?= $info['label'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Main Card: Filter + Table -->
<div class="card">
    <div class="card-header border-0">
        <form method="GET" action="<?= url('contacts') ?>" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="search-box">
                    <input type="text" class="form-control form-control-sm search" name="search" placeholder="Tìm tên, email, SĐT..." value="<?= e($filters['search'] ?? '') ?>">
                    <i class="ri-search-line search-icon"></i>
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Trạng thái</option>
                    <?php foreach (['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="source_id" class="form-select form-select-sm">
                    <option value="">Nguồn</option>
                    <?php foreach ($sources ?? [] as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($filters['source_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="owner_id" class="form-select form-select-sm">
                    <option value="">Phụ trách</option>
                    <?php foreach ($users ?? [] as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($filters['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="ri-equalizer-fill me-1"></i> Lọc</button>
                <?php if (!empty(array_filter($filters ?? []))): ?>
                    <a href="<?= url('contacts') ?>" class="btn btn-soft-danger btn-sm"><i class="ri-close-line"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="text-muted table-light">
                    <tr>
                        <th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                        <th>Khách hàng</th>
                        <th>Liên hệ</th>
                        <th>Công ty</th>
                        <th>Trạng thái</th>
                        <th>Phụ trách</th>
                        <th>Ngày tạo</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contacts['items'])): ?>
                        <?php
                        $sColors = ['new'=>'info','contacted'=>'primary','qualified'=>'warning','converted'=>'success','lost'=>'danger'];
                        $sLabels = ['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất'];
                        ?>
                        <?php foreach ($contacts['items'] as $c): ?>
                        <tr>
                            <td><input type="checkbox" class="form-check-input contact-check" value="<?= $c['id'] ?>"></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs flex-shrink-0 me-2">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-13">
                                            <?= strtoupper(substr($c['first_name'], 0, 1)) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <a href="<?= url('contacts/' . $c['id']) ?>" class="fw-medium text-dark"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></a>
                                        <?php if ($c['position']): ?>
                                            <div class="text-muted fs-12"><?= e($c['position']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($c['email']): ?><div class="fs-12"><i class="ri-mail-line me-1 text-muted"></i><?= e($c['email']) ?></div><?php endif; ?>
                                <?php if ($c['phone']): ?><div class="fs-12"><i class="ri-phone-line me-1 text-muted"></i><?= e($c['phone']) ?></div><?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['company_id']): ?>
                                    <a href="<?= url('companies/' . $c['company_id']) ?>" class="text-body"><?= e($c['company_name']) ?></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $sColors[$c['status']] ?? 'secondary' ?>-subtle text-<?= $sColors[$c['status']] ?? 'secondary' ?>">
                                    <?= $sLabels[$c['status']] ?? $c['status'] ?>
                                </span>
                            </td>
                            <td class="fs-13"><?= e($c['owner_name'] ?? '-') ?></td>
                            <td class="text-muted fs-12"><?= time_ago($c['created_at']) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-soft-secondary btn-sm btn-icon" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= url('contacts/' . $c['id']) ?>"><i class="ri-eye-line me-2 align-middle"></i>Xem</a></li>
                                        <li><a class="dropdown-item" href="<?= url('contacts/' . $c['id'] . '/edit') ?>"><i class="ri-pencil-line me-2 align-middle"></i>Sửa</a></li>
                                        <li><a class="dropdown-item" href="<?= url('contacts/' . $c['id'] . '/bonus-points') ?>"><i class="ri-star-line me-2 align-middle"></i>Điểm thưởng</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="<?= url('contacts/' . $c['id'] . '/delete') ?>" data-confirm="Xóa khách hàng <?= e($c['first_name']) ?>?">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2 align-middle"></i>Xóa</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="avatar-md mx-auto mb-3">
                                    <span class="avatar-title bg-primary-subtle rounded-circle">
                                        <i class="ri-contacts-line text-primary fs-24"></i>
                                    </span>
                                </div>
                                <h5 class="text-muted">Chưa có khách hàng nào</h5>
                                <a href="<?= url('contacts/create') ?>" class="btn btn-primary btn-sm mt-2"><i class="ri-add-line me-1"></i> Thêm khách hàng</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($contacts['total_pages'] ?? 0) > 1): ?>
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <div class="text-muted fs-13">
                Hiển thị <strong><?= count($contacts['items']) ?></strong> / <strong><?= number_format($contacts['total']) ?></strong> khách hàng
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($contacts['page'] > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= url('contacts?page=' . ($contacts['page']-1) . '&' . http_build_query(array_filter($filters ?? []))) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $contacts['page']-2); $i <= min($contacts['total_pages'], $contacts['page']+2); $i++): ?>
                        <li class="page-item <?= $i === $contacts['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('contacts?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($contacts['page'] < $contacts['total_pages']): ?>
                        <li class="page-item"><a class="page-link" href="<?= url('contacts?page=' . ($contacts['page']+1) . '&' . http_build_query(array_filter($filters ?? []))) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
