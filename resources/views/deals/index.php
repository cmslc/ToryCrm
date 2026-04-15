<?php
$pageTitle = 'Cơ hội kinh doanh';
$defaultVisible = ['col-title', 'col-value', 'col-stageid', 'col-contactid', 'col-companyid', 'col-priority', 'col-expectedclosedate', 'col-ownerid'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Cơ hội kinh doanh</h4>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-soft-secondary" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Hiển thị cột">
                        <i class="ri-layout-column-line me-1"></i> Cột
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:200px">
                        <h6 class="dropdown-header px-0">Hiển thị cột</h6>
                        <?php foreach ($displayColumns ?? [] as $col): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input column-toggle" type="checkbox" id="<?= $col['key'] ?>" data-column="<?= $col['key'] ?>" checked>
                            <label class="form-check-label" for="<?= $col['key'] ?>"><?= e($col['label']) ?></label>
                        </div>
                        <?php endforeach; ?>
                        <hr class="my-2">
                        <button type="button" class="btn btn-soft-primary w-100" id="resetColumns"><i class="ri-refresh-line me-1"></i>Đặt lại</button>
                    </div>
                </div>
                <a href="<?= url('deals/pipeline') ?>" class="btn btn-soft-info"><i class="ri-git-branch-line me-1"></i> Pipeline</a>
                <a href="<?= url('deals/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm cơ hội</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('deals') ?>" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Đang mở</option>
                            <option value="won" <?= ($filters['status'] ?? '') === 'won' ? 'selected' : '' ?>>Thắng</option>
                            <option value="lost" <?= ($filters['status'] ?? '') === 'lost' ? 'selected' : '' ?>>Thua</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="stage_id" class="form-select">
                            <option value="">Giai đoạn</option>
                            <?php foreach ($stages ?? [] as $stage): ?>
                                <option value="<?= $stage['id'] ?>" <?= ($filters['stage_id'] ?? '') == $stage['id'] ? 'selected' : '' ?>><?= e($stage['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('deals') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                        <div class="text-muted">Hiển thị <?= count($deals['items']) ?> / <?= $deals['total'] ?></div>
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
(function() {
    var storageKey = 'deal_columns';
    var defaultVisible = <?= json_encode($defaultVisible) ?>;
    var saved = JSON.parse(localStorage.getItem(storageKey) || '{}');

    function applyColumns() {
        document.querySelectorAll('.column-toggle').forEach(function(cb) {
            var col = cb.dataset.column;
            var visible = saved.hasOwnProperty(col) ? saved[col] : defaultVisible.indexOf(col) !== -1;
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
