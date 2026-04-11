<?php $pageTitle = e($department['name']); ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0"><?= e($department['name']) ?></h4>
        <?php if ($department['parent_name']): ?><p class="text-muted mb-0"><i class="ri-arrow-up-line me-1"></i><?= e($department['parent_name']) ?></p><?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('departments/' . $department['id'] . '/members') ?>" class="btn btn-soft-primary"><i class="ri-team-line me-1"></i> Thành viên</a>
        <a href="<?= url('departments') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
    </div>
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

<div class="row">
    <div class="col-xl-8">
        <!-- Members -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Thành viên <span class="badge bg-primary ms-1"><?= count($members) ?></span></h5>
                <a href="<?= url('departments/' . $department['id'] . '/members') ?>" class="btn btn-soft-primary"><i class="ri-user-add-line me-1"></i> Quản lý</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Nhân viên</th><th>Vai trò</th><th>Đăng nhập cuối</th></tr></thead>
                        <tbody>
                        <?php
                        $roleLabels = ['admin'=>'Admin','manager'=>'Quản lý','staff'=>'Nhân viên'];
                        $roleColors = ['admin'=>'danger','manager'=>'warning','staff'=>'info'];
                        foreach ($members as $m): ?>
                        <tr>
                            <td><?= user_avatar($m['name'] ?? null, 'primary', $m['avatar'] ?? null) ?></td>
                            <td><span class="badge bg-<?= $roleColors[$m['role']] ?? 'secondary' ?>-subtle text-<?= $roleColors[$m['role']] ?? 'secondary' ?>"><?= $roleLabels[$m['role']] ?? $m['role'] ?></span></td>
                            <td class="text-muted fs-12"><?= $m['last_login'] ? time_ago($m['last_login']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($members)): ?><tr><td colspan="3" class="text-center text-muted py-3">Chưa có thành viên</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Task Progress -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Tiến độ công việc</h5></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="progress flex-grow-1" style="height:10px"><div class="progress-bar bg-success" style="width:<?= $taskPct ?>%"></div></div>
                    <span class="fw-semibold"><?= $taskPct ?>%</span>
                </div>
                <div class="d-flex justify-content-between text-muted fs-12">
                    <span>Hoàn thành: <?= $stats['tasks_done'] ?></span>
                    <span>Tổng: <?= $stats['tasks_total'] ?></span>
                </div>
            </div>
        </div>

        <!-- Child Departments -->
        <?php if (!empty($childDepts)): ?>
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Phòng ban con</h5></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Tên</th><th>Thành viên</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($childDepts as $cd): ?>
                        <tr>
                            <td><span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:<?= e($cd['color']) ?>"></span><a href="<?= url('departments/' . $cd['id']) ?>" class="fw-medium text-dark"><?= e($cd['name']) ?></a></td>
                            <td><?= $cd['member_count'] ?></td>
                            <td><a href="<?= url('departments/' . $cd['id']) ?>" class="btn btn-soft-primary"><i class="ri-eye-line"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-xl-4">
        <!-- Info -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted">Tên</th><td><?= e($department['name']) ?></td></tr>
                    <tr><th class="text-muted">Trưởng phòng</th><td><?= user_avatar($department['manager_name'] ?? null, 'primary', $department['manager_avatar'] ?? null) ?></td></tr>
                    <tr><th class="text-muted">Phó phòng</th><td><?= user_avatar($department['vice_manager_name'] ?? null, 'info', $department['vice_manager_avatar'] ?? null) ?></td></tr>
                    <?php if ($department['parent_name']): ?><tr><th class="text-muted">Thuộc</th><td><?= e($department['parent_name']) ?></td></tr><?php endif; ?>
                    <tr><th class="text-muted">Màu</th><td><span class="d-inline-block rounded-circle me-1" style="width:14px;height:14px;background:<?= e($department['color']) ?>"></span><?= e($department['color']) ?></td></tr>
                    <?php if ($department['description']): ?><tr><th class="text-muted">Mô tả</th><td><?= e($department['description']) ?></td></tr><?php endif; ?>
                </table>
            </div>
        </div>

        <!-- KPI -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">KPI tháng <?= date('m/Y') ?></h5>
            </div>
            <div class="card-body">
                <?php
                $kpiItems = [
                    ['label'=>'Doanh thu','target'=>(float)($kpi['target_revenue'] ?? 0),'actual'=>$stats['revenue'],'format'=>'money','color'=>'success'],
                    ['label'=>'Deal thắng','target'=>(int)($kpi['target_deals'] ?? 0),'actual'=>$stats['deals'],'color'=>'primary'],
                    ['label'=>'Task hoàn thành','target'=>(int)($kpi['target_tasks'] ?? 0),'actual'=>$stats['tasks_done'],'color'=>'info'],
                    ['label'=>'Khách hàng','target'=>(int)($kpi['target_contacts'] ?? 0),'actual'=>$stats['contacts'],'color'=>'warning'],
                ];
                foreach ($kpiItems as $ki):
                    $kpPct = $ki['target'] > 0 ? min(100, round($ki['actual'] / $ki['target'] * 100)) : 0;
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fs-12"><?= $ki['label'] ?></span>
                        <span class="fs-12 fw-medium"><?= ($ki['format'] ?? '') === 'money' ? format_money($ki['actual']) : $ki['actual'] ?> / <?= ($ki['format'] ?? '') === 'money' ? format_money($ki['target']) : $ki['target'] ?></span>
                    </div>
                    <div class="progress" style="height:6px"><div class="progress-bar bg-<?= $ki['color'] ?>" style="width:<?= $kpPct ?>%"></div></div>
                </div>
                <?php endforeach; ?>

                <button class="btn btn-soft-primary w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#kpiForm"><i class="ri-settings-3-line me-1"></i> Cài đặt KPI</button>
                <div class="collapse mt-3" id="kpiForm">
                    <form method="POST" action="<?= url('departments/' . $department['id'] . '/kpi') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="period" value="<?= date('Y-m') ?>">
                        <div class="mb-2"><label class="form-label fs-12">Doanh thu mục tiêu</label><input type="number" class="form-control" name="target_revenue" value="<?= $kpi['target_revenue'] ?? 0 ?>"></div>
                        <div class="mb-2"><label class="form-label fs-12">Deal mục tiêu</label><input type="number" class="form-control" name="target_deals" value="<?= $kpi['target_deals'] ?? 0 ?>"></div>
                        <div class="mb-2"><label class="form-label fs-12">Task mục tiêu</label><input type="number" class="form-control" name="target_tasks" value="<?= $kpi['target_tasks'] ?? 0 ?>"></div>
                        <div class="mb-2"><label class="form-label fs-12">KH mục tiêu</label><input type="number" class="form-control" name="target_contacts" value="<?= $kpi['target_contacts'] ?? 0 ?>"></div>
                        <button type="submit" class="btn btn-primary w-100"><i class="ri-save-line me-1"></i> Lưu KPI</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Positions -->
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Vị trí / Chức danh</h5>
                <button class="btn btn-soft-primary" data-bs-toggle="collapse" data-bs-target="#posForm"><i class="ri-add-line"></i></button>
            </div>
            <div class="card-body">
                <?php if (!empty($positions)): ?>
                    <?php foreach ($positions as $pos): ?>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <?= user_avatar($pos['user_name'] ?? null, 'primary', $pos['avatar'] ?? null) ?>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary"><?= e($pos['position']) ?></span>
                            <form method="POST" action="<?= url('departments/' . $department['id'] . '/positions/' . $pos['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa?">
                                <?= csrf_field() ?><button class="btn btn-link text-danger p-0"><i class="ri-close-line"></i></button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center mb-0 fs-12">Chưa có vị trí nào</p>
                <?php endif; ?>

                <div class="collapse mt-3" id="posForm">
                    <form method="POST" action="<?= url('departments/' . $department['id'] . '/positions') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <select name="user_id" class="form-select" required>
                                <option value="">Chọn nhân viên...</option>
                                <?php foreach ($members as $m): ?><option value="<?= $m['id'] ?>"><?= e($m['name']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <select name="position" class="form-select" required>
                                <option value="">Chọn vị trí...</option>
                                <option value="Trưởng nhóm">Trưởng nhóm</option>
                                <option value="Phó nhóm">Phó nhóm</option>
                                <option value="Chuyên viên">Chuyên viên</option>
                                <option value="Nhân viên">Nhân viên</option>
                                <option value="Thực tập sinh">Thực tập sinh</option>
                                <option value="Cố vấn">Cố vấn</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="ri-save-line me-1"></i> Lưu</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <?php if (!empty($activityLog)): ?>
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Hoạt động gần đây</h5></div>
            <div class="card-body p-0">
                <div data-simplebar style="max-height:300px" class="p-3">
                    <?php foreach ($activityLog as $al): ?>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0 me-2">
                            <div class="avatar-xs"><div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-10"><?= mb_strtoupper(mb_substr($al['user_name'] ?? '', 0, 1)) ?></div></div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-0 fs-12"><strong><?= e($al['user_name'] ?? '') ?></strong> <?= e($al['title']) ?></p>
                            <small class="text-muted"><?= time_ago($al['created_at']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
