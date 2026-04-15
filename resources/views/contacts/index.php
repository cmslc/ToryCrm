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
$columns = [
    'col-customer' => 'Khách hàng',
    'col-code' => 'Mã KH',
    'col-contact' => 'Liên hệ',
    'col-mobile' => 'Di động',
    'col-company' => 'Công ty',
    'col-position' => 'Chức vụ',
    'col-source' => 'Nguồn',
    'col-status' => 'Trạng thái',
    'col-owner' => 'Phụ trách',
    'col-gender' => 'Giới tính',
    'col-address' => 'Địa chỉ',
    'col-birthday' => 'Ngày sinh',
    'col-group' => 'Nhóm KH',
    'col-taxcode' => 'MST',
    'col-website' => 'Website',
    'col-fax' => 'Fax',
    'col-referrer' => 'Người giới thiệu',
    'col-tags' => 'Nhãn',
    'col-lastcontact' => 'Liên hệ lần cuối',
    'col-created' => 'Ngày tạo',
];
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

<!-- Column Options Panel (WordPress-style) -->
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
                <hr class="my-2">
                <h6 class="mb-2">Chế độ xem</h6>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="split-view-check">
                    <label class="form-check-label" for="split-view-check">Xem nhanh (bấm vào dòng để xem chi tiết bên phải)</label>
                </div>
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
                <option value="du_an" <?= ($filters['customer_group'] ?? '') === 'du_an' ? 'selected' : '' ?>>Khách dự án</option>
                <option value="le" <?= ($filters['customer_group'] ?? '') === 'le' ? 'selected' : '' ?>>Khách lẻ</option>
                <option value="dai_ly" <?= ($filters['customer_group'] ?? '') === 'dai_ly' ? 'selected' : '' ?>>Khách đại lý</option>
                <option value="doanh_nghiep" <?= ($filters['customer_group'] ?? '') === 'doanh_nghiep' ? 'selected' : '' ?>>Doanh nghiệp</option>
                <option value="vip" <?= ($filters['customer_group'] ?? '') === 'vip' ? 'selected' : '' ?>>VIP</option>
            </select>
            <input type="hidden" name="status" id="statusInput" value="<?= e($currentStatus) ?>">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
            <?php if (!empty(array_filter($filters ?? []))): ?>
                <a href="<?= url('contacts') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body py-2 px-3 d-flex align-items-center gap-1 border-top">
    <button type="button" class="btn btn-link text-muted p-0 px-1 flex-shrink-0 d-none" id="tabScrollLeft"><i class="ri-arrow-left-s-line fs-18"></i></button>
    <div class="flex-grow-1 d-flex" id="tabScrollContainer" style="overflow-x:auto;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;scrollbar-width:none;min-width:0">
    <style>#tabScrollContainer::-webkit-scrollbar{display:none}</style>
        <div class="d-flex gap-1 flex-nowrap" id="tabScrollInner">
            <a href="<?= url('contacts') ?>" class="btn <?= !$currentStatus ? 'btn-dark' : 'btn-soft-dark' ?> btn-label right rounded-pill text-nowrap waves-effect">
                Tất cả <span class="label-icon align-middle rounded-pill fs-12 ms-2"><?= number_format($totalAll) ?></span>
            </a>
            <a href="<?= url('contacts?status=today') ?>" class="btn <?= $currentStatus === 'today' ? 'btn-success' : 'btn-soft-success' ?> btn-label right rounded-pill text-nowrap waves-effect">
                Mới cập nhật <span class="label-icon align-middle rounded-pill fs-12 ms-2"><?= $todayCount ?? 0 ?></span>
            </a>
            <?php foreach ($sLabels as $key => $label):
                $count = 0;
                foreach ($statusCounts ?? [] as $sc) { if ($sc['status'] === $key) $count = $sc['count']; }
                $color = $sColors[$key] ?? 'secondary';
                $isActive = $currentStatus === $key;
            ?>
            <a href="<?= url('contacts?status=' . $key . '&' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>"
               class="btn <?= $isActive ? "btn-{$color}" : "btn-soft-{$color}" ?> btn-label right rounded-pill text-nowrap waves-effect">
                <?= $label ?> <span class="label-icon align-middle rounded-pill fs-12 ms-2"><?= number_format($count) ?></span>
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
                        <th class="col-customer">Khách hàng</th>
                        <th class="col-code">Mã KH</th>
                        <th class="col-contact">Liên hệ</th>
                        <th class="col-mobile">Di động</th>
                        <th class="col-company">Công ty</th>
                        <th class="col-position">Chức vụ</th>
                        <th class="col-source">Nguồn</th>
                        <th class="col-status">Trạng thái</th>
                        <th class="col-owner">Người phụ trách</th>
                        <th class="col-gender">Giới tính</th>
                        <th class="col-address">Địa chỉ</th>
                        <th class="col-birthday">Ngày sinh</th>
                        <th class="col-group">Nhóm KH</th>
                        <th class="col-taxcode">MST</th>
                        <th class="col-website">Website</th>
                        <th class="col-fax">Fax</th>
                        <th class="col-referrer">Người GT</th>
                        <th class="col-tags">Nhãn</th>
                        <th class="col-lastcontact">Liên hệ lần cuối</th>
                        <th class="col-created">Ngày tạo</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contacts['items'])): ?>
                        <?php foreach ($contacts['items'] as $c): ?>
                        <tr data-id="<?= $c['id'] ?>">
                            <td class="ps-3"><input type="checkbox" class="form-check-input row-check" value="<?= $c['id'] ?>"></td>
                            <td class="col-customer">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs flex-shrink-0 me-2">
                                        <?php if (!empty($c['avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $c['avatar'])): ?>
                                            <img src="<?= url('uploads/avatars/' . $c['avatar']) ?>" class="rounded-circle object-fit-cover" style="width:100%;height:100%">
                                        <?php else: ?>
                                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-13"><?= strtoupper(substr($c['first_name'], 0, 1)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="<?= url('contacts/' . $c['id']) ?>" class="fw-medium text-dark"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></a>
                                        <?php if ($c['position']): ?>
                                            <div class="text-muted fs-12"><?= e($c['position']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="col-code"><code class="fs-12"><?= e($c['account_code'] ?? '') ?: '-' ?></code></td>
                            <td class="col-contact">
                                <?php if ($c['email']): ?><div class="fs-12"><i class="ri-mail-line me-1 text-muted"></i><?= e($c['email']) ?></div><?php endif; ?>
                                <?php if ($c['phone']): ?><div class="fs-12"><i class="ri-phone-line me-1 text-muted"></i><?= e($c['phone']) ?></div><?php endif; ?>
                            </td>
                            <td class="col-mobile fs-12"><?= e($c['mobile'] ?? '') ?: '-' ?></td>
                            <td class="col-company">
                                <?php if ($c['company_id']): ?>
                                    <a href="<?= url('companies/' . $c['company_id']) ?>" class="text-body"><?= e($c['company_name']) ?></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-position fs-12 text-muted"><?= e($c['position'] ?? '') ?: '-' ?></td>
                            <td class="col-source">
                                <?php if (!empty($c['source_name'])): ?>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= e($c['source_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-status">
                                <span data-inline-edit data-url="<?= url('contacts/' . $c['id'] . '/quick-update') ?>" data-field="status" data-type="select"
                                      data-options='<?= json_encode($sLabels) ?>' data-value="<?= e($c['status']) ?>">
                                    <span class="badge bg-<?= $sColors[$c['status']] ?? 'secondary' ?>-subtle text-<?= $sColors[$c['status']] ?? 'secondary' ?>">
                                        <?= $sLabels[$c['status']] ?? $c['status'] ?>
                                    </span>
                                </span>
                            </td>
                            <td class="col-owner">
                                <span data-inline-edit data-url="<?= url('contacts/' . $c['id'] . '/quick-update') ?>" data-field="owner_id" data-type="user"
                                      data-value="<?= e($c['owner_id'] ?? '') ?>">
                                    <?= user_avatar($c['owner_name'] ?? null, 'primary', $c['owner_avatar'] ?? null) ?>
                                </span>
                            </td>
                            <td class="col-gender fs-12"><?php $gl=['male'=>'Nam','female'=>'Nữ','other'=>'Khác']; echo $gl[$c['gender'] ?? ''] ?? '-'; ?></td>
                            <td class="col-address fs-12 text-muted"><?= e($c['address'] ?? '-') ?></td>
                            <td class="col-birthday fs-12"><?= !empty($c['date_of_birth']) ? date('d/m/Y', strtotime($c['date_of_birth'])) : '-' ?></td>
                            <td class="col-group">
                                <?php
                                $groupLabels = ['du_an'=>'Khách dự án','le'=>'Khách lẻ','dai_ly'=>'Khách đại lý','doanh_nghiep'=>'Doanh nghiệp','vip'=>'VIP'];
                                $groupColors = ['du_an'=>'info','le'=>'secondary','dai_ly'=>'warning','doanh_nghiep'=>'primary','vip'=>'danger'];
                                $grp = $c['customer_group'] ?? '';
                                ?>
                                <?php if ($grp && isset($groupLabels[$grp])): ?>
                                    <span class="badge bg-<?= $groupColors[$grp] ?>-subtle text-<?= $groupColors[$grp] ?>"><?= $groupLabels[$grp] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-taxcode fs-12 text-muted"><?= e($c['tax_code'] ?? '-') ?></td>
                            <td class="col-website fs-12"><?= !empty($c['website']) ? '<a href="' . e($c['website']) . '" target="_blank" class="text-truncate d-inline-block" style="max-width:120px">' . e($c['website']) . '</a>' : '-' ?></td>
                            <td class="col-fax fs-12 text-muted"><?= e($c['fax'] ?? '') ?: '-' ?></td>
                            <td class="col-referrer fs-12 text-muted"><?= e($c['referrer_code'] ?? '') ?: '-' ?></td>
                            <td class="col-tags">
                                <?php
                                $cTags = \Core\Database::fetchAll(
                                    "SELECT t.name, t.color FROM taggables tg JOIN tags t ON tg.tag_id = t.id WHERE tg.entity_type = 'contact' AND tg.entity_id = ?",
                                    [$c['id']]
                                );
                                ?>
                                <?php if (!empty($cTags)): ?>
                                    <?php foreach ($cTags as $cTag): ?>
                                        <span class="badge me-1" style="background-color:<?= e($cTag['color']) ?>"><?= e($cTag['name']) ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-lastcontact text-muted fs-12"><?= !empty($c['last_activity_at']) ? time_ago($c['last_activity_at']) : '-' ?></td>
                            <td class="col-created text-muted fs-12"><?= time_ago($c['created_at']) ?></td>
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
                Hiển thị <strong><?= (($contacts['page'] - 1) * 20) + 1 ?> - <?= min($contacts['page'] * 20, $contacts['total']) ?></strong> / <strong><?= number_format($contacts['total']) ?></strong> khách hàng
            </div>
            <nav>
                <ul class="pagination mb-0">
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
    var allColumns = ['col-customer','col-code','col-contact','col-mobile','col-company','col-position','col-source','col-status','col-owner','col-gender','col-address','col-birthday','col-group','col-taxcode','col-website','col-fax','col-referrer','col-tags','col-lastcontact','col-created'];
    var defaultVisible = ['col-customer','col-contact','col-company','col-status','col-owner','col-lastcontact','col-created'];

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
