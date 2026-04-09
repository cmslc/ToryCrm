<?php $pageTitle = 'Khách hàng'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Khách hàng</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('contacts/trash') ?>" class="btn btn-soft-danger btn"><i class="ri-delete-bin-line me-1"></i> Thùng rác</a>
        <a href="<?= url('contacts/create') ?>" class="btn btn-primary btn"><i class="ri-add-line me-1"></i> Thêm KH</a>
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
                    <input type="text" class="form-control form-control search" name="search" placeholder="Tìm tên, email, SĐT..." value="<?= e($filters['search'] ?? '') ?>">
                    <i class="ri-search-line search-icon"></i>
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select">
                    <option value="">Trạng thái</option>
                    <?php foreach (['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="source_id" class="form-select form-select">
                    <option value="">Nguồn</option>
                    <?php foreach ($sources ?? [] as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($filters['source_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="owner_id" class="form-select form-select">
                    <option value="">Phụ trách</option>
                    <?php foreach ($users ?? [] as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($filters['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary"><i class="ri-equalizer-fill me-1"></i> Lọc</button>
                <?php if (!empty(array_filter($filters ?? []))): ?>
                    <a href="<?= url('contacts') ?>" class="btn btn-soft-danger"><i class="ri-close-line"></i></a>
                <?php endif; ?>
                <?php $module = 'contacts'; include BASE_PATH . '/resources/views/components/saved-views.php'; ?>
                <div class="dropdown">
                    <button class="btn btn-soft-secondary" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Tùy chọn cột">
                        <i class="ri-settings-3-line me-1"></i> Hiển thị
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width: 220px;">
                        <h6 class="dropdown-header px-0">Hiển thị cột</h6>
                        <?php
                        $columns = [
                            'col-customer' => 'Khách hàng',
                            'col-contact' => 'Liên hệ',
                            'col-company' => 'Công ty',
                            'col-source' => 'Nguồn',
                            'col-status' => 'Trạng thái',
                            'col-owner' => 'Phụ trách',
                            'col-address' => 'Địa chỉ',
                            'col-birthday' => 'Ngày sinh',
                            'col-tags' => 'Nhãn',
                            'col-created' => 'Ngày tạo',
                        ];
                        foreach ($columns as $colId => $colLabel): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="<?= $colId ?>" data-column="<?= $colId ?>" checked>
                            <label class="form-check-label" for="<?= $colId ?>"><?= $colLabel ?></label>
                        </div>
                        <?php endforeach; ?>
                        <hr class="my-2">
                        <button type="button" class="btn btn-soft-primary w-100" id="resetColumns"><i class="ri-refresh-line me-1"></i>Đặt lại</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="text-muted table-light">
                    <tr>
                        <th style="width:30px"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                        <th class="col-customer">Khách hàng</th>
                        <th class="col-contact">Liên hệ</th>
                        <th class="col-company">Công ty</th>
                        <th class="col-source">Nguồn</th>
                        <th class="col-status">Trạng thái</th>
                        <th class="col-owner">Phụ trách</th>
                        <th class="col-address">Địa chỉ</th>
                        <th class="col-birthday">Ngày sinh</th>
                        <th class="col-tags">Nhãn</th>
                        <th class="col-created">Ngày tạo</th>
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
                            <td><input type="checkbox" class="form-check-input row-check" value="<?= $c['id'] ?>"></td>
                            <td class="col-customer">
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
                            <td class="col-contact">
                                <?php if ($c['email']): ?><div class="fs-12"><i class="ri-mail-line me-1 text-muted"></i><?= e($c['email']) ?></div><?php endif; ?>
                                <?php if ($c['phone']): ?><div class="fs-12"><i class="ri-phone-line me-1 text-muted"></i><?= e($c['phone']) ?></div><?php endif; ?>
                            </td>
                            <td class="col-company">
                                <?php if ($c['company_id']): ?>
                                    <a href="<?= url('companies/' . $c['company_id']) ?>" class="text-body"><?= e($c['company_name']) ?></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-source">
                                <?php if (!empty($c['source_name'])): ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= e($c['source_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-status">
                                <span data-inline-edit data-url="<?= url('contacts/' . $c['id'] . '/quick-update') ?>" data-field="status" data-type="select"
                                      data-options='<?= json_encode(['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất']) ?>'
                                      data-value="<?= e($c['status']) ?>">
                                    <span class="badge bg-<?= $sColors[$c['status']] ?? 'secondary' ?>-subtle text-<?= $sColors[$c['status']] ?? 'secondary' ?>">
                                        <?= $sLabels[$c['status']] ?? $c['status'] ?>
                                    </span>
                                </span>
                            </td>
                            <td class="col-owner fs-13">
                                <span data-inline-edit data-url="<?= url('contacts/' . $c['id'] . '/quick-update') ?>" data-field="owner_id" data-type="user"
                                      data-value="<?= e($c['owner_id'] ?? '') ?>">
                                    <?= e($c['owner_name'] ?? '-') ?>
                                </span>
                            </td>
                            <td class="col-address fs-12 text-muted"><?= e($c['address'] ?? '-') ?></td>
                            <td class="col-birthday fs-12"><?= !empty($c['date_of_birth']) ? date('d/m/Y', strtotime($c['date_of_birth'])) : '-' ?></td>
                            <td class="col-tags">
                                <?php if (!empty($c['tags'])): ?>
                                    <?php foreach (explode(',', $c['tags']) as $tag): ?>
                                        <span class="badge bg-info-subtle text-info me-1"><?= e(trim($tag)) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-created text-muted fs-12"><?= time_ago($c['created_at']) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-soft-secondary btn " data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= url('contacts/' . $c['id']) ?>"><i class="ri-eye-line me-2 align-middle"></i>Xem</a></li>
                                        <li><a class="dropdown-item" href="<?= url('contacts/' . $c['id'] . '/edit') ?>"><i class="ri-pencil-line me-2 align-middle"></i>Sửa</a></li>
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
                            <td colspan="13" class="text-center py-5">
                                <div class="avatar-md mx-auto mb-3">
                                    <span class="avatar-title bg-primary-subtle rounded-circle">
                                        <i class="ri-contacts-line text-primary fs-24"></i>
                                    </span>
                                </div>
                                <h5 class="text-muted">Chưa có khách hàng nào</h5>
                                <a href="<?= url('contacts/create') ?>" class="btn btn-primary btn mt-2"><i class="ri-add-line me-1"></i> Thêm khách hàng</a>
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
                <ul class="pagination pagination mb-0">
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

<script>
// Inline edit: preload users
window.__inlineEditUsers = <?= json_encode($users ?? []) ?>;
</script>
<script src="<?= url('js/inline-edit.js') ?>"></script>
<script>
// Bulk actions config
window.__bulkConfig = {
    url: '<?= url('contacts/bulk') ?>',
    module: 'contacts',
    statuses: {new:'Mới', contacted:'Đã liên hệ', qualified:'Tiềm năng', converted:'Chuyển đổi', lost:'Mất'},
    users: <?= json_encode($users ?? []) ?>
};
</script>
<script src="<?= url('js/bulk-actions.js') ?>"></script>

<script>
(function() {
    const STORAGE_KEY = 'torycrm_contacts_columns';
    const allColumns = ['col-customer','col-contact','col-company','col-source','col-status','col-owner','col-address','col-birthday','col-tags','col-created'];
    const defaults = ['col-customer','col-contact','col-company','col-status','col-owner','col-created'];

    function getVisible() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || defaults; }
        catch(e) { return defaults; }
    }

    function applyColumns(visible) {
        allColumns.forEach(col => {
            const show = visible.includes(col);
            document.querySelectorAll('.' + col).forEach(el => {
                el.style.display = show ? '' : 'none';
            });
            const cb = document.getElementById(col);
            if (cb) cb.checked = show;
        });
    }

    applyColumns(getVisible());

    document.querySelectorAll('.column-toggle').forEach(cb => {
        cb.addEventListener('change', function() {
            const visible = [];
            document.querySelectorAll('.column-toggle:checked').forEach(c => visible.push(c.dataset.column));
            if (visible.length === 0) { this.checked = true; return; }
            localStorage.setItem(STORAGE_KEY, JSON.stringify(visible));
            applyColumns(visible);
        });
    });

    document.getElementById('resetColumns')?.addEventListener('click', function() {
        localStorage.removeItem(STORAGE_KEY);
        applyColumns(defaults);
    });
})();
</script>
