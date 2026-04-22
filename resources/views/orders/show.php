<?php
$pageTitle = ($order['type'] === 'quote' ? 'Báo giá' : 'Đơn hàng') . ' ' . $order['order_number'];
$sc = ['draft'=>'secondary','pending'=>'warning','approved'=>'primary','processing'=>'info','completed'=>'success','cancelled'=>'danger'];
$sl = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','processing'=>'Đang xử lý','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
$pc = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success'];
$pl = ['unpaid'=>'Chưa thanh toán','partial'=>'Thanh toán một phần','paid'=>'Đã thanh toán'];

$cName = $order['c_company_name'] ?: ($order['c_full_name'] ?: trim(($order['contact_first_name'] ?? '') . ' ' . ($order['contact_last_name'] ?? '')));
$cPhone = $order['c_company_phone'] ?: ($order['c_phone'] ?? '');
$cEmail = $order['c_company_email'] ?: ($order['c_email'] ?? '');
$cAddress = $order['shipping_address'] ?: ($order['c_address'] ?? '');
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <span class="text-muted"><?= $order['type'] === 'quote' ? 'Báo giá' : 'Đơn hàng' ?></span><br>
        <h4 class="mb-0">
            <?= e($order['order_number']) ?>
            <span class="badge bg-<?= $sc[$order['status']] ?? 'secondary' ?> ms-2"><?= $sl[$order['status']] ?? $order['status'] ?></span>
            <span class="badge bg-<?= $pc[$order['payment_status']] ?? 'secondary' ?>-subtle text-<?= $pc[$order['payment_status']] ?? 'secondary' ?> ms-1"><?= $pl[$order['payment_status']] ?? '' ?></span>
        </h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('orders/' . $order['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
        <a href="<?= url('orders/pdf/' . $order['id']) ?>" class="btn btn-soft-info" target="_blank"><i class="ri-printer-line me-1"></i>In</a>
        <?php if ($order['status'] === 'pending'): ?>
            <form method="POST" action="<?= url('orders/' . $order['id'] . '/approve') ?>" class="d-inline" data-confirm="Duyệt đơn hàng này?">
                <?= csrf_field() ?><button class="btn btn-success"><i class="ri-check-line me-1"></i>Duyệt</button>
            </form>
        <?php endif; ?>
        <?php if (!in_array($order['status'], ['completed','cancelled'])): ?>
            <form method="POST" action="<?= url('orders/' . $order['id'] . '/cancel') ?>" class="d-inline" data-confirm="Hủy đơn hàng này?">
                <?= csrf_field() ?><button class="btn btn-soft-warning"><i class="ri-close-circle-line me-1"></i>Hủy</button>
            </form>
        <?php endif; ?>
        <form method="POST" action="<?= url('orders/' . $order['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa đơn hàng?">
            <?= csrf_field() ?><button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-9">
        <!-- Thông tin khách hàng -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2"><i class="ri-user-3-line me-1"></i>Khách hàng</h6>
                        <?php if ($cName): ?>
                            <p class="mb-1 fw-medium">
                                <a href="<?= url('contacts/' . $order['contact_id']) ?>"><?= e($cName) ?></a>
                                <?php if ($order['c_account_code'] ?? ''): ?><span class="text-muted">(<?= e($order['c_account_code']) ?>)</span><?php endif; ?>
                            </p>
                            <?php if ($order['c_tax_code'] ?? ''): ?><p class="mb-1 text-muted"><i class="ri-hashtag me-1"></i>MST: <?= e($order['c_tax_code']) ?></p><?php endif; ?>
                            <?php if ($cAddress): ?><p class="mb-1 text-muted"><i class="ri-map-pin-line me-1"></i><?= e($cAddress) ?></p><?php endif; ?>
                            <?php if ($cPhone): ?><p class="mb-1 text-muted"><i class="ri-phone-line me-1"></i><?= e($cPhone) ?></p><?php endif; ?>
                            <?php if ($cEmail): ?><p class="mb-0 text-muted"><i class="ri-mail-line me-1"></i><?= e($cEmail) ?></p><?php endif; ?>
                        <?php else: ?><p class="text-muted">-</p><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2"><i class="ri-contacts-book-line me-1"></i>Người liên hệ</h6>
                        <?php
                        $cp = null;
                        if ($order['contact_id']) {
                            $cp = \Core\Database::fetch("SELECT * FROM contact_persons WHERE contact_id = ? ORDER BY is_primary DESC, id LIMIT 1", [$order['contact_id']]);
                        }
                        ?>
                        <?php if ($cp): ?>
                            <p class="mb-1 fw-medium">
                                <?php if ($cp['title']): ?><?= e(ucfirst($cp['title'])) ?> <?php endif; ?>
                                <?= e($cp['full_name']) ?>
                                <?php if ($cp['position']): ?><span class="text-muted">- <?= e($cp['position']) ?></span><?php endif; ?>
                            </p>
                            <?php if ($cp['phone']): ?><p class="mb-1 text-muted"><i class="ri-phone-line me-1"></i><?= e($cp['phone']) ?></p><?php endif; ?>
                            <?php if ($cp['email']): ?><p class="mb-0 text-muted"><i class="ri-mail-line me-1"></i><?= e($cp['email']) ?></p><?php endif; ?>
                        <?php else: ?><p class="text-muted">-</p><?php endif; ?>

                        <?php if ($order['shipping_address'] ?? ''): ?>
                        <h6 class="text-muted mb-2 mt-3"><i class="ri-truck-line me-1"></i>Địa chỉ giao hàng</h6>
                        <p class="mb-0 text-muted"><?= e($order['shipping_address']) ?></p>
                        <?php if ($order['shipping_contact'] ?? ''): ?><p class="mb-0 text-muted"><i class="ri-user-line me-1"></i><?= e($order['shipping_contact']) ?> <?php if ($order['shipping_phone'] ?? ''): ?>· <i class="ri-phone-line me-1"></i><?= e($order['shipping_phone']) ?><?php endif; ?></p><?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sản phẩm -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-shopping-bag-line me-1"></i> Chi tiết sản phẩm</h5></div>
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
                                <th class="text-end">CK</th>
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
                                <td class="text-end"><?= ($item['discount'] ?? 0) > 0 ? format_money($item['discount']) : '-' ?></td>
                                <td class="text-end"><?= $item['tax_rate'] ?>%</td>
                                <td class="text-end fw-medium"><?= format_money($item['total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-end">Tạm tính:</td>
                                <td class="text-end fw-medium"><?= format_money($order['subtotal'] ?? 0) ?></td>
                            </tr>
                            <?php if (($order['tax_amount'] ?? 0) > 0): ?>
                            <tr>
                                <td colspan="8" class="text-end">Thuế VAT:</td>
                                <td class="text-end"><?= format_money($order['tax_amount']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (($order['discount_amount'] ?? 0) > 0): ?>
                            <tr>
                                <td colspan="8" class="text-end">Chiết khấu:</td>
                                <td class="text-end text-danger">-<?= format_money($order['discount_amount']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (($order['transport_amount'] ?? $order['shipping_fee'] ?? 0) > 0): ?>
                            <tr>
                                <td colspan="8" class="text-end">Phí vận chuyển:</td>
                                <td class="text-end"><?= format_money($order['transport_amount'] ?? $order['shipping_fee'] ?? 0) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (($order['installation_amount'] ?? $order['installation_fee'] ?? 0) > 0): ?>
                            <tr>
                                <td colspan="8" class="text-end">Phí lắp đặt:</td>
                                <td class="text-end"><?= format_money($order['installation_amount'] ?? $order['installation_fee'] ?? 0) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <td colspan="8" class="text-end fw-bold fs-5">Tổng cộng:</td>
                                <td class="text-end fw-bold fs-5 text-primary"><?= format_money($order['total'] ?? 0) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ghi chú & Điều khoản -->
        <?php if (($order['notes'] ?? null) || ($order['order_terms'] ?? null)): ?>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <?php if ($order['notes'] ?? null): ?>
                    <div class="<?= ($order['order_terms'] ?? null) ? 'col-md-6' : 'col-12' ?>">
                        <h6 class="text-muted mb-2"><i class="ri-sticky-note-line me-1"></i> Ghi chú</h6>
                        <p class="mb-0"><?= nl2br(e($order['notes'])) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['order_terms'] ?? null): ?>
                    <div class="<?= ($order['notes'] ?? null) ? 'col-md-6' : 'col-12' ?>">
                        <h6 class="text-muted mb-2"><i class="ri-shield-check-line me-1"></i> Điều khoản</h6>
                        <p class="mb-0"><?= nl2br(e($order['order_terms'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Trao đổi (Plugin) -->
        <?php if (function_exists('activity_exchange_render')) activity_exchange_render('order', $order['id']); ?>
    </div>

    <div class="col-lg-3">
        <!-- Thông tin -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-information-line me-1"></i> Thông tin</h5></div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width:40%">Trạng thái</td>
                            <td><span class="badge bg-<?= $sc[$order['status']] ?? 'secondary' ?>"><?= $sl[$order['status']] ?? $order['status'] ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Thanh toán</td>
                            <td><span class="badge bg-<?= $pc[$order['payment_status']] ?? 'secondary' ?>"><?= $pl[$order['payment_status']] ?? '' ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ngày lập</td>
                            <td><?= ($order['issued_date'] ?? null) ? format_date($order['issued_date']) : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Hạn thanh toán</td>
                            <td><?= ($order['due_date'] ?? null) ? format_date($order['due_date']) : '-' ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phương thức TT</td>
                            <td><?= e($order['payment_method'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Đã thanh toán</td>
                            <td class="fw-medium text-success"><?= format_money($order['paid_amount'] ?? 0) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Còn nợ</td>
                            <td class="fw-medium text-danger"><?= format_money(max(0, ($order['total'] ?? 0) - ($order['paid_amount'] ?? 0))) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Người phụ trách</td>
                            <td class="fw-medium"><?= e($order['owner_name'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Người tạo</td>
                            <td><?= e($order['created_by_name'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ngày tạo</td>
                            <td><?= format_datetime($order['created_at']) ?></td>
                        </tr>
                        <?php if ($order['deal_title'] ?? null): ?>
                        <tr>
                            <td class="text-muted">Cơ hội</td>
                            <td><a href="<?= url('deals/' . $order['deal_id']) ?>"><?= e($order['deal_title']) ?></a></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($order['lading_code'] ?? null): ?>
                        <tr>
                            <td class="text-muted">Mã vận đơn</td>
                            <td><code><?= e($order['lading_code']) ?></code></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kế toán (KT) -->
        <?php
        $ktInvoice = null;
        if (\App\Services\KtAccountingService::isConfigured() && ($order['type'] ?? '') === 'order') {
            $ktInvoice = \App\Services\KtAccountingService::fetchInvoiceForOrder((int)$order['id']);
        }
        $vatNum = $order['vat_invoice_number'] ?? ($ktInvoice['invoice_number'] ?? null);
        ?>
        <?php if ($vatNum || $ktInvoice || !empty($order['accounting_synced_at'])): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="ri-file-text-line me-1"></i> Kế toán</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <?php if ($vatNum): ?>
                        <tr>
                            <td class="text-muted" style="width:40%">Số HDBH</td>
                            <td class="fw-medium"><?= e($vatNum) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($ktInvoice['invoice_date'] ?? null): ?>
                        <tr>
                            <td class="text-muted">Ngày HD</td>
                            <td><?= format_date($ktInvoice['invoice_date']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($ktInvoice['accounting_status'] ?? null): ?>
                        <tr>
                            <td class="text-muted">Trạng thái KT</td>
                            <td><span class="badge bg-success-subtle text-success"><?= e($ktInvoice['accounting_status']) ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($order['accounting_entity'])): ?>
                        <tr>
                            <td class="text-muted">Pháp nhân</td>
                            <td><?= e($order['accounting_entity']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($order['accounting_synced_at'])): ?>
                        <tr>
                            <td class="text-muted">Sync lần cuối</td>
                            <td><?= format_datetime($order['accounting_synced_at']) ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Thanh toán -->
        <?php if (($order['payment_status'] ?? '') !== 'paid' && ($order['status'] ?? '') !== 'cancelled'): ?>
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-1"></i> Ghi nhận thanh toán</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('orders/' . $order['id'] . '/payment') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-2">
                        <input type="number" class="form-control" name="amount" placeholder="Số tiền" required min="1" value="<?= max(0, ($order['total'] ?? 0) - ($order['paid_amount'] ?? 0)) ?>">
                    </div>
                    <div class="mb-2">
                        <select name="payment_method" class="form-select">
                            <option value="bank_transfer">Chuyển khoản</option>
                            <option value="cash">Tiền mặt</option>
                            <option value="credit_card">Thẻ tín dụng</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="date" class="form-control" name="pay_date" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-2">
                        <input type="text" class="form-control" name="description" placeholder="Ghi chú thanh toán">
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="ri-money-dollar-circle-line me-1"></i> Ghi nhận</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Người liên quan -->
        <?php $rpEntityType = 'order'; $rpEntityId = $order['id']; $rpOwnerId = $order['owner_id'] ?? 0; $rpOwnerName = $order['owner_name'] ?? '-'; include BASE_PATH . '/resources/views/partials/related-people.php'; ?>

        <!-- Dòng thời gian -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-time-line me-1"></i> Dòng thời gian</h5></div>
            <div class="card-body">
                <div class="acitivity-timeline acitivity-main">
                    <div class="acitivity-item d-flex">
                        <div class="flex-shrink-0"><div class="avatar-xs acitivity-avatar"><div class="avatar-title rounded-circle bg-soft-primary text-primary"><i class="ri-add-line"></i></div></div></div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Tạo đơn hàng <span class="fw-normal text-muted">#<?= e($order['order_number']) ?></span></h6>
                            <p class="mb-0"><small>Người tạo: <strong><?= e($order['created_by_name'] ?? '-') ?></strong></small></p>
                            <p class="text-muted mb-0"><small><i class="ri-time-line me-1"></i><?= format_datetime($order['created_at']) ?></small></p>
                        </div>
                    </div>

                    <?php if ($order['approved_at'] ?? null): ?>
                    <div class="acitivity-item d-flex">
                        <div class="flex-shrink-0"><div class="avatar-xs acitivity-avatar"><div class="avatar-title rounded-circle bg-soft-success text-success"><i class="ri-checkbox-circle-line"></i></div></div></div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-success">Đã duyệt</h6>
                            <p class="text-muted mb-0"><small><i class="ri-time-line me-1"></i><?= format_datetime($order['approved_at']) ?></small></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (($order['paid_amount'] ?? 0) > 0): ?>
                    <div class="acitivity-item d-flex">
                        <div class="flex-shrink-0"><div class="avatar-xs acitivity-avatar"><div class="avatar-title rounded-circle bg-soft-info text-info"><i class="ri-money-dollar-circle-line"></i></div></div></div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Đã thanh toán</h6>
                            <p class="mb-0"><small><strong class="text-success"><?= format_money($order['paid_amount']) ?></strong> / <?= format_money($order['total'] ?? 0) ?></small></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($order['status'] === 'completed'): ?>
                    <div class="acitivity-item d-flex">
                        <div class="flex-shrink-0"><div class="avatar-xs acitivity-avatar"><div class="avatar-title rounded-circle bg-soft-success text-success"><i class="ri-check-double-line"></i></div></div></div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-success">Hoàn thành</h6>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($order['status'] === 'cancelled'): ?>
                    <div class="acitivity-item d-flex">
                        <div class="flex-shrink-0"><div class="avatar-xs acitivity-avatar"><div class="avatar-title rounded-circle bg-soft-danger text-danger"><i class="ri-close-line"></i></div></div></div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-danger">Đã hủy</h6>
                            <?php if ($order['cancelled_reason'] ?? null): ?><p class="mb-0"><small><?= e($order['cancelled_reason']) ?></small></p><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
