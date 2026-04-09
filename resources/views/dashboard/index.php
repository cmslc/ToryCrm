<?php $pageTitle = 'Dashboard'; ?>

<!-- Greeting -->
<div class="row">
    <div class="col-12">
        <div class="d-flex align-items-lg-center flex-lg-row flex-column mb-4">
            <div class="flex-grow-1">
                <h4 class="fs-16 mb-1">Chào <?= e($_SESSION['user']['name'] ?? '') ?>!</h4>
                <p class="text-muted mb-0"><?= date('l, d/m/Y') ?> - Tổng quan hoạt động kinh doanh hôm nay.</p>
            </div>
            <div class="mt-3 mt-lg-0">
                <div class="d-flex gap-2">
                    <a href="<?= url('deals/create') ?>" class="btn btn-soft-success"><i class="ri-add-line align-middle me-1"></i> Cơ hội mới</a>
                    <a href="<?= url('contacts/create') ?>" class="btn btn-soft-info"><i class="ri-user-add-line align-middle me-1"></i> Khách hàng mới</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROW 1: Smart Insights -->
<?php if (!empty($insights)): ?>
<div class="row" id="insights-row">
    <?php foreach ($insights as $insight): ?>
        <div class="col-xl-3 col-md-6" id="insight-<?= $insight['id'] ?>">
            <div class="card border-<?= e($insight['color'] ?? 'primary') ?> border-opacity-25">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <div class="avatar-xs">
                                <div class="avatar-title rounded-circle bg-<?= e($insight['color'] ?? 'primary') ?>-subtle text-<?= e($insight['color'] ?? 'primary') ?>">
                                    <i class="<?= e($insight['icon'] ?? 'ri-lightbulb-line') ?>"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3 overflow-hidden">
                            <h6 class="mb-1 text-truncate"><?= e($insight['title']) ?></h6>
                            <p class="text-muted fs-12 mb-2"><?= e($insight['message']) ?></p>
                            <?php if (!empty($insight['action_url'])): ?>
                                <a href="<?= url(ltrim($insight['action_url'], '/')) ?>" class="btn btn-<?= e($insight['color'] ?? 'primary') ?> btn-soft-<?= e($insight['color'] ?? 'primary') ?>"><?= e($insight['action_label'] ?? 'Xem') ?></a>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn-close ms-2" onclick="dismissInsight(<?= $insight['id'] ?>)" aria-label="Đóng"></button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ROW 2: Stat Cards with trend -->
<div class="row">
    <?php
    $statCards = [
        [
            'label' => 'Khách hàng',
            'value' => $stats['total_contacts'] ?? 0,
            'change' => $stats['contacts_change'] ?? 0,
            'format' => 'number',
            'icon' => 'ri-contacts-book-2-line',
            'color' => 'primary',
            'link' => 'contacts',
        ],
        [
            'label' => 'Cơ hội đang mở',
            'value' => $stats['total_deals'] ?? 0,
            'change' => $stats['deals_change'] ?? 0,
            'format' => 'number',
            'icon' => 'ri-hand-coin-line',
            'color' => 'warning',
            'link' => 'deals',
        ],
        [
            'label' => 'Doanh thu',
            'value' => $stats['total_revenue'] ?? 0,
            'change' => $stats['revenue_change'] ?? 0,
            'format' => 'money',
            'icon' => 'ri-money-dollar-circle-line',
            'color' => 'success',
            'link' => 'reports/revenue',
        ],
        [
            'label' => 'Đơn hàng',
            'value' => $stats['total_orders'] ?? 0,
            'change' => $stats['orders_change'] ?? 0,
            'format' => 'number',
            'icon' => 'ri-shopping-bag-line',
            'color' => 'info',
            'link' => 'orders',
        ],
    ];
    ?>
    <?php foreach ($statCards as $card): ?>
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0"><?= $card['label'] ?></p>
                        </div>
                        <div class="flex-shrink-0">
                            <?php
                            $changeVal = (int)$card['change'];
                            $changeColor = $changeVal >= 0 ? 'success' : 'danger';
                            $changeIcon = $changeVal >= 0 ? 'ri-arrow-up-line' : 'ri-arrow-down-line';
                            ?>
                            <span class="badge bg-<?= $changeColor ?>-subtle text-<?= $changeColor ?>">
                                <i class="<?= $changeIcon ?> align-middle"></i> <?= abs($changeVal) ?>%
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-4">
                        <div>
                            <h4 class="fs-22 fw-semibold ff-secondary mb-4">
                                <?php if ($card['format'] === 'money'): ?>
                                    <?= format_money($card['value']) ?>
                                <?php else: ?>
                                    <span class="counter-value" data-target="<?= $card['value'] ?>">0</span>
                                <?php endif; ?>
                            </h4>
                            <a href="<?= url($card['link']) ?>" class="text-decoration-underline text-muted">Xem tất cả</a>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-<?= $card['color'] ?>-subtle rounded fs-3">
                                <i class="<?= $card['icon'] ?> text-<?= $card['color'] ?>"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ROW 3: Revenue Chart + Sales Funnel -->
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
                <h4 class="card-title mb-0 flex-grow-1">Phễu bán hàng</h4>
                <a href="<?= url('deals/pipeline') ?>" class="btn btn-soft-primary">Xem Pipeline</a>
            </div>
            <div class="card-body">
                <?php if (!empty($pipelineSummary)): ?>
                    <?php
                    $maxCount = max(array_column($pipelineSummary, 'count') ?: [1]);
                    if ($maxCount === 0) $maxCount = 1;
                    ?>
                    <?php foreach ($pipelineSummary as $stage): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fs-13 fw-medium"><?= e($stage['name']) ?></span>
                                <span class="text-muted fs-12"><?= $stage['count'] ?? 0 ?> cơ hội - <?= format_money($stage['total_value'] ?? 0) ?></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: <?= round(($stage['count'] / $maxCount) * 100) ?>%; background-color: <?= safe_color($stage['color'] ?? null) ?>"
                                     aria-valuenow="<?= $stage['count'] ?>" aria-valuemin="0" aria-valuemax="<?= $maxCount ?>">
                                </div>
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

<!-- ROW 4: Action Items + Health Score -->
<div class="row">
    <div class="col-xl-6">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-alarm-warning-line me-1 text-danger"></i> Cần hành động</h4>
            </div>
            <div class="card-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-overdue" role="tab">
                            Quá hạn <span class="badge bg-danger rounded-circle"><?= count($overdueTasks ?? []) ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-closing" role="tab">
                            Sắp chốt <span class="badge bg-warning rounded-circle"><?= count($dealsClosingSoon ?? []) ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-inactive" role="tab">
                            Không hoạt động <span class="badge bg-secondary rounded-circle"><?= count($inactiveContacts ?? []) ?></span>
                        </a>
                    </li>
                </ul>
                <div class="tab-content pt-3" style="max-height: 320px; overflow-y: auto;">
                    <!-- Overdue Tasks -->
                    <div class="tab-pane active" id="tab-overdue" role="tabpanel">
                        <?php if (!empty($overdueTasks)): ?>
                            <?php foreach ($overdueTasks as $task): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <a href="<?= url('tasks/' . $task['id']) ?>" class="text-dark fw-medium text-truncate d-block"><?= e($task['title']) ?></a>
                                        <small class="text-danger"><i class="ri-time-line me-1"></i><?= format_datetime($task['due_date']) ?></small>
                                        <?php if (!empty($task['assigned_name'])): ?>
                                            <small class="text-muted ms-2"><i class="ri-user-line me-1"></i><?= e($task['assigned_name']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <?php $pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger']; ?>
                                        <span class="badge bg-<?= $pc[$task['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$task['priority']] ?? 'secondary' ?>">
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

                    <!-- Deals Closing Soon -->
                    <div class="tab-pane" id="tab-closing" role="tabpanel">
                        <?php if (!empty($dealsClosingSoon)): ?>
                            <?php foreach ($dealsClosingSoon as $deal): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <a href="<?= url('deals/' . $deal['id']) ?>" class="text-dark fw-medium text-truncate d-block"><?= e($deal['title']) ?></a>
                                        <small class="text-warning"><i class="ri-calendar-line me-1"></i><?= date('d/m/Y', strtotime($deal['expected_close_date'])) ?></small>
                                        <small class="text-muted ms-2"><?= format_money($deal['value'] ?? 0) ?></small>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="text-muted fs-12"><?= e(trim(($deal['first_name'] ?? '') . ' ' . ($deal['last_name'] ?? ''))) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="ri-calendar-check-line text-success" style="font-size:48px"></i>
                                <p class="text-muted mb-0 mt-2">Không có cơ hội sắp chốt</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Inactive Contacts -->
                    <div class="tab-pane" id="tab-inactive" role="tabpanel">
                        <?php if (!empty($inactiveContacts)): ?>
                            <?php foreach ($inactiveContacts as $contact): ?>
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <a href="<?= url('contacts/' . $contact['id']) ?>" class="text-dark fw-medium text-truncate d-block">
                                            <?= e(trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? ''))) ?>
                                        </a>
                                        <small class="text-muted"><?= e($contact['email'] ?? '') ?></small>
                                    </div>
                                    <div class="flex-shrink-0 ms-2">
                                        <span class="badge bg-secondary-subtle text-secondary"><?= $contact['days_inactive'] ?> ngày</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="ri-user-heart-line text-success" style="font-size:48px"></i>
                                <p class="text-muted mb-0 mt-2">Tất cả khách hàng đều hoạt động</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-heart-pulse-line me-1 text-danger"></i> Sức khỏe khách hàng</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5 d-flex align-items-center justify-content-center">
                        <div style="width:100%;max-width:200px">
                            <canvas id="healthChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex flex-column gap-3 mt-3 mt-md-0">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2" style="width:12px;height:12px;padding:0;border-radius:50%"></span>
                                <span class="flex-grow-1">Thấp (Tốt)</span>
                                <span class="fw-semibold"><?= $healthDist['low'] ?? 0 ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2" style="width:12px;height:12px;padding:0;border-radius:50%"></span>
                                <span class="flex-grow-1">Trung bình</span>
                                <span class="fw-semibold"><?= $healthDist['medium'] ?? 0 ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2" style="width:12px;height:12px;padding:0;border-radius:50%"></span>
                                <span class="flex-grow-1">Cao</span>
                                <span class="fw-semibold"><?= $healthDist['high'] ?? 0 ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2" style="width:12px;height:12px;padding:0;border-radius:50%"></span>
                                <span class="flex-grow-1">Nghiêm trọng</span>
                                <span class="fw-semibold"><?= $healthDist['critical'] ?? 0 ?></span>
                            </div>
                        </div>

                        <?php if (!empty($criticalContacts)): ?>
                            <hr class="my-3">
                            <h6 class="text-danger mb-2">Khách hàng cần chú ý:</h6>
                            <?php foreach ($criticalContacts as $cc): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <a href="<?= url('contacts/' . $cc['id']) ?>" class="text-dark fw-medium flex-grow-1 text-truncate">
                                        <?= e(trim(($cc['first_name'] ?? '') . ' ' . ($cc['last_name'] ?? ''))) ?>
                                    </a>
                                    <span class="badge bg-<?= $cc['churn_risk'] === 'critical' ? 'danger' : 'warning' ?>-subtle text-<?= $cc['churn_risk'] === 'critical' ? 'danger' : 'warning' ?> ms-2">
                                        <?= $cc['overall_score'] ?>/100
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROW 5: Activity Timeline + Today's Calendar -->
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Hoạt động gần đây</h4>
                <a href="<?= url('activities') ?>" class="btn btn-soft-primary">Xem tất cả</a>
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

    <div class="col-xl-6">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1"><i class="ri-calendar-2-line me-1 text-primary"></i> Lịch hẹn hôm nay</h4>
                <a href="<?= url('calendar') ?>" class="btn btn-soft-primary">Xem lịch</a>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
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

    // Health Score Pie Chart
    var healthCtx = document.getElementById('healthChart');
    if (healthCtx) {
        new Chart(healthCtx, {
            type: 'doughnut',
            data: {
                labels: ['Thấp (Tốt)', 'Trung bình', 'Cao', 'Nghiêm trọng'],
                datasets: [{
                    data: [
                        <?= (int)($healthDist['low'] ?? 0) ?>,
                        <?= (int)($healthDist['medium'] ?? 0) ?>,
                        <?= (int)($healthDist['high'] ?? 0) ?>,
                        <?= (int)($healthDist['critical'] ?? 0) ?>
                    ],
                    backgroundColor: ['#0ab39c', '#299cdb', '#f7b84b', '#f06548'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});

// Dismiss insight via AJAX
function dismissInsight(id) {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                 || document.querySelector('input[name="_token"]')?.value
                 || '';

    fetch('/insights/' + id + '/dismiss', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: '_token=' + encodeURIComponent(csrfToken)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            var el = document.getElementById('insight-' + id);
            if (el) {
                el.style.transition = 'opacity 0.3s';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 300);
            }
        }
    })
    .catch(function() {});
}
</script>
