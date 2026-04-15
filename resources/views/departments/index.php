<?php
$pageTitle = 'Phòng ban';
$tree = []; $byId = [];
foreach ($departments as $d) { $byId[$d['id']] = $d; $byId[$d['id']]['children'] = []; }
foreach ($byId as &$d) {
    if ($d['parent_id'] && isset($byId[$d['parent_id']])) { $byId[$d['parent_id']]['children'][] = &$d; }
    else { $tree[] = &$d; }
}
unset($d);

$flatList = [];
if (!function_exists('flattenDeptTree')) {
    function flattenDeptTree($nodes, $level, &$out) {
        $count = count($nodes);
        foreach ($nodes as $i => $node) {
            $node['_level'] = $level;
            $node['_last'] = ($i === $count - 1);
            $out[] = $node;
            if (!empty($node['children'])) {
                flattenDeptTree($node['children'], $level + 1, $out);
            }
        }
    }
}
flattenDeptTree($tree, 0, $flatList);
$activeView = $_GET['view'] ?? 'chart';
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Quản lý phòng ban</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('departments/kpi-comparison') ?>" class="btn btn-soft-info"><i class="ri-bar-chart-grouped-line me-1"></i> So sánh KPI</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="ri-add-line me-1"></i> Thêm mới</button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?= $activeView === 'chart' ? 'active' : '' ?>" data-bs-toggle="tab" href="#viewChart" role="tab">
                    <i class="ri-organization-chart me-1"></i> Sơ đồ tổ chức
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $activeView === 'list' ? 'active' : '' ?>" data-bs-toggle="tab" href="#viewList" role="tab">
                    <i class="ri-list-check me-1"></i> Danh sách
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#viewSort" role="tab">
                    <i class="ri-drag-move-line me-1"></i> Sắp xếp
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <!-- Sơ đồ tổ chức -->
            <div class="tab-pane fade <?= $activeView === 'chart' ? 'show active' : '' ?>" id="viewChart" role="tabpanel">
                <?php if (!empty($tree)):
                    if (!function_exists('renderOrgChart')) {
                        function renderOrgChart($node) { ?>
                            <li>
                                <a href="<?= url('departments/' . $node['id']) ?>" class="org-node text-decoration-none">
                                    <div class="org-card" style="border-top: 3px solid <?= e($node['color']) ?>">
                                        <div class="org-card-title"><?= e($node['name']) ?></div>
                                        <?php if (!empty($node['manager_name'])): ?>
                                        <div class="org-card-manager">
                                            <div class="org-avatar" style="background:<?= e($node['color']) ?>20;color:<?= e($node['color']) ?>"><?= mb_strtoupper(mb_substr($node['manager_name'], 0, 1)) ?></div>
                                            <span><?= e($node['manager_name']) ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="org-card-meta"><i class="ri-team-line"></i> <?= $node['member_count'] ?> thành viên</div>
                                    </div>
                                </a>
                                <?php if (!empty($node['children'])): ?>
                                <ul>
                                    <?php foreach ($node['children'] as $child) renderOrgChart($child); ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                        <?php }
                    }
                ?>
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-soft-secondary py-1 px-2" id="zoomIn" title="Phóng to"><i class="ri-zoom-in-line"></i></button>
                    <button type="button" class="btn btn-soft-secondary py-1 px-2" id="zoomOut" title="Thu nhỏ"><i class="ri-zoom-out-line"></i></button>
                    <button type="button" class="btn btn-soft-secondary py-1 px-2" id="zoomReset" title="Đặt lại">100%</button>
                    <span class="text-muted fs-13 d-flex align-items-center" id="zoomLevel">100%</span>
                </div>
                <div class="org-tree-wrap" id="orgTreeWrap" style="overflow:auto">
                    <div id="orgTreeZoom" style="transform-origin:top center;transition:transform .2s;display:inline-block;min-width:100%">
                        <ul class="org-tree">
                            <?php foreach ($tree as $root) renderOrgChart($root); ?>
                        </ul>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="ri-organization-chart fs-1 d-block mb-2"></i>
                    <h5>Chưa có phòng ban nào</h5>
                </div>
                <?php endif; ?>
            </div>

            <!-- Danh sách -->
            <div class="tab-pane fade <?= $activeView === 'list' ? 'show active' : '' ?>" id="viewList" role="tabpanel">
                <?php if (!empty($flatList)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:300px">Phòng ban</th>
                                <th style="width:180px">Trưởng phòng</th>
                                <th style="width:180px">Phó phòng</th>
                                <th style="width:90px" class="text-center">Thành viên</th>
                                <th style="width:120px" class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($flatList as $dept):
                                $indent = $dept['_level'] * 24;
                                $prefix = '';
                                if ($dept['_level'] > 0) {
                                    $prefix = $dept['_last'] ? '└' : '├';
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center" style="padding-left:<?= $indent ?>px">
                                        <?php if ($prefix): ?>
                                            <span class="text-muted me-2 fs-12" style="font-family:monospace;line-height:1"><?= $prefix ?></span>
                                        <?php endif; ?>
                                        <span class="d-inline-block rounded-circle me-2 flex-shrink-0" style="width:10px;height:10px;background:<?= e($dept['color']) ?>"></span>
                                        <a href="<?= url('departments/' . $dept['id']) ?>" class="fw-semibold text-dark"><?= e($dept['name']) ?></a>
                                    </div>
                                </td>
                                <td><?= $dept['manager_name'] ? user_avatar($dept['manager_name'], 'primary', $dept['manager_avatar'] ?? null) : '<span class="text-muted">—</span>' ?></td>
                                <td><?= $dept['vice_manager_name'] ? user_avatar($dept['vice_manager_name'], 'info', $dept['vice_manager_avatar'] ?? null) : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-center">
                                    <a href="<?= url('departments/' . $dept['id'] . '/members') ?>" class="badge bg-secondary-subtle text-secondary"><?= $dept['member_count'] ?></a>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?= url('departments/' . $dept['id']) ?>" class="btn btn-soft-primary btn-icon" title="Chi tiết"><i class="ri-eye-line"></i></a>
                                        <a href="#" class="btn btn-soft-secondary btn-icon edit-dept"
                                            data-id="<?= $dept['id'] ?>" data-name="<?= e($dept['name']) ?>"
                                            data-parent="<?= $dept['parent_id'] ?? '' ?>" data-manager="<?= $dept['manager_id'] ?? '' ?>"
                                            data-vicemanager="<?= $dept['vice_manager_id'] ?? '' ?>"
                                            data-description="<?= e($dept['description'] ?? '') ?>" data-color="<?= e($dept['color']) ?>"
                                            title="Sửa"><i class="ri-pencil-line"></i></a>
                                        <form method="POST" action="<?= url('departments/' . $dept['id'] . '/delete') ?>" data-confirm="Xóa phòng ban <?= e($dept['name']) ?>?" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-soft-danger btn-icon" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="ri-organization-chart fs-1 d-block mb-2"></i>
                    <h5>Chưa có phòng ban nào</h5>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sắp xếp kéo thả -->
            <div class="tab-pane fade" id="viewSort" role="tabpanel">
                <div class="alert alert-info mb-3">
                    <i class="ri-information-line me-1"></i> Kéo thả để sắp xếp và thay đổi cấp phòng ban. Kéo vào phòng ban khác để đặt làm phòng con.
                </div>
                <div id="sortTree">
                    <?php
                    if (!function_exists('renderSortTree')) {
                        function renderSortTree($nodes) { ?>
                            <ul class="sort-list list-unstyled mb-0">
                            <?php foreach ($nodes as $node): ?>
                                <li class="sort-item" data-id="<?= $node['id'] ?>">
                                    <div class="sort-handle d-flex align-items-center gap-2 border rounded px-3 py-2 mb-1 bg-white">
                                        <i class="ri-draggable text-muted" style="cursor:grab"></i>
                                        <span class="d-inline-block rounded-circle flex-shrink-0" style="width:10px;height:10px;background:<?= e($node['color']) ?>"></span>
                                        <span class="fw-medium"><?= e($node['name']) ?></span>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-auto"><?= $node['member_count'] ?></span>
                                    </div>
                                    <?php if (!empty($node['children'])) renderSortTree($node['children']); ?>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        <?php }
                    }
                    if (!empty($tree)) renderSortTree($tree);
                    ?>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary" id="saveSortBtn"><i class="ri-save-line me-1"></i>Lưu sắp xếp</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('departments/store') ?>" id="deptForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="deptModalTitle">Thêm phòng ban</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Tên phòng ban <span class="text-danger">*</span></label><input type="text" class="form-control" name="name" id="deptName" required></div>
                    <div class="mb-3">
                        <label class="form-label">Phòng ban cha</label>
                        <select name="parent_id" class="form-select" id="deptParent"><option value="">Không</option>
                            <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Màu sắc</label>
                        <div class="d-flex gap-2">
                            <?php foreach (['#405189','#0ab39c','#299cdb','#f7b84b','#f06548','#3577f1','#878a99'] as $c): ?>
                                <label style="cursor:pointer"><input type="radio" name="color" value="<?= $c ?>" class="d-none" <?= $c === '#405189' ? 'checked' : '' ?>><span class="d-inline-block rounded-circle border border-2" style="width:28px;height:28px;background:<?= $c ?>"></span></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="row">
                        <?php $deptGrouped = []; foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; } ?>
                        <div class="col-6 mb-3">
                            <label class="form-label">Trưởng phòng</label>
                            <select name="manager_id" class="form-select" id="deptManager"><option value="">Chọn...</option>
                                <?php foreach ($deptGrouped as $dept => $dUsers): ?><optgroup label="<?= e($dept) ?>"><?php foreach ($dUsers as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Phó phòng</label>
                            <select name="vice_manager_id" class="form-select" id="deptVice"><option value="">Chọn...</option>
                                <?php foreach ($deptGrouped as $dept => $dUsers): ?><optgroup label="<?= e($dept) ?>"><?php foreach ($dUsers as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Mô tả</label><textarea class="form-control" name="description" id="deptDesc" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="ri-check-line me-1"></i> Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Org Chart Styles */
.org-tree-wrap { overflow-x: auto; padding: 20px 0; }
.org-tree, .org-tree ul { list-style: none; padding: 0; margin: 0; }
.org-tree { display: flex; justify-content: center; }
.org-tree ul { display: flex; justify-content: center; margin-top: 0; position: relative; padding-top: 24px; }
.org-tree ul::before {
    content: ''; position: absolute; top: 0; left: calc(50% - 0px);
    border-left: 2px solid #cbd5e1; height: 24px;
    display: none;
}
.org-tree li {
    display: flex; flex-direction: column; align-items: center;
    padding: 0 8px; position: relative;
}
/* Vertical line from parent down */
.org-tree li::before {
    content: ''; position: absolute; top: 0; left: 50%;
    border-left: 2px solid #cbd5e1; height: 24px;
}
/* Horizontal connector line */
.org-tree li::after {
    content: ''; position: absolute; top: 0;
    border-top: 2px solid #cbd5e1;
    width: 100%; left: 0;
}
.org-tree li:first-child::after { left: 50%; width: 50%; }
.org-tree li:last-child::after { width: 50%; }
.org-tree li:only-child::after { display: none; }
/* Root level: no connectors */
.org-tree > li::before, .org-tree > li::after { display: none; }

.org-node { display: block; margin-top: 24px; position: relative; }
.org-tree > li > .org-node { margin-top: 0; }

.org-card {
    background: #fef7ed; border: 1px solid #f0e0c8; border-radius: 8px;
    padding: 12px 18px; min-width: 170px; max-width: 220px;
    text-align: center; transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.org-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.12); transform: translateY(-2px); }
.org-card-title { font-weight: 600; font-size: 13px; color: #1a1a2e; margin-bottom: 6px; }
.org-card-manager { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px; color: #555; margin-bottom: 4px; }
.org-avatar {
    width: 22px; height: 22px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 600; flex-shrink: 0;
}
.org-card-meta { font-size: 11px; color: #888; }
/* Sort tree */
.sort-list { padding-left: 24px; }
#sortTree > .sort-list { padding-left: 0; }
.sort-item > .sort-handle { transition: background .15s; }
.sort-item > .sort-handle:hover { background: #f3f6f9 !important; }
.sortable-ghost > .sort-handle { background: #e8effc !important; border-color: #405189 !important; }
.sortable-drag { opacity: 0.8; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.querySelectorAll('.edit-dept').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('deptForm').action = '<?= url('departments/') ?>' + this.dataset.id + '/update';
        document.getElementById('deptModalTitle').textContent = 'Sửa phòng ban';
        document.getElementById('deptName').value = this.dataset.name;
        document.getElementById('deptParent').value = this.dataset.parent;
        document.getElementById('deptManager').value = this.dataset.manager || '';
        document.getElementById('deptVice').value = this.dataset.vicemanager || '';
        document.getElementById('deptDesc').value = this.dataset.description;
        var c = this.dataset.color;
        document.querySelectorAll('[name=color]').forEach(function(r) { r.checked = r.value === c; });
        new bootstrap.Modal(document.getElementById('addDeptModal')).show();
    });
});
document.getElementById('addDeptModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('deptForm').action = '<?= url('departments/store') ?>';
    document.getElementById('deptModalTitle').textContent = 'Thêm phòng ban';
    document.getElementById('deptName').value = '';
    document.getElementById('deptParent').value = '';
    document.getElementById('deptManager').value = '';
    document.getElementById('deptVice').value = '';
    document.getElementById('deptDesc').value = '';
});

// Org chart zoom
(function() {
    var zoom = 1, min = 0.3, max = 1.5, step = 0.1;
    var el = document.getElementById('orgTreeZoom');
    var label = document.getElementById('zoomLevel');
    if (!el) return;

    function apply() {
        el.style.transform = 'scale(' + zoom + ')';
        label.textContent = Math.round(zoom * 100) + '%';
    }

    document.getElementById('zoomIn')?.addEventListener('click', function() { zoom = Math.min(max, zoom + step); apply(); });
    document.getElementById('zoomOut')?.addEventListener('click', function() { zoom = Math.max(min, zoom - step); apply(); });
    document.getElementById('zoomReset')?.addEventListener('click', function() { zoom = 1; apply(); });

    // Ctrl+scroll / pinch zoom
    document.getElementById('orgTreeWrap')?.addEventListener('wheel', function(e) {
        if (e.ctrlKey) {
            e.preventDefault();
            zoom = Math.max(min, Math.min(max, zoom + (e.deltaY < 0 ? step : -step)));
            apply();
        }
    }, {passive: false});
})();

// Nested sortable for department tree
(function() {
    var sortLists = document.querySelectorAll('#sortTree .sort-list');
    if (!sortLists.length) return;

    sortLists.forEach(function(list) {
        new Sortable(list, {
            group: 'dept-tree',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            handle: '.sort-handle',
            draggable: '.sort-item',
            ghostClass: 'sortable-ghost',
        });
    });

    // Also make each sort-item a potential drop target for nesting
    document.querySelectorAll('.sort-item').forEach(function(item) {
        if (!item.querySelector('.sort-list')) {
            var ul = document.createElement('ul');
            ul.className = 'sort-list list-unstyled mb-0';
            item.appendChild(ul);
            new Sortable(ul, {
                group: 'dept-tree',
                animation: 150,
                fallbackOnBody: true,
                swapThreshold: 0.65,
                handle: '.sort-handle',
                draggable: '.sort-item',
                ghostClass: 'sortable-ghost',
            });
        }
    });

    // Save button
    document.getElementById('saveSortBtn')?.addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Đang lưu...';

        var items = [];
        function collectTree(parentEl, parentId) {
            var list = parentEl.querySelector(':scope > .sort-list');
            if (!list) return;
            var children = list.querySelectorAll(':scope > .sort-item');
            children.forEach(function(child, index) {
                var id = parseInt(child.dataset.id);
                items.push({ id: id, parent_id: parentId, sort_order: index });
                collectTree(child, id);
            });
        }
        collectTree(document.getElementById('sortTree'), null);

        fetch('<?= url("departments/reorder") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrf_token() ?>'},
            body: JSON.stringify({ items: items })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                btn.innerHTML = '<i class="ri-check-line me-1"></i>Đã lưu!';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                alert(data.error || 'Lỗi');
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-save-line me-1"></i>Lưu sắp xếp';
            }
        })
        .catch(function() {
            alert('Lỗi kết nối');
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-save-line me-1"></i>Lưu sắp xếp';
        });
    });
})();
</script>
