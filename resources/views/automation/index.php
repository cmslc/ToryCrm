<?php $pageTitle = 'Automation'; ?>

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Automation</h4>
                    <div class="page-title-right">
                        <a href="<?= url('automation/create') ?>" class="btn btn-primary">
                            <i class="ri-add-line align-bottom me-1"></i> Tạo rule
                        </a>
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
                                <th>Tên</th>
                                <th>Module</th>
                                <th>Trigger</th>
                                <th>Actions</th>
                                <th>Trạng thái</th>
                                <th>Đã chạy</th>
                                <th>Lần chạy cuối</th>
                                <th>Người tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $moduleLabels = ['contact' => 'Khách hàng', 'deal' => 'Cơ hội', 'task' => 'Công việc', 'ticket' => 'Ticket', 'order' => 'Đơn hàng'];
                            $triggerLabels = ['created' => 'Khi tạo mới', 'updated' => 'Khi cập nhật', 'status_changed' => 'Khi đổi trạng thái'];
                            ?>
                            <?php if (!empty($rules)): ?>
                                <?php foreach ($rules as $rule): ?>
                                    <?php
                                    $actions = json_decode($rule['actions'] ?? '[]', true);
                                    $actionCount = is_array($actions) ? count($actions) : 0;
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="fw-medium"><?= e($rule['name']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary">
                                                <?= $moduleLabels[$rule['module']] ?? e($rule['module']) ?>
                                            </span>
                                        </td>
                                        <td><?= $triggerLabels[$rule['trigger_event']] ?? e($rule['trigger_event']) ?></td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info"><?= $actionCount ?> action<?= $actionCount > 1 ? 's' : '' ?></span>
                                        </td>
                                        <td>
                                            <?php if ($rule['is_active']): ?>
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-checkbox-circle-fill me-1"></i>Bật
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="ri-close-circle-fill me-1"></i>Tắt
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= number_format($rule['run_count'] ?? 0) ?></td>
                                        <td>
                                            <?= $rule['last_run_at'] ? time_ago($rule['last_run_at']) : '<span class="text-muted">-</span>' ?>
                                        </td>
                                        <td><?= e($rule['created_by_name'] ?? '-') ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <form method="POST" action="<?= url('automation/' . $rule['id'] . '/toggle-active') ?>">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-soft-<?= $rule['is_active'] ? 'warning' : 'success' ?>" title="<?= $rule['is_active'] ? 'Tắt' : 'Bật' ?>">
                                                        <i class="ri-<?= $rule['is_active'] ? 'stop-circle-line' : 'play-circle-line' ?>"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-soft-info btn-view-logs" data-rule-id="<?= $rule['id'] ?>" title="Xem logs">
                                                    <i class="ri-file-list-line"></i>
                                                </button>
                                                <form method="POST" action="<?= url('automation/' . $rule['id'] . '/delete') ?>" data-confirm="Xác nhận xóa automation rule này?">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-soft-danger" title="Xóa">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ri-robot-line fs-1 d-block mb-2"></i>
                                            Chưa có automation rule nào
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
                        <h5 class="modal-title">Automation Logs</h5>
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
                    var ruleId = this.getAttribute('data-rule-id');
                    var modal = new bootstrap.Modal(document.getElementById('logsModal'));
                    var modalBody = document.getElementById('logsModalBody');

                    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div></div>';
                    modal.show();

                    fetch('<?= url('automation/') ?>' + ruleId + '/logs')
                        .then(function(response) { return response.json(); })
                        .then(function(data) {
                            if (data.logs && data.logs.length > 0) {
                                var html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0">';
                                html += '<thead class="table-light"><tr><th>Thời gian</th><th>Kết quả</th><th>Người kích hoạt</th><th>Chi tiết</th></tr></thead><tbody>';
                                data.logs.forEach(function(log) {
                                    var statusBadge = log.status === 'success'
                                        ? '<span class="badge bg-success-subtle text-success">Thành công</span>'
                                        : '<span class="badge bg-danger-subtle text-danger">Lỗi</span>';
                                    html += '<tr>';
                                    html += '<td>' + (log.created_at || '-') + '</td>';
                                    html += '<td>' + statusBadge + '</td>';
                                    html += '<td>' + (log.triggered_by_name || 'Hệ thống') + '</td>';
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
