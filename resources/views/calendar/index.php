<?php $pageTitle = 'Lịch làm việc'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Lịch làm việc</h4>
            <a href="<?= url('calendar/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm lịch hẹn</a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <!-- Today's events -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ri-sun-line me-1"></i> Hôm nay</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($today)): ?>
                            <?php foreach ($today as $event): ?>
                                <div class="d-flex align-items-start mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatar-xs">
                                            <span class="avatar-title rounded-circle" style="background-color:<?= $event['color'] ?? '#405189' ?>">
                                                <?php
                                                $icons = ['meeting'=>'ri-team-line','call'=>'ri-phone-line','visit'=>'ri-map-pin-line','reminder'=>'ri-alarm-line','other'=>'ri-calendar-event-line'];
                                                ?>
                                                <i class="<?= $icons[$event['type']] ?? 'ri-calendar-event-line' ?> text-white"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <a href="<?= url('calendar/' . $event['id']) ?>" class="fw-medium text-dark"><?= e($event['title']) ?></a>
                                        <p class="text-muted mb-0 small">
                                            <i class="ri-time-line me-1"></i><?= date('H:i', strtotime($event['start_at'])) ?>
                                            <?php if ($event['contact_first_name']): ?>
                                                <span class="ms-2"><i class="ri-user-line me-1"></i><?= e($event['contact_first_name'] . ' ' . ($event['contact_last_name'] ?? '')) ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">Không có lịch hẹn hôm nay</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Upcoming -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ri-calendar-todo-line me-1"></i> Sắp tới</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($upcoming)): ?>
                            <?php foreach ($upcoming as $event): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="flex-shrink-0">
                                        <div style="width:4px;height:40px;border-radius:2px;background:<?= $event['color'] ?? '#405189' ?>"></div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <a href="<?= url('calendar/' . $event['id']) ?>" class="fw-medium text-dark d-block"><?= e($event['title']) ?></a>
                                        <small class="text-muted">
                                            <?= format_datetime($event['start_at']) ?>
                                            <?php if ($event['contact_first_name']): ?>
                                                &middot; <?= e($event['contact_first_name']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">Không có lịch hẹn sắp tới</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- FullCalendar CDN -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'vi',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                buttonText: {
                    today: 'Hôm nay',
                    month: 'Tháng',
                    week: 'Tuần',
                    day: 'Ngày',
                    list: 'Danh sách'
                },
                events: function(info, successCallback, failureCallback) {
                    fetch(`<?= url('calendar/events') ?>?start=${info.startStr}&end=${info.endStr}`)
                        .then(res => res.json())
                        .then(data => successCallback(data))
                        .catch(err => failureCallback(err));
                },
                eventClick: function(info) {
                    window.location.href = '<?= url('calendar/') ?>' + info.event.id;
                },
                dateClick: function(info) {
                    window.location.href = '<?= url('calendar/create?date=') ?>' + info.dateStr;
                },
                height: 'auto',
                editable: false,
                selectable: true,
            });
            calendar.render();
        });
        </script>
        <style>
            .fc .fc-toolbar-title { font-size: 1.2rem; }
            .fc .fc-button { font-size: 0.85rem; }
            .fc-event { cursor: pointer; }
        </style>
