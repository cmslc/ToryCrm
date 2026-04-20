<?php $pageTitle = 'Sơ đồ tổ chức'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Sơ đồ tổ chức</h4>
    <a href="<?= url('departments') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Danh sách</a>
</div>

<div class="card">
    <div class="card-body">
        <?php
        // Build tree
        $tree = [];
        $byId = [];
        foreach ($departments as $d) { $byId[$d['id']] = $d; $byId[$d['id']]['children'] = []; }
        foreach ($byId as &$d) {
            if ($d['parent_id'] && isset($byId[$d['parent_id']])) {
                $byId[$d['parent_id']]['children'][] = &$d;
            } else {
                $tree[] = &$d;
            }
        }
        unset($d);

        function renderOrgNode($node) { ?>
            <li>
                <a href="<?= url('departments/' . $node['id']) ?>" class="text-decoration-none">
                    <div class="card border shadow-none mb-0" style="border-left:4px solid <?= e($node['color']) ?> !important;min-width:200px">
                        <div class="card-body p-3">
                            <h6 class="mb-1"><?= e($node['name']) ?></h6>
                            <?php if ($node['manager_name']): ?>
                            <div class="d-flex align-items-center mb-1">
                                <?php if (!empty($node['manager_avatar'])): ?>
                                    <img src="<?= asset($node['manager_avatar']) ?>" class="rounded-circle me-2" style="width:24px;height:24px;object-fit:cover">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary me-2" style="width:24px;height:24px;font-size:10px"><?= mb_strtoupper(mb_substr($node['manager_name'], 0, 1)) ?></div>
                                <?php endif; ?>
                                <span class="fs-12"><?= e($node['manager_name']) ?></span>
                            </div>
                            <?php endif; ?>
                            <span class="text-muted fs-11"><i class="ri-team-line me-1"></i><?= $node['member_count'] ?> thành viên</span>
                        </div>
                    </div>
                </a>
                <?php if (!empty($node['children'])): ?>
                <ul>
                    <?php foreach ($node['children'] as $child) renderOrgNode($child); ?>
                </ul>
                <?php endif; ?>
            </li>
        <?php }
        ?>

        <?php if (!empty($tree)): ?>
        <div class="org-tree-container" style="overflow-x:auto">
            <ul class="org-tree">
                <?php foreach ($tree as $root) renderOrgNode($root); ?>
            </ul>
        </div>
        <?php else: ?>
        <div class="text-center py-5 text-muted">
            <i class="ri-organization-chart fs-1 d-block mb-2"></i>
            <p>Chưa có phòng ban nào</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.org-tree, .org-tree ul { list-style: none; padding: 0; margin: 0; }
.org-tree { display: flex; justify-content: center; }
.org-tree ul { display: flex; justify-content: center; margin-top: 20px; position: relative; }
.org-tree ul::before {
    content: ''; position: absolute; top: 0; left: 25%; right: 25%;
    border-top: 2px solid var(--vz-border-color);
}
.org-tree li {
    display: flex; flex-direction: column; align-items: center;
    padding: 0 12px; position: relative;
}
.org-tree li::before, .org-tree li::after {
    content: ''; position: absolute; top: 0; width: 50%;
    border-top: 2px solid var(--vz-border-color); height: 20px;
}
.org-tree li::before { left: 0; }
.org-tree li::after { right: 0; }
.org-tree li:first-child::before, .org-tree li:last-child::after { border: 0; }
.org-tree li:only-child::before, .org-tree li:only-child::after { border: 0; }
.org-tree > li::before, .org-tree > li::after { border: 0; }
.org-tree li > a {
    position: relative; margin-top: 20px;
}
.org-tree li > a::before {
    content: ''; position: absolute; top: -20px; left: 50%;
    border-left: 2px solid var(--vz-border-color); height: 20px;
}
.org-tree > li > a::before { border: 0; margin-top: 0; }
.org-tree > li > a { margin-top: 0; }
</style>
