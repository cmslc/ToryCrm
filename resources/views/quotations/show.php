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
                <button type="button" class="btn btn-soft-info" data-bs-toggle="modal" data-bs-target="#pdfTemplateModal"><i class="ri-printer-line me-1"></i>PDF</button>

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
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2"><i class="ri-user-3-line me-1"></i>Khách hàng</h6>
                                <?php if ($cName): ?>
                                    <p class="mb-1 fw-medium">
                                        <a href="<?= url('contacts/' . $quotation['contact_id']) ?>"><?= e($cName) ?></a>
                                        <?php if ($cCode): ?><span class="text-muted">(<?= e($cCode) ?>)</span><?php endif; ?>
                                    </p>
                                    <?php if ($cTax): ?><p class="mb-1 text-muted"><i class="ri-hashtag me-1"></i>MST: <?= e($cTax) ?></p><?php endif; ?>
                                    <?php if ($cAddress): ?><p class="mb-1 text-muted"><i class="ri-map-pin-line me-1"></i><?= e($cAddress) ?></p><?php endif; ?>
                                    <?php if ($cPhone): ?><p class="mb-1 text-muted"><i class="ri-phone-line me-1"></i><?= e($cPhone) ?></p><?php endif; ?>
                                    <?php if ($cEmail): ?><p class="mb-0 text-muted"><i class="ri-mail-line me-1"></i><?= e($cEmail) ?></p><?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">-</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2"><i class="ri-contacts-book-line me-1"></i>Người liên hệ</h6>
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

                <?php if ($quotation['content'] ?? null): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-file-text-line me-1"></i> Nội dung điều khoản</h5></div>
                    <div class="card-body"><?= $quotation['content'] ?></div>
                </div>
                <?php endif; ?>

                <?php if (($quotation['notes'] ?? null) || ($quotation['terms'] ?? null)): ?>
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <?php if ($quotation['notes']): ?>
                            <div class="<?= $quotation['terms'] ? 'col-md-6' : 'col-12' ?>">
                                <h6 class="text-muted mb-2"><i class="ri-sticky-note-line me-1"></i> Ghi chú</h6>
                                <p class="mb-0"><?= nl2br(e($quotation['notes'])) ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($quotation['terms']): ?>
                            <div class="<?= $quotation['notes'] ? 'col-md-6' : 'col-12' ?>">
                                <h6 class="text-muted mb-2"><i class="ri-shield-check-line me-1"></i> Điều khoản</h6>
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

                <!-- Trao đổi -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="ri-chat-3-line me-1"></i> Trao đổi</h5>
                    </div>
                    <div class="card-body">
                        <!-- Compose Area -->
                        <form method="POST" action="<?= url('activities/store') ?>" enctype="multipart/form-data" id="composeForm" style="display:none">
                            <?= csrf_field() ?>
                            <input type="hidden" name="quotation_id" value="<?= $quotation['id'] ?>">
                            <input type="hidden" name="type" value="note" id="activityType">
                            <input type="hidden" name="tagged_users" id="taggedUsers" value="">

                            <div class="border rounded mb-3">
                                <textarea name="title" class="form-control border-0" rows="4" placeholder="Nhập nội dung trao đổi, ghi chú..." required id="activityTextarea" style="resize:none"></textarea>
                                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light" style="border-radius:0 0 6px 6px">
                                    <div class="d-flex gap-3">
                                        <label class="text-muted" style="cursor:pointer" title="Đính kèm file">
                                            <i class="ri-attachment-2 fs-18"></i>
                                            <input type="file" name="attachments[]" class="d-none" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.dwg,.dxf,.cad,.dwf,.skp,.3ds,.obj,.stl,.step,.stp,.iges,.igs" multiple onchange="previewAttach(this)">
                                        </label>
                                        <span class="text-muted" style="cursor:pointer" title="Tag @người dùng" onclick="var ta=document.getElementById('activityTextarea');ta.value+=' @';ta.focus();ta.dispatchEvent(new Event('input'));">
                                            <i class="ri-at-line fs-18"></i>
                                        </span>
                                        <span class="text-muted" style="cursor:pointer" title="Emoji" onclick="var ta=document.getElementById('activityTextarea');ta.value+=' 😊';ta.focus();">
                                            <i class="ri-emotion-happy-line fs-18"></i>
                                        </span>
                                    </div>
                                    <button type="submit" class="btn btn-primary px-4">Gửi</button>
                                </div>
                                <div id="attachBadge" style="display:none" class="px-3 py-2 border-top bg-light"></div>
                            </div>
                        </form>

                        <!-- Activity Feed -->
                        <div id="activityFeed" style="max-height:600px;overflow-y:auto">
                            <?php if (!empty($activities)):
                                $userAvatars = [];
                                foreach ($allUsers ?? [] as $u) { $userAvatars[$u['name']] = $u['avatar'] ?? null; }
                            ?>
                                <?php foreach ($activities as $act):
                                    $userName = $act['user_name'] ?? 'Hệ thống';
                                    $userAvatar = $userAvatars[$userName] ?? null;
                                    $initial = mb_substr($userName, 0, 1);
                                    $isSystem = in_array($act['type'], ['system','deal']);
                                    $content = e($act['title']);
                                    $content = preg_replace('/@([^\s,\.]+(?:\s[^\s,\.@]+)?)/', '<span class="text-primary fw-medium">@$1</span>', $content);
                                    $content = preg_replace('/(https?:\/\/\S+)/', '<a href="$1" target="_blank" class="text-primary">$1</a>', $content);
                                ?>
                                <div class="d-flex gap-3 py-3 <?= $isSystem ? 'bg-light rounded px-3' : '' ?>" style="border-bottom:1px solid #f3f3f3">
                                    <div class="flex-shrink-0">
                                        <?php if ($userAvatar): ?>
                                        <img src="<?= asset($userAvatar) ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover">
                                        <?php else: ?>
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:14px"><?= strtoupper($initial) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <strong style="font-size:14px"><?= e($userName) ?></strong>
                                            <small class="text-muted"><?= !empty($act['created_at']) ? date('d/m/Y H:i', strtotime($act['created_at'])) : '' ?></small>
                                        </div>
                                        <div style="white-space:pre-wrap;word-break:break-word"><?= $content ?></div>
                                        <?php if (!empty($act['description'])): ?>
                                        <div class="text-muted mt-1" style="font-size:13px;white-space:pre-wrap"><?= e($act['description']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($act['attachment'])):
                                            $attPaths = explode('|', $act['attachment']);
                                            $attNames = explode('|', $act['attachment_name'] ?? '');
                                            $fileIcons = ['pdf'=>'ri-file-pdf-line text-danger','doc'=>'ri-file-word-line text-primary','docx'=>'ri-file-word-line text-primary','xls'=>'ri-file-excel-line text-success','xlsx'=>'ri-file-excel-line text-success','dwg'=>'ri-draft-line text-dark','dxf'=>'ri-draft-line text-dark','cad'=>'ri-draft-line text-dark'];
                                        ?>
                                        <div class="mt-2 d-flex flex-wrap gap-2">
                                            <?php foreach ($attPaths as $ai => $aPath):
                                                $aPath = trim($aPath); if (!$aPath) continue;
                                                $aExt = strtolower(pathinfo($aPath, PATHINFO_EXTENSION));
                                                $isImage = in_array($aExt, ['jpg','jpeg','png','gif','webp']);
                                                $aName = trim($attNames[$ai] ?? basename($aPath));
                                            ?>
                                                <?php if ($isImage): ?>
                                                <a href="<?= asset($aPath) ?>" target="_blank"><img src="<?= asset($aPath) ?>" class="rounded border" style="max-width:200px;max-height:150px;object-fit:cover"></a>
                                                <?php else: ?>
                                                <a href="<?= asset($aPath) ?>" target="_blank" class="d-flex align-items-center gap-2 p-2 bg-light rounded text-decoration-none">
                                                    <i class="<?= $fileIcons[$aExt] ?? 'ri-file-line text-muted' ?> fs-20"></i>
                                                    <span class="text-dark" style="font-size:13px"><?= e($aName) ?></span>
                                                    <i class="ri-download-line text-muted"></i>
                                                </a>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Like / Dislike / Reply -->
                                        <div class="d-flex align-items-center gap-3 mt-2" style="font-size:13px">
                                            <span class="act-btn <?= ($act['my_reaction'] ?? '') === 'like' ? 'text-primary fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $act['id'] ?>,'like',this)">
                                                <i class="ri-thumb-up-<?= ($act['my_reaction'] ?? '') === 'like' ? 'fill' : 'line' ?>"></i><?php if (($act['likes'] ?? 0) > 0): ?> <span class="react-count"><?= $act['likes'] ?></span><?php endif; ?>
                                            </span>
                                            <span class="act-btn <?= ($act['my_reaction'] ?? '') === 'dislike' ? 'text-danger fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $act['id'] ?>,'dislike',this)">
                                                <i class="ri-thumb-down-<?= ($act['my_reaction'] ?? '') === 'dislike' ? 'fill' : 'line' ?>"></i><?php if (($act['dislikes'] ?? 0) > 0): ?> <span class="react-count"><?= $act['dislikes'] ?></span><?php endif; ?>
                                            </span>
                                            <span class="text-muted act-btn" style="cursor:pointer" onclick="toggleReplyBox(<?= $act['id'] ?>)">
                                                <i class="ri-reply-line"></i> Trả lời
                                            </span>
                                        </div>

                                        <!-- Replies -->
                                        <?php if (!empty($act['replies'])): ?>
                                        <div class="ms-4 mt-2 border-start ps-3">
                                            <?php foreach ($act['replies'] as $reply):
                                                $rAvatar = $reply['user_avatar'] ?? null;
                                                $rName = $reply['user_name'] ?? 'Hệ thống';
                                                $rContent = e($reply['title']);
                                                $rContent = preg_replace('/@([^\s,\.]+(?:\s[^\s,\.@]+)?)/', '<span class="text-primary fw-medium">@$1</span>', $rContent);
                                            ?>
                                            <div class="d-flex gap-2 py-2" style="border-bottom:1px solid #f8f8f8">
                                                <?php if ($rAvatar): ?>
                                                <img src="<?= asset($rAvatar) ?>" class="rounded-circle" width="28" height="28" style="object-fit:cover">
                                                <?php else: ?>
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:11px"><?= mb_substr($rName, 0, 1) ?></div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <strong style="font-size:13px"><?= e($rName) ?></strong>
                                                    <small class="text-muted ms-1"><?= date('d/m H:i', strtotime($reply['created_at'])) ?></small>
                                                    <div style="font-size:13px"><?= $rContent ?></div>
                                                    <?php if (!empty($reply['attachment'])):
                                                        $rPath = trim($reply['attachment']);
                                                        $rExt = strtolower(pathinfo($rPath, PATHINFO_EXTENSION));
                                                        $rIsImg = in_array($rExt, ['jpg','jpeg','png','gif','webp']);
                                                    ?>
                                                    <div class="mt-1">
                                                        <?php if ($rIsImg): ?><a href="<?= asset($rPath) ?>" target="_blank"><img src="<?= asset($rPath) ?>" class="rounded border" style="max-width:200px;max-height:120px"></a>
                                                        <?php else: ?><a href="<?= asset($rPath) ?>" target="_blank" class="text-primary" style="font-size:12px"><i class="ri-file-line me-1"></i><?= e($reply['attachment_name'] ?? basename($rPath)) ?></a><?php endif; ?>
                                                    </div>
                                                    <?php endif; ?>
                                                    <div class="d-flex align-items-center gap-3 mt-1" style="font-size:12px">
                                                        <span class="act-btn <?= ($reply['my_reaction'] ?? '') === 'like' ? 'text-primary fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $reply['id'] ?>,'like',this)">
                                                            <i class="ri-thumb-up-<?= ($reply['my_reaction'] ?? '') === 'like' ? 'fill' : 'line' ?>"></i><?php if (($reply['likes'] ?? 0) > 0): ?> <?= $reply['likes'] ?><?php endif; ?>
                                                        </span>
                                                        <span class="act-btn <?= ($reply['my_reaction'] ?? '') === 'dislike' ? 'text-danger fw-medium' : 'text-muted' ?>" style="cursor:pointer" onclick="reactActivity(<?= $reply['id'] ?>,'dislike',this)">
                                                            <i class="ri-thumb-down-<?= ($reply['my_reaction'] ?? '') === 'dislike' ? 'fill' : 'line' ?>"></i><?php if (($reply['dislikes'] ?? 0) > 0): ?> <?= $reply['dislikes'] ?><?php endif; ?>
                                                        </span>
                                                        <span class="text-muted act-btn" style="cursor:pointer" onclick="toggleReplyBox(<?= $act['id'] ?>)"><i class="ri-reply-line"></i> Trả lời</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php endif; ?>

                                        <!-- Reply Box -->
                                        <div class="ms-4 mt-2 d-none" id="replyBox-<?= $act['id'] ?>">
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="text" class="form-control" placeholder="Viết trả lời..." id="replyInput-<?= $act['id'] ?>" onkeydown="if(event.key==='Enter'){event.preventDefault();submitReply(<?= $act['id'] ?>)}">
                                                <label class="btn btn-soft-secondary btn-sm mb-0" title="Đính kèm file">
                                                    <i class="ri-attachment-2"></i>
                                                    <input type="file" class="d-none" id="replyFile-<?= $act['id'] ?>" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.dwg,.dxf,.cad,.zip,.rar">
                                                </label>
                                                <button class="btn btn-primary btn-sm" onclick="submitReply(<?= $act['id'] ?>)">Gửi</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="ri-chat-3-line fs-48 text-muted"></i>
                                    <p class="text-muted mt-2">Chưa có hoạt động trao đổi</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Thông tin -->
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-information-line me-1"></i> Thông tin</h5></div>
                    <div class="card-body p-0">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width:40%">Trạng thái</td>
                                    <td><span class="badge bg-<?= $sc[$quotation['status']] ?? 'secondary' ?>"><?= $sl[$quotation['status']] ?? '' ?></span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Hiệu lực đến</td>
                                    <td>
                                        <?php if ($quotation['valid_until']):
                                            $isExpired = $quotation['valid_until'] < date('Y-m-d');
                                        ?>
                                            <span class="<?= $isExpired ? 'text-danger' : 'text-success' ?>"><?= format_date($quotation['valid_until']) ?></span>
                                            <?php if ($isExpired): ?><span class="badge bg-danger ms-1">Hết hạn</span><?php endif; ?>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Lần báo giá</td>
                                    <td><?= (int)($quotation['revision'] ?? 1) ?></td>
                                </tr>
                                <?php if ($quotation['description'] ?? null): ?>
                                <tr>
                                    <td class="text-muted">Mô tả</td>
                                    <td><?= e($quotation['description']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($quotation['project'] ?? null): ?>
                                <tr>
                                    <td class="text-muted">Dự án</td>
                                    <td><?= e($quotation['project']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($quotation['location'] ?? null): ?>
                                <tr>
                                    <td class="text-muted">Địa điểm</td>
                                    <td><?= e($quotation['location']) ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($quotation['campaign_id'] ?? null):
                                    $campName = \Core\Database::fetch("SELECT name FROM campaigns WHERE id = ?", [$quotation['campaign_id']]);
                                ?>
                                <tr>
                                    <td class="text-muted">Chiến dịch</td>
                                    <td><?= e($campName['name'] ?? '-') ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted">Người thực hiện</td>
                                    <td class="fw-medium"><?= e($quotation['owner_name'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Người tạo</td>
                                    <td><?= e($quotation['created_by_name'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Ngày tạo</td>
                                    <td><?= format_datetime($quotation['created_at']) ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Lượt xem</td>
                                    <td><i class="ri-eye-line me-1 text-muted"></i><?= (int)($quotation['view_count'] ?? 0) ?></td>
                                </tr>
                                <?php if ($quotation['deal_title']): ?>
                                <tr>
                                    <td class="text-muted">Cơ hội</td>
                                    <td><a href="<?= url('deals/' . $quotation['deal_id']) ?>"><?= e($quotation['deal_title']) ?></a></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Link khách hàng -->
                <?php if ($quotation['portal_token']): ?>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-link me-1"></i> Link khách hàng</h5></div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light" id="portalLink" value="<?= url('quote/' . $quotation['portal_token']) ?>" readonly>
                            <button class="btn btn-soft-primary" id="copyLinkBtn" onclick="navigator.clipboard.writeText(document.getElementById('portalLink').value).then(function(){var b=document.getElementById('copyLinkBtn');b.innerHTML='<i class=\'ri-check-line\'></i> Đã sao chép';b.classList.add('btn-success');b.classList.remove('btn-soft-primary');setTimeout(function(){b.innerHTML='<i class=\'ri-file-copy-line\'></i> Sao chép';b.classList.remove('btn-success');b.classList.add('btn-soft-primary')},2000)})">
                                <i class="ri-file-copy-line"></i> Sao chép
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block"><i class="ri-information-line me-1"></i>Chia sẻ link để khách hàng xem và phản hồi báo giá.</small>
                    </div>
                </div>
                <?php endif; ?>

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

                            <!-- Gửi khách -->
                            <?php if ($quotation['sent_at'] ?? null): ?>
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-soft-info text-info"><i class="ri-mail-send-line"></i></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Đã gửi khách hàng</h6>
                                    <?php if ($quotation['contact_email'] ?? $quotation['contact_phone'] ?? null): ?>
                                    <p class="mb-1"><small>
                                        <?php if ($quotation['contact_email']): ?><i class="ri-mail-line me-1"></i><?= e($quotation['contact_email']) ?><?php endif; ?>
                                        <?php if ($quotation['contact_phone']): ?> · <i class="ri-phone-line me-1"></i><?= e($quotation['contact_phone']) ?><?php endif; ?>
                                    </small></p>
                                    <?php endif; ?>
                                    <p class="text-muted mb-0"><small><i class="ri-time-line me-1"></i><?= format_datetime($quotation['sent_at']) ?></small></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- KH đã xem -->
                            <?php if (($quotation['view_count'] ?? 0) > 0): ?>
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-soft-warning text-warning"><i class="ri-eye-line"></i></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Khách hàng đã xem</h6>
                                    <p class="mb-1"><small><i class="ri-eye-line me-1"></i><?= $quotation['view_count'] ?> lượt xem</small></p>
                                    <?php if ($quotation['last_viewed_at'] ?? null): ?>
                                    <p class="text-muted mb-0"><small><i class="ri-time-line me-1"></i>Lần cuối: <?= format_datetime($quotation['last_viewed_at']) ?></small></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- KH chấp nhận -->
                            <?php if ($quotation['accepted_at']): ?>
                            <div class="acitivity-item d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar-xs acitivity-avatar">
                                        <div class="avatar-title rounded-circle bg-soft-success text-success"><i class="ri-check-double-line"></i></div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 text-success">Khách hàng chấp nhận</h6>
                                    <p class="mb-1"><small>Báo giá đã được KH đồng ý. Có thể tạo đơn hàng hoặc hợp đồng.</small></p>
                                    <p class="text-muted mb-0"><small><i class="ri-time-line me-1"></i><?= format_datetime($quotation['accepted_at']) ?></small></p>
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
                            <?php if (($quotation['valid_until'] ?? null) && $quotation['valid_until'] < date('Y-m-d') && !$quotation['accepted_at'] && !$quotation['rejected_at']): ?>
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
// Move compose form below feed
(function(){
    var form = document.getElementById('composeForm');
    var feed = document.getElementById('activityFeed');
    if (form && feed) { feed.parentNode.insertBefore(feed, form); form.style.display = ''; }
})();

// Attachment preview
function previewAttach(input) {
    var badge = document.getElementById('attachBadge');
    if (!input.files || !input.files.length) { badge.style.display = 'none'; return; }
    var icons = {pdf:'ri-file-pdf-line text-danger',doc:'ri-file-word-line text-primary',docx:'ri-file-word-line text-primary',xls:'ri-file-excel-line text-success',xlsx:'ri-file-excel-line text-success',dwg:'ri-draft-line text-dark'};
    var html = '<div class="d-flex flex-wrap gap-2">';
    Array.from(input.files).forEach(function(file, i) {
        var size = file.size > 1048576 ? (file.size/1048576).toFixed(1)+'MB' : Math.round(file.size/1024)+'KB';
        var ext = file.name.split('.').pop().toLowerCase();
        var isImg = file.type.startsWith('image/');
        var xBtn = '<span class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:18px;height:18px;cursor:pointer;font-size:10px;transform:translate(5px,-5px)" onclick="removeAttachFile('+i+')"><i class="ri-close-line"></i></span>';
        if (isImg) { html += '<div class="position-relative" style="width:80px;height:80px;margin:5px"><img src="" class="attach-thumb rounded border" data-idx="'+i+'" style="width:100%;height:100%;object-fit:cover">'+xBtn+'</div>'; }
        else { html += '<div class="border rounded p-2 d-flex align-items-center gap-2 position-relative" style="max-width:180px"><i class="'+(icons[ext]||'ri-file-line text-muted')+' fs-20"></i><div style="min-width:0"><div class="text-truncate" style="font-size:12px;max-width:120px">'+file.name+'</div><small class="text-muted">'+size+'</small></div>'+xBtn+'</div>'; }
    });
    html += '</div>';
    badge.innerHTML = html; badge.style.display = 'block';
    Array.from(input.files).forEach(function(file, i) {
        if (!file.type.startsWith('image/')) return;
        var reader = new FileReader();
        reader.onload = function(e) { var img = badge.querySelector('.attach-thumb[data-idx="'+i+'"]'); if (img) img.src = e.target.result; };
        reader.readAsDataURL(file);
    });
}
function clearAttach() { var input = document.querySelector('input[name="attachments[]"]'); input.value = ''; document.getElementById('attachBadge').style.display = 'none'; }
function removeAttachFile(idx) { var input = document.querySelector('input[name="attachments[]"]'); var dt = new DataTransfer(); var files = input._filteredFiles||input.files; for(var i=0;i<files.length;i++){if(i!==idx)dt.items.add(files[i]);} input.files=dt.files; input._filteredFiles=dt.files; if(!dt.files.length)clearAttach();else previewAttach(input); }

// React
function reactActivity(id, type, el) {
    fetch('<?= url("activities") ?>/'+id+'/react', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'<?= csrf_token() ?>'}, body:JSON.stringify({type:type}) })
    .then(r=>r.json()).then(function(data) {
        if (!data.likes && data.likes !== 0) return;
        var row = el.closest('.d-flex.align-items-center.gap-3');
        var btns = row.querySelectorAll('.act-btn');
        btns[0].className = 'act-btn '+(data.my==='like'?'text-primary fw-medium':'text-muted');
        btns[0].innerHTML = '<i class="ri-thumb-up-'+(data.my==='like'?'fill':'line')+'"></i>'+(data.likes>0?' <span class="react-count">'+data.likes+'</span>':'');
        btns[1].className = 'act-btn '+(data.my==='dislike'?'text-danger fw-medium':'text-muted');
        btns[1].innerHTML = '<i class="ri-thumb-down-'+(data.my==='dislike'?'fill':'line')+'"></i>'+(data.dislikes>0?' <span class="react-count">'+data.dislikes+'</span>':'');
    });
}

// Reply
function toggleReplyBox(id) { var box=document.getElementById('replyBox-'+id); box.classList.toggle('d-none'); if(!box.classList.contains('d-none'))document.getElementById('replyInput-'+id).focus(); }

function submitReply(id) {
    var input=document.getElementById('replyInput-'+id); var fileInput=document.getElementById('replyFile-'+id);
    var content=input.value.trim(); if(!content&&(!fileInput||!fileInput.files[0]))return;
    var fd=new FormData(); fd.append('content',content); fd.append('_token','<?= csrf_token() ?>');
    if(fileInput&&fileInput.files[0])fd.append('attachment',fileInput.files[0]);
    fetch('<?= url("activities") ?>/'+id+'/reply',{method:'POST',headers:{'X-CSRF-TOKEN':'<?= csrf_token() ?>'},body:fd})
    .then(r=>r.json()).then(function(data){
        if(!data.success)return;
        var r=data.reply; var initial=(r.user_name||'?').charAt(0).toUpperCase();
        var avatar=r.user_avatar?'<img src="/'+r.user_avatar+'" class="rounded-circle" width="28" height="28" style="object-fit:cover">':'<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:11px">'+initial+'</div>';
        var actions='<div class="d-flex align-items-center gap-3 mt-1" style="font-size:12px"><span class="act-btn text-muted" style="cursor:pointer" onclick="reactActivity('+r.id+',\'like\',this)"><i class="ri-thumb-up-line"></i></span><span class="act-btn text-muted" style="cursor:pointer" onclick="reactActivity('+r.id+',\'dislike\',this)"><i class="ri-thumb-down-line"></i></span><span class="text-muted act-btn" style="cursor:pointer" onclick="toggleReplyBox('+id+')"><i class="ri-reply-line"></i> Trả lời</span></div>';
        var attachHtml='';
        if(r.attachment){var aExt=r.attachment.split('.').pop().toLowerCase();var isImg=['jpg','jpeg','png','gif','webp'].indexOf(aExt)!==-1;if(isImg){attachHtml='<div class="mt-1"><a href="/'+r.attachment+'" target="_blank"><img src="/'+r.attachment+'" class="rounded border" style="max-width:200px;max-height:120px"></a></div>';}else{attachHtml='<div class="mt-1"><a href="/'+r.attachment+'" target="_blank" class="text-primary" style="font-size:12px"><i class="ri-file-line me-1"></i>'+(r.attachment_name||r.attachment.split('/').pop())+'</a></div>';}}
        var html='<div class="d-flex gap-2 py-2" style="border-bottom:1px solid #f8f8f8">'+avatar+'<div class="flex-grow-1"><strong style="font-size:13px">'+r.user_name+'</strong> <small class="text-muted">vừa xong</small><div style="font-size:13px">'+(r.title||'')+'</div>'+attachHtml+actions+'</div></div>';
        var box=document.getElementById('replyBox-'+id);
        if(fileInput){fileInput.value='';}
        var repliesDiv=box.previousElementSibling;
        if(!repliesDiv||!repliesDiv.classList.contains('border-start')){var newDiv=document.createElement('div');newDiv.className='ms-4 mt-2 border-start ps-3';box.parentNode.insertBefore(newDiv,box);repliesDiv=newDiv;}
        repliesDiv.insertAdjacentHTML('beforeend',html);
        input.value='';
    });
}

// @mention autocomplete
(function(){
    var users=<?= json_encode(array_map(function($u){return['id'=>$u['id'],'name'=>$u['name'],'avatar'=>$u['avatar']??null];}, $allUsers ?? [])) ?>;
    var dd=document.createElement('div'); dd.className='border rounded bg-white shadow'; dd.style.cssText='position:fixed;z-index:1070;display:none;max-height:250px;overflow-y:auto;width:280px'; document.body.appendChild(dd);
    var activeInput=null;
    function updatePos(){if(!activeInput||dd.style.display==='none')return;var rect=activeInput.getBoundingClientRect();dd.style.top=(rect.bottom+4)+'px';dd.style.left=rect.left+'px';}
    window.addEventListener('scroll',updatePos,true);
    function showMention(input,query){
        activeInput=input;var q=(query||'').toLowerCase();
        var filtered=users.filter(function(u){return!q||u.name.toLowerCase().indexOf(q)!==-1;}).slice(0,10);
        if(!filtered.length){dd.style.display='none';return;}
        dd.innerHTML=filtered.map(function(u){
            var av=u.avatar?'<img src="/'+u.avatar+'" class="rounded-circle me-2" width="24" height="24" style="object-fit:cover">':'<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width:24px;height:24px;font-size:10px">'+u.name.charAt(0).toUpperCase()+'</div>';
            return'<div class="d-flex align-items-center px-3 py-2" style="cursor:pointer;font-size:13px" onmousedown="pickMention(\''+u.name.replace(/'/g,"\\'")+'\','+u.id+')">'+av+'<span>'+u.name+'</span></div>';
        }).join('');
        updatePos(); dd.style.display='block';
    }
    window.pickMention=function(name,id){
        if(!activeInput)return;
        var val=activeInput.value; var pos=activeInput.selectionStart;
        var before=val.substring(0,pos); var after=val.substring(pos);
        var atIdx=before.lastIndexOf('@');
        if(atIdx!==-1){activeInput.value=before.substring(0,atIdx)+'@'+name+' '+after;activeInput.selectionStart=activeInput.selectionEnd=atIdx+name.length+2;}
        var hidden=document.getElementById('taggedUsers');
        if(hidden){var ids=hidden.value?hidden.value.split(','):[];if(ids.indexOf(String(id))===-1)ids.push(id);hidden.value=ids.join(',');}
        dd.style.display='none'; activeInput.focus();
    };
    document.addEventListener('input',function(e){
        if(e.target.tagName!=='TEXTAREA'&&e.target.tagName!=='INPUT')return;
        var val=e.target.value;var pos=e.target.selectionStart;var before=val.substring(0,pos);
        var match=before.match(/@([^\s@]*)$/);
        if(match){showMention(e.target,match[1]);}else{dd.style.display='none';}
    });
    document.addEventListener('keydown',function(e){if(e.key==='Escape')dd.style.display='none';});
    document.addEventListener('click',function(e){if(!dd.contains(e.target))dd.style.display='none';});
})();

// Auto-scroll
var feed=document.getElementById('activityFeed'); if(feed)feed.scrollTop=feed.scrollHeight;
</script>
