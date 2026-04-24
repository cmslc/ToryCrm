<?php
$pageTitle = 'Cơ hội kinh doanh';
$colKeys = array_column($displayColumns ?? [], 'key');
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Cơ hội kinh doanh</h4>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-soft-secondary btn-icon" id="toggleColumnPanel" title="Hiển thị cột"><i class="ri-layout-column-line"></i></button>
                <a href="<?= url('deals/pipeline') ?>" class="btn btn-soft-info"><i class="ri-git-branch-line me-1"></i> Pipeline</a>
                <a href="<?= url('deals/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm cơ hội</a>
            </div>
        </div>

        <!-- Column Options Panel -->
        <div class="card mb-2 d-none" id="columnPanel">
            <div class="card-body py-3">
                <h6 class="mb-2">Cột hiển thị</h6>
                <div class="d-flex flex-wrap gap-3 mb-3">
                    <?php foreach ($displayColumns as $dc): ?>
                    <div class="form-check">
                        <input class="form-check-input column-toggle" type="checkbox" id="<?= $dc['key'] ?>" data-column="<?= $dc['key'] ?>" checked>
                        <label class="form-check-label" for="<?= $dc['key'] ?>"><?= e($dc['label']) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex justify-content-end">
                    <div class="text-end">
                        <button type="button" class="btn btn-soft-secondary py-1 px-2" id="resetColumns"><i class="ri-refresh-line me-1"></i>Đặt lại</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header p-2">
                <form method="GET" action="<?= url('deals') ?>" class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="search-box" style="min-width:200px;max-width:300px">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= e($filters['search'] ?? '') ?>">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                    <select name="status" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                        <option value="">Trạng thái</option>
                        <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Đang mở</option>
                        <option value="won" <?= ($filters['status'] ?? '') === 'won' ? 'selected' : '' ?>>Thắng</option>
                        <option value="lost" <?= ($filters['status'] ?? '') === 'lost' ? 'selected' : '' ?>>Thua</option>
                    </select>
                    <select name="stage_id" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                        <option value="">Giai đoạn</option>
                        <?php foreach ($stages ?? [] as $stage): ?>
                            <option value="<?= $stage['id'] ?>" <?= ($filters['stage_id'] ?? '') == $stage['id'] ? 'selected' : '' ?>><?= e($stage['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                    <?php if (!empty(array_filter($filters ?? []))): ?>
                        <a href="<?= url('deals') ?>" class="btn btn-soft-danger btn-icon" title="Xóa lọc"><i class="ri-refresh-line"></i></a>
                    <?php endif; ?>
                    <input type="hidden" name="per_page" value="<?= e($filters['per_page'] ?? 20) ?>">
                </form>
            </div>
            <div class="card-body p-2">

                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sticky mb-0">
                        <thead class="table-light">
                            <tr>
                                <?php foreach ($displayColumns ?? [] as $col): ?>
                                <th class="<?= $col['key'] ?>"><?= e($col['label']) ?></th>
                                <?php endforeach; ?>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($deals['items'])): ?>
                                <?php foreach ($deals['items'] as $deal): ?>
                                    <tr>
                                        <?php foreach ($displayColumns ?? [] as $col):
                                            $field = $col['field'];
                                        ?>
                                        <td class="<?= $col['key'] ?>">
                                            <?php if ($field === 'title'): ?>
                                                <a href="<?= url('deals/' . $deal['id']) ?>" class="fw-medium text-dark"><?= e($deal['title']) ?></a>
                                            <?php elseif ($field === 'value'): ?>
                                                <span class="fw-medium"><?= format_money($deal['value']) ?></span>
                                            <?php elseif ($field === 'stage_id'): ?>
                                                <span class="badge" style="background-color:<?= safe_color($deal['stage_color'] ?? null) ?>"><?= e($deal['stage_name'] ?? '') ?></span>
                                            <?php elseif ($field === 'contact_id'): ?>
                                                <?= e(($deal['contact_first_name'] ?? '') . ' ' . ($deal['contact_last_name'] ?? '')) ?: '-' ?>
                                            <?php elseif ($field === 'company_id'): ?>
                                                <?= e($deal['company_name'] ?? '-') ?>
                                            <?php elseif ($field === 'status'): ?>
                                                <?php $stl = ['open'=>'Đang mở','won'=>'Thắng','lost'=>'Thua']; $stc = ['open'=>'primary','won'=>'success','lost'=>'danger']; ?>
                                                <span class="badge bg-<?= $stc[$deal['status']] ?? 'secondary' ?>-subtle text-<?= $stc[$deal['status']] ?? 'secondary' ?>"><?= $stl[$deal['status']] ?? $deal['status'] ?></span>
                                            <?php elseif ($field === 'priority'): ?>
                                                <?php $pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger']; $pl = ['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn']; ?>
                                                <span class="badge bg-<?= $pc[$deal['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$deal['priority']] ?? 'secondary' ?>"><?= $pl[$deal['priority']] ?? '' ?></span>
                                            <?php elseif ($field === 'expected_close_date'): ?>
                                                <?= $deal['expected_close_date'] ? format_date($deal['expected_close_date']) : '-' ?>
                                            <?php elseif ($field === 'owner_id'): ?>
                                                <?= user_avatar($deal['owner_name'] ?? null) ?>
                                            <?php else: ?>
                                                <?= e($deal[$field] ?? '-') ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php endforeach; ?>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('deals/' . $deal['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('deals/' . $deal['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('deals/' . $deal['id'] . '/delete') ?>" data-confirm="Xác nhận xóa?">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="<?= count($displayColumns ?? []) + 1 ?>" class="text-center py-4 text-muted"><i class="ri-hand-coin-line fs-1 d-block mb-2"></i>Chưa có cơ hội</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($deals['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fs-13">Hiển thị <strong><?= (($deals['page'] - 1) * ($filters['per_page'] ?? 20)) + 1 ?> - <?= min($deals['page'] * ($filters['per_page'] ?? 20), $deals['total']) ?></strong> / <strong><?= number_format($deals['total']) ?></strong></span>
                            <?php $currentPerPage = $filters['per_page'] ?? 20; include __DIR__ . '/../components/per-page-select.php'; ?>
                        </div>
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

<script>
// Toggle column panel
document.getElementById('toggleColumnPanel')?.addEventListener('click', function() {
    var panel = document.getElementById('columnPanel');
    panel.classList.toggle('d-none');
    var isOpen = !panel.classList.contains('d-none');
    this.classList.toggle('active', isOpen);
});

(function() {
    var STORAGE_KEY = 'torycrm_deals_columns';
    var allColumns = <?= json_encode($colKeys) ?>;
    var defaultVisible = ['col-title','col-value','col-stageid','col-contactid','col-companyid','col-priority','col-expectedclosedate','col-ownerid'];

    function getVisible() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY)) || defaultVisible; }
        catch(e) { return defaultVisible; }
    }

    function applyColumns(visible) {
        allColumns.forEach(function(col) {
            var show = visible.includes(col);
            document.querySelectorAll('.' + col).forEach(function(el) { el.style.display = show ? '' : 'none'; });
            var cb = document.getElementById(col);
            if (cb) cb.checked = show;
        });
    }

    document.querySelectorAll('.column-toggle').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var visible = getVisible();
            if (this.checked) {
                if (!visible.includes(this.dataset.column)) visible.push(this.dataset.column);
            } else {
                visible = visible.filter(function(c) { return c !== cb.dataset.column; });
            }
            localStorage.setItem(STORAGE_KEY, JSON.stringify(visible));
            applyColumns(visible);
        });
    });

    document.getElementById('resetColumns')?.addEventListener('click', function() {
        localStorage.removeItem(STORAGE_KEY);
        applyColumns(defaultVisible);
    });

    applyColumns(getVisible());
})();
</script>
