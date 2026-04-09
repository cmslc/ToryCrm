<?php
$pageTitle = 'Báo giá ' . $quotation['quote_number'];
$sc = ['draft'=>'secondary','sent'=>'info','accepted'=>'success','rejected'=>'danger','expired'=>'warning'];
$sl = ['draft'=>'Nháp','sent'=>'Đã gửi','accepted'=>'Chấp nhận','rejected'=>'Từ chối','expired'=>'Hết hạn'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= $pageTitle ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('quotations') ?>">Báo giá</a></li>
                <li class="breadcrumb-item active"><?= e($quotation['quote_number']) ?></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Quotation Info -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1"><?= e($quotation['quote_number']) ?> <?= $quotation['title'] ? '- ' . e($quotation['title']) : '' ?></h5>
                            <span class="badge bg-<?= $sc[$quotation['status']] ?? 'secondary' ?>"><?= $sl[$quotation['status']] ?? '' ?></span>
                        </div>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="<?= url('quotations/' . $quotation['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                            <a href="<?= url('quotations/' . $quotation['id'] . '/pdf') ?>" class="btn btn-soft-info" target="_blank"><i class="ri-printer-line me-1"></i>PDF</a>
                            <?php if ($quotation['status'] === 'draft'): ?>
                                <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/send') ?>" class="d-inline" data-confirm="Gửi báo giá này?">
                                    <?= csrf_field() ?><button class="btn btn-soft-success"><i class="ri-send-plane-line me-1"></i>Gửi</button>
                                </form>
                            <?php endif; ?>
                            <?php if (in_array($quotation['status'], ['accepted', 'sent'])): ?>
                                <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/convert') ?>" class="d-inline" data-confirm="Chuyển thành đơn hàng?">
                                    <?= csrf_field() ?><button class="btn btn-soft-warning"><i class="ri-swap-line me-1"></i>Chuyển đơn hàng</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa báo giá này?">
                                <?= csrf_field() ?><button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Khách hàng</h6>
                                <?php if ($quotation['contact_first_name']): ?>
                                    <p class="mb-1 fw-medium"><?= e(trim($quotation['contact_first_name'] . ' ' . ($quotation['contact_last_name'] ?? ''))) ?></p>
                                    <?php if ($quotation['contact_email']): ?><p class="mb-1 text-muted"><i class="ri-mail-line me-1"></i><?= e($quotation['contact_email']) ?></p><?php endif; ?>
                                    <?php if ($quotation['contact_phone']): ?><p class="mb-0 text-muted"><i class="ri-phone-line me-1"></i><?= e($quotation['contact_phone']) ?></p><?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">-</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Công ty</h6>
                                <?php if ($quotation['company_name']): ?>
                                    <p class="mb-1 fw-medium"><?= e($quotation['company_name']) ?></p>
                                    <?php if ($quotation['company_address']): ?><p class="mb-1 text-muted"><?= e($quotation['company_address']) ?></p><?php endif; ?>
                                    <?php if ($quotation['company_tax_code']): ?><p class="mb-0 text-muted">MST: <?= e($quotation['company_tax_code']) ?></p><?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">-</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Chi tiết sản phẩm / Dịch vụ</h5></div>
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
                                        <td class="text-end fw-medium"><?= format_money($quotation['subtotal'] ?? 0) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" class="text-end">Thuế:</td>
                                        <td class="text-end"><?= format_money($quotation['tax_amount'] ?? 0) ?></td>
                                    </tr>
                                    <?php if (($quotation['discount_amount'] ?? 0) > 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-end">Giảm giá:</td>
                                        <td class="text-end text-danger">-<?= format_money($quotation['discount_amount']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="7" class="text-end fw-bold fs-5">Tổng cộng:</td>
                                        <td class="text-end fw-bold fs-5 text-primary"><?= format_money($quotation['total'] ?? 0) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if ($quotation['notes']): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Ghi chú</h5></div>
                    <div class="card-body"><?= nl2br(e($quotation['notes'])) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($quotation['terms']): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Điều khoản</h5></div>
                    <div class="card-body"><?= nl2br(e($quotation['terms'])) ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Info card -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Hiệu lực đến</span>
                            <?php if ($quotation['valid_until']): ?>
                                <?php $isExpired = $quotation['valid_until'] < date('Y-m-d'); ?>
                                <span class="badge bg-<?= $isExpired ? 'danger' : 'success' ?>"><?= format_date($quotation['valid_until']) ?></span>
                            <?php else: ?>
                                <span>-</span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Người phụ trách</span>
                            <span><?= e($quotation['owner_name'] ?? '-') ?></span>
                        </div>
                        <?php if ($quotation['deal_title']): ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Cơ hội</span>
                            <a href="<?= url('deals/' . $quotation['deal_id']) ?>"><?= e($quotation['deal_title']) ?></a>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Lượt xem</span>
                            <span><i class="ri-eye-line me-1"></i><?= (int)($quotation['view_count'] ?? 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Người tạo</span>
                            <span><?= e($quotation['created_by_name'] ?? '-') ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Ngày tạo</span>
                            <span><?= format_datetime($quotation['created_at']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Portal Link -->
                <?php if ($quotation['portal_token']): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-link me-1"></i> Link khách hàng</h5></div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" class="form-control" id="portalLink" value="<?= url('quote/' . $quotation['portal_token']) ?>" readonly>
                            <button class="btn btn-soft-primary" onclick="navigator.clipboard.writeText(document.getElementById('portalLink').value); this.innerHTML='<i class=\'ri-check-line\'></i>'">
                                <i class="ri-file-copy-line"></i>
                            </button>
                        </div>
                        <small class="text-muted mt-1 d-block">Chia sẻ link này để khách hàng xem và phản hồi báo giá.</small>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Timeline -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-time-line me-1"></i> Dòng thời gian</h5></div>
                    <div class="card-body">
                        <div class="timeline-sm">
                            <div class="timeline-sm-item">
                                <span class="timeline-sm-date"><?= format_datetime($quotation['created_at']) ?></span>
                                <h6 class="mb-0">Tạo báo giá</h6>
                            </div>
                            <?php if ($quotation['sent_at']): ?>
                            <div class="timeline-sm-item">
                                <span class="timeline-sm-date"><?= format_datetime($quotation['sent_at']) ?></span>
                                <h6 class="mb-0 text-info">Đã gửi</h6>
                            </div>
                            <?php endif; ?>
                            <?php if (($quotation['view_count'] ?? 0) > 0): ?>
                            <div class="timeline-sm-item">
                                <span class="timeline-sm-date">Đã xem <?= $quotation['view_count'] ?> lần</span>
                                <h6 class="mb-0 text-primary">Khách hàng đã xem</h6>
                            </div>
                            <?php endif; ?>
                            <?php if ($quotation['accepted_at']): ?>
                            <div class="timeline-sm-item">
                                <span class="timeline-sm-date"><?= format_datetime($quotation['accepted_at']) ?></span>
                                <h6 class="mb-0 text-success">Khách hàng chấp nhận</h6>
                            </div>
                            <?php endif; ?>
                            <?php if ($quotation['rejected_at']): ?>
                            <div class="timeline-sm-item">
                                <span class="timeline-sm-date"><?= format_datetime($quotation['rejected_at']) ?></span>
                                <h6 class="mb-0 text-danger">Khách hàng từ chối</h6>
                                <?php if ($quotation['reject_reason']): ?>
                                    <p class="text-muted mb-0"><?= e($quotation['reject_reason']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if ($quotation['converted_order_id']): ?>
                <div class="card border-success">
                    <div class="card-body text-center">
                        <i class="ri-checkbox-circle-line text-success fs-1"></i>
                        <p class="mb-2">Đã chuyển thành đơn hàng</p>
                        <a href="<?= url('orders/' . $quotation['converted_order_id']) ?>" class="btn btn-success">Xem đơn hàng</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
