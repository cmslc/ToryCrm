<?php $pageTitle = 'Chính sách SLA'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Chính sách SLA</h4>
            <div>
                <a href="<?= url('sla/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo chính sách</a>
            </div>
        </div>

        <?php
            $pc = ['low' => 'info', 'medium' => 'warning', 'high' => 'danger', 'urgent' => 'danger'];
            $pl = ['low' => 'Thấp', 'medium' => 'Trung bình', 'high' => 'Cao', 'urgent' => 'Khẩn cấp'];
        ?>

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                    <i class="ri-ticket-2-line fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Tổng ticket SLA</p>
                                <h4 class="mb-0"><?= $stats['total_tickets'] ?? 0 ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title rounded-circle bg-danger-subtle text-danger">
                                    <i class="ri-alarm-warning-line fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Vi phạm SLA</p>
                                <h4 class="mb-0 text-danger"><?= $stats['breached_tickets'] ?? 0 ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title rounded-circle bg-info-subtle text-info">
                                    <i class="ri-reply-line fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-muted mb-1">TB phản hồi đầu</p>
                                <h4 class="mb-0"><?= $stats['avg_first_response'] ?? 0 ?>h</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title rounded-circle bg-success-subtle text-success">
                                    <i class="ri-timer-line fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-muted mb-1">TB xử lý</p>
                                <h4 class="mb-0"><?= $stats['avg_resolution'] ?? 0 ?>h</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên chính sách</th>
                                <th>Ưu tiên</th>
                                <th>Phản hồi đầu tiên</th>
                                <th>Thời gian xử lý</th>
                                <th>Chuyển tiếp đến</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($policies)): ?>
                                <?php foreach ($policies as $policy): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($policy['name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $pc[$policy['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$policy['priority']] ?? 'secondary' ?>">
                                                <?= $pl[$policy['priority']] ?? $policy['priority'] ?>
                                            </span>
                                        </td>
                                        <td><?= $policy['first_response_hours'] ?>h</td>
                                        <td><?= $policy['resolution_hours'] ?>h</td>
                                        <td><?= e($policy['escalate_to_name'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($policy['is_active']): ?>
                                                <span class="badge bg-success-subtle text-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary">Tắt</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="<?= url('sla/' . $policy['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line"></i></a>
                                                <form method="POST" action="<?= url('sla/' . $policy['id'] . '/delete') ?>" data-confirm="Xác nhận xóa chính sách SLA này?">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-timer-line fs-1 d-block mb-2"></i>Chưa có chính sách SLA</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
