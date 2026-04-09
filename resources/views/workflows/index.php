<?php $pageTitle = 'Workflow'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Workflow</h4>
                    <div class="page-title-right">
                        <a href="<?= url('workflows/create') ?>" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i> Tạo workflow
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $triggerLabels = [
            'contact.created' => 'Khách hàng mới',
            'deal.stage_changed' => 'Deal thay đổi',
            'task.overdue' => 'Task quá hạn',
            'order.created' => 'Đơn hàng mới',
            'ticket.created' => 'Ticket mới',
        ];
        $triggerColors = [
            'contact.created' => 'primary',
            'deal.stage_changed' => 'warning',
            'task.overdue' => 'danger',
            'order.created' => 'success',
            'ticket.created' => 'info',
        ];
        ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên workflow</th>
                                <th>Trigger</th>
                                <th>Trạng thái</th>
                                <th>Đã chạy</th>
                                <th>Lần chạy cuối</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($workflows)): ?>
                                <?php foreach ($workflows as $wf): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-medium"><?= e($wf['name']) ?></span>
                                            <?php if (!empty($wf['description'])): ?>
                                                <br><small class="text-muted"><?= e($wf['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $tt = $wf['trigger_type'] ?? ''; ?>
                                            <span class="badge bg-<?= $triggerColors[$tt] ?? 'secondary' ?>-subtle text-<?= $triggerColors[$tt] ?? 'secondary' ?>">
                                                <?= $triggerLabels[$tt] ?? e($tt) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" action="<?= url('workflows/' . $wf['id'] . '/toggle') ?>" class="d-inline">
                                                <?= csrf_field() ?>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                           <?= $wf['is_active'] ? 'checked' : '' ?>
                                                           onchange="this.closest('form').submit()">
                                                </div>
                                            </form>
                                        </td>
                                        <td><?= number_format($wf['run_count'] ?? 0) ?></td>
                                        <td>
                                            <?= !empty($wf['last_run_at']) ? time_ago($wf['last_run_at']) : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="<?= url('workflows/' . $wf['id'] . '/edit') ?>" class="btn btn-soft-primary" title="Sửa">
                                                    <i class="ri-pencil-line"></i>
                                                </a>
                                                <button type="button" class="btn btn-soft-info btn-view-logs" data-workflow-id="<?= $wf['id'] ?>" title="Xem logs">
                                                    <i class="ri-file-list-line"></i>
                                                </button>
                                                <form method="POST" action="<?= url('workflows/' . $wf['id'] . '/delete') ?>" data-confirm="Xác nhận xóa workflow này?">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-soft-danger" title="Xóa">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-flow-chart fs-1 d-block mb-2"></i>
                                            Chưa có workflow nào
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Logs Modal -->
        <div class="modal fade" id="logsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Workflow Logs</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="logsModalBody">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-view-logs').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var workflowId = this.getAttribute('data-workflow-id');
                    var modal = new bootstrap.Modal(document.getElementById('logsModal'));
                    var modalBody = document.getElementById('logsModalBody');

                    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div></div>';
                    modal.show();

                    fetch('<?= url('workflows/') ?>' + workflowId + '/logs')
                        .then(function(response) { return response.json(); })
                        .then(function(data) {
                            if (data.logs && data.logs.length > 0) {
                                var html = '<div class="table-responsive"><table class="table align-middle mb-0">';
                                html += '<thead class="table-light"><tr><th>Thời gian</th><th>Kết quả</th><th>Chi tiết</th></tr></thead><tbody>';
                                data.logs.forEach(function(log) {
                                    var statusBadge = log.status === 'success'
                                        ? '<span class="badge bg-success-subtle text-success">Thành công</span>'
                                        : '<span class="badge bg-danger-subtle text-danger">Lỗi</span>';
                                    html += '<tr>';
                                    html += '<td>' + (log.created_at || '-') + '</td>';
                                    html += '<td>' + statusBadge + '</td>';
                                    html += '<td><small class="text-muted">' + (log.message || '-') + '</small></td>';
                                    html += '</tr>';
                                });
                                html += '</tbody></table></div>';
                                modalBody.innerHTML = html;
                            } else {
                                modalBody.innerHTML = '<div class="text-center py-4 text-muted">Chưa có log nào.</div>';
                            }
                        })
                        .catch(function() {
                            modalBody.innerHTML = '<div class="text-center py-4 text-danger">Không thể tải dữ liệu.</div>';
                        });
                });
            });
        });
        </script>
