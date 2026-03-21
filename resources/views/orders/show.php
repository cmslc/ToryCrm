<?php
$pageTitle = ($order['type'] === 'quote' ? 'Báo giá' : 'Đơn hàng') . ' ' . $order['order_number'];
$sc = ['draft'=>'secondary','sent'=>'info','confirmed'=>'primary','processing'=>'warning','completed'=>'success','cancelled'=>'danger'];
$sl = ['draft'=>'Nháp','sent'=>'Đã gửi','confirmed'=>'Đã xác nhận','processing'=>'Đang xử lý','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$pc = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success'];
$pl = ['unpaid'=>'Chưa thanh toán','partial'=>'Thanh toán một phần','paid'=>'Đã thanh toán'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $pageTitle ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('orders') ?>">Đơn hàng</a></li>
                <li class="breadcrumb-item active"><?= e($order['order_number']) ?></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Order Info -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= e($order['order_number']) ?></h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-<?= $sc[$order['status']] ?? 'secondary' ?>"><?= $sl[$order['status']] ?? '' ?></span>
                                <span class="badge bg-<?= $pc[$order['payment_status']] ?? 'secondary' ?>-subtle text-<?= $pc[$order['payment_status']] ?? 'secondary' ?>"><?= $pl[$order['payment_status']] ?? '' ?></span>
                                <?= $order['type'] === 'quote' ? '<span class="badge bg-info">Báo giá</span>' : '<span class="badge bg-primary">Đơn hàng</span>' ?>
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="<?= url('orders/' . $order['id'] . '/edit') ?>" class="btn btn-sm btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                            <a href="<?= url('orders/pdf/' . $order['id']) ?>" class="btn btn-sm btn-soft-info" target="_blank"><i class="ri-printer-line me-1"></i>In</a>
                            <?php if (in_array($order['status'], ['draft', 'sent'])): ?>
                                <form method="POST" action="<?= url('orders/' . $order['id'] . '/approve') ?>" class="d-inline" data-confirm="Duyệt đơn hàng này?">
                                    <?= csrf_field() ?><button class="btn btn-sm btn-soft-success"><i class="ri-check-line me-1"></i>Duyệt</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                <form method="POST" action="<?= url('orders/' . $order['id'] . '/cancel') ?>" class="d-inline" data-confirm="Hủy đơn hàng này?">
                                    <?= csrf_field() ?><button class="btn btn-sm btn-soft-warning"><i class="ri-close-circle-line me-1"></i>Hủy</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('orders/' . $order['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa đơn hàng?">
                                <?= csrf_field() ?><button class="btn btn-sm btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Khách hàng</h6>
                                <?php if ($order['contact_first_name']): ?>
                                    <p class="mb-1 fw-medium"><?= e(trim($order['contact_first_name'] . ' ' . ($order['contact_last_name'] ?? ''))) ?></p>
                                    <?php if ($order['contact_email']): ?><p class="mb-1 text-muted"><i class="ri-mail-line me-1"></i><?= e($order['contact_email']) ?></p><?php endif; ?>
                                    <?php if ($order['contact_phone']): ?><p class="mb-0 text-muted"><i class="ri-phone-line me-1"></i><?= e($order['contact_phone']) ?></p><?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">-</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Công ty</h6>
                                <?php if ($order['company_name']): ?>
                                    <p class="mb-1 fw-medium"><?= e($order['company_name']) ?></p>
                                    <?php if ($order['company_address']): ?><p class="mb-1 text-muted"><?= e($order['company_address']) ?></p><?php endif; ?>
                                    <?php if ($order['company_tax_code']): ?><p class="mb-0 text-muted">MST: <?= e($order['company_tax_code']) ?></p><?php endif; ?>
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
                                        <th>ĐVT</th>
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
                                        <td><code><?= e($item['product_sku'] ?? '-') ?></code></td>
                                        <td class="text-end"><?= $item['quantity'] ?></td>
                                        <td><?= e($item['unit']) ?></td>
                                        <td class="text-end"><?= format_money($item['unit_price']) ?></td>
                                        <td class="text-end"><?= $item['tax_rate'] ?>%</td>
                                        <td class="text-end fw-medium"><?= format_money($item['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7" class="text-end">Tạm tính:</td>
                                        <td class="text-end fw-medium"><?= format_money($order['subtotal']) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-end">Thuế:</td>
                                        <td class="text-end"><?= format_money($order['tax_amount']) ?></td>
                                    </tr>
                                    <?php if ($order['discount_amount'] > 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-end">Giảm giá:</td>
                                        <td class="text-end text-danger">-<?= format_money($order['discount_amount']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="7" class="text-end fw-bold fs-5">Tổng cộng:</td>
                                        <td class="text-end fw-bold fs-5 text-primary"><?= format_money($order['total']) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($order['notes']): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Ghi chú</h5></div>
                    <div class="card-body"><?= nl2br(e($order['notes'])) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Ngày lập</span>
                            <span><?= $order['issued_date'] ? format_date($order['issued_date']) : '-' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Hạn thanh toán</span>
                            <span><?= $order['due_date'] ? format_date($order['due_date']) : '-' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Phương thức TT</span>
                            <span><?= e($order['payment_method'] ?? '-') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Đã thanh toán</span>
                            <span class="fw-medium"><?= format_money($order['paid_amount']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Còn nợ</span>
                            <span class="fw-medium text-danger"><?= format_money(max(0, $order['total'] - $order['paid_amount'])) ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Người phụ trách</span>
                            <span><?= e($order['owner_name'] ?? '-') ?></span>
                        </div>
                        <?php if ($order['deal_title']): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Cơ hội</span>
                            <a href="<?= url('deals/' . $order['deal_id']) ?>"><?= e($order['deal_title']) ?></a>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Ngày tạo</span>
                            <span><?= format_datetime($order['created_at']) ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($order['payment_status'] !== 'paid' && $order['status'] !== 'cancelled'): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-1"></i> Thanh toán</h5></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('orders/' . $order['id'] . '/payment') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <input type="number" class="form-control form-control-sm" name="amount" placeholder="Số tiền" required min="1" value="<?= max(0, $order['total'] - $order['paid_amount']) ?>">
                            </div>
                            <div class="mb-2">
                                <select name="payment_method" class="form-select form-select-sm">
                                    <option value="bank_transfer">Chuyển khoản</option>
                                    <option value="cash">Tiền mặt</option>
                                    <option value="credit_card">Thẻ tín dụng</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <input type="date" class="form-control form-control-sm" name="pay_date" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm" name="description" placeholder="Ghi chú thanh toán">
                            </div>
                            <button type="submit" class="btn btn-success btn-sm w-100"><i class="ri-money-dollar-circle-line me-1"></i> Ghi nhận thanh toán</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
