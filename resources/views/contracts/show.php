<?php
$pageTitle = 'Chi tiết hợp đồng ' . $contract['contract_number'];
$sc = ['pending' => 'warning', 'approved' => 'info', 'in_progress' => 'primary', 'renewed' => 'success', 'auto_renewed' => 'success', 'completed' => 'secondary', 'cancelled' => 'danger',
       'draft' => 'secondary', 'sent' => 'info', 'signed' => 'primary', 'active' => 'success', 'expired' => 'danger'];
$sl = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'in_progress' => 'Đang thực hiện', 'renewed' => 'Đã gia hạn', 'auto_renewed' => 'Tự động gia hạn', 'completed' => 'Đã kết thúc', 'cancelled' => 'Đã hủy',
       'draft' => 'Nháp', 'sent' => 'Đã gửi', 'signed' => 'Đã ký', 'active' => 'Hoạt động', 'expired' => 'Hết hạn'];
$pmethods = ['bank_transfer'=>'chuyển khoản','cash'=>'tiền mặt','credit_card'=>'thẻ tín dụng','other'=>'khác'];
$usageTypes = ['one_time'=>'Một lần','multiple'=>'Nhiều lần'];
?>

<!-- Header -->
<div class="page-title-box d-flex align-items-center justify-content-between">
    <div>
        <span class="text-muted">Quản lý hợp đồng / Chi tiết hợp đồng bán</span><br>
        <h4 class="mb-0">
            <span class="text-primary">Số: <?= e($contract['contract_number']) ?></span>
            <?php if (!empty($contract['title'])): ?> - <?= e($contract['title']) ?><?php endif; ?>
            <a href="<?= url('contracts/' . $contract['id'] . '/edit') ?>" class="text-muted ms-1"><i class="ri-pencil-line"></i></a>
            <span class="badge bg-<?= $sc[$contract['status']] ?? 'secondary' ?> ms-2"><?= $sl[$contract['status']] ?? $contract['status'] ?></span>
        </h4>
    </div>
    <div class="d-flex gap-2">
        <?php if (!in_array($contract['status'], ['cancelled', 'completed'])): ?>
        <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/cancel') ?>" class="d-inline" data-confirm="Hủy hợp đồng này?">
            <?= csrf_field() ?><button class="btn btn-soft-secondary">Hủy</button>
        </form>
        <?php endif; ?>

        <a href="mailto:?subject=<?= urlencode('Hợp đồng ' . $contract['contract_number']) ?>" class="btn btn-primary">Gửi email</a>

        <?php if (in_array($contract['status'], ['approved', 'in_progress'])): ?>
        <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/create-order') ?>" class="d-inline" data-confirm="Tạo đơn hàng bán từ hợp đồng này?">
            <?= csrf_field() ?><button class="btn btn-success">Tạo đơn hàng bán</button>
        </form>
        <?php endif; ?>

        <?php if ($contract['status'] === 'pending'): ?>
        <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/approve') ?>" class="d-inline" data-confirm="Duyệt hợp đồng này?">
            <?= csrf_field() ?><button class="btn btn-info">Duyệt</button>
        </form>
        <?php endif; ?>

        <?php if ($contract['status'] === 'approved'): ?>
        <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/start') ?>" class="d-inline" data-confirm="Bắt đầu thực hiện hợp đồng?">
            <?= csrf_field() ?><button class="btn btn-primary">Thực hiện</button>
        </form>
        <?php endif; ?>

        <?php if ($contract['status'] === 'in_progress'): ?>
        <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/complete') ?>" class="d-inline" data-confirm="Hoàn thành hợp đồng?">
            <?= csrf_field() ?><button class="btn btn-warning">Hoàn thành</button>
        </form>
        <?php endif; ?>

        <?php if (in_array($contract['status'], ['completed', 'in_progress'])): ?>
        <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/renew') ?>" class="d-inline" data-confirm="Gia hạn hợp đồng? Sẽ tạo hợp đồng mới.">
            <?= csrf_field() ?><button class="btn btn-soft-warning">Gia hạn</button>
        </form>
        <?php endif; ?>

        <a href="<?= url('contracts/' . $contract['id'] . '/print') ?>" class="btn btn-dark" target="_blank">In hợp đồng</a>
    </div>
</div>

<div class="row">
    <!-- MAIN CONTENT -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">

                <!-- Contract Title -->
                <h3 class="text-center fw-bold mb-4" style="text-transform:uppercase">HỢP ĐỒNG KINH TẾ</h3>

                <!-- BÊN A -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <strong class="text-primary">Bên A: <?= e($contract['party_a_name'] ?? '-') ?></strong>
                        <span>Mã số thuế: <?= e($contract['party_a_tax_code'] ?? '') ?></span>
                    </div>
                    <table class="table table-bordered table-sm mb-0">
                        <tr>
                            <td colspan="2"><strong>Địa chỉ:</strong> <?= e($contract['party_a_address'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Điện thoại:</strong> <?= e($contract['party_a_phone'] ?? '') ?></td>
                            <td><strong>Fax:</strong> <?= e($contract['party_a_fax'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Người đại diện:</strong> <?= e($contract['party_a_representative'] ?? '') ?></td>
                            <td><strong>Chức vụ:</strong> <?= e($contract['party_a_position'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tài khoản số:</strong> <?= e($contract['party_a_bank_account'] ?? '') ?></td>
                            <td><strong>Ngân hàng:</strong> <?= e($contract['party_a_bank_name'] ?? '') ?></td>
                        </tr>
                    </table>
                </div>

                <!-- BÊN B -->
                <div class="mb-4">
                    <?php
                    $partyBName = $contract['party_b_name'] ?? trim(($contract['contact_first_name'] ?? '') . ' ' . ($contract['contact_last_name'] ?? ''));
                    ?>
                    <div class="d-flex justify-content-between mb-1">
                        <strong>Bên B: <?= e($partyBName ?: '-') ?></strong>
                        <span>Mã số thuế: <?= e($contract['party_b_tax_code'] ?? '') ?></span>
                    </div>
                    <table class="table table-bordered table-sm mb-0">
                        <tr>
                            <td colspan="2"><strong>Địa chỉ:</strong> <?= e($contract['party_b_address'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Điện thoại:</strong> <?= e($contract['party_b_phone'] ?? $contract['contact_phone'] ?? '') ?></td>
                            <td><strong>Fax:</strong> <?= e($contract['party_b_fax'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Người đại diện:</strong> <?= e($contract['party_b_representative'] ?? '') ?></td>
                            <td><strong>Chức vụ:</strong> <?= e($contract['party_b_position'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tài khoản số:</strong> <?= e($contract['party_b_bank_account'] ?? '') ?></td>
                            <td><strong>Ngân hàng:</strong> <?= e($contract['party_b_bank_name'] ?? '') ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Hiệu lực -->
                <p class="mb-3">
                    <em>Hợp đồng có hiệu lực từ ngày: <strong><?= !empty($contract['start_date']) ? date('d/m/Y', strtotime($contract['start_date'])) : '—' ?></strong>
                    tới ngày: <strong><?= !empty($contract['end_date']) ? date('d/m/Y', strtotime($contract['end_date'])) : '—' ?></strong></em>
                </p>

                <!-- PRODUCTS TABLE -->
                <?php if (!empty($items)): ?>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Tên sản phẩm</th>
                                <th style="width:70px">Đơn vị</th>
                                <th style="width:80px" class="text-end">Số lượng</th>
                                <th style="width:110px" class="text-end">Đơn giá</th>
                                <th style="width:80px" class="text-end">Chiết khấu %</th>
                                <th style="width:100px" class="text-end">CK thành tiền</th>
                                <th style="width:80px" class="text-end">Thuế VAT %</th>
                                <th style="width:120px" class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $i => $item): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($item['product_sku'])): ?>
                                        <span class="text-primary"><?= e($item['product_sku']) ?></span> -
                                    <?php endif; ?>
                                    <?= e($item['product_name']) ?>
                                </td>
                                <td><?= e($item['unit'] ?? '') ?></td>
                                <td class="text-end"><?= number_format((float)$item['quantity'], 2) ?></td>
                                <td class="text-end"><?= number_format((float)$item['unit_price']) ?></td>
                                <td class="text-end"><?= number_format((float)($item['discount_percent'] ?? 0), 2) ?></td>
                                <td class="text-end"><?= number_format((float)($item['discount'] ?? 0)) ?></td>
                                <td class="text-end"><?= number_format((float)($item['tax_rate'] ?? 0), 2) ?></td>
                                <td class="text-end text-primary fw-medium"><?= number_format((float)$item['total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-end fw-medium">Tổng tiền hàng</td>
                                <td class="text-end text-primary fw-medium"><?= number_format((float)($contract['subtotal'] ?? 0)) ?></td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-end">Phí vận chuyển <?= ($contract['shipping_after_tax'] ?? 0) ? 'sau' : 'trước' ?> thuế <?= number_format((float)($contract['shipping_fee_percent'] ?? 0), 2) ?>%</td>
                                <td class="text-end text-primary"><?= number_format((float)($contract['shipping_fee'] ?? 0)) ?></td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-end">Chiết khấu <?= ($contract['discount_after_tax'] ?? 0) ? 'sau' : 'trước' ?> thuế <?= number_format((float)($contract['discount_percent'] ?? 0), 2) ?> %</td>
                                <td class="text-end text-primary"><?= number_format((float)($contract['discount_amount'] ?? 0)) ?></td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-end">Thuế VAT <?= number_format((float)($contract['vat_percent'] ?? 0), 2) ?> %</td>
                                <td class="text-end text-primary"><?= number_format((float)($contract['vat_amount'] ?? 0)) ?></td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-end">Phí lắp đặt <?= number_format((float)($contract['installation_fee_percent'] ?? 0), 2) ?>%</td>
                                <td class="text-end text-primary"><?= number_format((float)($contract['installation_fee'] ?? 0)) ?></td>
                            </tr>
                            <tr class="table-light">
                                <td colspan="8" class="text-end fw-bold">Tổng tiền thanh toán</td>
                                <td class="text-end fw-bold text-primary fs-5"><?= number_format((float)($contract['value'] ?? 0)) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endif; ?>

                <!-- KỲ THANH TOÁN -->
                <h6 class="fw-bold mb-2">Kỳ thanh toán</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tiêu đề</th>
                                <th style="width:80px" class="text-end">%</th>
                                <th style="width:120px" class="text-end">Thành tiền</th>
                                <th style="width:120px">Ngày</th>
                                <th>Mô tả</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($paymentSchedules)): ?>
                                <?php foreach ($paymentSchedules as $ps): ?>
                                <tr>
                                    <td><?= e($ps['title'] ?? '') ?></td>
                                    <td class="text-end"><?= number_format((float)($ps['percent'] ?? 0), 2) ?> %</td>
                                    <td class="text-end"><?= number_format((float)($ps['amount'] ?? 0)) ?></td>
                                    <td><?= !empty($ps['due_date']) ? date('d/m/Y', strtotime($ps['due_date'])) : '' ?></td>
                                    <td><?= e($ps['description'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr class="table-light">
                                <td class="fw-medium">Tổng</td>
                                <td class="text-end fw-medium">0.00 %</td>
                                <td class="text-end fw-medium">0</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TERMS (điều khoản) -->
                <?php if (!empty($contract['terms'])): ?>
                <div class="mb-4">
                    <div class="text-muted" style="white-space:pre-wrap"><?= nl2br(e($contract['terms'])) ?></div>
                </div>
                <?php endif; ?>

                <!-- THÔNG TIN HỢP ĐỒNG -->
                <h6 class="fw-bold mb-2">Thông tin hợp đồng</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm mb-0">
                        <tr>
                            <td style="width:33%"><strong>Số hợp đồng:</strong> <?= e($contract['contract_number']) ?></td>
                            <td style="width:33%"><strong>Kiểu hợp đồng:</strong> <?= e($contract['type'] ?? '') ?></td>
                            <td style="width:34%"><strong>Báo giá:</strong> <?= e($contract['quote_number'] ?? '') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Hình thức thanh toán:</strong> <?= $pmethods[$contract['payment_method'] ?? ''] ?? ($contract['payment_method'] ?? '') ?></td>
                            <td><strong>Thời gian thực tế bắt đầu:</strong> <?= !empty($contract['actual_start_date']) ? date('d/m/Y', strtotime($contract['actual_start_date'])) : '' ?></td>
                            <td><strong>Thời gian thực tế kết thúc:</strong> <?= !empty($contract['actual_end_date']) ? date('d/m/Y', strtotime($contract['actual_end_date'])) : '' ?></td>
                        </tr>
                        <tr>
                            <td><strong>Hợp đồng sử dụng:</strong> <?= $usageTypes[$contract['usage_type'] ?? ''] ?? ($contract['usage_type'] ?? '') ?></td>
                            <td><strong>Giá trị thực hiện:</strong> <?= number_format((float)($contract['executed_amount'] ?? 0)) ?></td>
                            <td><strong>Đã thực hiện:</strong> <?= number_format((float)($contract['actual_value'] ?? 0)) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Dự án:</strong> <?= e($contract['project'] ?? '') ?></td>
                            <td><strong>Địa điểm:</strong> <?= e($contract['location'] ?? '') ?></td>
                            <td><strong>Hợp đồng gốc:</strong> <?= e($contract['parent_contract_number'] ?? '') ?></td>
                        </tr>
                    </table>
                </div>

                <!-- HỢP ĐỒNG LIÊN QUAN -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">Hợp đồng liên quan</h6>
                </div>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên hợp đồng</th>
                                <th>Số hợp đồng</th>
                                <th>Trạng thái</th>
                                <th>Kiểu hợp đồng</th>
                                <th>Ngày có hiệu lực</th>
                                <th>Ngày hết hiệu lực</th>
                                <th>Khách hàng</th>
                                <th>Người phụ trách</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($relatedContracts)): ?>
                                <?php foreach ($relatedContracts as $rc): ?>
                                <tr>
                                    <td><a href="<?= url('contracts/' . $rc['id']) ?>"><?= e($rc['title']) ?></a></td>
                                    <td><?= e($rc['contract_number']) ?></td>
                                    <td><span class="badge bg-<?= $sc[$rc['status']] ?? 'secondary' ?>"><?= $sl[$rc['status']] ?? $rc['status'] ?></span></td>
                                    <td><?= e($rc['type']) ?></td>
                                    <td><?= !empty($rc['start_date']) ? date('d/m/Y', strtotime($rc['start_date'])) : '' ?></td>
                                    <td><?= !empty($rc['end_date']) ? date('d/m/Y', strtotime($rc['end_date'])) : '' ?></td>
                                    <td><?= e($rc['contact_name'] ?? '') ?></td>
                                    <td><?= e($rc['owner_name'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center text-muted">Không có hợp đồng liên quan</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- CÁC THÔNG TIN BỔ TRỢ -->
                <h6 class="fw-bold mb-2">Các thông tin bổ trợ</h6>
                <div class="mb-3">
                    <?php if (!empty($contract['installation_address'])): ?>
                    <p><strong>Địa chỉ lắp đặt:</strong> <?= e($contract['installation_address']) ?></p>
                    <?php endif; ?>
                    <div class="d-flex flex-column gap-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" disabled <?= ($contract['auto_renew'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label">Tự động gia hạn hợp đồng</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" disabled <?= ($contract['auto_create_order'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label">Tự động tạo đơn hàng</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" disabled <?= ($contract['auto_notify_expiry'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label">Tự động báo động sắp hết hạn hợp đồng</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" disabled <?= ($contract['auto_send_sms'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label">Tự động gửi SMS thông báo</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" disabled <?= ($contract['auto_send_email'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label">Tự động gửi email hợp đồng mới</label>
                        </div>
                    </div>
                </div>

                <?php if (!empty($contract['notes'])): ?>
                <div class="mb-3">
                    <h6 class="fw-bold mb-1">Ghi chú</h6>
                    <div class="border rounded p-3 bg-light"><?= nl2br(e($contract['notes'])) ?></div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Trao đổi (Plugin) -->
        <?php if (function_exists('activity_exchange_render')) activity_exchange_render('contract', $contract['id']); ?>
    </div>

    <!-- SIDEBAR -->
    <div class="col-lg-3">
        <!-- Người liên quan -->
        <?php $rpEntityType = 'contract'; $rpEntityId = $contract['id']; $rpOwnerId = $contract['owner_id'] ?? 0; $rpOwnerName = $contract['owner_name'] ?? '-'; include BASE_PATH . '/resources/views/partials/related-people.php'; ?>

        <!-- Đơn hàng liên quan -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-file-list-3-line me-1"></i> Đơn hàng liên quan</h5></div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php
                                $osc = ['draft'=>'secondary','sent'=>'info','confirmed'=>'primary','processing'=>'warning','completed'=>'success','cancelled'=>'danger',
                                         'pending'=>'warning','approved'=>'info','unpaid'=>'danger','paid'=>'success','collected'=>'success'];
                                $osl = ['draft'=>'Nháp','sent'=>'Đã gửi','confirmed'=>'Xác nhận','processing'=>'Đang xử lý','completed'=>'Hoàn thành','cancelled'=>'Đã hủy',
                                         'pending'=>'Chờ duyệt','approved'=>'Đã duyệt','unpaid'=>'Chưa TT','paid'=>'Đã TT','collected'=>'Đã thu'];
                                foreach ($orders as $o):
                                ?>
                                <tr>
                                    <td><a href="<?= url('orders/' . $o['id']) ?>"><?= e($o['order_number']) ?></a></td>
                                    <td><span class="badge bg-<?= $osc[$o['status']] ?? 'secondary' ?>"><?= $osl[$o['status']] ?? $o['status'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-muted text-center">Chưa có</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Cơ hội liên quan -->
        <?php if (!empty($contract['deal_title'])): ?>
        <div class="card">
            <div class="card-header"><h6 class="card-title mb-0 fw-bold">CƠ HỘI LIÊN QUAN</h6></div>
            <div class="card-body">
                <a href="<?= url('deals/' . $contract['deal_id']) ?>" class="fw-medium">
                    <i class="ri-hand-coin-line me-1"></i><?= e($contract['deal_title']) ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tài liệu đính kèm -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-attachment-2 me-1"></i> Tài liệu đính kèm</h5></div>
            <div class="card-body">
                <?php
                $fileIcons = ['pdf'=>'ri-file-pdf-line text-danger','doc'=>'ri-file-word-line text-primary','docx'=>'ri-file-word-line text-primary',
                    'xls'=>'ri-file-excel-line text-success','xlsx'=>'ri-file-excel-line text-success',
                    'jpg'=>'ri-image-line text-warning','jpeg'=>'ri-image-line text-warning','png'=>'ri-image-line text-warning','gif'=>'ri-image-line text-warning'];
                ?>
                <?php if (!empty($attachments)): ?>
                    <?php foreach ($attachments as $att):
                        $ext = strtolower(pathinfo($att['file_name'], PATHINFO_EXTENSION));
                        $icon = $fileIcons[$ext] ?? 'ri-file-line text-muted';
                        $size = $att['file_size'] > 1048576 ? round($att['file_size']/1048576,1).'MB' : round($att['file_size']/1024).'KB';
                    ?>
                    <div class="d-flex align-items-center gap-2 p-2 rounded mb-2" style="background:#f8f9fa">
                        <i class="<?= $icon ?> fs-20"></i>
                        <div class="flex-grow-1 overflow-hidden">
                            <a href="<?= asset($att['file_path']) ?>" target="_blank" class="d-block text-truncate fw-medium" style="font-size:13px"><?= e($att['file_name']) ?></a>
                            <small class="text-muted"><?= $size ?></small>
                        </div>
                        <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/attachment/' . $att['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa file này?">
                            <?= csrf_field() ?>
                            <button class="btn btn-soft-danger btn-icon" style="width:28px;height:28px"><i class="ri-delete-bin-line" style="font-size:12px"></i></button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <form method="POST" action="<?= url('contracts/' . $contract['id'] . '/attachment') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <label class="d-flex align-items-center justify-content-center gap-2 p-3 rounded border-dashed text-muted" style="border:2px dashed #ddd;cursor:pointer;font-size:13px">
                        <i class="ri-upload-cloud-line fs-18"></i>
                        <span id="uploadLabel">Kéo thả hoặc bấm để chọn file</span>
                        <input type="file" name="file" class="d-none" required onchange="document.getElementById('uploadLabel').textContent=this.files[0]?.name||'Chọn file';this.closest('form').querySelector('.btn-upload').style.display='inline-block'">
                    </label>
                    <button type="submit" class="btn btn-primary w-100 mt-2 btn-upload" style="display:none"><i class="ri-upload-line me-1"></i> Tải lên</button>
                </form>
                <small class="text-muted d-block mt-1">PDF, Word, Excel, hình ảnh. Tối đa 10MB</small>
            </div>
        </div>
    </div>
</div>
