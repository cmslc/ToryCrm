<?php
$pageTitle = 'Báo giá';
$currentStatus = $filters['status'] ?? '';
$qsc = ['pending'=>'warning','approved'=>'primary','has_order'=>'success','no_order'=>'info','deleted'=>'danger'];
$qsl = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','has_order'=>'Đã tạo ĐH','no_order'=>'Chưa tạo ĐH','deleted'=>'Đã xóa'];
$sc = ['draft'=>'secondary','sent'=>'info','accepted'=>'success','rejected'=>'danger','expired'=>'warning','converted'=>'primary'];
$sl = ['draft'=>'Nháp','sent'=>'Đã gửi','accepted'=>'Chấp nhận','rejected'=>'Từ chối','expired'=>'Hết hạn','converted'=>'Đã chuyển ĐH'];
$colKeys = array_column($displayColumns ?? [], 'key');
$totalAll = 0;
foreach ($stats as $v) $totalAll += (int)$v;
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo giá</h4>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-soft-secondary" id="toggleColumnPanel">Hiển thị cột <i class="ri-arrow-down-s-line ms-1"></i></button>
                <a href="<?= url('quotations/export?format=csv') ?>" class="btn btn-soft-info"><i class="ri-download-line me-1"></i> Export</a>
                <a href="<?= url('quotations/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo báo giá</a>
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

        <div class="card mb-3">
            <div class="card-header p-2">
                <form method="GET" action="<?= url('quotations') ?>" class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="search-box" style="min-width:160px;max-width:200px">
                        <input type="text" class="form-control" name="search" placeholder="Tìm mã BG, KH..." value="<?= e($filters['search'] ?? '') ?>">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                    <input type="hidden" name="contact_id" value="<?= e($filters['contact_id'] ?? '') ?>">
                    <input type="hidden" name="owner_id" id="qOwnerIdInput" value="<?= e($filters['owner_id'] ?? '') ?>">
                    <div class="position-relative" id="qOwnerDropdown">
                        <div class="form-select d-flex align-items-center gap-2" style="cursor:pointer;width:auto;white-space:nowrap" id="qOwnerBtn">
                            <?php
                            $selectedOwner = null;
                            foreach ($users ?? [] as $u) { if (($filters['owner_id'] ?? '') == $u['id']) $selectedOwner = $u; }
                            ?>
                            <?php if ($selectedOwner): ?>
                                <?php if (!empty($selectedOwner['avatar'])): ?>
                                <img src="<?= asset($selectedOwner['avatar']) ?>" class="rounded-circle" width="20" height="20" style="object-fit:cover">
                                <?php else: ?>
                                <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:10px"><?= mb_substr($selectedOwner['name'], 0, 1) ?></span>
                                <?php endif; ?>
                                <span><?= e($selectedOwner['name']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Người phụ trách</span>
                            <?php endif; ?>
                        </div>
                        <div class="border rounded bg-white shadow" id="qOwnerList" style="position:absolute;z-index:1060;min-width:220px;display:none;top:100%;left:0;margin-top:2px;max-height:280px;overflow-y:auto">
                            <div class="q-owner-opt px-3 py-2 text-primary fw-medium" style="cursor:pointer" data-id="">Tất cả</div>
                            <?php foreach ($users ?? [] as $u): ?>
                            <div class="q-owner-opt d-flex align-items-center gap-2 px-3 py-2 <?= ($filters['owner_id'] ?? '') == $u['id'] ? 'bg-primary bg-opacity-10' : '' ?>" style="cursor:pointer" data-id="<?= $u['id'] ?>">
                                <?php if (!empty($u['avatar'])): ?>
                                <img src="<?= asset($u['avatar']) ?>" class="rounded-circle" width="24" height="24" style="object-fit:cover">
                                <?php else: ?>
                                <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:24px;height:24px;font-size:11px"><?= mb_substr($u['name'], 0, 1) ?></span>
                                <?php endif; ?>
                                <span style="font-size:13px"><?= e($u['name']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php $dp = $filters['date_period'] ?? ''; ?>
                    <select name="date_period" class="form-select" style="width:auto;min-width:140px" onchange="if(this.value==='custom'){document.getElementById('qCustomDate').classList.remove('d-none')}else{this.form.submit()}">
                        <option value="">Thời gian</option>
                        <option value="today" <?= $dp === 'today' ? 'selected' : '' ?>>Hôm nay</option>
                        <option value="yesterday" <?= $dp === 'yesterday' ? 'selected' : '' ?>>Hôm qua</option>
                        <option value="this_week" <?= $dp === 'this_week' ? 'selected' : '' ?>>Tuần này</option>
                        <option value="this_month" <?= $dp === 'this_month' ? 'selected' : '' ?>>Tháng này</option>
                        <option value="last_month" <?= $dp === 'last_month' ? 'selected' : '' ?>>Tháng trước</option>
                        <option value="this_year" <?= $dp === 'this_year' ? 'selected' : '' ?>>Năm nay</option>
                        <option value="custom" <?= $dp === 'custom' ? 'selected' : '' ?>>Thời gian khác</option>
                    </select>
                    <div id="qCustomDate" class="d-flex gap-1 <?= $dp === 'custom' ? '' : 'd-none' ?>">
                        <input type="date" name="date_from" class="form-control" style="width:auto" value="<?= e($filters['date_from'] ?? '') ?>">
                        <input type="date" name="date_to" class="form-control" style="width:auto" value="<?= e($filters['date_to'] ?? '') ?>">
                    </div>
                    <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                    <?php if (!empty(array_filter($filters ?? []))): ?>
                        <a href="<?= url('quotations') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
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
                        <a href="<?= url('quotations?' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>" class="btn <?= !$currentStatus ? 'btn-dark' : 'btn-soft-dark' ?> rounded-pill text-nowrap waves-effect">
                            Tất cả <span class="badge rounded-pill bg-danger ms-1"><?= number_format($totalAll) ?></span>
                        </a>
                        <?php foreach ($qsl as $key => $label):
                            $count = (int)($stats[$key] ?? 0);
                            $color = $qsc[$key] ?? 'secondary';
                            $isActive = $currentStatus === $key;
                        ?>
                        <a href="<?= url('quotations?status=' . $key . '&' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>"
                           class="btn <?= $isActive ? "btn-{$color}" : "btn-soft-{$color}" ?> rounded-pill text-nowrap waves-effect">
                            <?= $label ?> <span class="badge rounded-pill bg-danger ms-1"><?= number_format($count) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="button" class="btn btn-link text-muted p-0 px-1 flex-shrink-0 d-none" id="tabScrollRight"><i class="ri-arrow-right-s-line fs-18"></i></button>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <?php foreach ($displayColumns as $dc): ?>
                                <th class="<?= $dc['key'] ?>"><?= e($dc['label']) ?></th>
                                <?php endforeach; ?>
                                <th>PDF</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($quotations['items'])): ?>
                                <?php foreach ($quotations['items'] as $q): ?>
                                    <tr>
                                        <?php foreach ($displayColumns as $dc):
                                            $field = $dc['field'];
                                            $key = $dc['key'];
                                            $val = $q[$field] ?? '';
                                        ?>
                                        <td class="<?= $key ?>">
                                        <?php switch ($field):
                                            case 'quote_number': ?>
                                                <a href="<?= url('quotations/' . $q['id']) ?>" class="fw-medium"><?= e($val) ?></a>
                                            <?php break; case 'contact_id': ?>
                                                <?php $cName = trim(($q['contact_first_name'] ?? '') . ' ' . ($q['contact_last_name'] ?? '')); ?>
                                                <?php if ($cName): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($q['contact_avatar']) && file_exists(BASE_PATH . '/public/' . $q['contact_avatar'])): ?>
                                                        <img src="<?= url($q['contact_avatar']) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
                                                    <?php else: ?>
                                                        <span class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px"><?= strtoupper(mb_substr($cName, 0, 1)) ?></span>
                                                    <?php endif; ?>
                                                    <?= e($cName) ?>
                                                </div>
                                                <?php else: ?>-<?php endif; ?>
                                            <?php break; case 'company_id': ?>
                                                <?= !empty($q['company_name']) ? e($q['company_name']) : '-' ?>
                                            <?php break; case 'total': case 'subtotal': case 'tax_amount': case 'discount_amount': ?>
                                                <?= ($val + 0) > 0 ? format_money($val) : '-' ?>
                                            <?php break; case 'valid_until': ?>
                                                <?php if ($val): $isExpired = $val < date('Y-m-d'); ?>
                                                    <span class="<?= $isExpired ? 'text-danger' : 'text-success' ?>"><?= format_date($val) ?></span>
                                                <?php else: echo '-'; endif; ?>
                                            <?php break; case 'status': ?>
                                                <span class="badge bg-<?= $sc[$val] ?? 'secondary' ?>"><?= $sl[$val] ?? $val ?></span>
                                            <?php break; case 'view_count': ?>
                                                <i class="ri-eye-line me-1"></i><?= (int)$val ?>
                                            <?php break; case 'owner_id': ?>
                                                <?php if (!empty($q['owner_name'])): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($q['owner_avatar']) && file_exists(BASE_PATH . '/public/' . $q['owner_avatar'])): ?>
                                                        <img src="<?= url($q['owner_avatar']) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
                                                    <?php else: ?>
                                                        <span class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px"><?= strtoupper(mb_substr($q['owner_name'], 0, 1)) ?></span>
                                                    <?php endif; ?>
                                                    <?= e($q['owner_name']) ?>
                                                </div>
                                                <?php else: ?>-<?php endif; ?>
                                            <?php break; case 'deal_id': ?>
                                                <?= $val ? e($q['deal_title'] ?? $val) : '-' ?>
                                            <?php break; case 'created_by': ?>
                                                <?php if (!empty($q['creator_name'])): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($q['creator_avatar']) && file_exists(BASE_PATH . '/public/' . $q['creator_avatar'])): ?>
                                                        <img src="<?= url($q['creator_avatar']) ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover">
                                                    <?php else: ?>
                                                        <span class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:13px"><?= strtoupper(mb_substr($q['creator_name'], 0, 1)) ?></span>
                                                    <?php endif; ?>
                                                    <?= e($q['creator_name']) ?>
                                                </div>
                                                <?php else: ?>-<?php endif; ?>
                                            <?php break; case 'created_at': case 'updated_at': case 'accepted_at': case 'rejected_at': case 'last_viewed_at': ?>
                                                <?= $val ? time_ago($val) : '-' ?>
                                            <?php break; default: ?>
                                                <?= e($val ?: '-') ?>
                                        <?php endswitch; ?>
                                        </td>
                                        <?php endforeach; ?>
                                        <td><a href="<?= url('quotations/' . $q['id'] . '/pdf') ?>" target="_blank" class="btn btn-soft-danger btn-icon" title="PDF"><i class="ri-file-pdf-2-line"></i></a></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('quotations/' . $q['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('quotations/' . $q['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <?php if ($q['status'] === 'draft'): ?>
                                                    <li>
                                                        <form method="POST" action="<?= url('quotations/' . $q['id'] . '/send') ?>" data-confirm="Gửi báo giá này?">
                                                            <?= csrf_field() ?><button class="dropdown-item"><i class="ri-send-plane-line me-2"></i>Gửi</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if (in_array($q['status'], ['accepted', 'sent'])): ?>
                                                    <li>
                                                        <form method="POST" action="<?= url('quotations/' . $q['id'] . '/convert') ?>" data-confirm="Chuyển thành đơn hàng?">
                                                            <?= csrf_field() ?><button class="dropdown-item"><i class="ri-swap-line me-2"></i>Chuyển đơn hàng</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('quotations/' . $q['id'] . '/delete') ?>" data-confirm="Xác nhận xóa báo giá?">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="<?= count($displayColumns) + 2 ?>" class="text-center py-4 text-muted"><i class="ri-file-text-line fs-1 d-block mb-2"></i>Chưa có báo giá</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($quotations['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted fs-13">Hiển thị <strong><?= (($quotations['page'] - 1) * ($filters['per_page'] ?? 20)) + 1 ?> - <?= min($quotations['page'] * ($filters['per_page'] ?? 20), $quotations['total']) ?></strong> / <strong><?= number_format($quotations['total']) ?></strong></span>
                            <?php $currentPerPage = $filters['per_page'] ?? 20; include __DIR__ . '/../components/per-page-select.php'; ?>
                        </div>
                        <?php
                        $pg = $quotations['page'];
                        $tp = $quotations['total_pages'];
                        $qs = http_build_query(array_filter($filters ?? []));
                        $pgUrl = function($p) use ($qs) { return url('quotations?page=' . $p . '&' . $qs); };
                        $pages = [1];
                        for ($i = max(2, $pg - 2); $i <= min($tp - 1, $pg + 2); $i++) $pages[] = $i;
                        if ($tp > 1) $pages[] = $tp;
                        $pages = array_unique($pages); sort($pages);
                        ?>
                        <nav><ul class="pagination mb-0">
                            <?php if ($pg > 1): ?>
                            <li class="page-item"><a class="page-link" href="<?= $pgUrl($pg - 1) ?>"><i class="ri-arrow-left-s-line"></i></a></li>
                            <?php endif; ?>
                            <?php $prev = 0; foreach ($pages as $p):
                                if ($p - $prev > 1): ?><li class="page-item disabled"><span class="page-link">...</span></li><?php endif; ?>
                                <li class="page-item <?= $p === $pg ? 'active' : '' ?>"><a class="page-link" href="<?= $pgUrl($p) ?>"><?= $p ?></a></li>
                            <?php $prev = $p; endforeach; ?>
                            <?php if ($pg < $tp): ?>
                            <li class="page-item"><a class="page-link" href="<?= $pgUrl($pg + 1) ?>"><i class="ri-arrow-right-s-line"></i></a></li>
                            <?php endif; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<script>
// Owner dropdown with avatars
(function(){
    var btn = document.getElementById('qOwnerBtn');
    var list = document.getElementById('qOwnerList');
    if (!btn || !list) return;
    btn.addEventListener('click', function(e) { e.stopPropagation(); list.style.display = list.style.display === 'none' ? 'block' : 'none'; });
    document.addEventListener('click', function(e) { if (!document.getElementById('qOwnerDropdown').contains(e.target)) list.style.display = 'none'; });
    list.querySelectorAll('.q-owner-opt').forEach(function(opt) {
        opt.addEventListener('mouseenter', function() { this.style.backgroundColor = '#f3f6f9'; });
        opt.addEventListener('mouseleave', function() { this.style.backgroundColor = ''; });
        opt.addEventListener('click', function() {
            document.getElementById('qOwnerIdInput').value = this.dataset.id;
            this.closest('form').submit();
        });
    });
})();

document.getElementById('toggleColumnPanel')?.addEventListener('click', function() {
    var panel = document.getElementById('columnPanel');
    panel.classList.toggle('d-none');
    var isOpen = !panel.classList.contains('d-none');
    this.innerHTML = 'Hiển thị cột <i class="ri-arrow-' + (isOpen ? 'up' : 'down') + '-s-line ms-1"></i>';
});

(function() {
    var STORAGE_KEY = 'torycrm_quotations_columns';
    var allColumns = <?= json_encode($colKeys) ?>;
    var defaultVisible = ['col-quotenumber','col-contactid','col-companyid','col-total','col-validuntil','col-status','col-viewcount','col-createdby'];

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
