<?php $pageTitle = 'Hoạt động'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Lịch sử hoạt động</h4>
            <div class="d-flex gap-2">
                <div class="btn-group" id="viewToggle">
                    <button class="btn btn-light active" data-view="list" id="btnListView">
                        <i class="ri-list-unordered me-1"></i> Danh sách
                    </button>
                    <button class="btn btn-light" data-view="calendar" id="btnCalendarView">
                        <i class="ri-calendar-line me-1"></i> Lịch
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-3" id="filtersCard">
            <div class="card-body">
                <form method="GET" action="<?= url('activities') ?>" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Loại</label>
                        <select name="type" class="form-select">
                            <option value="">Tất cả</option>
                            <?php
                            $typeOptions = ['note'=>'Ghi chú','call'=>'Cuộc gọi','email'=>'Email','meeting'=>'Cuộc họp','task'=>'Công việc','deal'=>'Cơ hội','system'=>'Hệ thống'];
                            foreach ($typeOptions as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($filters['type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Người thực hiện</label>
                        <select name="user_id" class="form-select">
                            <option value="">Tất cả</option>
                            <?php foreach ($users ?? [] as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($filters['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Khách hàng</label>
                        <input type="text" name="contact_id" class="form-control" placeholder="ID khách hàng" value="<?= e($filters['contact_id'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="ri-filter-line me-1"></i> Lọc</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- List View -->
        <div class="card" id="listView">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted"><?= $total ?? 0 ?> hoạt động</span>
                </div>
                <div class="activity-timeline">
                    <?php if (!empty($activities)): ?>
                        <?php foreach ($activities as $act): ?>
                            <?php
                            $typeIcons = ['note'=>'ri-file-text-line','call'=>'ri-phone-line','email'=>'ri-mail-line','meeting'=>'ri-calendar-line','task'=>'ri-task-line','deal'=>'ri-hand-coin-line','system'=>'ri-settings-3-line'];
                            $typeColors = ['note'=>'primary','call'=>'success','email'=>'info','meeting'=>'warning','task'=>'danger','deal'=>'success','system'=>'secondary'];
                            ?>
                            <div class="activity-item d-flex mb-3" data-activity-id="<?= $act['id'] ?>">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs">
                                        <div class="avatar-title rounded-circle bg-<?= $typeColors[$act['type']] ?? 'primary' ?>-subtle text-<?= $typeColors[$act['type']] ?? 'primary' ?>">
                                            <i class="<?= $typeIcons[$act['type']] ?? 'ri-file-text-line' ?>"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <h6 class="mb-1"><?= e($act['title']) ?></h6>
                                        <div class="dropdown">
                                            <button class="btn btn-ghost-secondary btn-icon" data-bs-toggle="dropdown">
                                                <i class="ri-more-2-fill"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item edit-activity-btn" href="#" data-id="<?= $act['id'] ?>" data-type="<?= e($act['type']) ?>" data-title="<?= e($act['title']) ?>" data-description="<?= e($act['description'] ?? '') ?>" data-scheduled="<?= e($act['scheduled_at'] ?? '') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                <li><a class="dropdown-item text-danger delete-activity-btn" href="#" data-id="<?= $act['id'] ?>"><i class="ri-delete-bin-line me-2"></i>Xóa</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php if (!empty($act['description'])): ?>
                                        <p class="text-muted mb-1"><?= e($act['description']) ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <small class="text-muted"><?= time_ago($act['created_at']) ?></small>
                                        <?php if (!empty($act['user_name'])): ?>
                                            <small class="text-muted">- <?= e($act['user_name']) ?></small>
                                        <?php endif; ?>
                                        <?php if (!empty($act['contact_first_name'])): ?>
                                            <a href="<?= url('contacts/' . $act['contact_id']) ?>" class="badge bg-primary-subtle text-primary"><?= e($act['contact_first_name'] . ' ' . ($act['contact_last_name'] ?? '')) ?></a>
                                        <?php endif; ?>
                                        <?php if (!empty($act['deal_title'])): ?>
                                            <a href="<?= url('deals/' . $act['deal_id']) ?>" class="badge bg-warning-subtle text-warning"><?= e($act['deal_title']) ?></a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4"><i class="ri-history-line fs-1 d-block mb-2"></i>Chưa có hoạt động nào</p>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if (($totalPages ?? 1) > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center mb-0">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php
                                $queryParams = $filters ?? [];
                                $queryParams['page'] = $i;
                                $queryParams = array_filter($queryParams);
                                $qs = http_build_query($queryParams);
                                ?>
                                <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('activities' . ($qs ? '?' . $qs : '')) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

        <!-- Calendar View -->
        <div class="card" id="calendarView" style="display:none;">
            <div class="card-body">
                <div id="activityCalendar"></div>
            </div>
        </div>

        <!-- Edit Activity Modal -->
        <div class="modal fade" id="editActivityModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sửa hoạt động</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editActivityForm">
                        <div class="modal-body">
                            <input type="hidden" id="editActId">
                            <div class="mb-3">
                                <label class="form-label">Loại</label>
                                <select class="form-select" id="editActType">
                                    <option value="note">Ghi chú</option>
                                    <option value="call">Cuộc gọi</option>
                                    <option value="email">Email</option>
                                    <option value="meeting">Cuộc họp</option>
                                    <option value="task">Công việc</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tiêu đề</label>
                                <input type="text" class="form-control" id="editActTitle" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" id="editActDesc" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lịch hẹn</label>
                                <input type="datetime-local" class="form-control" id="editActScheduled">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle
    var listView = document.getElementById('listView');
    var calendarView = document.getElementById('calendarView');
    var btnList = document.getElementById('btnListView');
    var btnCalendar = document.getElementById('btnCalendarView');
    var calendarInitialized = false;

    btnList.addEventListener('click', function() {
        listView.style.display = '';
        calendarView.style.display = 'none';
        btnList.classList.add('active');
        btnCalendar.classList.remove('active');
    });

    btnCalendar.addEventListener('click', function() {
        listView.style.display = 'none';
        calendarView.style.display = '';
        btnCalendar.classList.add('active');
        btnList.classList.remove('active');

        if (!calendarInitialized && typeof FullCalendar !== 'undefined') {
            calendarInitialized = true;
            var calEl = document.getElementById('activityCalendar');
            var calendar = new FullCalendar.Calendar(calEl, {
                initialView: 'dayGridMonth',
                locale: 'vi',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: function(info, successCallback, failureCallback) {
                    fetch(BASE_URL + '/activities/calendar?start=' + info.startStr.split('T')[0] + '&end=' + info.endStr.split('T')[0], {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(function(r) { return r.json(); })
                    .then(successCallback)
                    .catch(failureCallback);
                },
                eventClick: function(info) {
                    var props = info.event.extendedProps;
                    alert(info.event.title + '\n' + (props.description || '') + '\n' + (props.user_name || ''));
                },
                height: 'auto'
            });
            calendar.render();
        }
    });

    // Edit activity
    document.querySelectorAll('.edit-activity-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('editActId').value = this.dataset.id;
            document.getElementById('editActType').value = this.dataset.type;
            document.getElementById('editActTitle').value = this.dataset.title;
            document.getElementById('editActDesc').value = this.dataset.description;
            var scheduled = this.dataset.scheduled;
            if (scheduled) {
                // Convert to datetime-local format
                document.getElementById('editActScheduled').value = scheduled.replace(' ', 'T').substring(0, 16);
            } else {
                document.getElementById('editActScheduled').value = '';
            }
            new bootstrap.Modal(document.getElementById('editActivityModal')).show();
        });
    });

    document.getElementById('editActivityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var id = document.getElementById('editActId').value;
        var formData = new FormData();
        formData.append('type', document.getElementById('editActType').value);
        formData.append('title', document.getElementById('editActTitle').value.trim());
        formData.append('description', document.getElementById('editActDesc').value.trim());
        formData.append('scheduled_at', document.getElementById('editActScheduled').value.replace('T', ' '));
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        fetch(BASE_URL + '/activities/' + id + '/update', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Có lỗi xảy ra');
            }
        });
    });

    // Delete activity
    document.querySelectorAll('.delete-activity-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('Xác nhận xóa hoạt động này?')) return;
            var id = this.dataset.id;
            var formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

            fetch(BASE_URL + '/activities/' + id + '/delete', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var item = document.querySelector('.activity-item[data-activity-id="' + id + '"]');
                    if (item) item.remove();
                }
            });
        });
    });
});
</script>
