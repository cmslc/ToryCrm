<?php
$pageTitle = 'Đơn hàng mua ' . $order['order_code'];
$sc = ['draft'=>'secondary','pending'=>'warning','approved'=>'primary','receiving'=>'info','completed'=>'success','cancelled'=>'danger'];
$sl = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','receiving'=>'Đang nhận','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$pc = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success'];
$pl = ['unpaid'=>'Chưa thanh toán','partial'=>'Thanh toán một phần','paid'=>'Đã thanh toán'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $pageTitle ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('purchase-orders') ?>">Đơn hàng mua</a></li>
                <li class="breadcrumb-item active"><?= e($order['order_code']) ?></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Order Info -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= e($order['order_code']) ?></h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-<?= $sc[$order['status']] ?? 'secondary' ?>"><?= $sl[$order['status']] ?? '' ?></span>
                                <span class="badge bg-<?= $pc[$order['payment_status']] ?? 'secondary' ?>-subtle text-<?= $pc[$order['payment_status']] ?? 'secondary' ?>"><?= $pl[$order['payment_status']] ?? '' ?></span>
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="<?= url('purchase-orders/' . $order['id'] . '/edit') ?>" class="btn btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                            <?php if (in_array($order['status'], ['draft', 'pending'])): ?>
                                <form method="POST" action="<?= url('purchase-orders/' . $order['id'] . '/approve') ?>" class="d-inline" data-confirm="Duyệt đơn mua này?">
                                    <?= csrf_field() ?><button class="btn btn btn-soft-success"><i class="ri-check-line me-1"></i>Duyệt</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                <form method="POST" action="<?= url('purchase-orders/' . $order['id'] . '/cancel') ?>" class="d-inline" data-confirm="Hủy đơn mua này?">
                                    <?= csrf_field() ?><button class="btn btn btn-soft-warning"><i class="ri-close-circle-line me-1"></i>Hủy</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('purchase-orders/' . $order['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa đơn mua?">
                                <?= csrf_field() ?><button class="btn btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Nhà cung cấp</h6>
                                <?php if (!empty($order['supplier_name'])): ?>
                                    <p class="mb-1 fw-medium"><?= e($order['supplier_name']) ?></p>
                                    <?php if (!empty($order['supplier_email'])): ?><p class="mb-1 text-muted"><i class="ri-mail-line me-1"></i><?= e($order['supplier_email']) ?></p><?php endif; ?>
                                    <?php if (!empty($order['supplier_phone'])): ?><p class="mb-0 text-muted"><i class="ri-phone-line me-1"></i><?= e($order['supplier_phone']) ?></p><?php endif; ?>
                                    <?php if (!empty($order['supplier_address'])): ?><p class="mb-0 text-muted"><i class="ri-map-pin-line me-1"></i><?= e($order['supplier_address']) ?></p><?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">-</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Chi tiết sản phẩm</h5></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Sản phẩm</th>
                                        <th>SKU</th>
                                        <th class="text-end">SL</th>
                                        <th class="text-end">Đơn giá</th>
                                        <th class="text-end">Thuế</th>
                                        <th class="text-end">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $i => $item): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td class="fw-medium"><?= e($item['product_name']) ?></td>
                                        <td><code><?= e($item['sku'] ?? '-') ?></code></td>
                                        <td class="text-end"><?= $item['quantity'] ?></td>
                                        <td class="text-end"><?= format_money($item['unit_price']) ?></td>
                                        <td class="text-end"><?= $item['tax_rate'] ?? 0 ?>%</td>
                                        <td class="text-end fw-medium"><?= format_money($item['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end">Tạm tính:</td>
                                        <td class="text-end fw-medium"><?= format_money($order['subtotal']) ?></td>
                                    </tr>
                                    <?php if (($order['discount_amount'] ?? 0) > 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-end">Giảm giá:</td>
                                        <td class="text-end text-danger">-<?= format_money($order['discount_amount']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold fs-5">Tổng cộng:</td>
                                        <td class="text-end fw-bold fs-5 text-primary"><?= format_money($order['total']) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if (!empty($order['notes'])): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Ghi chú</h5></div>
                    <div class="card-body"><?= nl2br(e($order['notes'])) ?></div>
                </div>
                <?php endif; ?>
                <!-- Trao đổi (Plugin) -->
                <?php if (function_exists('activity_exchange_render')) activity_exchange_render('purchase_order', $order['id']); ?>
            </div>

            <div class="col-lg-4">
                <!-- Người liên quan -->
                <?php $rpEntityType = 'purchase_order'; $rpEntityId = $order['id']; $rpOwnerId = $order['owner_id'] ?? 0; $rpOwnerName = $order['owner_name'] ?? '-'; include BASE_PATH . '/resources/views/partials/related-people.php'; ?>

                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-information-line me-1"></i> Thông tin</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Ngày dự kiến nhận</span>
                            <span><?= !empty($order['expected_date']) ? format_date($order['expected_date']) : '-' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Phương thức TT</span>
                            <span><?= e($order['payment_method'] ?? '-') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Đã thanh toán</span>
                            <span class="fw-medium"><?= format_money($order['paid_amount'] ?? 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Còn nợ</span>
                            <span class="fw-medium text-danger"><?= format_money(max(0, ($order['total'] ?? 0) - ($order['paid_amount'] ?? 0))) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Người phụ trách</span>
                            <span><?= e($order['owner_name'] ?? '-') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Người duyệt</span>
                            <span><?= e($order['approved_by_name'] ?? '-') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Ngày tạo</span>
                            <span><?= format_datetime($order['created_at']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Cập nhật</span>
                            <span><?= format_datetime($order['updated_at']) ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($order['payment_status'] !== 'paid' && $order['status'] !== 'cancelled'): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-1"></i> Thanh toán</h5></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('purchase-orders/' . $order['id'] . '/payment') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <input type="number" class="form-control" name="amount" placeholder="Số tiền" required min="1" value="<?= max(0, $order['total'] - $order['paid_amount']) ?>">
                            </div>
                            <button type="submit" class="btn btn-success btn w-100"><i class="ri-money-dollar-circle-line me-1"></i> Ghi nhận thanh toán</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
