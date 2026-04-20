<?php
$pageTitle = 'Hợp đồng';
$sc = ['pending' => 'warning', 'approved' => 'primary', 'renewed' => 'info', 'in_progress' => 'success', 'auto_renewed' => 'info', 'completed' => 'dark', 'cancelled' => 'danger'];
$sl = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'renewed' => 'Đã gia hạn', 'in_progress' => 'Đang thực hiện', 'auto_renewed' => 'Tự động gia hạn lần 1', 'completed' => 'Đã kết thúc', 'cancelled' => 'Đã hủy'];
$tc = ['Mới' => 'primary', 'Gia hạn' => 'info', 'Bổ sung' => 'warning', 'service' => 'primary', 'product' => 'success', 'rental' => 'warning', 'maintenance' => 'info', 'other' => 'secondary'];
$tl = ['Mới' => 'Mới', 'Gia hạn' => 'Gia hạn', 'Bổ sung' => 'Bổ sung'];
$colKeys = array_column($displayColumns ?? [], 'key');
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Hợp đồng</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-soft-secondary" id="toggleColumnPanel">Hiển thị cột <i class="ri-arrow-down-s-line ms-1"></i></button>
        <a href="<?= url('contracts/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo hợp đồng</a>
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
            <button type="button" class="btn btn-soft-secondary py-1 px-2" id="resetColumns"><i class="ri-refresh-line me-1"></i>Đặt lại</button>
        </div>
    </div>
</div>

<?php if (($stats['expiring_soon'] ?? 0) > 0): ?>
<div class="alert alert-warning alert-dismissible fade show mb-3">
    <i class="ri-alarm-warning-line me-2"></i> <strong><?= $stats['expiring_soon'] ?> hợp đồng sắp hết hạn</strong> trong 30 ngày tới.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php $currentStatus = $filters['status'] ?? ''; ?>
<div class="card">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('contracts') ?>" class="d-flex align-items-center gap-2 flex-wrap">
            <div class="search-box" style="min-width:160px;max-width:200px">
                <input type="text" class="form-control" name="search" placeholder="Tìm số HĐ, tên..." value="<?= e($filters['search'] ?? '') ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <select name="type" class="form-select" style="width:auto;min-width:100px" onchange="this.form.submit()">
                <option value="">Loại HĐ</option>
                <?php foreach ($tl as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($filters['type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" class="form-control" style="width:auto" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            <input type="date" class="form-control" style="width:auto" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i></button>
            <?php if (!empty(array_filter($filters ?? []))): ?>
            <a href="<?= url('contracts') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line"></i></a>
            <?php endif; ?>
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
        </form>
    </div>
    <div class="card-body py-2 px-3 d-flex align-items-center gap-1 border-top">
        <button type="button" class="btn btn-link text-muted p-0 px-1 flex-shrink-0 d-none" id="tabScrollLeft"><i class="ri-arrow-left-s-line fs-18"></i></button>
        <div class="flex-grow-1 d-flex" id="tabScrollContainer" style="overflow-x:auto;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;scrollbar-width:none;min-width:0">
        <style>#tabScrollContainer::-webkit-scrollbar{display:none}</style>
            <div class="d-flex gap-1 flex-nowrap" id="tabScrollInner">
                <a href="<?= url('contracts?' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>" class="btn <?= !$currentStatus ? 'btn-dark' : 'btn-soft-dark' ?> rounded-pill text-nowrap waves-effect">
                    Tất cả <span class="badge rounded-pill bg-danger ms-1"><?= $totalAll ?></span>
                </a>
                <?php foreach ($sl as $key => $label):
                    $count = (int)($stats[$key] ?? 0);
                    $color = $sc[$key] ?? 'secondary';
                    $isActive = $currentStatus === $key;
                ?>
                <a href="<?= url('contracts?status=' . $key . '&' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>"
                   class="btn <?= $isActive ? "btn-{$color}" : "btn-soft-{$color}" ?> rounded-pill text-nowrap waves-effect">
                    <?= $label ?> <span class="badge rounded-pill bg-danger ms-1"><?= $count ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="button" class="btn btn-link text-muted p-0 px-1 flex-shrink-0 d-none" id="tabScrollRight"><i class="ri-arrow-right-s-line fs-18"></i></button>
    </div>
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <?php foreach ($displayColumns as $dc): ?>
                        <th class="<?= $dc['key'] ?>"><?= e($dc['label']) ?></th>
                        <?php endforeach; ?>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contracts['items'])): ?>
                        <?php foreach ($contracts['items'] as $ct):
                            $contactName = $ct['c_company_name'] ?? ($ct['c_full_name'] ?? trim(($ct['contact_first_name'] ?? '') . ' ' . ($ct['contact_last_name'] ?? '')));
                        ?>
                        <tr>
                            <?php foreach ($displayColumns as $dc):
                                $field = $dc['field'];
                                $key = $dc['key'];
                                $val = $ct[$field] ?? '';
                            ?>
                            <td class="<?= $key ?>">
                            <?php switch ($field):
                                case 'contract_number': ?>
                                    <a href="<?= url('contracts/' . $ct['id']) ?>" class="fw-medium"><?= e($val) ?></a>
                                <?php break; case 'title': ?>
                                    <?= e($val) ?>
                                <?php break; case 'status': ?>
                                    <span class="badge bg-<?= $sc[$val] ?? 'secondary' ?>"><?= $sl[$val] ?? $val ?></span>
                                <?php break; case 'type': ?>
                                    <span class="badge bg-<?= $tc[$val] ?? 'secondary' ?>-subtle text-<?= $tc[$val] ?? 'secondary' ?>"><?= $tl[$val] ?? $val ?></span>
                                <?php break; case 'contact_id': ?>
                                    <?php if ($contactName): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($ct['contact_avatar'] ?? null): ?><img src="<?= asset($ct['contact_avatar']) ?>" class="rounded-circle" width="24" height="24" style="object-fit:cover">
                                        <?php else: ?><span class="rounded-circle bg-info text-white d-inline-flex align-items-center justify-content-center" style="width:24px;height:24px;font-size:10px"><?= mb_strtoupper(mb_substr($contactName, 0, 1)) ?></span><?php endif; ?>
                                        <div><?= e($contactName) ?></div>
                                    </div>
                                    <?php else: ?>-<?php endif; ?>
                                <?php break; case 'company_id': ?>
                                    <?= !empty($ct['company_name']) ? e($ct['company_name']) : '-' ?>
                                <?php break; case 'owner_id': ?>
                                    <?php if (!empty($ct['owner_name'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if ($ct['owner_avatar'] ?? null): ?><img src="<?= asset($ct['owner_avatar']) ?>" class="rounded-circle" width="24" height="24" style="object-fit:cover">
                                        <?php else: ?><span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:24px;height:24px;font-size:10px"><?= mb_strtoupper(mb_substr($ct['owner_name'], 0, 1)) ?></span><?php endif; ?>
                                        <?= e($ct['owner_name']) ?>
                                    </div>
                                    <?php else: ?>-<?php endif; ?>
                                <?php break; case 'value': case 'subtotal': case 'discount_amount': case 'shipping_fee': case 'installation_fee': case 'tax_amount': case 'actual_value': case 'executed_amount': case 'paid_amount': ?>
                                    <?= format_money($val) ?>
                                <?php break; case 'start_date': case 'end_date': case 'signed_date': ?>
                                    <?= $val ? format_date($val) : '-' ?>
                                <?php break; case 'created_at': ?>
                                    <?= $val ? date('d/m/Y', strtotime($val)) : '-' ?>
                                <?php break; default: ?>
                                    <?= e($val ?: '-') ?>
                                <?php break; endswitch; ?>
                            </td>
                            <?php endforeach; ?>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-soft-secondary btn-sm" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?= url('contracts/' . $ct['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                        <li><a class="dropdown-item" href="<?= url('contracts/' . $ct['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="<?= url('contracts/' . $ct['id'] . '/delete') ?>" data-confirm="Xóa hợp đồng này?">
                                                <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?= count($displayColumns) + 1 ?>" class="text-center py-4 text-muted"><i class="ri-file-shield-2-line fs-1 d-block mb-2"></i>Chưa có hợp đồng</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($contracts['total_pages'] ?? 0) > 1): ?>
        <div class="d-flex justify-content-between align-items-center p-3">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted">Hiển thị <?= count($contracts['items']) ?> / <?= $contracts['total'] ?></span>
                <?php $currentPerPage = $filters['per_page'] ?? 20; include __DIR__ . '/../components/per-page-select.php'; ?>
            </div>
            <nav><ul class="pagination mb-0">
                <?php
                $curPage = $contracts['page'];
                $totalPages = $contracts['total_pages'];
                $qs = http_build_query(array_filter($filters ?? []));
                $pageUrl = function($p) use ($qs) { return url('contracts?page=' . $p . ($qs ? '&' . $qs : '')); };
                if ($curPage > 1): ?><li class="page-item"><a class="page-link" href="<?= $pageUrl($curPage - 1) ?>"><i class="ri-arrow-left-s-line"></i></a></li><?php endif;
                if ($curPage > 3): ?><li class="page-item"><a class="page-link" href="<?= $pageUrl(1) ?>">1</a></li><?php if ($curPage > 4): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; endif;
                for ($i = max(1, $curPage - 2); $i <= min($totalPages, $curPage + 2); $i++): ?>
                    <li class="page-item <?= $i === $curPage ? 'active' : '' ?>"><a class="page-link" href="<?= $pageUrl($i) ?>"><?= $i ?></a></li>
                <?php endfor;
                if ($curPage < $totalPages - 2): ?><?php if ($curPage < $totalPages - 3): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?><li class="page-item"><a class="page-link" href="<?= $pageUrl($totalPages) ?>"><?= $totalPages ?></a></li><?php endif;
                if ($curPage < $totalPages): ?><li class="page-item"><a class="page-link" href="<?= $pageUrl($curPage + 1) ?>"><i class="ri-arrow-right-s-line"></i></a></li><?php endif; ?>
            </ul></nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('toggleColumnPanel')?.addEventListener('click', function() {
    var panel = document.getElementById('columnPanel');
    panel.classList.toggle('d-none');
    var isOpen = !panel.classList.contains('d-none');
    this.innerHTML = 'Hiển thị cột <i class="ri-arrow-' + (isOpen ? 'up' : 'down') + '-s-line ms-1"></i>';
});

(function() {
    var STORAGE_KEY = 'torycrm_contracts_columns';
    var allColumns = <?= json_encode($colKeys) ?>;
    var defaultVisible = ['col-contractnumber','col-title','col-contactid','col-type','col-value','col-startdate','col-enddate','col-status'];

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
            if (this.checked) { if (!visible.includes(this.dataset.column)) visible.push(this.dataset.column); }
            else { visible = visible.filter(function(c) { return c !== cb.dataset.column; }); }
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
