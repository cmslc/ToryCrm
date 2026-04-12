<?php
$pageTitle = 'Tự động hóa';
$tab = $_GET['tab'] ?? 'workflows';
$triggerLabels = ['contact.created'=>'Khách hàng mới','deal.stage_changed'=>'Deal thay đổi','task.overdue'=>'Task quá hạn','order.created'=>'Đơn hàng mới','ticket.created'=>'Ticket mới'];
$triggerColors = ['contact.created'=>'primary','deal.stage_changed'=>'warning','task.overdue'=>'danger','order.created'=>'success','ticket.created'=>'info'];
$moduleLabels = ['contact'=>'Khách hàng','deal'=>'Cơ hội','task'=>'Công việc','ticket'=>'Ticket','order'=>'Đơn hàng'];
$autoTriggerLabels = ['created'=>'Khi tạo mới','updated'=>'Khi cập nhật','status_changed'=>'Khi đổi trạng thái'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-flow-chart me-2"></i> Tự động hóa</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('workflows/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo workflow</a>
        <a href="<?= url('automation/create') ?>" class="btn btn-soft-primary"><i class="ri-add-line me-1"></i> Tạo rule</a>
    </div>
</div>

<ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link <?= $tab !== 'automation' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tabWorkflows"><i class="ri-flow-chart me-1"></i> Workflow <span class="badge bg-primary-subtle text-primary ms-1"><?= count($workflows ?? []) ?></span></a></li>
    <li class="nav-item"><a class="nav-link <?= $tab === 'automation' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tabAutomation"><i class="ri-robot-line me-1"></i> Automation Rules <span class="badge bg-info-subtle text-info ms-1"><?= count($rules ?? []) ?></span></a></li>
</ul>

<div class="tab-content">
<!-- Workflow Tab -->
<div class="tab-pane <?= $tab !== 'automation' ? 'active' : '' ?>" id="tabWorkflows">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Tên workflow</th><th>Trigger</th><th>Trạng thái</th><th>Đã chạy</th><th>Lần chạy cuối</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php if (!empty($workflows)): ?>
                        <?php foreach ($workflows as $wf): ?>
                        <tr>
                            <td><span class="fw-medium"><?= e($wf['name']) ?></span><?php if (!empty($wf['description'])): ?><br><small class="text-muted"><?= e($wf['description']) ?></small><?php endif; ?></td>
                            <td><?php $tt = $wf['trigger_type'] ?? ''; ?><span class="badge bg-<?= $triggerColors[$tt] ?? 'secondary' ?>-subtle text-<?= $triggerColors[$tt] ?? 'secondary' ?>"><?= $triggerLabels[$tt] ?? e($tt) ?></span></td>
                            <td>
                                <form method="POST" action="<?= url('workflows/' . $wf['id'] . '/toggle') ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" <?= $wf['is_active'] ? 'checked' : '' ?> onchange="this.closest('form').submit()"></div>
                                </form>
                            </td>
                            <td><?= number_format($wf['run_count'] ?? 0) ?></td>
                            <td><?= !empty($wf['last_run_at']) ? time_ago($wf['last_run_at']) : '<span class="text-muted">-</span>' ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= url('workflows/' . $wf['id'] . '/edit') ?>" class="btn btn-soft-primary btn-icon" title="Sửa"><i class="ri-pencil-line"></i></a>
                                    <form method="POST" action="<?= url('workflows/' . $wf['id'] . '/delete') ?>" onsubmit="return confirm('Xóa workflow này?')">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-soft-danger btn-icon" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4"><i class="ri-flow-chart fs-1 d-block mb-2"></i>Chưa có workflow nào</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Automation Tab -->
<div class="tab-pane <?= $tab === 'automation' ? 'active' : '' ?>" id="tabAutomation">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Tên</th><th>Module</th><th>Trigger</th><th>Actions</th><th>Trạng thái</th><th>Đã chạy</th><th>Lần chạy cuối</th><th>Người tạo</th><th>Thao tác</th></tr></thead>
                    <tbody>
                    <?php if (!empty($rules)): ?>
                        <?php foreach ($rules as $rule): ?>
                        <?php $actions = json_decode($rule['actions'] ?? '[]', true); $actionCount = is_array($actions) ? count($actions) : 0; ?>
                        <tr>
                            <td><span class="fw-medium"><?= e($rule['name']) ?></span></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= $moduleLabels[$rule['module']] ?? e($rule['module']) ?></span></td>
                            <td><?= $autoTriggerLabels[$rule['trigger_event']] ?? e($rule['trigger_event']) ?></td>
                            <td><span class="badge bg-info-subtle text-info"><?= $actionCount ?> action<?= $actionCount > 1 ? 's' : '' ?></span></td>
                            <td>
                                <?php if ($rule['is_active']): ?><span class="badge bg-success-subtle text-success"><i class="ri-checkbox-circle-fill me-1"></i>Bật</span>
                                <?php else: ?><span class="badge bg-danger-subtle text-danger"><i class="ri-close-circle-fill me-1"></i>Tắt</span><?php endif; ?>
                            </td>
                            <td><?= number_format($rule['run_count'] ?? 0) ?></td>
                            <td><?= $rule['last_run_at'] ? time_ago($rule['last_run_at']) : '<span class="text-muted">-</span>' ?></td>
                            <td><?= user_avatar($rule['created_by_name'] ?? null) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="POST" action="<?= url('automation/' . $rule['id'] . '/toggle-active') ?>">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-soft-<?= $rule['is_active'] ? 'warning' : 'success' ?> btn-icon" title="<?= $rule['is_active'] ? 'Tắt' : 'Bật' ?>"><i class="ri-<?= $rule['is_active'] ? 'stop-circle-line' : 'play-circle-line' ?>"></i></button>
                                    </form>
                                    <form method="POST" action="<?= url('automation/' . $rule['id'] . '/delete') ?>" onsubmit="return confirm('Xóa rule này?')">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-soft-danger btn-icon" title="Xóa"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="ri-robot-line fs-1 d-block mb-2"></i>Chưa có automation rule nào</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
