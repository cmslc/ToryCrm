<?php
$pageTitle = 'Doanh nghiệp';
$industries = ['Công nghệ', 'Tài chính', 'Bất động sản', 'Sản xuất', 'Thương mại', 'Y tế', 'Giáo dục', 'Truyền thông', 'Vận tải', 'F&B', 'Du lịch', 'Nông nghiệp', 'Khác'];
$sizes = ['1-10', '10-20', '20-50', '50-100', '100-500', '200-500', '500+'];
$columns = [
    'col-company' => 'Doanh nghiệp',
    'col-contact' => 'Liên hệ',
    'col-industry' => 'Ngành nghề',
    'col-size' => 'Quy mô',
    'col-customers' => 'KH',
    'col-deals' => 'Cơ hội',
    'col-revenue' => 'Doanh thu',
    'col-owner' => 'Phụ trách',
    'col-lastact' => 'Liên hệ cuối',
];
?>

<!-- Title Row -->
<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Doanh nghiệp</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-soft-secondary" id="toggleColumnPanel">Hiển thị cột <i class="ri-arrow-down-s-line ms-1"></i></button>
        <a href="<?= url('companies/trash') ?>" class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Thùng rác</a>
        <a href="<?= url('companies/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm DN</a>
    </div>
</div>

<!-- Column Options Panel -->
<div class="card mb-2 d-none" id="columnPanel">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h6 class="mb-2">Cột</h6>
                <div class="d-flex flex-wrap gap-3">
                    <?php foreach ($columns as $colId => $colLabel): ?>
                    <div class="form-check">
                        <input class="form-check-input column-toggle" type="checkbox" id="<?= $colId ?>" data-column="<?= $colId ?>" checked>
                        <label class="form-check-label" for="<?= $colId ?>"><?= $colLabel ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="button" class="btn btn-soft-secondary py-1 px-2" id="resetColumns"><i class="ri-refresh-line me-1"></i>Đặt lại</button>
        </div>
    </div>
</div>

<!-- Filter Row -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('companies') ?>" class="d-flex align-items-center gap-2 flex-wrap">
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

                <?php $deptGroupedFilter = []; foreach ($users ?? [] as $u) { $deptGroupedFilter[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                <select name="owner_id" class="form-select" style="width:auto;min-width:150px" onchange="this.form.submit()">
                    <option value="">Phụ trách</option>
                    <?php foreach ($deptGroupedFilter as $dept => $dUsers): ?>
                    <optgroup label="<?= e($dept) ?>">
                        <?php foreach ($dUsers as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($filters['owner_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                <?php if (!empty(array_filter($filters ?? []))): ?>
                    <a href="<?= url('companies') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
                <?php endif; ?>
            </form>
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
                        <th class="col-company">Doanh nghiệp</th>
                        <th class="col-contact">Liên hệ</th>
                        <th class="col-industry">Ngành nghề</th>
                        <th class="col-size">Quy mô</th>
                        <th class="col-customers">KH</th>
                        <th class="col-deals">Cơ hội</th>
                        <th class="col-revenue">Doanh thu</th>
                        <th class="col-owner">Phụ trách</th>
                        <th class="col-lastact">Liên hệ cuối</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($companies['items'])): ?>
                        <?php foreach ($companies['items'] as $c): ?>
                        <tr>
                            <td class="ps-3"><input type="checkbox" class="form-check-input row-check" value="<?= $c['id'] ?>"></td>
                            <td class="col-company">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs flex-shrink-0 me-2">
                                        <?php if (!empty($c['logo']) && file_exists(BASE_PATH . '/public/uploads/logos/' . $c['logo'])): ?>
                                            <img src="<?= url('uploads/logos/' . $c['logo']) ?>" class="rounded-circle object-fit-cover" style="width:100%;height:100%">
                                        <?php else: ?>
                                            <span class="avatar-title bg-info-subtle text-info rounded-circle fs-13"><?= strtoupper(substr($c['name'], 0, 1)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="<?= url('companies/' . $c['id']) ?>" class="fw-medium text-dark"><?= e($c['name']) ?></a>
                                        <?php if ($c['city']): ?>
                                            <div class="text-muted fs-12"><i class="ri-map-pin-line me-1"></i><?= e($c['city']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="col-contact">
                                <?php if ($c['email']): ?><div class="fs-12"><i class="ri-mail-line me-1 text-muted"></i><?= e($c['email']) ?></div><?php endif; ?>
                                <?php if ($c['phone']): ?><div class="fs-12"><i class="ri-phone-line me-1 text-muted"></i><?= e($c['phone']) ?></div><?php endif; ?>
                            </td>
                            <td class="col-industry">
                                <?php if ($c['industry']): ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= e($c['industry']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-size text-muted fs-13"><?= e($c['company_size'] ?? '-') ?></td>
                            <td class="col-customers"><span class="badge bg-primary-subtle text-primary"><?= $c['contact_count'] ?? 0 ?></span></td>
                            <td class="col-deals"><span class="badge bg-warning-subtle text-warning"><?= $c['deal_count'] ?? 0 ?></span></td>
                            <td class="col-revenue fw-medium"><?= ($c['total_revenue'] ?? 0) > 0 ? format_money($c['total_revenue']) : '-' ?></td>
                            <td class="col-owner"><?= user_avatar($c['owner_name'] ?? null) ?></td>
                            <td class="col-lastact text-muted fs-12"><?= !empty($c['last_activity_at']) ? time_ago($c['last_activity_at']) : '-' ?></td>
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

<script>
// Toggle column panel
document.getElementById('toggleColumnPanel')?.addEventListener('click', function() {
    var panel = document.getElementById('columnPanel');
    panel.classList.toggle('d-none');
    var isOpen = !panel.classList.contains('d-none');
    this.innerHTML = 'Hiển thị cột <i class="ri-arrow-' + (isOpen ? 'up' : 'down') + '-s-line ms-1"></i>';
});

// Column toggle
(function() {
    var storageKey = 'company_columns';
    var saved = JSON.parse(localStorage.getItem(storageKey) || '{}');

    function applyColumns() {
        document.querySelectorAll('.column-toggle').forEach(function(cb) {
            var col = cb.dataset.column;
            var visible = saved[col] !== false;
            cb.checked = visible;
            document.querySelectorAll('.' + col).forEach(function(el) {
                el.style.display = visible ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('.column-toggle').forEach(function(cb) {
        cb.addEventListener('change', function() {
            saved[this.dataset.column] = this.checked;
            localStorage.setItem(storageKey, JSON.stringify(saved));
            applyColumns();
        });
    });

    document.getElementById('resetColumns')?.addEventListener('click', function() {
        saved = {};
        localStorage.removeItem(storageKey);
        applyColumns();
    });

    applyColumns();
})();
</script>
