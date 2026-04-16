<?php
$pageTitle = 'Khách hàng';
$totalAll = 0;
foreach ($statusCounts ?? [] as $sc) { $totalAll += $sc['count']; }

// Build status maps from DB
$sColors = [];
$sLabels = [];
$sIcons = [];
foreach ($contactStatuses ?? [] as $cs) {
    $sColors[$cs['slug']] = $cs['color'];
    $sLabels[$cs['slug']] = $cs['name'];
    $sIcons[$cs['slug']] = $cs['icon'];
}
// Fallback if no DB data
if (empty($sLabels)) {
    $sColors = ['new'=>'info','contacted'=>'primary','qualified'=>'warning','converted'=>'success','lost'=>'danger'];
    $sLabels = ['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất'];
}
$currentStatus = $filters['status'] ?? '';
// Columns from ColumnService (dynamic)
$colKeys = array_column($displayColumns ?? [], 'key');
?>

<!-- Title Row -->
<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Khách hàng</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-soft-secondary" id="toggleColumnPanel">Hiển thị cột <i class="ri-arrow-down-s-line ms-1"></i></button>
        <button class="btn btn-soft-info" data-bs-toggle="modal" data-bs-target="#importExportModal"><i class="ri-upload-2-line me-1"></i> Import / Export</button>
        <a href="<?= url('contacts/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm khách hàng</a>
    </div>
</div>

<!-- Import/Export Modal -->
<div class="modal fade" id="importExportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="ri-upload-2-line me-2"></i> Import / Export Khách hàng</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabImportC">Import</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabExportC">Export</a></li>
                </ul>
                <div class="tab-content pt-3">
                    <div class="tab-pane active" id="tabImportC">
                        <form method="POST" action="<?= url('import-export/import-contacts') ?>" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label">Chọn file CSV</label>
                                <input type="file" class="form-control" name="file" accept=".csv" required>
                            </div>
                            <div class="alert alert-light border py-2 mb-3">
                                <i class="ri-information-line me-1"></i> File CSV UTF-8, phân cách dấu phẩy. Cột bắt buộc: <code>first_name</code>. Khác: <code>last_name, email, phone, company, source, status</code>.
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="ri-upload-2-line me-1"></i> Import</button>
                                <a href="<?= url('import-export/template/contacts') ?>" class="btn btn-soft-info"><i class="ri-download-line me-1"></i> Tải template</a>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane" id="tabExportC">
                        <div class="mb-3">
                            <label class="form-label">Khoảng thời gian (tùy chọn)</label>
                            <div class="row g-2">
                                <div class="col-6"><input type="date" class="form-control" id="expCDateFrom"></div>
                                <div class="col-6"><input type="date" class="form-control" id="expCDateTo"></div>
                            </div>
                        </div>
                        <a href="<?= url('import-export/export-contacts') ?>" class="btn btn-success" id="btnExpContacts"><i class="ri-download-2-line me-1"></i> Export Khách hàng</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var df=document.getElementById('expCDateFrom'), dt=document.getElementById('expCDateTo'), btn=document.getElementById('btnExpContacts');
    if(!df||!dt||!btn) return;
    var base='<?= url("import-export/export-contacts") ?>';
    function upd(){ var p=[]; if(df.value)p.push('date_from='+df.value); if(dt.value)p.push('date_to='+dt.value); btn.href=base+(p.length?'?'+p.join('&'):''); }
    df.addEventListener('change',upd); dt.addEventListener('change',upd);
})();
</script>

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
        <hr class="my-2">
        <div class="d-flex justify-content-between align-items-center">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="split-view-check">
                <label class="form-check-label" for="split-view-check">Xem nhanh (bấm vào dòng để xem chi tiết bên phải)</label>
            </div>
            <button type="button" class="btn btn-soft-secondary py-1 px-2" id="resetColumns"><i class="ri-refresh-line me-1"></i>Đặt lại</button>
        </div>
    </div>
</div>

<!-- Filter + Status -->
<div class="card mb-3">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('contacts') ?>" class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">
            <div class="search-box" style="min-width:200px;max-width:300px">
                <input type="text" class="form-control" name="search" placeholder="Tên, email, SĐT..." value="<?= e($filters['search'] ?? '') ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <select name="source_id" class="form-select" style="width:auto;min-width:140px" onchange="this.form.submit()">
                <option value="">Chọn nguồn</option>
                <?php foreach ($sources ?? [] as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($filters['source_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
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
            <select name="customer_group" class="form-select" style="width:auto;min-width:140px" onchange="this.form.submit()">
                <option value="">Nhóm KH</option>
                <option value="Khách Lẻ" <?= ($filters['customer_group'] ?? '') === 'Khách Lẻ' ? 'selected' : '' ?>>Khách Lẻ</option>
                <option value="Khách Dự Án" <?= ($filters['customer_group'] ?? '') === 'Khách Dự Án' ? 'selected' : '' ?>>Khách Dự Án</option>
                <option value="Khách Đại Lý" <?= ($filters['customer_group'] ?? '') === 'Khách Đại Lý' ? 'selected' : '' ?>>Khách Đại Lý</option>
            </select>
            <input type="hidden" name="status" id="statusInput" value="<?= e($currentStatus) ?>">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
            <?php if (!empty(array_filter($filters ?? []))): ?>
                <a href="<?= url('contacts') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
            <select name="per_page" class="form-select ms-auto" style="width:auto;min-width:90px" onchange="this.form.submit()">
                <?php foreach ([10,20,50,100] as $pp): ?>
                <option value="<?= $pp ?>" <?= ($filters['per_page'] ?? 20) == $pp ? 'selected' : '' ?>><?= $pp ?> dòng</option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="card-body py-2 px-3 d-flex align-items-center gap-1 border-top">
    <button type="button" class="btn btn-link text-muted p-0 px-1 flex-shrink-0 d-none" id="tabScrollLeft"><i class="ri-arrow-left-s-line fs-18"></i></button>
    <div class="flex-grow-1 d-flex" id="tabScrollContainer" style="overflow-x:auto;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;scrollbar-width:none;min-width:0">
    <style>#tabScrollContainer::-webkit-scrollbar{display:none}</style>
        <div class="d-flex gap-1 flex-nowrap" id="tabScrollInner">
            <a href="<?= url('contacts') ?>" class="btn <?= !$currentStatus ? 'btn-dark' : 'btn-soft-dark' ?> rounded-pill text-nowrap waves-effect">
                Tất cả <span class="badge rounded-pill bg-danger ms-1"><?= number_format($totalAll) ?></span>
            </a>
            <a href="<?= url('contacts?status=today') ?>" class="btn <?= $currentStatus === 'today' ? 'btn-success' : 'btn-soft-success' ?> rounded-pill text-nowrap waves-effect">
                Mới cập nhật <span class="badge rounded-pill bg-danger ms-1"><?= $todayCount ?? 0 ?></span>
            </a>
            <?php foreach ($sLabels as $key => $label):
                $count = 0;
                foreach ($statusCounts ?? [] as $sc) { if ($sc['status'] === $key) $count = $sc['count']; }
                $color = $sColors[$key] ?? 'secondary';
                $isActive = $currentStatus === $key;
            ?>
            <a href="<?= url('contacts?status=' . $key . '&' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>"
               class="btn <?= $isActive ? "btn-{$color}" : "btn-soft-{$color}" ?> rounded-pill text-nowrap waves-effect">
                <?= $label ?> <span class="badge rounded-pill bg-danger ms-1"><?= number_format($count) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <button type="button" class="btn btn-link text-muted p-0 px-1 flex-shrink-0 d-none" id="tabScrollRight"><i class="ri-arrow-right-s-line fs-18"></i></button>
    <div class="dropdown flex-shrink-0 ms-auto">
        <button class="btn btn-soft-secondary py-1 px-2" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= url('contacts/trash') ?>"><i class="ri-delete-bin-line me-2"></i>Thùng rác</a></li>
            <li><a class="dropdown-item" href="<?= url('duplicates') ?>"><i class="ri-file-copy-line me-2"></i>Kiểm tra trùng</a></li>
        </ul>
    </div>
    <script>
    (function() {
        var container = document.getElementById('tabScrollContainer');
        var inner = document.getElementById('tabScrollInner');
        var btnL = document.getElementById('tabScrollLeft');
        var btnR = document.getElementById('tabScrollRight');
        var step = 250;

        function update() {
            var overflow = inner.scrollWidth > container.clientWidth + 2;
            btnL.classList.toggle('d-none', !overflow || container.scrollLeft <= 0);
            btnR.classList.toggle('d-none', !overflow || container.scrollLeft + container.clientWidth >= inner.scrollWidth - 2);
        }

        btnL.addEventListener('click', function() { container.scrollLeft -= step; setTimeout(update, 300); });
        btnR.addEventListener('click', function() { container.scrollLeft += step; setTimeout(update, 300); });
        container.addEventListener('scroll', update);
        window.addEventListener('resize', update);
        setTimeout(update, 100);
    })();
    </script>
    </div>
</div>

<!-- Table -->
<div class="card" id="tableCard">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="text-muted table-light">
                    <tr>
                        <th style="width:30px" class="ps-3"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                        <?php foreach ($displayColumns as $dc): ?>
                        <th class="<?= $dc['key'] ?>"><?= e($dc['label']) ?></th>
                        <?php endforeach; ?>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contacts['items'])): ?>
                        <?php foreach ($contacts['items'] as $c): ?>
                        <?php
                        // Special renderers for known fields
                        $groupLabels = ['du_an'=>'Khách dự án','le'=>'Khách lẻ','dai_ly'=>'Khách đại lý','doanh_nghiep'=>'Doanh nghiệp','vip'=>'VIP',
                            'Khách Dự Án'=>'Khách Dự Án','Khách Lẻ'=>'Khách Lẻ','Khách Đại Lý'=>'Khách Đại Lý'];
                        $groupColors = ['du_an'=>'info','le'=>'secondary','dai_ly'=>'warning','doanh_nghiep'=>'primary','vip'=>'danger',
                            'Khách Dự Án'=>'info','Khách Lẻ'=>'secondary','Khách Đại Lý'=>'warning'];
                        $genderLabels = ['male'=>'Nam','female'=>'Nữ','other'=>'Khác'];
                        ?>
                        <tr data-id="<?= $c['id'] ?>">
                            <td class="ps-3"><input type="checkbox" class="form-check-input row-check" value="<?= $c['id'] ?>"></td>
                            <?php foreach ($displayColumns as $dc):
                                $field = $dc['field'];
                                $key = $dc['key'];
                                $val = $c[$field] ?? '';
                            ?>
                            <td class="<?= $key ?>">
                            <?php
                            // Custom rendering per field
                            switch ($field):
                                case 'full_name':
                                    $contactName = $c['primary_contact_name'] ?? trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
                                    $initial = mb_substr($contactName ?: '?', 0, 1);
                                    echo '<div class="d-flex align-items-center gap-2">';
                                    if (!empty($c['avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $c['avatar'])) {
                                        echo '<img src="' . url('uploads/avatars/' . $c['avatar']) . '" class="rounded-circle" width="32" height="32" style="object-fit:cover">';
                                    } else {
                                        echo '<span class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-medium" style="width:32px;height:32px;font-size:13px">' . strtoupper($initial) . '</span>';
                                    }
                                    echo '<a href="' . url('contacts/' . $c['id']) . '" class="fw-medium text-dark">' . e($contactName ?: '-') . '</a>';
                                    echo '</div>';
                                    break;
                                case 'account_code':
                                    echo '<code>' . e($val ?: '-') . '</code>';
                                    break;
                                case 'email':
                                    $em = $val ?: ($c['company_email'] ?? '');
                                    echo $em ? '<i class="ri-mail-line me-1 text-muted"></i>' . e($em) : '-';
                                    break;
                                case 'phone':
                                    $ph = $val ?: ($c['mobile'] ?? '') ?: ($c['company_phone'] ?? '');
                                    echo $ph ? '<i class="ri-phone-line me-1 text-muted"></i>' . e($ph) : '-';
                                    break;
                                case 'mobile': case 'fax': case 'shipping_phone':
                                    echo $val ? '<i class="ri-phone-line me-1 text-muted"></i>' . e($val) : '-';
                                    break;
                                case 'company_id': case 'company_name':
                                    echo !empty($c['company_name']) ? e($c['company_name']) : '<span class="text-muted">-</span>';
                                    break;
                                case 'source_id':
                                    echo !empty($c['source_name']) ? '<span class="badge bg-secondary-subtle text-secondary">' . e($c['source_name']) . '</span>' : '<span class="text-muted">-</span>';
                                    break;
                                case 'status':
                                    $stColor = $sColors[$val] ?? 'secondary';
                                    echo '<span class="badge bg-' . $stColor . '-subtle text-' . $stColor . '">' . ($sLabels[$val] ?? $val) . '</span>';
                                    break;
                                case 'owner_id':
                                    echo user_avatar($c['owner_name'] ?? null, 'primary', $c['owner_avatar'] ?? null);
                                    break;
                                case 'gender':
                                    echo $genderLabels[$val] ?? '-';
                                    break;
                                case 'date_of_birth':
                                    echo $val ? date('d/m/Y', strtotime($val)) : '-';
                                    break;
                                case 'customer_group':
                                    echo ($val && isset($groupLabels[$val])) ? '<span class="badge bg-' . ($groupColors[$val] ?? 'secondary') . '-subtle text-' . ($groupColors[$val] ?? 'secondary') . '">' . $groupLabels[$val] . '</span>' : '<span class="text-muted">-</span>';
                                    break;
                                case 'website':
                                    echo $val ? '<a href="' . e($val) . '" target="_blank" class="text-truncate d-inline-block" style="max-width:120px">' . e($val) . '</a>' : '-';
                                    break;
                                case 'is_private':
                                    echo $val ? '<span class="badge bg-warning">Có</span>' : '-';
                                    break;
                                case 'created_at': case 'updated_at': case 'last_activity_at':
                                    echo $val ? '<span class="text-muted">' . time_ago($val) . '</span>' : '-';
                                    break;
                                default:
                                    echo e($val ?: '-');
                            endswitch;
                            ?>
                            </td>
                            <?php endforeach; ?>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
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
                            <td colspan="20" class="text-center py-5">
                                <div class="avatar-md mx-auto mb-3">
                                    <span class="avatar-title bg-primary-subtle rounded-circle">
                                        <i class="ri-contacts-line text-primary fs-24"></i>
                                    </span>
                                </div>
                                <h5 class="text-muted">Chưa có khách hàng nào</h5>
                                <a href="<?= url('contacts/create') ?>" class="btn btn-primary mt-2"><i class="ri-add-line me-1"></i> Thêm khách hàng</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Bottom Pagination -->
        <?php if (($contacts['total_pages'] ?? 0) > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
            <div class="text-muted fs-13">
                Hiển thị <strong><?= (($contacts['page'] - 1) * ($filters['per_page'] ?? 20)) + 1 ?> - <?= min($contacts['page'] * ($filters['per_page'] ?? 20), $contacts['total']) ?></strong> / <strong><?= number_format($contacts['total']) ?></strong> khách hàng
            </div>
            <nav>
                <?php
                $pg = $contacts['page'];
                $tp = $contacts['total_pages'];
                $qs = http_build_query(array_filter($filters ?? []));
                $pgUrl = function($p) use ($qs) { return url('contacts?page=' . $p . '&' . $qs); };

                // Build page numbers: 1 ... [pg-2, pg-1, pg, pg+1, pg+2] ... last
                $pages = [];
                $pages[] = 1;
                for ($i = max(2, $pg - 2); $i <= min($tp - 1, $pg + 2); $i++) {
                    $pages[] = $i;
                }
                if ($tp > 1) $pages[] = $tp;
                $pages = array_unique($pages);
                sort($pages);
                ?>
                <ul class="pagination mb-0">
                    <?php if ($pg > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= $pgUrl($pg - 1) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
                    <?php endif; ?>
                    <?php
                    $prev = 0;
                    foreach ($pages as $p):
                        if ($p - $prev > 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item <?= $p === $pg ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $pgUrl($p) ?>"><?= $p ?></a>
                        </li>
                    <?php $prev = $p; endforeach; ?>
                    <?php if ($pg < $tp): ?>
                        <li class="page-item"><a class="page-link" href="<?= $pgUrl($pg + 1) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
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

// Toggle column panel
document.getElementById('toggleColumnPanel')?.addEventListener('click', function() {
    var panel = document.getElementById('columnPanel');
    panel.classList.toggle('d-none');
    var isOpen = !panel.classList.contains('d-none');
    this.innerHTML = 'Hiển thị cột <i class="ri-arrow-' + (isOpen ? 'up' : 'down') + '-s-line ms-1"></i>';
});

// Column toggle
(function() {
    var STORAGE_KEY = 'torycrm_contacts_columns';
    var allColumns = <?= json_encode($colKeys) ?>;
    var defaultVisible = ['col-accountcode','col-fullname','col-email','col-phone','col-companyname','col-status','col-ownerid','col-customergroup','col-lastactivityat','col-createdat'];

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

    applyColumns(getVisible());

    document.querySelectorAll('.column-toggle').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var visible = [];
            document.querySelectorAll('.column-toggle:checked').forEach(function(c) { visible.push(c.dataset.column); });
            if (visible.length === 0) { this.checked = true; return; }
            localStorage.setItem(STORAGE_KEY, JSON.stringify(visible));
            applyColumns(visible);
        });
    });

    document.getElementById('resetColumns')?.addEventListener('click', function() {
        localStorage.removeItem(STORAGE_KEY);
        applyColumns(defaultVisible);
    });
})();

// Bulk actions config
window.__bulkConfig = {
    module: 'contacts',
    bulkUrl: '<?= url("contacts/bulk") ?>',
    csrfToken: '<?= $_SESSION["csrf_token"] ?? "" ?>',
    statuses: <?= json_encode($sLabels) ?>,
    users: <?= json_encode($users ?? []) ?>
};
</script>
<script src="<?= asset('js/inline-edit.js') ?>?v=<?= time() ?>"></script>
<script src="<?= asset('js/bulk-actions.js') ?>?v=<?= time() ?>"></script>
