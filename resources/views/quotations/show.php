<?php
$pageTitle = 'Báo giá ' . $quotation['quote_number'];
$sc = ['draft'=>'secondary','pending'=>'warning','approved'=>'primary','sent'=>'info','accepted'=>'success','rejected'=>'danger','expired'=>'warning','converted'=>'dark'];
$sl = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','sent'=>'Đã gửi KH','accepted'=>'KH chấp nhận','rejected'=>'Từ chối','expired'=>'Hết hạn','converted'=>'Đã chuyển ĐH'];
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <div>
                <span class="text-muted">Báo giá</span><br>
                <h4 class="mb-0">
                    BÁO GIÁ <?= e($quotation['quote_number']) ?>
                    <span class="badge bg-<?= $sc[$quotation['status']] ?? 'secondary' ?> ms-2"><?= $sl[$quotation['status']] ?? '' ?></span>
                </h4>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= url('quotations/' . $quotation['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line me-1"></i>Sửa</a>
                <a href="<?= url('quotations/' . $quotation['id'] . '/pdf') ?>" class="btn btn-soft-info" target="_blank"><i class="ri-printer-line me-1"></i>PDF</a>

                <?php if ($quotation['status'] === 'draft'): ?>
                    <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/submit') ?>" class="d-inline" data-confirm="Gửi duyệt báo giá này?">
                        <?= csrf_field() ?><button class="btn btn-warning"><i class="ri-send-plane-line me-1"></i>Gửi duyệt</button>
                    </form>
                <?php endif; ?>

                <?php if ($quotation['status'] === 'pending'): ?>
                    <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/approve') ?>" class="d-inline" data-confirm="Duyệt báo giá này?">
                        <?= csrf_field() ?><button class="btn btn-success"><i class="ri-check-line me-1"></i>Duyệt</button>
                    </form>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectApprovalModal"><i class="ri-close-line me-1"></i>Từ chối</button>
                <?php endif; ?>

                <?php if ($quotation['status'] === 'approved'): ?>
                    <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/send') ?>" class="d-inline" data-confirm="Gửi báo giá cho khách hàng?">
                        <?= csrf_field() ?><button class="btn btn-success"><i class="ri-mail-send-line me-1"></i>Gửi khách</button>
                    </form>
                <?php endif; ?>

                <?php if (in_array($quotation['status'], ['approved', 'accepted', 'sent'])): ?>
                    <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/convert') ?>" class="d-inline" data-confirm="Tạo đơn hàng từ báo giá này?">
                        <?= csrf_field() ?><button class="btn btn-soft-success"><i class="ri-shopping-cart-line me-1"></i>Tạo đơn hàng</button>
                    </form>
                    <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/create-contract') ?>" class="d-inline" data-confirm="Tạo hợp đồng từ báo giá này?">
                        <?= csrf_field() ?><button class="btn btn-soft-warning"><i class="ri-file-shield-line me-1"></i>Tạo hợp đồng</button>
                    </form>
                <?php endif; ?>

                <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/delete') ?>" class="d-inline" data-confirm="Xóa báo giá này?">
                    <?= csrf_field() ?><button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa</button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Quotation Info -->
                <div class="card">
                    <div class="card-header">
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
                                    <?php if (($quotation['shipping_fee'] ?? 0) > 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-end">Phí vận chuyển<?= !empty($quotation['shipping_note']) ? ' <small class="text-muted">(' . e($quotation['shipping_note']) . ')</small>' : '' ?>:</td>
                                        <td class="text-end"><?= format_money($quotation['shipping_fee']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (($quotation['installation_fee'] ?? 0) > 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-end">Phí lắp đặt:</td>
                                        <td class="text-end"><?= format_money($quotation['installation_fee']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="table-primary">
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

                <!-- Attachments -->
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0"><i class="ri-attachment-2 me-1"></i> Tài liệu đính kèm</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/attachment') ?>" enctype="multipart/form-data" class="mb-3">
                            <?= csrf_field() ?>
                            <div class="d-flex gap-2">
                                <input type="file" name="attachment" class="form-control" required>
                                <button type="submit" class="btn btn-primary flex-shrink-0"><i class="ri-upload-2-line me-1"></i> Tải lên</button>
                            </div>
                            <small class="text-muted">Tối đa 10MB. PDF, Word, Excel, hình ảnh...</small>
                        </form>
                        <?php if (!empty($attachments)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($attachments as $att):
                                $icon = 'ri-file-line';
                                $mime = $att['mime_type'] ?? '';
                                if (str_contains($mime, 'pdf')) $icon = 'ri-file-pdf-line text-danger';
                                elseif (str_contains($mime, 'word') || str_contains($mime, 'document')) $icon = 'ri-file-word-line text-primary';
                                elseif (str_contains($mime, 'sheet') || str_contains($mime, 'excel')) $icon = 'ri-file-excel-line text-success';
                                elseif (str_contains($mime, 'image')) $icon = 'ri-image-line text-info';
                                $size = $att['file_size'] < 1048576 ? round($att['file_size'] / 1024) . ' KB' : round($att['file_size'] / 1048576, 1) . ' MB';
                            ?>
                            <div class="list-group-item d-flex align-items-center px-0">
                                <i class="<?= $icon ?> fs-4 me-3"></i>
                                <div class="flex-grow-1">
                                    <a href="<?= url('uploads/quotations/' . $att['filename']) ?>" target="_blank" class="fw-medium"><?= e($att['original_name']) ?></a>
                                    <div class="text-muted fs-12"><?= $size ?> &middot; <?= e($att['user_name'] ?? '') ?> &middot; <?= date('d/m/Y H:i', strtotime($att['created_at'])) ?></div>
                                </div>
                                <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/attachment/' . $att['id'] . '/delete') ?>" onsubmit="return confirm('Xóa tài liệu này?')" class="ms-2">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-soft-danger btn-icon btn-sm"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center mb-0">Chưa có tài liệu đính kèm</p>
                        <?php endif; ?>
                    </div>
                </div>
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
                            <?php if ($quotation['sent_at'] ?? null): ?>
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

                <?php if ($quotation['converted_order_id'] ?? null): ?>
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

<!-- Modal từ chối duyệt -->
<div class="modal fade" id="rejectApprovalModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('quotations/' . $quotation['id'] . '/reject-approval') ?>">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Từ chối duyệt báo giá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Lý do từ chối</label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="Nhập lý do từ chối duyệt..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger"><i class="ri-close-line me-1"></i>Từ chối duyệt</button>
                </div>
            </div>
        </form>
    </div>
</div>
