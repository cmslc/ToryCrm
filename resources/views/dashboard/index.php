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

<div class="row">
<div class="col-xl-10">
<!-- ROW 1: Smart Insights -->
<?php if (!empty($insights)): ?>
<div class="row" id="insights-row">
    <?php foreach ($insights as $insight): ?>
        <div class="col-xl-4 col-md-6" id="insight-<?= $insight['id'] ?>">
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
            'link' => ($_SESSION['user']['role'] ?? 'staff') !== 'staff' ? 'reports/revenue' : 'deals',
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
        <div class="col-md-6 col-xl-3">
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
                <h4 class="card-title mb-0 flex-grow-1">Doanh thu</h4>
                <div class="d-flex gap-1">
                    <button class="btn btn-soft-secondary btn-sm active" data-period="1y">1Y</button>
                    <button class="btn btn-soft-secondary btn-sm" data-period="6m">6M</button>
                    <button class="btn btn-soft-secondary btn-sm" data-period="1m">1M</button>
                </div>
            </div>
            <div class="card-body">
                <?php
                $yearRevenue = array_sum($revenueData ?? []);
                $yearOrders = $stats['total_orders'] ?? 0;
                $wonDeals = (int)(\Core\Database::fetch("SELECT COUNT(*) as c FROM deals WHERE status='won' AND tenant_id=?", [$_SESSION['tenant_id'] ?? 1])['c'] ?? 0);
                $convRate = $stats['total_deals'] > 0 ? round($wonDeals / ($stats['total_deals'] + $wonDeals) * 100, 1) : 0;
                ?>
                <div class="row text-center mb-3">
                    <div class="col-3">
                        <h5 class="mb-1 ff-secondary"><?= number_format($yearOrders) ?></h5>
                        <p class="text-muted mb-0 fs-12">Đơn hàng</p>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-1 text-success ff-secondary"><?= format_money($yearRevenue) ?></h5>
                        <p class="text-muted mb-0 fs-12">Doanh thu</p>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-1 ff-secondary"><?= $wonDeals ?></h5>
                        <p class="text-muted mb-0 fs-12">Deal thắng</p>
                    </div>
                    <div class="col-3">
                        <h5 class="mb-1 text-success ff-secondary"><?= $convRate ?>%</h5>
                        <p class="text-muted mb-0 fs-12">Tỷ lệ chuyển đổi</p>
                    </div>
                </div>
                <div style="height:300px">
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

<!-- ROW 4: Deal Status + Source + Task Rate -->
<div class="row">
    <!-- Deal Status Donut -->
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Trạng thái Deal</h4>
            </div>
            <div class="card-body">
                <div style="height:220px" class="d-flex justify-content-center"><canvas id="dealStatusChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Contact Source -->
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Nguồn khách hàng</h4>
            </div>
            <div class="card-body">
                <div style="height:220px" class="d-flex justify-content-center"><canvas id="sourceChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Task Completion Rate -->
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Tiến độ công việc</h4>
            </div>
            <div class="card-body text-center">
                <?php
                $tPct = ($taskStats['total'] ?? 0) > 0 ? round(($taskStats['done'] ?? 0) / $taskStats['total'] * 100) : 0;
                $twPct = ($taskStats['week_total'] ?? 0) > 0 ? round(($taskStats['week_done'] ?? 0) / $taskStats['week_total'] * 100) : 0;
                ?>
                <div style="height:180px" class="d-flex justify-content-center"><canvas id="taskRateChart"></canvas></div>
                <h5 class="mt-3 mb-1"><?= $tPct ?>%</h5>
                <p class="text-muted mb-3">Tổng hoàn thành (<?= $taskStats['done'] ?? 0 ?>/<?= $taskStats['total'] ?? 0 ?>)</p>
                <div class="row text-start border-top pt-3">
                    <div class="col-6">
                        <p class="text-muted mb-1 fs-12">Tuần này</p>
                        <h6 class="mb-0"><?= $taskStats['week_done'] ?? 0 ?>/<?= $taskStats['week_total'] ?? 0 ?> <small class="text-success">(<?= $twPct ?>%)</small></h6>
                    </div>
                    <div class="col-6">
                        <p class="text-muted mb-1 fs-12">Quá hạn</p>
                        <h6 class="mb-0 text-danger"><?= count($overdueTasks ?? []) ?></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROW 5: Top Staff -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Top nhân viên theo doanh thu</h4>
                <a href="<?= url('leaderboard') ?>" class="btn btn-soft-primary">Bảng xếp hạng</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Nhân viên</th><th>Deal thắng</th><th>Doanh thu</th><th>Tiến độ</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $maxRev = max(array_column($topStaff ?? [], 'revenue') ?: [1]) ?: 1;
                            foreach ($topStaff ?? [] as $i => $staff): ?>
                            <tr>
                                <td><span class="fw-semibold"><?= $i + 1 ?></span></td>
                                <td><?= user_avatar($staff['name'] ?? null, 'primary', $staff['avatar'] ?? null) ?></td>
                                <td><span class="badge bg-success-subtle text-success"><?= $staff['deal_count'] ?></span></td>
                                <td class="fw-semibold"><?= format_money($staff['revenue']) ?></td>
                                <td style="min-width:120px">
                                    <div class="progress" style="height:6px">
                                        <div class="progress-bar bg-primary" style="width:<?= round($staff['revenue'] / $maxRev * 100) ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($topStaff)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">Chưa có dữ liệu</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROW 6: Action Items + Health Score -->
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

<!-- Today's Calendar -->
<div class="row">
    <div class="col-12">
        <div class="card">
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
                    <div class="text-center py-3 text-muted">
                        <i class="ri-calendar-line fs-1 d-block mb-2"></i>
                        Không có lịch hẹn hôm nay
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div><!-- end col-xl-10 -->

<!-- Right sidebar: Recent Activity (Velzon style) -->
<div class="col-xl-2">
    <div class="card">
        <div class="card-header border-bottom-dashed align-items-center d-flex">
            <h4 class="card-title mb-0 flex-grow-1 fs-14">Hoạt động</h4>
            <a href="<?= url('activities') ?>" class="text-muted fs-12">Xem tất cả <i class="ri-arrow-right-s-line"></i></a>
        </div>
        <div class="card-body p-0">
            <div>
                <?php if (!empty($recentActivities)): ?>
                    <?php
                    $icons = ['note'=>'ri-file-text-line','call'=>'ri-phone-line','email'=>'ri-mail-line','meeting'=>'ri-calendar-line','task'=>'ri-task-line','deal'=>'ri-hand-coin-line','contact'=>'ri-user-add-line','system'=>'ri-settings-3-line','order'=>'ri-shopping-cart-line'];
                    $colors = ['note'=>'primary','call'=>'success','email'=>'info','meeting'=>'warning','task'=>'danger','deal'=>'success','contact'=>'info','system'=>'secondary','order'=>'warning'];

                    $lastDate = '';
                    foreach ($recentActivities as $a):
                        $dateKey = date('Y-m-d', strtotime($a['created_at']));
                        $today = date('Y-m-d');
                        $yesterday = date('Y-m-d', strtotime('-1 day'));
                        if ($dateKey !== $lastDate):
                            $lastDate = $dateKey;
                    ?>
                        <p class="text-muted fs-11 fw-semibold mb-0 px-3 pt-3 pb-1 text-uppercase">
                            <?= date('h:i A', strtotime($a['created_at'])) ?>
                            <?php if ($dateKey === $today) echo 'Hôm nay'; elseif ($dateKey === $yesterday) echo 'Hôm qua'; else echo date('d/m/Y', strtotime($dateKey)); ?>
                        </p>
                    <?php endif;
                        $userName = $a['user_name'] ?? '';
                        $avatarHtml = '';
                        if ($userName) {
                            static $dashAvatarCache2 = [];
                            if (!isset($dashAvatarCache2[$userName])) {
                                try { $dashAvatarCache2[$userName] = \Core\Database::fetch("SELECT avatar FROM users WHERE name = ? LIMIT 1", [$userName])['avatar'] ?? ''; } catch (\Exception $e) { $dashAvatarCache2[$userName] = ''; }
                            }
                            $av = $dashAvatarCache2[$userName];
                            if ($av && file_exists(BASE_PATH . '/public/uploads/avatars/' . $av)) {
                                $avatarHtml = '<img src="' . url('uploads/avatars/' . $av) . '" class="rounded-circle" style="width:32px;height:32px;object-fit:cover">';
                            } else {
                                $c = $colors[$a['type']] ?? 'primary';
                                $avatarHtml = '<div class="avatar-title rounded-circle bg-' . $c . '-subtle text-' . $c . '" style="width:32px;height:32px;font-size:13px">' . mb_strtoupper(mb_substr($userName, 0, 1)) . '</div>';
                            }
                        } else {
                            $avatarHtml = '<div class="avatar-title rounded-circle bg-secondary-subtle text-secondary" style="width:32px;height:32px"><i class="' . ($icons[$a['type']] ?? 'ri-file-text-line') . ' fs-14"></i></div>';
                        }
                    ?>
                    <div class="d-flex px-3 py-2 border-bottom">
                        <div class="flex-shrink-0 me-2"><?= $avatarHtml ?></div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-1 fs-13 text-truncate"><?php if ($userName): ?><span class="fw-semibold"><?= e($userName) ?></span> <?php endif; ?><?= e($a['title']) ?></h6>
                            <?php if (!empty($a['description'])): ?>
                                <p class="text-muted mb-1 fs-12 text-truncate"><?= e(mb_substr($a['description'], 0, 60)) ?></p>
                            <?php endif; ?>
                            <small class="text-muted"><?= date('d M, Y', strtotime($a['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5 px-3 text-muted">
                        <i class="ri-history-line fs-1 d-block mb-2"></i>
                        Chưa có hoạt động
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <?php
        $openTickets = 0;
        try { $openTickets = (int)(\Core\Database::fetch("SELECT COUNT(*) as c FROM tickets WHERE tenant_id = ? AND status IN ('open','in_progress')", [$_SESSION['tenant_id'] ?? 1])['c'] ?? 0); } catch (\Exception $e) {}
        $pendingApprovals = 0;
        try { $pendingApprovals = (int)(\Core\Database::fetch("SELECT COUNT(*) as c FROM approval_requests WHERE tenant_id = ? AND status = 'pending'", [$_SESSION['tenant_id'] ?? 1])['c'] ?? 0); } catch (\Exception $e) {}
        $newContactsWeek = 0;
        try { $newContactsWeek = (int)(\Core\Database::fetch("SELECT COUNT(*) as c FROM contacts WHERE tenant_id = ? AND is_deleted = 0 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)", [$_SESSION['tenant_id'] ?? 1])['c'] ?? 0); } catch (\Exception $e) {}
        ?>
        <div class="card-body border-top p-3">
            <h6 class="text-uppercase fs-11 text-muted mb-3">Tổng quan</h6>
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-xs me-2"><span class="avatar-title rounded bg-danger-subtle text-danger"><i class="ri-customer-service-line"></i></span></div>
                <div class="flex-grow-1"><p class="mb-0 fs-12 text-muted">Ticket mở</p></div>
                <h6 class="mb-0"><?= $openTickets ?></h6>
            </div>
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-xs me-2"><span class="avatar-title rounded bg-warning-subtle text-warning"><i class="ri-checkbox-circle-line"></i></span></div>
                <div class="flex-grow-1"><p class="mb-0 fs-12 text-muted">Chờ duyệt</p></div>
                <h6 class="mb-0"><?= $pendingApprovals ?></h6>
            </div>
            <div class="d-flex align-items-center">
                <div class="avatar-xs me-2"><span class="avatar-title rounded bg-info-subtle text-info"><i class="ri-user-add-line"></i></span></div>
                <div class="flex-grow-1"><p class="mb-0 fs-12 text-muted">KH mới (7 ngày)</p></div>
                <h6 class="mb-0"><?= $newContactsWeek ?></h6>
            </div>
        </div>

        <!-- Upcoming Events -->
        <?php
        $upcomingEvents = [];
        try {
            $upcomingEvents = \Core\Database::fetchAll(
                "SELECT title, start_at, color FROM calendar_events WHERE (user_id = ? OR created_by = ?) AND start_at > NOW() ORDER BY start_at LIMIT 4",
                [$_SESSION['user']['id'] ?? 0, $_SESSION['user']['id'] ?? 0]
            );
        } catch (\Exception $e) {}
        ?>
        <?php if (!empty($upcomingEvents)): ?>
        <div class="card-body border-top p-3">
            <h6 class="text-uppercase fs-11 text-muted mb-3">Lịch sắp tới</h6>
            <?php foreach ($upcomingEvents as $ev): ?>
            <div class="d-flex align-items-center mb-2">
                <span class="me-2" style="width:8px;height:8px;border-radius:50%;background:<?= safe_color($ev['color']) ?>;display:inline-block;flex-shrink:0"></span>
                <div class="flex-grow-1 overflow-hidden">
                    <p class="mb-0 fs-12 text-truncate"><?= e($ev['title']) ?></p>
                    <small class="text-muted fs-11"><?= date('d/m H:i', strtotime($ev['start_at'])) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div><!-- end col-xl-2 -->
</div><!-- end row -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart - Velzon style (bar + line + area)
    var revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        var revenueRaw = <?= json_encode($revenueData ?? array_fill(0, 12, 0)) ?>;
        <?php
        // Orders per month
        $orderMonthly = array_fill(0, 12, 0);
        try {
            $omRows = \Core\Database::fetchAll("SELECT MONTH(created_at) as m, COUNT(*) as c FROM orders WHERE tenant_id = ? AND YEAR(created_at) = YEAR(CURDATE()) AND is_deleted = 0 GROUP BY MONTH(created_at)", [$_SESSION['tenant_id'] ?? 1]);
            foreach ($omRows as $om) $orderMonthly[$om['m'] - 1] = (int)$om['c'];
        } catch (\Exception $e) {}
        // Tasks completed per month
        $taskMonthly = array_fill(0, 12, 0);
        try {
            $tmRows = \Core\Database::fetchAll("SELECT MONTH(completed_at) as m, COUNT(*) as c FROM tasks WHERE tenant_id = ? AND status = 'done' AND YEAR(completed_at) = YEAR(CURDATE()) GROUP BY MONTH(completed_at)", [$_SESSION['tenant_id'] ?? 1]);
            foreach ($tmRows as $tm) $taskMonthly[$tm['m'] - 1] = (int)$tm['c'];
        } catch (\Exception $e) {}
        ?>
        var ordersData = <?= json_encode($orderMonthly) ?>;
        var tasksData = <?= json_encode($taskMonthly) ?>;

        new Chart(revenueCtx, {
            data: {
                labels: ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'],
                datasets: [
                    {
                        type: 'bar',
                        label: 'Doanh thu',
                        data: revenueRaw,
                        backgroundColor: 'rgba(10, 179, 156, 0.8)',
                        borderColor: '#0ab39c',
                        borderWidth: 0,
                        borderRadius: 3,
                        barPercentage: 0.5,
                        order: 2,
                    },
                    {
                        type: 'line',
                        label: 'Đơn hàng',
                        data: ordersData,
                        borderColor: '#405189',
                        backgroundColor: 'rgba(64, 81, 137, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 0,
                        yAxisID: 'y1',
                        order: 1,
                    },
                    {
                        type: 'line',
                        label: 'Năm trước',
                        data: <?= json_encode($lastMonthRevenueData ?? array_fill(0, 12, 0)) ?>,
                        borderColor: '#f06548',
                        borderDash: [5, 5],
                        borderWidth: 2,
                        pointRadius: 0,
                        fill: false,
                        tension: 0.4,
                        order: 0,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, font: { size: 12 } } }
                },
                scales: {
                    y: { beginAtZero: true, position: 'left', grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { callback: function(v) { return v >= 1000000 ? (v/1000000).toFixed(0) + 'M' : v >= 1000 ? (v/1000).toFixed(0) + 'k' : v; } } },
                    y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { stepSize: 1 } },
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

    // Deal Status Donut
    // Shared datalabels config for donut charts
    var donutLabelOpts = {
        color: '#fff',
        font: { weight: 'bold', size: 12 },
        formatter: function(value, ctx) {
            var sum = ctx.dataset.data.reduce(function(a,b){return a+b},0);
            return sum > 0 ? (value/sum*100).toFixed(1)+'%' : '';
        },
        display: function(ctx) {
            var sum = ctx.dataset.data.reduce(function(a,b){return a+b},0);
            return sum > 0 && ctx.dataset.data[ctx.dataIndex] > 0;
        }
    };

    // Deal Status Donut
    var dealCtx = document.getElementById('dealStatusChart');
    if (dealCtx) {
        <?php
        $dsData = []; $dsLbl = []; $dsCl = [];
        $dsMap = ['open'=>['Đang mở','#405189'],'won'=>['Thắng','#0ab39c'],'lost'=>['Thua','#f06548']];
        foreach ($dealStatusDist ?? [] as $ds) {
            $dsLbl[] = $dsMap[$ds['status']][0] ?? $ds['status'];
            $dsData[] = (int)$ds['count'];
            $dsCl[] = $dsMap[$ds['status']][1] ?? '#6c757d';
        }
        ?>
        new Chart(dealCtx, {
            type: 'doughnut',
            plugins: [ChartDataLabels],
            data: { labels: <?= json_encode($dsLbl) ?>, datasets: [{ data: <?= json_encode($dsData) ?>, backgroundColor: <?= json_encode($dsCl) ?>, borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, font: {size:11} } }, datalabels: donutLabelOpts } }
        });
    }

    // Source Donut
    var srcCtx = document.getElementById('sourceChart');
    if (srcCtx) {
        var srcColors = ['#405189','#0ab39c','#f7b84b','#f06548','#299cdb','#6c757d','#3577f1','#e83e8c'];
        new Chart(srcCtx, {
            type: 'doughnut',
            plugins: [ChartDataLabels],
            data: {
                labels: <?= json_encode(array_column($sourceDist ?? [], 'source_name')) ?>,
                datasets: [{ data: <?= json_encode(array_column($sourceDist ?? [], 'count')) ?>, backgroundColor: srcColors.slice(0, <?= count($sourceDist ?? []) ?>), borderWidth: 2, borderColor: '#fff' }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, font: {size:11} } }, datalabels: donutLabelOpts } }
        });
    }

    // Task Rate Ring
    var taskCtx = document.getElementById('taskRateChart');
    if (taskCtx) {
        var done = <?= $taskStats['done'] ?? 0 ?>, remaining = <?= ($taskStats['total'] ?? 0) - ($taskStats['done'] ?? 0) ?>;
        new Chart(taskCtx, {
            type: 'doughnut',
            plugins: [ChartDataLabels],
            data: { labels: ['Hoàn thành','Còn lại'], datasets: [{ data: [done, remaining], backgroundColor: ['#0ab39c','#e9ebec'], borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false }, datalabels: { color: function(ctx){return ctx.dataIndex===0?'#fff':'#878a99'}, font:{weight:'bold',size:13}, formatter: function(v,ctx){var s=ctx.dataset.data.reduce(function(a,b){return a+b},0); return s>0?(v/s*100).toFixed(0)+'%':'';}, display: function(ctx){return ctx.dataset.data[ctx.dataIndex]>0;} } } }
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
