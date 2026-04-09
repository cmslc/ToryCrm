<?php
$noLayout = true;
$pageTitle = 'Dashboard';
ob_start();
?>

        <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Xin chào, <?= e(($portalContact['first_name'] ?? '') . ' ' . ($portalContact['last_name'] ?? '')) ?>!</h4>
        </div>

        <!-- Stats cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                    <i class="ri-file-list-3-line fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Đơn hàng</p>
                                <h4 class="mb-0"><?= $orderCount ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title rounded-circle bg-info-subtle text-info">
                                    <i class="ri-customer-service-line fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Tổng ticket</p>
                                <h4 class="mb-0"><?= $ticketCount ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3">
                                <div class="avatar-title rounded-circle bg-warning-subtle text-warning">
                                    <i class="ri-time-line fs-5"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Ticket đang mở</p>
                                <h4 class="mb-0"><?= $openTickets ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact info -->
        <?php if ($contact): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-user-line me-1"></i> Thông tin của bạn</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width:140px">Họ tên</td>
                                <td class="fw-medium"><?= e(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td><?= e($contact['email'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Điện thoại</td>
                                <td><?= e($contact['phone'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted" style="width:140px">Địa chỉ</td>
                                <td><?= e($contact['address'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Nguồn</td>
                                <td><?= e($contact['source'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Recent orders -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Đơn hàng gần đây</h5>
                        <a href="<?= url('portal/orders') ?>" class="btn btn-link p-0">Xem tất cả</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentOrders)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã</th>
                                        <th>Ngày</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $orderStatusLabels = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
                                    $orderStatusColors = ['draft'=>'secondary','pending'=>'warning','approved'=>'primary','completed'=>'success','cancelled'=>'danger'];
                                    ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($order['order_number'] ?? '-') ?></td>
                                        <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                        <td><?= number_format($order['total'] ?? 0) ?></td>
                                        <td>
                                            <?php $os = $order['status'] ?? 'draft'; ?>
                                            <span class="badge bg-<?= $orderStatusColors[$os] ?? 'secondary' ?>-subtle text-<?= $orderStatusColors[$os] ?? 'secondary' ?>">
                                                <?= $orderStatusLabels[$os] ?? $os ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted py-3">Chưa có đơn hàng nào.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent tickets -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">Ticket gần đây</h5>
                        <a href="<?= url('portal/tickets') ?>" class="btn btn-link p-0">Xem tất cả</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentTickets)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã</th>
                                        <th>Tiêu đề</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ticketStatusLabels = ['open'=>'Mở','in_progress'=>'Đang xử lý','waiting'=>'Chờ phản hồi','resolved'=>'Đã xử lý','closed'=>'Đóng'];
                                    $ticketStatusColors = ['open'=>'info','in_progress'=>'primary','waiting'=>'warning','resolved'=>'success','closed'=>'secondary'];
                                    ?>
                                    <?php foreach ($recentTickets as $ticket): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($ticket['ticket_code'] ?? '-') ?></td>
                                        <td><?= e($ticket['title'] ?? '') ?></td>
                                        <td>
                                            <?php $ts = $ticket['status'] ?? 'open'; ?>
                                            <span class="badge bg-<?= $ticketStatusColors[$ts] ?? 'secondary' ?>-subtle text-<?= $ticketStatusColors[$ts] ?? 'secondary' ?>">
                                                <?= $ticketStatusLabels[$ts] ?? $ts ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted py-3">Chưa có ticket nào.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
