<?php
$pageTitle = 'Hợp đồng';
$sc = ['draft' => 'secondary', 'sent' => 'info', 'signed' => 'primary', 'active' => 'success', 'expired' => 'danger', 'cancelled' => 'dark'];
$sl = ['draft' => 'Nháp', 'sent' => 'Đã gửi', 'signed' => 'Đã ký', 'active' => 'Hoạt động', 'expired' => 'Hết hạn', 'cancelled' => 'Đã hủy'];
$tc = ['service' => 'primary', 'product' => 'success', 'rental' => 'warning', 'maintenance' => 'info', 'other' => 'secondary'];
$tl = ['service' => 'Dịch vụ', 'product' => 'Sản phẩm', 'rental' => 'Cho thuê', 'maintenance' => 'Bảo trì', 'other' => 'Khác'];
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
                <input class="form-check-input column-toggle" type="checkbox" id="col-<?= $dc['key'] ?>" data-column="<?= $dc['key'] ?>" checked>
                <label class="form-check-label" for="col-<?= $dc['key'] ?>"><?= e($dc['label']) ?></label>
            </div>
            <?php endforeach; ?>
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
        <div class="flex-grow-1 d-flex" style="overflow-x:auto;scrollbar-width:none">
            <div class="d-flex gap-1 flex-nowrap">
                <a href="<?= url('contracts?' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>" class="btn <?= !$currentStatus ? 'btn-dark' : 'btn-soft-dark' ?> btn-label right rounded-pill text-nowrap waves-effect">
                    Tất cả <span class="label-icon align-middle rounded-pill fs-12 ms-2"><?= $totalAll ?></span>
                </a>
                <?php foreach ($sl as $key => $label):
                    $count = (int)($stats[$key] ?? 0);
                    $color = $sc[$key] ?? 'secondary';
                    $isActive = $currentStatus === $key;
                ?>
                <a href="<?= url('contracts?status=' . $key . '&' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>"
                   class="btn <?= $isActive ? "btn-{$color}" : "btn-soft-{$color}" ?> btn-label right rounded-pill text-nowrap waves-effect">
                    <?= $label ?> <span class="label-icon align-middle rounded-pill fs-12 ms-2"><?= $count ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
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
                            $contactName = trim(($ct['contact_first_name'] ?? '') . ' ' . ($ct['contact_last_name'] ?? ''));
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
                                    <?= $contactName ? e($contactName) : '-' ?>
                                    <?php if (!empty($ct['company_name'])): ?><br><small class="text-muted"><?= e($ct['company_name']) ?></small><?php endif; ?>
                                <?php break; case 'company_id': ?>
                                    <?= !empty($ct['company_name']) ? e($ct['company_name']) : '-' ?>
                                <?php break; case 'owner_id': ?>
                                    <?= !empty($ct['owner_name']) ? e($ct['owner_name']) : '-' ?>
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
            <div class="text-muted">Hiển thị <?= count($contracts['items']) ?> / <?= $contracts['total'] ?></div>
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
    document.getElementById('columnPanel').classList.toggle('d-none');
});
document.querySelectorAll('.column-toggle').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var col = this.dataset.column;
        var show = this.checked;
        document.querySelectorAll('.' + col).forEach(function(el) { el.style.display = show ? '' : 'none'; });
    });
});
</script>
