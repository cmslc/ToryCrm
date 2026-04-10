<?php $pageTitle = 'Lịch công việc'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Lịch công việc</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('tasks') ?>" class="btn btn-soft-secondary"><i class="ri-list-check me-1"></i> Danh sách</a>
        <a href="<?= url('tasks/kanban') ?>" class="btn btn-soft-info"><i class="ri-layout-masonry-line me-1"></i> Kanban</a>
        <a href="<?= url('tasks/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div id="taskCalendar"></div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var cal = new FullCalendar.Calendar(document.getElementById('taskCalendar'), {
        initialView: 'dayGridMonth',
        locale: 'vi',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
        height: 'auto',
        events: '<?= url("tasks/calendar/events") ?>',
        eventClick: function(info) { info.jsEvent.preventDefault(); if (info.event.url) window.location = info.event.url; },
        eventDidMount: function(info) {
            if (info.event.extendedProps.assigned) {
                info.el.title = info.event.extendedProps.assigned;
            }
        }
    });
    cal.render();
});
</script>
