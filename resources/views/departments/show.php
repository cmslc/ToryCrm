<?php
$pageTitle = e($department['name']);
$posMap = [];
foreach ($positions ?? [] as $p) { $posMap[$p['user_id']] = $p; }

// Build position tree (parent_id in department_positions)
$posList = [];
try {
    $posList = \Core\Database::fetchAll(
        "SELECT dp.*, u.name as user_name, u.avatar FROM department_positions dp LEFT JOIN users u ON dp.user_id = u.id WHERE dp.department_id = ? ORDER BY dp.sort_order, dp.position",
        [$department['id']]
    );
} catch (\Exception $e) { $posList = $positions ?? []; }
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0"><?= e($department['name']) ?></h4>
        <?php if ($department['parent_name']): ?><p class="text-muted mb-0"><i class="ri-arrow-up-line me-1"></i><?= e($department['parent_name']) ?></p><?php endif; ?>
    </div>
    <a href="<?= url('departments') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<!-- Stat Cards -->
<div class="row">
    <?php
    $taskPct = ($stats['tasks_total'] ?? 0) > 0 ? round($stats['tasks_done'] / $stats['tasks_total'] * 100) : 0;
    $statCards = [
        ['label'=>'Thành viên','value'=>count($members),'icon'=>'ri-team-line','color'=>'primary'],
        ['label'=>'Khách hàng','value'=>$stats['contacts'],'icon'=>'ri-contacts-line','color'=>'info'],
        ['label'=>'Deal thắng','value'=>$stats['deals'],'icon'=>'ri-trophy-line','color'=>'success'],
        ['label'=>'Doanh thu','value'=>format_money($stats['revenue']),'icon'=>'ri-money-dollar-circle-line','color'=>'warning','raw'=>true],
    ];
    foreach ($statCards as $sc): ?>
    <div class="col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3"><span class="avatar-title bg-<?= $sc['color'] ?>-subtle rounded-circle"><i class="<?= $sc['icon'] ?> text-<?= $sc['color'] ?> fs-20"></i></span></div>
                    <div>
                        <p class="text-muted mb-0 text-uppercase fs-11"><?= $sc['label'] ?></p>
                        <h4 class="mb-0"><?= $sc['raw'] ?? false ? $sc['value'] : number_format($sc['value']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Tabs -->
<div class="card">
    <div class="card-header p-0">
        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabInfo" role="tab">Thông tin</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabMembers" role="tab">Nhân viên <span class="badge bg-primary-subtle text-primary ms-1"><?= count($members) ?></span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabKpi" role="tab">KPI</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabHistory" role="tab">Lịch sử</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <!-- Tab: Thông tin -->
            <div class="tab-pane active" id="tabInfo" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th class="text-muted" width="140">Tên</th><td class="fw-medium"><?= e($department['name']) ?></td></tr>
                            <tr><th class="text-muted">Trưởng phòng</th><td><?= user_avatar($department['manager_name'] ?? null, 'primary', $department['manager_avatar'] ?? null) ?></td></tr>
                            <tr><th class="text-muted">Phó phòng</th><td><?= user_avatar($department['vice_manager_name'] ?? null, 'info', $department['vice_manager_avatar'] ?? null) ?></td></tr>
                            <?php if ($department['parent_name']): ?><tr><th class="text-muted">Thuộc</th><td><?= e($department['parent_name']) ?></td></tr><?php endif; ?>
                            <tr><th class="text-muted">Màu</th><td><span class="d-inline-block rounded-circle me-2" style="width:14px;height:14px;background:<?= e($department['color']) ?>"></span><?= e($department['color']) ?></td></tr>
                            <?php if ($department['description']): ?><tr><th class="text-muted">Mô tả</th><td><?= e($department['description']) ?></td></tr><?php endif; ?>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Tiến độ công việc</h6>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="progress flex-grow-1" style="height:10px"><div class="progress-bar bg-success" style="width:<?= $taskPct ?>%"></div></div>
                            <span class="fw-semibold"><?= $taskPct ?>%</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted fs-12 mb-4">
                            <span>Hoàn thành: <?= $stats['tasks_done'] ?></span><span>Tổng: <?= $stats['tasks_total'] ?></span>
                        </div>

                        <?php if (!empty($childDepts)): ?>
                        <h6 class="mb-3">Phòng ban con</h6>
                        <?php foreach ($childDepts as $cd): ?>
                        <div class="d-flex align-items-center mb-2">
                            <span class="d-inline-block rounded-circle me-2" style="width:8px;height:8px;background:<?= e($cd['color']) ?>"></span>
                            <a href="<?= url('departments/' . $cd['id']) ?>" class="flex-grow-1"><?= e($cd['name']) ?></a>
                            <span class="badge bg-secondary-subtle text-secondary"><?= $cd['member_count'] ?></span>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Nhân viên + Vị trí (gộp) -->
            <div class="tab-pane" id="tabMembers" role="tabpanel">
                <?php
                $posOptions = ['Trưởng nhóm','Phó nhóm','Chuyên viên cao cấp','Chuyên viên','Kỹ sư','Kế toán','NV kinh doanh','Tư vấn viên','CSKH','Thực tập sinh','Cố vấn','Giám sát'];
                $allUsers = \Core\Database::fetchAll("SELECT id, name FROM users WHERE tenant_id = ? AND is_active = 1 AND (department_id IS NULL OR department_id != ?) ORDER BY name", [$_SESSION['tenant_id'] ?? 1, $department['id']]);
                ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Nhân viên</th><th style="width:200px">Chức vụ</th><th>Đăng nhập cuối</th><th style="width:60px"></th></tr></thead>
                        <tbody>
                        <?php foreach ($members as $m):
                            $curPos = '';
                            if ($m['id'] == $department['manager_id']) { $curPos = 'Trưởng phòng'; }
                            elseif ($m['id'] == $department['vice_manager_id']) { $curPos = 'Phó phòng'; }
                            elseif (isset($posMap[$m['id']])) { $curPos = $posMap[$m['id']]['position']; }
                            $isFixed = ($m['id'] == $department['manager_id'] || $m['id'] == $department['vice_manager_id']);
                        ?>
                        <tr>
                            <td><?= user_avatar($m['name'] ?? null, 'primary', $m['avatar'] ?? null) ?></td>
                            <td>
                                <?php if ($isFixed): ?>
                                    <span class="badge bg-<?= $m['id'] == $department['manager_id'] ? 'danger' : 'warning' ?>-subtle text-<?= $m['id'] == $department['manager_id'] ? 'danger' : 'warning' ?>"><?= e($curPos) ?></span>
                                <?php else: ?>
                                    <form method="POST" action="<?= url('departments/' . $department['id'] . '/positions') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $m['id'] ?>">
                                        <select name="position" class="form-select" style="width:auto;min-width:150px" onchange="this.form.submit()">
                                            <option value="">Nhân viên</option>
                                            <?php foreach ($posOptions as $po): ?>
                                                <option value="<?= e($po) ?>" <?= $curPos === $po ? 'selected' : '' ?>><?= e($po) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted fs-12"><?= $m['last_login'] ? time_ago($m['last_login']) : '-' ?></td>
                            <td>
                                <form method="POST" action="<?= url('departments/' . $department['id'] . '/members/' . $m['id'] . '/remove') ?>" data-confirm="Xóa <?= e($m['name']) ?>?" class="d-inline">
                                    <?= csrf_field() ?><button class="btn btn-soft-danger btn-icon"><i class="ri-user-unfollow-line"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?><tr><td colspan="4" class="text-center text-muted py-3">Chưa có thành viên</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="border-top p-3">
                    <form method="POST" action="<?= url('departments/' . $department['id'] . '/members/add') ?>" class="d-flex align-items-end gap-2">
                        <?= csrf_field() ?>
                        <div class="flex-grow-1">
                            <label class="form-label fs-12">Thêm nhân viên</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">Chọn...</option>
                                <?php foreach ($allUsers as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="ri-user-add-line me-1"></i> Thêm</button>
                    </form>
                </div>
            </div>

            <!-- Tab: KPI -->
            <div class="tab-pane" id="tabKpi" role="tabpanel">
                <?php
                $kpiItems = [
                    ['label'=>'Doanh thu','target'=>(float)($kpi['target_revenue'] ?? 0),'actual'=>$stats['revenue'],'format'=>'money','color'=>'success'],
                    ['label'=>'Deal thắng','target'=>(int)($kpi['target_deals'] ?? 0),'actual'=>$stats['deals'],'color'=>'primary'],
                    ['label'=>'Task hoàn thành','target'=>(int)($kpi['target_tasks'] ?? 0),'actual'=>$stats['tasks_done'],'color'=>'info'],
                    ['label'=>'Khách hàng','target'=>(int)($kpi['target_contacts'] ?? 0),'actual'=>$stats['contacts'],'color'=>'warning'],
                ];
                ?>
                <div class="row mb-4">
                    <?php foreach ($kpiItems as $ki):
                        $kpPct = $ki['target'] > 0 ? min(100, round($ki['actual'] / $ki['target'] * 100)) : 0;
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium"><?= $ki['label'] ?></span>
                            <span class="fs-12"><?= ($ki['format'] ?? '') === 'money' ? format_money($ki['actual']) : $ki['actual'] ?> / <?= ($ki['format'] ?? '') === 'money' ? format_money($ki['target']) : $ki['target'] ?></span>
                        </div>
                        <div class="progress" style="height:8px"><div class="progress-bar bg-<?= $ki['color'] ?>" style="width:<?= $kpPct ?>%"></div></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <h6 class="mb-3">Cài đặt mục tiêu tháng <?= date('m/Y') ?></h6>
                <form method="POST" action="<?= url('departments/' . $department['id'] . '/kpi') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="period" value="<?= date('Y-m') ?>">
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">Doanh thu</label><input type="number" class="form-control" name="target_revenue" value="<?= $kpi['target_revenue'] ?? 0 ?>"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Deal</label><input type="number" class="form-control" name="target_deals" value="<?= $kpi['target_deals'] ?? 0 ?>"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Task</label><input type="number" class="form-control" name="target_tasks" value="<?= $kpi['target_tasks'] ?? 0 ?>"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Khách hàng</label><input type="number" class="form-control" name="target_contacts" value="<?= $kpi['target_contacts'] ?? 0 ?>"></div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu KPI</button>
                </form>
            </div>

            <!-- Tab: Lịch sử -->
            <div class="tab-pane" id="tabHistory" role="tabpanel">
                <?php if (!empty($activityLog)): ?>
                <div class="timeline-2">
                    <?php foreach ($activityLog as $log): ?>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-xs"><div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-14"><i class="ri-history-line"></i></div></div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0"><?= e($log['title'] ?? '') ?></p>
                            <div class="text-muted fs-12"><?= e($log['user_name'] ?? 'Hệ thống') ?> &bull; <?= created_ago($log['created_at']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4"><i class="ri-history-line fs-1 d-block mb-2"></i>Chưa có lịch sử</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
