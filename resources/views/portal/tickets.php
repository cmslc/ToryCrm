<?php
$noLayout = true;
$pageTitle = 'Ticket hỗ trợ';
ob_start();
?>

        <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Ticket hỗ trợ</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('portal') ?>" class="btn btn-light"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
                <a href="<?= url('portal/tickets/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo ticket</a>
            </div>
        </div>

        <?php
        $ticketStatusLabels = ['open'=>'Mở','in_progress'=>'Đang xử lý','waiting'=>'Chờ phản hồi','resolved'=>'Đã xử lý','closed'=>'Đóng'];
        $ticketStatusColors = ['open'=>'info','in_progress'=>'primary','waiting'=>'warning','resolved'=>'success','closed'=>'secondary'];
        $priorityLabels = ['low'=>'Thấp','medium'=>'Trung bình','high'=>'Cao','urgent'=>'Khẩn cấp'];
        $priorityColors = ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'];
        ?>

        <div class="card">
            <div class="card-body">
                <?php if (!empty($tickets)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã ticket</th>
                                <th>Tiêu đề</th>
                                <th>Danh mục</th>
                                <th>Độ ưu tiên</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td class="fw-medium"><?= e($ticket['ticket_code'] ?? '-') ?></td>
                                <td><?= e($ticket['title'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($ticket['category_name'])): ?>
                                        <span class="badge" style="background-color:<?= e($ticket['category_color'] ?? '#6c757d') ?>20;color:<?= e($ticket['category_color'] ?? '#6c757d') ?>">
                                            <?= e($ticket['category_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php $pr = $ticket['priority'] ?? 'medium'; ?>
                                    <span class="badge bg-<?= $priorityColors[$pr] ?? 'secondary' ?>-subtle text-<?= $priorityColors[$pr] ?? 'secondary' ?>">
                                        <?= $priorityLabels[$pr] ?? $pr ?>
                                    </span>
                                </td>
                                <td>
                                    <?php $ts = $ticket['status'] ?? 'open'; ?>
                                    <span class="badge bg-<?= $ticketStatusColors[$ts] ?? 'secondary' ?>-subtle text-<?= $ticketStatusColors[$ts] ?? 'secondary' ?>">
                                        <?= $ticketStatusLabels[$ts] ?? $ts ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <div class="text-muted">
                        <i class="ri-customer-service-line fs-1 d-block mb-2"></i>
                        Chưa có ticket nào.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
