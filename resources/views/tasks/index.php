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
?>

<!-- Title Row -->
<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Công việc</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('tasks/kanban') ?>" class="btn btn-soft-info"><i class="ri-layout-masonry-line me-1"></i> Kanban</a>
        <a href="<?= url('tasks/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm công việc</a>
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
            <select name="assigned_to" class="form-select" style="width:auto;min-width:150px" onchange="this.form.submit()">
                <option value="">Phụ trách</option>
                <?php foreach ($users ?? [] as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($filters['assigned_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="status" id="statusInput" value="<?= e($currentStatus) ?>">
            <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
            <?php if (!empty(array_filter($filters ?? []))): ?>
                <a href="<?= url('tasks') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
            <?php endif; ?>
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
                    <button class="btn btn-soft-secondary py-1 px-2" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Hiển thị cột">
                        <i class="ri-layout-column-line me-1"></i> Cột
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="min-width:200px">
                        <h6 class="dropdown-header px-0">Hiển thị cột</h6>
                        <?php
                        $columns = [
                            'col-task' => 'Công việc',
                            'col-status' => 'Trạng thái',
                            'col-priority' => 'Ưu tiên',
                            'col-assigned' => 'Phụ trách',
                            'col-created' => 'Ngày tạo',
                            'col-due' => 'Hạn',
                            'col-related' => 'Liên quan',
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
                <div class="dropdown">
                    <button class="btn btn-soft-secondary py-1 px-2" data-bs-toggle="dropdown" title="Thêm">
                        <i class="ri-more-fill"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= url('tasks/trash') ?>"><i class="ri-delete-bin-line me-2"></i>Thùng rác</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-nowrap mb-0">
                <thead class="text-muted table-light">
                    <tr>
                        <th class="col-task">Công việc</th>
                        <th class="col-status">Trạng thái</th>
                        <th class="col-priority">Ưu tiên</th>
                        <th class="col-assigned">Phụ trách</th>
                        <th class="col-created">Ngày tạo</th>
                        <th class="col-due">Hạn</th>
                        <th class="col-related">Liên quan</th>
                        <th style="width:60px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tasks['items'])): foreach ($tasks['items'] as $task): ?>
                        <tr>
                            <td class="col-task">
                                <a href="<?= url('tasks/' . $task['id']) ?>" class="fw-medium text-dark"><?= e($task['title']) ?></a>
                                <?php if (!empty($task['description'])): ?>
                                    <div class="text-muted fs-12 text-truncate" style="max-width:300px"><?= e(mb_substr($task['description'], 0, 60)) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="col-status"><span class="badge bg-<?= $sc[$task['status']] ?? 'secondary' ?>"><?= $sl[$task['status']] ?? '' ?></span></td>
                            <td class="col-priority"><span class="badge bg-<?= $pc[$task['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$task['priority']] ?? 'secondary' ?>"><?= $pl[$task['priority']] ?? '' ?></span></td>
                            <td class="col-assigned"><?= e($task['assigned_name'] ?? '-') ?></td>
                            <td class="col-created"><span class="text-muted"><?= $task['created_at'] ? date('d/m/Y H:i', strtotime($task['created_at'])) : '-' ?></span></td>
                            <td class="col-due">
                                <?php if ($task['due_date']): ?>
                                    <?php $isOverdue = strtotime($task['due_date']) < time() && $task['status'] !== 'done'; ?>
                                    <span class="<?= $isOverdue ? 'text-danger fw-medium' : 'text-muted' ?>"><?= date('d/m/Y H:i', strtotime($task['due_date'])) ?></span>
                                <?php else: ?>-<?php endif; ?>
                            </td>
                            <td class="col-related">
                                <?php if ($task['contact_first_name']): ?>
                                    <span><?= e($task['contact_first_name']) ?></span>
                                <?php endif; ?>
                                <?php if ($task['deal_title']): ?>
                                    <div class="text-muted fs-12"><?= e($task['deal_title']) ?></div>
                                <?php endif; ?>
                            </td>
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
                        <tr><td colspan="8" class="text-center py-4 text-muted"><i class="ri-task-line fs-1 d-block mb-2"></i>Chưa có công việc</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($tasks['total_pages'] ?? 0) > 1): ?>
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <div class="text-muted">Hiển thị <?= count($tasks['items']) ?> / <?= $tasks['total'] ?></div>
                <nav><ul class="pagination mb-0">
                    <?php for ($i = 1; $i <= $tasks['total_pages']; $i++): ?>
                        <li class="page-item <?= $i === $tasks['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('tasks?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var storageKey = 'task_columns';
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
