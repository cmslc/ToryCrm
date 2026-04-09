<?php
$noLayout = true;
$pageTitle = 'Đơn hàng';
ob_start();
?>

        <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0">Đơn hàng của bạn</h4>
            <a href="<?= url('portal') ?>" class="btn btn-light"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
        </div>

        <?php
        $orderStatusLabels = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
        $orderStatusColors = ['draft'=>'secondary','pending'=>'warning','approved'=>'primary','completed'=>'success','cancelled'=>'danger'];
        ?>

        <div class="card">
            <div class="card-body">
                <?php if (!empty($orders)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Ngày tạo</th>
                                <th>Tổng tiền</th>
                                <th>Thanh toán</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-medium"><?= e($order['order_number'] ?? '-') ?></td>
                                <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                <td><?= number_format($order['total'] ?? 0) ?> ₫</td>
                                <td>
                                    <?php
                                    $paid = (float)($order['paid_amount'] ?? 0);
                                    $total = (float)($order['total'] ?? 0);
                                    if ($total > 0 && $paid >= $total): ?>
                                        <span class="badge bg-success-subtle text-success">Đã thanh toán</span>
                                    <?php elseif ($paid > 0): ?>
                                        <span class="badge bg-warning-subtle text-warning">Thanh toán một phần</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>
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
                <div class="text-center py-4">
                    <div class="text-muted">
                        <i class="ri-file-list-3-line fs-1 d-block mb-2"></i>
                        Chưa có đơn hàng nào.
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
