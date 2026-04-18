<?php
$pageTitle = 'Công việc';
$sc = ['todo'=>'secondary','in_progress'=>'primary','review'=>'warning','done'=>'success'];
$sl = ['todo'=>'Cần làm','in_progress'=>'Đang làm','review'=>'Review','done'=>'Hoàn thành'];
$pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
$pl = ['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn'];
$currentStatus = $filters['status'] ?? '';
$currentPriority = $filters['priority'] ?? '';
$totalAll = 0;
$countMap = [];
foreach ($statusCounts ?? [] as $s) { $countMap[$s['status']] = $s['count']; $totalAll += $s['count']; }
$defaultVisible = ['col-title', 'col-status', 'col-priority', 'col-assignedto', 'col-duedate', 'col-contactid', 'col-dealid'];
?>

<!-- Title Row -->
<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Công việc</h4>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-soft-secondary" id="toggleColumnPanel">Hiển thị cột <i class="ri-arrow-down-s-line ms-1"></i></button>
        <a href="<?= url('tasks/kanban') ?>" class="btn btn-soft-info"><i class="ri-layout-masonry-line me-1"></i> Kanban</a>
        <a href="<?= url('tasks/calendar') ?>" class="btn btn-soft-warning"><i class="ri-calendar-line me-1"></i> Lịch</a>
        <a href="<?= url('tasks/gantt') ?>" class="btn btn-soft-secondary"><i class="ri-bar-chart-horizontal-line me-1"></i> Gantt</a>
        <a href="<?= url('tasks/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm công việc</a>
    </div>
</div>

<!-- Column Options Panel -->
<div class="card mb-2 d-none" id="columnPanel">
    <div class="card-body py-3">
        <h6 class="mb-2">Cột hiển thị</h6>
        <div class="d-flex flex-wrap gap-3 mb-3">
            <?php foreach ($displayColumns ?? [] as $dc): ?>
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

<!-- Filter Row -->
<div class="card mb-2">
    <div class="card-header p-2">
        <form method="GET" action="<?= url('tasks') ?>" class="d-flex align-items-center gap-2 flex-wrap" id="filterForm">
            <div class="search-box" style="min-width:200px;max-width:300px">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm công việc..." value="<?= e($filters['search'] ?? '') ?>">
                <i class="ri-search-line search-icon"></i>
            </div>
            <select name="priority" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                <option value="">Ưu tiên</option>
                <option value="urgent" <?= $currentPriority === 'urgent' ? 'selected' : '' ?>>Khẩn cấp</option>
                <option value="high" <?= $currentPriority === 'high' ? 'selected' : '' ?>>Cao</option>
                <option value="medium" <?= $currentPriority === 'medium' ? 'selected' : '' ?>>Trung bình</option>
                <option value="low" <?= $currentPriority === 'low' ? 'selected' : '' ?>>Thấp</option>
            </select>
            <?php $deptGroupedFilter = []; foreach ($users ?? [] as $u) { $deptGroupedFilter[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
            <select name="assigned_to" class="form-select" style="width:auto;min-width:150px" onchange="this.form.submit()">
                <option value="">Phụ trách</option>
                <?php foreach ($deptGroupedFilter as $dept => $dUsers): ?>
                <optgroup label="<?= e($dept) ?>">
                    <?php foreach ($dUsers as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($filters['assigned_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <?php endforeach; ?>
            </select>
            <input type="date" name="due_from" class="form-control" style="width:auto" value="<?= e($filters['due_from'] ?? '') ?>" placeholder="Hạn từ" title="Hạn từ ngày">
            <input type="date" name="due_to" class="form-control" style="width:auto" value="<?= e($filters['due_to'] ?? '') ?>" placeholder="Hạn đến" title="Hạn đến ngày">
            <input type="hidden" name="status" id="statusInput" value="<?= e($currentStatus) ?>">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
            <?php if (!empty(array_filter($filters ?? []))): ?>
                <a href="<?= url('tasks') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
            <select name="per_page" class="form-select ms-auto" style="width:auto;min-width:90px" onchange="this.form.submit()">
                <?php foreach ([10,20,50,100] as $pp): ?>
                <option value="<?= $pp ?>" <?= ($filters['per_page'] ?? 20) == $pp ? 'selected' : '' ?>><?= $pp ?> dòng</option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- Tabs Row -->
<div class="card mb-3">
    <div class="card-header p-2">
        <div class="d-flex align-items-center justify-content-between">
            <ul class="nav nav-custom nav-custom-light mb-0">
                <li class="nav-item">
                    <a class="nav-link py-2 <?= !$currentStatus ? 'active' : '' ?>" href="<?= url('tasks?' . http_build_query(array_diff_key($filters, ['status'=>'','page'=>'']))) ?>">
                        Tất cả <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-1"><?= $totalAll ?></span>
                    </a>
                </li>
                <?php foreach ($sl as $key => $label):
                    $count = $countMap[$key] ?? 0;
                    if ($count == 0 && $currentStatus !== $key) continue;
                    $qp = array_merge(array_diff_key($filters, ['status'=>'','page'=>'']), ['status' => $key]);
                ?>
                <li class="nav-item">
                    <a class="nav-link py-2 <?= $currentStatus === $key ? 'active' : '' ?>" href="<?= url('tasks?' . http_build_query($qp)) ?>">
                        <?= $label ?> <span class="badge bg-<?= $sc[$key] ?>-subtle text-<?= $sc[$key] ?> rounded-pill ms-1"><?= $count ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex align-items-center gap-2 ms-auto">
                <div class="dropdown">
                    <button class="btn btn-soft-secondary py-1 px-2" data-bs-toggle="dropdown" title="Thêm">
                        <i class="ri-more-fill"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('tasks/templates') ?>"><i class="ri-file-copy-line me-2"></i>Mẫu công việc</a></li>
                        <li><a class="dropdown-item" href="<?= url('tasks/export?format=csv') ?>"><i class="ri-download-line me-2"></i>Xuất CSV</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= url('tasks/trash') ?>"><i class="ri-delete-bin-line me-2"></i>Thùng rác</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Bar (hidden by default) -->
<div class="card mb-2 d-none" id="bulkBar">
    <div class="card-body py-2">
        <form method="POST" action="<?= url('tasks/bulk') ?>" id="bulkForm" class="d-flex align-items-center gap-2 flex-wrap">
            <?= csrf_field() ?>
            <span class="fw-medium"><span id="bulkCount">0</span> đã chọn</span>
            <div id="bulkIds"></div>
            <button type="submit" name="action" value="done" class="btn btn-soft-success"><i class="ri-check-line me-1"></i> Hoàn thành</button>
            <select name="bulk_priority" class="form-select" style="width:auto" onchange="if(this.value){this.form.querySelector('[name=action]').value='priority';this.form.submit()}">
                <option value="">Đổi ưu tiên</option><option value="urgent">Khẩn</option><option value="high">Cao</option><option value="medium">TB</option><option value="low">Thấp</option>
            </select>
            <select name="bulk_assign_to" class="form-select" style="width:auto" onchange="if(this.value){this.form.querySelector('[name=action]').value='assign';this.form.submit()}">
                <option value="">Gán cho</option>
                <?php foreach ($deptGroupedFilter as $dept => $dUsers): ?>
                <optgroup label="<?= e($dept) ?>">
                    <?php foreach ($dUsers as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?>
                </optgroup>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="action" value="delete" class="btn btn-soft-danger" data-confirm="Xóa các công việc đã chọn?"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="text-muted table-light">
                    <tr>
                        <th style="width:40px"><input type="checkbox" class="form-check-input" id="checkAll"></th>
                        <?php foreach ($displayColumns ?? [] as $col): ?>
                        <th class="<?= $col['key'] ?>"><?= e($col['label']) ?></th>
                        <?php endforeach; ?>
                        <th style="width:60px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tasks['items'])): foreach ($tasks['items'] as $task): ?>
                        <tr>
                            <td><input type="checkbox" class="form-check-input row-check" value="<?= $task['id'] ?>"></td>
                            <?php foreach ($displayColumns ?? [] as $col):
                                $field = $col['field'];
                            ?>
                            <td class="<?= $col['key'] ?>">
                                <?php if ($field === 'title'): ?>
                                    <a href="<?= url('tasks/' . $task['id']) ?>" class="fw-medium text-dark"><?= e($task['title']) ?></a>
                                    <?php if (!empty($task['description'])): ?>
                                        <div class="text-muted fs-12 text-truncate" style="max-width:300px"><?= e(mb_substr($task['description'], 0, 60)) ?></div>
                                    <?php endif; ?>
                                <?php elseif ($field === 'status'): ?>
                                    <span class="badge bg-<?= $sc[$task['status']] ?? 'secondary' ?>"><?= $sl[$task['status']] ?? '' ?></span>
                                <?php elseif ($field === 'priority'): ?>
                                    <span class="badge bg-<?= $pc[$task['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$task['priority']] ?? 'secondary' ?>"><?= $pl[$task['priority']] ?? '' ?></span>
                                <?php elseif ($field === 'assigned_to'): ?>
                                    <?= user_avatar($task['assigned_name'] ?? null) ?>
                                <?php elseif ($field === 'due_date'): ?>
                                    <?= due_label($task['due_date'] ?? null, $task['status']) ?>
                                <?php elseif ($field === 'contact_id'): ?>
                                    <?php if ($task['contact_first_name']): ?>
                                        <span><?= e($task['contact_first_name'] . ' ' . ($task['contact_last_name'] ?? '')) ?></span>
                                    <?php else: ?>-<?php endif; ?>
                                <?php elseif ($field === 'deal_id'): ?>
                                    <?php if ($task['deal_title']): ?>
                                        <span class="text-muted"><?= e($task['deal_title']) ?></span>
                                    <?php else: ?>-<?php endif; ?>
                                <?php else: ?>
                                    <?= e($task[$field] ?? '-') ?>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="<?= url('tasks/' . $task['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                        <li><form method="POST" action="<?= url('tasks/' . $task['id'] . '/delete') ?>" data-confirm="Xóa công việc này?"><?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button></form></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="<?= count($displayColumns ?? []) + 2 ?>" class="text-center py-4 text-muted"><i class="ri-task-line fs-1 d-block mb-2"></i>Chưa có công việc</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($tasks['total_pages'] ?? 0) > 1): ?>
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted">Hiển thị <strong><?= (($tasks['page'] - 1) * ($filters['per_page'] ?? 20)) + 1 ?> - <?= min($tasks['page'] * ($filters['per_page'] ?? 20), $tasks['total']) ?></strong> / <strong><?= number_format($tasks['total']) ?></strong></span>
                    <?php $currentPerPage = $filters['per_page'] ?? 20; include __DIR__ . '/../components/per-page-select.php'; ?>
                </div>
                <?php
                $pg = $tasks['page'];
                $tp = $tasks['total_pages'];
                $qs = http_build_query(array_filter($filters ?? []));
                $pgUrl = function($p) use ($qs) { return url('tasks?page=' . $p . '&' . $qs); };
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
// Toggle column panel
document.getElementById('toggleColumnPanel')?.addEventListener('click', function() {
    var panel = document.getElementById('columnPanel');
    panel.classList.toggle('d-none');
    var isOpen = !panel.classList.contains('d-none');
    this.innerHTML = 'Hiển thị cột <i class="ri-arrow-' + (isOpen ? 'up' : 'down') + '-s-line ms-1"></i>';
});

(function() {
    var STORAGE_KEY = 'torycrm_tasks_columns';
    var allColumns = <?= json_encode(array_column($displayColumns ?? [], 'key')) ?>;
    var defaultVisible = ['col-title','col-status','col-priority','col-assignedto','col-duedate','col-contactid','col-dealid'];

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

// Bulk actions
(function() {
    var checkAll = document.getElementById('checkAll');
    var bulkBar = document.getElementById('bulkBar');
    var bulkIds = document.getElementById('bulkIds');
    var bulkCount = document.getElementById('bulkCount');

    function updateBulk() {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length > 0) {
            bulkBar.classList.remove('d-none');
            bulkCount.textContent = checked.length;
            bulkIds.innerHTML = '';
            checked.forEach(function(cb) {
                var input = document.createElement('input');
                input.type = 'hidden'; input.name = 'ids[]'; input.value = cb.value;
                bulkIds.appendChild(input);
            });
        } else {
            bulkBar.classList.add('d-none');
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            document.querySelectorAll('.row-check').forEach(function(cb) { cb.checked = checkAll.checked; });
            updateBulk();
        });
    }
    document.querySelectorAll('.row-check').forEach(function(cb) { cb.addEventListener('change', updateBulk); });
})();
</script>
