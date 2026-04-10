<?php $pageTitle = 'Gantt Chart'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Gantt Chart</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('tasks') ?>" class="btn btn-soft-secondary"><i class="ri-list-check me-1"></i> Danh sách</a>
        <a href="<?= url('tasks/kanban') ?>" class="btn btn-soft-info"><i class="ri-layout-masonry-line me-1"></i> Kanban</a>
        <a href="<?= url('tasks/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm</a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div id="ganttChart" style="min-height:400px"></div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= url("tasks/gantt/data") ?>').then(r => r.json()).then(function(data) {
        if (!data.length) {
            document.getElementById('ganttChart').innerHTML = '<div class="text-center py-5 text-muted"><i class="ri-bar-chart-horizontal-line fs-1 d-block mb-2"></i>Chưa có công việc nào có ngày hạn</div>';
            return;
        }
        var tasks = data.map(function(t) {
            return {
                id: String(t.id),
                name: t.name,
                start: t.start,
                end: t.end,
                progress: t.progress,
                dependencies: t.dependencies,
                custom_class: t.status === 'done' ? 'bar-done' : (t.status === 'in_progress' ? 'bar-progress' : '')
            };
        });
        new Gantt('#ganttChart', tasks, {
            view_mode: 'Week',
            language: 'vi',
            on_click: function(task) { window.location = '<?= url("tasks") ?>/' + task.id; },
        });
    });
});
</script>
<style>
.bar-done .bar { fill: #45cb85 !important; }
.bar-progress .bar { fill: #405189 !important; }
</style>
