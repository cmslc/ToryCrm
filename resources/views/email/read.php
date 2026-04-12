<?php
$pageTitle = e($message['subject'] ?? 'Email');
$m = $message;
$folderLabels = ['inbox'=>'Hộp thư đến','sent'=>'Đã gửi','drafts'=>'Nháp','trash'=>'Thùng rác','spam'=>'Spam','archive'=>'Lưu trữ'];
$folderIcons = ['inbox'=>'ri-inbox-line','sent'=>'ri-send-plane-line','drafts'=>'ri-draft-line','trash'=>'ri-delete-bin-line','spam'=>'ri-spam-line','archive'=>'ri-archive-line'];
$folder = $m['folder'] ?? 'inbox';
?>

<div class="d-flex" style="min-height:calc(100vh - 140px)">
    <!-- Sidebar -->
    <div class="flex-shrink-0" style="width:220px">
        <a href="<?= url('email/compose') ?>" class="btn btn-primary w-100 mb-3 py-2">
            <i class="ri-edit-line me-1"></i> Soạn thư
        </a>
        <div class="nav flex-column">
            <?php
            $folderMap = [];
            foreach ($folders ?? [] as $f) $folderMap[$f['folder']] = $f;
            foreach (['inbox','sent','drafts','trash','spam'] as $f):
                $cnt = $folderMap[$f]['cnt'] ?? 0;
                $unread = $folderMap[$f]['unread'] ?? 0;
                if ($cnt == 0 && !in_array($f, ['inbox','sent','trash'])) continue;
                $isActive = ($folder === $f);
            ?>
            <a href="<?= url('email?account=' . $accountId . '&folder=' . $f) ?>"
               class="d-flex align-items-center px-3 py-2 rounded text-decoration-none mb-1 <?= $isActive ? 'bg-primary-subtle text-primary fw-medium' : 'text-body' ?>">
                <i class="<?= $folderIcons[$f] ?> me-2 fs-16"></i>
                <span class="flex-grow-1"><?= $folderLabels[$f] ?></span>
                <?php if ($unread > 0): ?><span class="fw-bold fs-12"><?= $unread ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <hr class="my-3">
        <div class="px-2">
            <?php if (count($accounts) > 1): ?>
            <select class="form-select" onchange="location.href='<?= url('email') ?>?account='+this.value">
                <?php foreach ($accounts as $acc): ?>
                <option value="<?= $acc['id'] ?>" <?= $acc['id'] == $accountId ? 'selected' : '' ?>><?= e($acc['email']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php else: ?>
            <small class="text-muted"><?= e($accounts[0]['email'] ?? '') ?></small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Email content -->
    <div class="flex-grow-1 ms-3">
        <!-- Toolbar -->
        <div class="bg-white rounded-top border-bottom px-3 py-2 d-flex align-items-center gap-2">
            <a href="<?= url('email?account=' . $accountId . '&folder=' . $folder) ?>" class="btn btn-soft-secondary btn"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
            <a href="<?= url('email/compose?reply_to=' . $m['id']) ?>" class="btn btn-soft-primary btn"><i class="ri-reply-line me-1"></i> Trả lời</a>
            <form method="POST" action="<?= url('email/' . $m['id'] . '/trash') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-soft-danger btn"><i class="ri-delete-bin-line me-1"></i> Xóa</button></form>
        </div>

        <!-- Subject -->
        <div class="bg-white border-bottom px-4 py-3">
            <h5 class="mb-0"><?= e($m['subject'] ?: '(Không tiêu đề)') ?></h5>
        </div>

        <!-- Sender info -->
        <div class="bg-white border-bottom px-4 py-3">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0 me-3" style="width:40px;height:40px;font-size:16px">
                    <?= strtoupper(mb_substr($m['from_name'] ?: $m['from_email'], 0, 1)) ?>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center">
                        <span class="fw-medium"><?= e($m['from_name'] ?: $m['from_email']) ?></span>
                        <span class="text-muted ms-2 fs-12">&lt;<?= e($m['from_email']) ?>&gt;</span>
                        <?php if ($m['contact_id']): ?>
                        <a href="<?= url('contacts/' . $m['contact_id']) ?>" class="badge bg-success-subtle text-success ms-2">Khách hàng</a>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted fs-12">
                        Đến: <?= e($m['to_emails'] ?? $m['account_email']) ?>
                        <?php if ($m['cc_emails']): ?> | CC: <?= e($m['cc_emails']) ?><?php endif; ?>
                    </div>
                </div>
                <span class="text-muted fs-12"><?= $m['sent_at'] ? date('d/m/Y H:i', strtotime($m['sent_at'])) : '' ?></span>
            </div>
        </div>

        <!-- Body -->
        <div class="bg-white rounded-bottom border px-4 py-4">
            <?php if ($m['body_html']): ?>
            <div class="email-body"><?= $m['body_html'] ?></div>
            <?php elseif ($m['body_text']): ?>
            <pre class="mb-0" style="white-space:pre-wrap;font-family:inherit"><?= e($m['body_text']) ?></pre>
            <?php else: ?>
            <p class="text-muted">(Không có nội dung)</p>
            <?php endif; ?>
        </div>

        <?php
        $attachments = [];
        try { $attachments = \Core\Database::fetchAll("SELECT * FROM email_attachments WHERE message_id = ?", [$m['id']]); } catch (\Exception $e) {}
        if (!empty($attachments)):
        ?>
        <!-- Attachments -->
        <div class="bg-white border-top px-4 py-3">
            <span class="text-muted fs-13 mb-2 d-block"><i class="ri-attachment-line me-1"></i> <?= count($attachments) ?> đính kèm</span>
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($attachments as $att): ?>
                <?php $attUrl = (str_starts_with($att['file_path'], 'http')) ? $att['file_path'] : url($att['file_path']); ?>
                <div class="border rounded px-3 py-2 d-flex align-items-center gap-2">
                    <div class="avatar-xs flex-shrink-0">
                        <div class="avatar-title bg-primary-subtle text-primary rounded fs-18"><i class="ri-file-line"></i></div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-medium fs-13"><?= e($att['filename']) ?></div>
                        <small class="text-muted"><?= $att['size'] < 1048576 ? round($att['size'] / 1024, 2) . ' KB' : round($att['size'] / 1048576, 2) . ' MB' ?></small>
                    </div>
                    <a href="<?= url('email/download?url=' . urlencode($attUrl) . '&name=' . urlencode($att['filename'])) ?>" class="btn btn-soft-primary btn-icon" title="Tải xuống"><i class="ri-download-2-line"></i></a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="mt-3 d-flex gap-2">
            <a href="<?= url('email/compose?reply_to=' . $m['id']) ?>" class="btn btn-outline-primary"><i class="ri-reply-line me-1"></i> Trả lời</a>
            <a href="<?= url('email/compose?forward=' . $m['id']) ?>" class="btn btn-outline-secondary"><i class="ri-share-forward-line me-1"></i> Chuyển tiếp</a>
        </div>

        <!-- Quick Reply -->
        <div class="mt-3 bg-white rounded border p-3">
            <form method="POST" action="<?= url('email/send') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="account_id" value="<?= $accountId ?>">
                <input type="hidden" name="to" value="<?= e($m['from_email']) ?>">
                <input type="hidden" name="subject" value="Re: <?= e($m['subject']) ?>">
                <div class="d-flex align-items-center mb-2">
                    <span class="text-muted fs-13"><i class="ri-reply-line me-1"></i> Trả lời <?= e($m['from_name'] ?: $m['from_email']) ?></span>
                </div>
                <textarea name="body" class="form-control mb-2" rows="3" placeholder="Nhập nội dung trả lời..."></textarea>
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ri-send-plane-line me-1"></i> Gửi</button>
                    <label class="btn btn-soft-secondary mb-0" style="cursor:pointer">
                        <i class="ri-attachment-line me-1"></i> Đính kèm
                        <input type="file" name="attachments[]" multiple class="d-none" onchange="previewReplyFiles(this)">
                    </label>
                </div>
                <div id="replyAttachPreview" class="d-flex gap-2 flex-wrap mt-2"></div>
                <script>
                var replyFiles = [];
                function previewReplyFiles(input) {
                    replyFiles = Array.from(input.files);
                    renderReplyPreview();
                }
                function removeReplyFile(idx) {
                    replyFiles.splice(idx, 1);
                    var dt = new DataTransfer();
                    replyFiles.forEach(function(f) { dt.items.add(f); });
                    input.files = dt.files;
                    renderReplyPreview();
                }
                function renderReplyPreview() {
                    var preview = document.getElementById('replyAttachPreview');
                    preview.innerHTML = '';
                    replyFiles.forEach(function(file, idx) {
                        var div = document.createElement('div');
                        div.className = 'border rounded p-2 d-flex align-items-center gap-2';
                        if (file.type.startsWith('image/')) {
                            var img = document.createElement('img');
                            img.style.cssText = 'width:36px;height:36px;object-fit:cover;border-radius:4px';
                            var r = new FileReader(); r.onload = function(e){img.src=e.target.result}; r.readAsDataURL(file);
                            div.appendChild(img);
                        } else {
                            var i = document.createElement('i'); i.className = 'ri-file-line fs-18 text-muted'; div.appendChild(i);
                        }
                        var info = document.createElement('div');
                        info.className = 'flex-grow-1';
                        info.innerHTML = '<div class="fw-medium fs-12 text-truncate" style="max-width:100px">' + file.name + '</div><small class="text-muted">' + (file.size<1048576?Math.round(file.size/1024)+' KB':(file.size/1048576).toFixed(1)+' MB') + '</small>';
                        div.appendChild(info);
                        var btn = document.createElement('button');
                        btn.type = 'button'; btn.className = 'btn-close'; btn.style.cssText = 'font-size:10px';
                        btn.onclick = function() { removeReplyFile(idx); };
                        div.appendChild(btn);
                        preview.appendChild(div);
                    });
                }
                </script>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.email-body img { max-width: 100%; height: auto; }
.email-body table { max-width: 100%; }
.email-body { word-break: break-word; line-height: 1.7; }
</style>
