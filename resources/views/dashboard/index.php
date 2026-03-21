<?php $pageTitle = 'Dashboard'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-lg-center flex-lg-row flex-column mb-4">
            <div class="flex-grow-1">
                <h4 class="fs-16 mb-1">Xin chào, <?= e($_SESSION['user']['name'] ?? '') ?>!</h4>
                <p class="text-muted mb-0">Tổng quan hoạt động kinh doanh hôm nay.</p>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Khách hàng</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="<?= $stats['total_contacts'] ?? 0 ?>">0</span>
                        </h4>
                        <a href="<?= url('contacts') ?>" class="text-decoration-underline text-muted">Xem tất cả</a>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle rounded fs-3"><i class="bx bxs-user-account text-primary"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Cơ hội</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="<?= $stats['total_deals'] ?? 0 ?>">0</span>
                        </h4>
                        <a href="<?= url('deals') ?>" class="text-decoration-underline text-muted">Xem tất cả</a>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle rounded fs-3"><i class="bx bx-dollar-circle text-warning"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Đơn hàng</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                            <span class="counter-value" data-target="<?= $stats['total_orders'] ?? 0 ?>">0</span>
                        </h4>
                        <a href="<?= url('orders') ?>" class="text-decoration-underline text-muted">Xem tất cả</a>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle rounded fs-3"><i class="bx bx-shopping-bag text-info"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Doanh thu</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><?= format_money($stats['total_revenue'] ?? 0) ?></h4>
                        <a href="<?= url('reports/revenue') ?>" class="text-decoration-underline text-muted">Xem báo cáo</a>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle rounded fs-3"><i class="bx bx-wallet text-success"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart + Pipeline -->
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Doanh thu theo tháng</h4>
            </div>
            <div class="card-body p-0 pb-2">
                <div class="w-100" style="height:350px;padding:0 15px">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Pipeline</h4>
                <a href="<?= url('deals/pipeline') ?>" class="btn btn-sm btn-soft-primary">Xem</a>
            </div>
            <div class="card-body">
                <?php if (!empty($pipelineSummary)): ?>
                    <?php foreach ($pipelineSummary as $stage): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <span class="badge rounded-pill" style="background-color:<?= safe_color($stage['color'] ?? null) ?>;min-width:28px"><?= $stage['count'] ?? 0 ?></span>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <p class="mb-0 fs-13"><?= e($stage['name']) ?></p>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="text-muted fs-12"><?= format_money($stage['total_value'] ?? 0) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Chưa có dữ liệu</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Today Events + Overdue Tasks -->
<div class="row">
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-calendar-2-line me-1 text-primary"></i> Lịch hẹn hôm nay</h4>
                <a href="<?= url('calendar') ?>" class="btn btn-sm btn-soft-primary">Xem lịch</a>
            </div>
            <div class="card-body">
                <?php if (!empty($todayEvents)): ?>
                    <?php foreach ($todayEvents as $ev): ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="flex-shrink-0">
                                <span class="badge fs-12" style="background-color:<?= safe_color($ev['color']) ?>"><?= date('H:i', strtotime($ev['start_at'])) ?></span>
                            </div>
                            <div class="flex-grow-1 ms-3 overflow-hidden">
                                <a href="<?= url('calendar/' . $ev['id']) ?>" class="fw-medium text-dark text-truncate d-block"><?= e($ev['title']) ?></a>
                                <?php if (!empty($ev['contact_first_name'])): ?>
                                    <small class="text-muted"><?= e($ev['contact_first_name'] . ' ' . ($ev['contact_last_name'] ?? '')) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <lord-icon src="https://cdn.lordicon.com/abgtphft.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:70px;height:70px"></lord-icon>
                        <p class="text-muted mb-0 mt-2">Không có lịch hẹn hôm nay</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-error-warning-line me-1 text-danger"></i> Công việc quá hạn</h4>
                <a href="<?= url('tasks') ?>" class="btn btn-sm btn-soft-danger">Xem</a>
            </div>
            <div class="card-body">
                <?php if (!empty($overdueTasks)): ?>
                    <?php foreach ($overdueTasks as $task): ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1 overflow-hidden">
                                <a href="<?= url('tasks/' . $task['id']) ?>" class="text-dark fw-medium text-truncate d-block"><?= e($task['title']) ?></a>
                                <small class="text-danger"><i class="ri-time-line me-1"></i><?= format_datetime($task['due_date']) ?></small>
                            </div>
                            <div class="flex-shrink-0 ms-2">
                                <?php $pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger']; ?>
                                <span class="badge bg-<?= $pc[$task['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$task['priority']] ?? 'secondary' ?> fs-10">
                                    <?= ucfirst($task['priority'] ?? '') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="ri-check-double-line text-success" style="font-size:48px"></i>
                        <p class="text-muted mb-0 mt-2">Không có công việc quá hạn</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Tổng quan công việc</h4>
                <a href="<?= url('tasks/kanban') ?>" class="btn btn-sm btn-soft-primary">Kanban</a>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="width:100%;max-width:250px">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Contacts + Activities -->
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Khách hàng mới</h4>
                <a href="<?= url('contacts') ?>" class="btn btn-sm btn-soft-primary">Xem tất cả</a>
            </div>
            <div class="card-body">
                <div class="table-responsive table-card">
                    <table class="table table-borderless table-hover align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr>
                                <th>Khách hàng</th>
                                <th>Công ty</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentContacts)): ?>
                                <?php
                                $sc = ['new'=>'info','contacted'=>'primary','qualified'=>'warning','converted'=>'success','lost'=>'danger'];
                                $sl = ['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất'];
                                ?>
                                <?php foreach ($recentContacts as $c): ?>
                                    <tr>
                                        <td><a href="<?= url('contacts/' . $c['id']) ?>" class="fw-medium"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></a></td>
                                        <td class="text-muted"><?= e($c['company_name'] ?? '-') ?></td>
                                        <td><span class="badge bg-<?= $sc[$c['status']] ?? 'secondary' ?>-subtle text-<?= $sc[$c['status']] ?? 'secondary' ?>"><?= $sl[$c['status']] ?? '' ?></span></td>
                                        <td class="text-muted"><?= time_ago($c['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-3">Chưa có khách hàng</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Hoạt động gần đây</h4>
                <a href="<?= url('activities') ?>" class="btn btn-sm btn-soft-primary">Xem tất cả</a>
            </div>
            <div class="card-body" style="max-height:380px;overflow-y:auto">
                <?php if (!empty($recentActivities)): ?>
                    <div class="acitivity-timeline acitivity-main">
                        <?php
                        $icons = ['note'=>'ri-file-text-line','call'=>'ri-phone-line','email'=>'ri-mail-line','meeting'=>'ri-calendar-line','task'=>'ri-task-line','deal'=>'ri-hand-coin-line','system'=>'ri-settings-3-line'];
                        $colors = ['note'=>'primary','call'=>'success','email'=>'info','meeting'=>'warning','task'=>'danger','deal'=>'success','system'=>'secondary'];
                        ?>
                        <?php foreach ($recentActivities as $a): ?>
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-<?= $colors[$a['type']] ?? 'primary' ?>-subtle text-<?= $colors[$a['type']] ?? 'primary' ?>">
                                            <i class="<?= $icons[$a['type']] ?? 'ri-file-text-line' ?>"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 lh-base"><?= e($a['title']) ?></h6>
                                    <?php if (!empty($a['description'])): ?>
                                        <p class="text-muted mb-1 fs-12"><?= e($a['description']) ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted fs-12"><?= time_ago($a['created_at']) ?> <?= !empty($a['user_name']) ? '· ' . e($a['user_name']) : '' ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">Chưa có hoạt động</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
                datasets: [{
                    label: 'Doanh thu',
                    data: <?= json_encode($revenueData ?? array_fill(0, 12, 0)) ?>,
                    backgroundColor: 'rgba(64, 81, 137, 0.85)',
                    borderColor: '#405189',
                    borderWidth: 0,
                    borderRadius: 4,
                    barPercentage: 0.6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { callback: function(v) { return (v/1000000).toFixed(0) + 'M'; } } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    var taskCtx = document.getElementById('taskChart');
    if (taskCtx) {
        new Chart(taskCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cần làm', 'Đang làm', 'Review', 'Hoàn thành'],
                datasets: [{
                    data: <?= json_encode($taskStats ?? [0, 0, 0, 0]) ?>,
                    backgroundColor: ['#405189', '#f7b84b', '#299cdb', '#0ab39c'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: { legend: { position: 'bottom', labels: { padding: 15, usePointStyle: true, pointStyle: 'circle' } } }
            }
        });
    }
});
</script>
