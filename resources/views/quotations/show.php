<?php
$pageTitle = 'Báo giá ' . $quotation['quote_number'];
$sc = ['draft'=>'secondary','pending'=>'warning','approved'=>'success','rejected'=>'danger','expired'=>'warning','converted'=>'info'];
$sl = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối','expired'=>'Hết hạn','converted'=>'Đã tạo ĐH'];
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
                <button type="button" class="btn btn-soft-info" data-bs-toggle="modal" data-bs-target="#pdfTemplateModal"><i class="ri-printer-line me-1"></i>In</button>

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

                <?php if (in_array($quotation['status'], ['approved', 'converted'], true)): ?>
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
            <div class="col-lg-9">
                <!-- Thông tin khách hàng -->
                <?php
                $cName = $quotation['c_company_name'] ?: ($quotation['c_full_name'] ?: trim(($quotation['contact_first_name'] ?? '') . ' ' . ($quotation['contact_last_name'] ?? '')));
                $cPhone = $quotation['contact_phone'] ?: ($quotation['c_company_phone'] ?: $quotation['c_phone'] ?? '');
                $cEmail = $quotation['contact_email'] ?: ($quotation['c_company_email'] ?: $quotation['c_email'] ?? '');
                $cAddress = $quotation['address'] ?: ($quotation['c_address'] ?? '');
                $cTax = $quotation['c_tax_code'] ?? '';
                $cCode = $quotation['c_account_code'] ?? '';
                ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-user-3-line me-1"></i> Thông tin khách hàng</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Khách hàng</h6>
                                <?php if ($cName): ?>
                                    <p class="mb-1 fw-medium">
                                        <a href="<?= url('contacts/' . $quotation['contact_id']) ?>"><?= e($cName) ?></a>
                                    </p>
                                    <?php if ($cCode): ?><p class="mb-1 text-muted"><i class="ri-user-line me-1"></i>Mã KH: <?= e($cCode) ?></p><?php endif; ?>
                                    <?php if ($cTax): ?><p class="mb-1 text-muted"><i class="ri-hashtag me-1"></i>MST: <?= e($cTax) ?></p><?php endif; ?>
                                    <?php if ($cAddress): ?><p class="mb-1 text-muted"><i class="ri-map-pin-line me-1"></i><?= e($cAddress) ?></p><?php endif; ?>
                                    <?php if ($cPhone): ?><p class="mb-1 text-muted"><i class="ri-phone-line me-1"></i><?= e($cPhone) ?></p><?php endif; ?>
                                    <?php if ($cEmail): ?><p class="mb-0 text-muted"><i class="ri-mail-line me-1"></i><?= e($cEmail) ?></p><?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">-</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Người liên hệ</h6>
                                <?php
                                $cp = null;
                                if ($quotation['contact_person_id'] ?? null) {
                                    $cp = \Core\Database::fetch("SELECT * FROM contact_persons WHERE id = ?", [$quotation['contact_person_id']]);
                                } elseif ($quotation['contact_id']) {
                                    $cp = \Core\Database::fetch("SELECT * FROM contact_persons WHERE contact_id = ? ORDER BY is_primary DESC, id LIMIT 1", [$quotation['contact_id']]);
                                }
                                ?>
                                <?php if ($cp): ?>
                                    <p class="mb-1 fw-medium">
                                        <?php if ($cp['title']): ?><span class="me-1"><?= e(ucfirst($cp['title'])) ?></span><?php endif; ?>
                                        <?= e($cp['full_name']) ?>
                                        <?php if ($cp['position']): ?><span class="text-muted">- <?= e($cp['position']) ?></span><?php endif; ?>
                                    </p>
                                    <?php if ($cp['phone']): ?><p class="mb-1 text-muted"><i class="ri-phone-line me-1"></i><?= e($cp['phone']) ?></p><?php endif; ?>
                                    <?php if ($cp['email']): ?><p class="mb-0 text-muted"><i class="ri-mail-line me-1"></i><?= e($cp['email']) ?></p><?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">-</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-shopping-bag-line me-1"></i> Chi tiết sản phẩm / Dịch vụ</h5></div>
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
                                        <td>
                                            <div class="fw-medium"><?= e($item['product_name']) ?></div>
                                            <?php if (!empty($item['description'])): ?>
                                                <small class="text-muted d-block mt-1" style="white-space:pre-wrap"><?= e($item['description']) ?></small>
                                            <?php endif; ?>
                                        </td>
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

                <?php if ($quotation['content'] ?? null): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-file-text-line me-1"></i> Nội dung điều khoản</h5></div>
                    <div class="card-body"><?= $quotation['content'] ?></div>
                </div>
                <?php endif; ?>

                <?php if (($quotation['notes'] ?? null) || ($quotation['terms'] ?? null)): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-sticky-note-line me-1"></i> Ghi chú &amp; Điều khoản</h5></div>
                    <div class="card-body">
                        <div class="row">
                            <?php if ($quotation['notes']): ?>
                            <div class="<?= $quotation['terms'] ? 'col-md-6' : 'col-12' ?>">
                                <h6 class="text-muted mb-2">Ghi chú</h6>
                                <p class="mb-0"><?= nl2br(e($quotation['notes'])) ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($quotation['terms']): ?>
                            <div class="<?= $quotation['notes'] ? 'col-md-6' : 'col-12' ?>">
                                <h6 class="text-muted mb-2">Điều khoản</h6>
                                <p class="mb-0"><?= nl2br(e($quotation['terms'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
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

                <!-- Trao đổi (Plugin) -->
                <?php if (function_exists('activity_exchange_render')) activity_exchange_render('quotation', $quotation['id']); ?>
            </div>

            <div class="col-lg-3">
                <!-- Thông tin -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-information-line me-1"></i> Thông tin</h5></div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Trạng thái</span>
                            <span class="badge bg-<?= $sc[$quotation['status']] ?? 'secondary' ?>"><?= $sl[$quotation['status']] ?? '' ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Hiệu lực đến</span>
                            <span>
                                <?php if ($quotation['valid_until']):
                                    $isExpired = $quotation['valid_until'] < date('Y-m-d');
                                ?>
                                    <span class="<?= $isExpired ? 'text-danger' : 'text-success' ?>"><?= format_date($quotation['valid_until']) ?></span>
                                    <?php if ($isExpired): ?><span class="badge bg-danger ms-1">Hết hạn</span><?php endif; ?>
                                <?php else: ?>-<?php endif; ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Lần báo giá</span>
                            <span><?= (int)($quotation['revision'] ?? 1) ?></span>
                        </div>
                        <?php if ($quotation['description'] ?? null): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Mô tả</span>
                            <span class="text-end"><?= e($quotation['description']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($quotation['project'] ?? null): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Dự án</span>
                            <span class="text-end"><?= e($quotation['project']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($quotation['location'] ?? null): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Địa điểm</span>
                            <span class="text-end"><?= e($quotation['location']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($quotation['campaign_id'] ?? null):
                            $campName = \Core\Database::fetch("SELECT name FROM campaigns WHERE id = ?", [$quotation['campaign_id']]);
                        ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Chiến dịch</span>
                            <span><?= e($campName['name'] ?? '-') ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Người thực hiện</span>
                            <span class="fw-medium"><?= e($quotation['owner_name'] ?? '-') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Người tạo</span>
                            <span><?= e($quotation['created_by_name'] ?? '-') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Ngày tạo</span>
                            <span><?= format_datetime($quotation['created_at']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between <?= ($quotation['deal_title'] ?? null) ? 'mb-2' : 'mb-0' ?>">
                            <span class="text-muted">Lượt xem</span>
                            <span><i class="ri-eye-line me-1 text-muted"></i><?= (int)($quotation['view_count'] ?? 0) ?></span>
                        </div>
                        <?php if ($quotation['deal_title']): ?>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Cơ hội</span>
                            <a href="<?= url('deals/' . $quotation['deal_id']) ?>"><?= e($quotation['deal_title']) ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Đơn hàng đã tạo -->
                <?php if (!empty($relatedOrders)): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-shopping-cart-line me-1"></i> Đơn hàng đã tạo (<?= count($relatedOrders) ?>)</h5></div>
                    <div class="card-body p-0">
                        <?php
                        $osc = ['draft'=>'secondary','pending'=>'warning','approved'=>'success','processing'=>'info','completed'=>'success','cancelled'=>'danger'];
                        $osl = ['draft'=>'Nháp','pending'=>'Chờ duyệt','approved'=>'Đã duyệt','processing'=>'Đang xử lý','completed'=>'Hoàn thành','cancelled'=>'Đã hủy'];
                        ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($relatedOrders as $o): ?>
                            <a href="<?= url('orders/' . $o['id']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-medium"><?= e($o['order_number']) ?></div>
                                        <small class="text-muted"><?= format_date($o['created_at']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $osc[$o['status']] ?? 'secondary' ?>"><?= $osl[$o['status']] ?? $o['status'] ?></span>
                                        <div class="fw-medium small"><?= format_money($o['total']) ?></div>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Người liên quan -->
                <?php $rpEntityType = 'quotation'; $rpEntityId = $quotation['id']; $rpOwnerId = $quotation['owner_id'] ?? 0; $rpOwnerName = $quotation['owner_name'] ?? '-'; include BASE_PATH . '/resources/views/partials/related-people.php'; ?>

                <!-- Dòng thời gian -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-time-line me-1"></i> Dòng thời gian</h5></div>
                    <div class="card-body">
                        <div class="acitivity-timeline acitivity-main">
                            <!-- Tạo báo giá -->
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-soft-primary text-primary"><i class="ri-add-line"></i></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Tạo báo giá <span class="fw-normal text-muted">#<?= e($quotation['quote_number']) ?></span></h6>
                                    <p class="mb-1"><small>Người tạo: <strong><?= e($quotation['created_by_name'] ?? '-') ?></strong></small></p>
                                    <p class="text-muted mb-0 mt-1"><small><i class="ri-time-line me-1"></i><?= format_datetime($quotation['created_at']) ?></small></p>
                                </div>
                            </div>

                            <!-- Gửi duyệt -->
                            <?php if ($quotation['submitted_at'] ?? null): ?>
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-soft-warning text-warning"><i class="ri-send-plane-line"></i></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Gửi duyệt</h6>
                                    <p class="text-muted mb-0"><small><i class="ri-time-line me-1"></i><?= format_datetime($quotation['submitted_at']) ?></small></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Đã duyệt -->
                            <?php if ($quotation['approved_at'] ?? null): ?>
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-soft-success text-success"><i class="ri-checkbox-circle-line"></i></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 text-success">Đã duyệt</h6>
                                    <?php if ($quotation['approved_by_name'] ?? null): ?><p class="mb-1"><small>Người duyệt: <strong><?= e($quotation['approved_by_name']) ?></strong></small></p><?php endif; ?>
                                    <p class="text-muted mb-0"><small><i class="ri-time-line me-1"></i><?= format_datetime($quotation['approved_at']) ?></small></p>
                                </div>
                            </div>
                            <?php endif; ?>


                            <!-- KH từ chối -->
                            <?php if ($quotation['rejected_at']): ?>
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-soft-danger text-danger"><i class="ri-close-line"></i></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 text-danger">Khách hàng từ chối</h6>
                                    <?php if ($quotation['reject_reason']): ?><p class="mb-1"><small><i class="ri-chat-quote-line me-1"></i>Lý do: <?= e($quotation['reject_reason']) ?></small></p><?php endif; ?>
                                    <p class="text-muted mb-0"><small><i class="ri-time-line me-1"></i><?= format_datetime($quotation['rejected_at']) ?></small></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Chuyển đơn hàng -->
                            <?php if ($quotation['converted_order_id'] ?? null): ?>
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-soft-dark text-dark"><i class="ri-shopping-cart-line"></i></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Đã chuyển thành đơn hàng</h6>
                                    <p class="mb-0"><small><a href="<?= url('orders/' . $quotation['converted_order_id']) ?>" class="text-primary">Xem đơn hàng <i class="ri-arrow-right-line"></i></a></small></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Hết hạn -->
                            <?php if (($quotation['valid_until'] ?? null) && $quotation['valid_until'] < date('Y-m-d') && !$quotation['rejected_at']): ?>
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-soft-danger text-danger"><i class="ri-alarm-warning-line"></i></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 text-danger">Báo giá đã hết hạn</h6>
                                    <p class="text-muted mb-0"><small>Hiệu lực đến: <?= format_date($quotation['valid_until']) ?></small></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- Modal chọn mẫu PDF -->
<div class="modal fade" id="pdfTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-printer-line me-1"></i> Chọn mẫu báo giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($pdfTemplates)): ?>
                <div class="list-group">
                    <?php foreach ($pdfTemplates as $tpl): ?>
                    <a href="<?= url('quotations/' . $quotation['id'] . '/pdf?template_id=' . $tpl['id']) ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between">
                        <div>
                            <i class="ri-file-list-2-line me-2 text-primary"></i>
                            <span class="fw-medium"><?= e($tpl['name']) ?></span>
                            <?php if ($tpl['is_default']): ?><span class="badge bg-warning ms-2">Mặc định</span><?php endif; ?>
                        </div>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="ri-file-warning-line" style="font-size:36px"></i>
                    <p class="mt-2 mb-0">Chưa có mẫu báo giá nào. <a href="<?= url('settings/document-templates/create?type=quotation') ?>">Tạo mẫu</a></p>
                </div>
                <?php endif; ?>
                <hr>
                <a href="<?= url('quotations/' . $quotation['id'] . '/pdf') ?>" target="_blank" class="btn btn-soft-secondary w-100">
                    <i class="ri-file-line me-1"></i> In mẫu mặc định hệ thống
                </a>
            </div>
        </div>
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

<script>
/* Activity exchange JS moved to partials/activity-exchange.php */
</script>
