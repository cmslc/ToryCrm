<?php $pageTitle = 'Tùy chỉnh Dashboard'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tùy chỉnh Dashboard</h4>
            <a href="<?= url('settings') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
        </div>

        <?php $flashMsg = flash(); if ($flashMsg): ?>
            <div class="alert alert-<?= $flashMsg['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= e($flashMsg['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php
        $widgets = [
            'stats_cards'       => ['name' => 'Thống kê tổng quan',      'desc' => 'Hiển thị số liệu tổng hợp: liên hệ, cơ hội, doanh thu, công việc',   'icon' => 'ri-bar-chart-box-line'],
            'revenue_chart'     => ['name' => 'Biểu đồ doanh thu',       'desc' => 'Biểu đồ cột/đường doanh thu theo tháng',                              'icon' => 'ri-line-chart-line'],
            'pipeline_summary'  => ['name' => 'Tổng hợp Pipeline',       'desc' => 'Số lượng và giá trị cơ hội theo từng giai đoạn',                      'icon' => 'ri-funnel-line'],
            'recent_contacts'   => ['name' => 'Liên hệ gần đây',         'desc' => 'Danh sách liên hệ mới tạo hoặc cập nhật gần đây',                     'icon' => 'ri-contacts-book-line'],
            'recent_activities' => ['name' => 'Hoạt động gần đây',       'desc' => 'Nhật ký hoạt động gần nhất của hệ thống',                             'icon' => 'ri-time-line'],
            'overdue_tasks'     => ['name' => 'Công việc quá hạn',       'desc' => 'Danh sách công việc đã quá hạn cần xử lý',                            'icon' => 'ri-alarm-warning-line'],
            'task_chart'        => ['name' => 'Biểu đồ công việc',       'desc' => 'Biểu đồ tròn phân bổ công việc theo trạng thái',                      'icon' => 'ri-pie-chart-line'],
            'today_events'      => ['name' => 'Lịch hôm nay',            'desc' => 'Các sự kiện và cuộc hẹn trong ngày hôm nay',                          'icon' => 'ri-calendar-event-line'],
            'orders_stats'      => ['name' => 'Thống kê đơn hàng',       'desc' => 'Tổng hợp đơn hàng: số lượng, doanh thu, trạng thái',                  'icon' => 'ri-shopping-cart-line'],
        ];

        // Current preferences (from controller or defaults)
        $savedWidgets = $widgetPreferences ?? [];
        $savedOrder = array_column($savedWidgets, 'widget_key');
        $savedVisible = [];
        foreach ($savedWidgets as $w) {
            $savedVisible[$w['widget_key']] = !empty($w['visible']);
        }

        // Build ordered list: saved order first, then remaining
        $orderedKeys = [];
        foreach ($savedOrder as $key) {
            if (isset($widgets[$key])) $orderedKeys[] = $key;
        }
        foreach (array_keys($widgets) as $key) {
            if (!in_array($key, $orderedKeys)) $orderedKeys[] = $key;
        }
        ?>

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1"><i class="ri-dashboard-line me-1"></i> Các widget Dashboard</h5>
                        <small class="text-muted"><i class="ri-drag-move-line me-1"></i> Kéo thả để sắp xếp thứ tự</small>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('settings/widgets') ?>" id="widgets-form">
                            <?= csrf_field() ?>
                            <div id="widget-list">
                                <?php foreach ($orderedKeys as $index => $key):
                                    $w = $widgets[$key];
                                    $isVisible = isset($savedVisible[$key]) ? $savedVisible[$key] : true;
                                ?>
                                    <div class="widget-item d-flex align-items-center p-3 mb-2 border rounded bg-light" data-key="<?= $key ?>">
                                        <div class="me-3 text-muted" style="cursor: grab;">
                                            <i class="ri-drag-move-2-fill fs-18"></i>
                                        </div>
                                        <div class="avatar-sm me-3 flex-shrink-0">
                                            <div class="avatar-title rounded bg-primary-subtle text-primary">
                                                <i class="<?= $w['icon'] ?> fs-18"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?= $w['name'] ?></h6>
                                            <small class="text-muted"><?= $w['desc'] ?></small>
                                            <input type="hidden" name="widgets[<?= $key ?>][key]" value="<?= $key ?>">
                                            <input type="hidden" name="widgets[<?= $key ?>][sort]" value="<?= $index ?>" class="widget-sort">
                                        </div>
                                        <div class="ms-3 flex-shrink-0">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                       name="widgets[<?= $key ?>][visible]" value="1"
                                                       id="widget-<?= $key ?>" <?= $isVisible ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="widget-<?= $key ?>"></label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu thay đổi</button>
                                <button type="button" class="btn btn-soft-warning ms-2" id="reset-defaults"><i class="ri-refresh-line me-1"></i> Đặt lại mặc định</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ri-information-line me-1"></i> Hướng dẫn</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="ri-drag-move-2-fill text-primary me-2"></i> Kéo thả để thay đổi thứ tự hiển thị</li>
                            <li class="mb-2"><i class="ri-toggle-line text-primary me-2"></i> Bật/tắt để hiển thị hoặc ẩn widget</li>
                            <li class="mb-2"><i class="ri-save-line text-primary me-2"></i> Nhấn "Lưu thay đổi" để áp dụng</li>
                            <li><i class="ri-refresh-line text-primary me-2"></i> "Đặt lại mặc định" sẽ khôi phục cài đặt ban đầu</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var widgetList = document.getElementById('widget-list');

    // SortableJS for reordering widgets
    new Sortable(widgetList, {
        animation: 200,
        handle: '.ri-drag-move-2-fill',
        ghostClass: 'bg-primary-subtle',
        onEnd: function() {
            // Update sort order hidden inputs
            widgetList.querySelectorAll('.widget-item').forEach(function(item, index) {
                var sortInput = item.querySelector('.widget-sort');
                if (sortInput) sortInput.value = index;
            });
        }
    });

    // Reset defaults
    document.getElementById('reset-defaults').addEventListener('click', function() {
        var modalEl = document.getElementById('confirmModal');
        if (!modalEl) return;
        document.getElementById('confirmTitle').textContent = 'Đặt lại mặc định';
        document.getElementById('confirmMessage').textContent = 'Tất cả widget sẽ được bật và về thứ tự ban đầu.';
        document.getElementById('confirmOk').className = 'btn w-sm btn-warning';
        modalEl.querySelector('.modal-body > div:first-child i').className = 'ri-refresh-line';
        modalEl.querySelector('.modal-body > div:first-child').className = 'text-warning mb-4';
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
        document.getElementById('confirmOk').onclick = function() {
            widgetList.querySelectorAll('.form-check-input').forEach(function(cb) { cb.checked = true; });
            widgetList.querySelectorAll('.widget-item').forEach(function(item, index) {
                var sortInput = item.querySelector('.widget-sort');
                if (sortInput) sortInput.value = index;
            });
            modal.hide();
            document.getElementById('confirmOk').onclick = null;
        };
    });
});
</script>
