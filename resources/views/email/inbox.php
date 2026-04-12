<?php
$pageTitle = 'Email';
$folderLabels = ['inbox'=>'Hộp thư đến','sent'=>'Đã gửi','drafts'=>'Nháp','trash'=>'Thùng rác','spam'=>'Spam','archive'=>'Lưu trữ'];
$folderIcons = ['inbox'=>'ri-inbox-line','sent'=>'ri-send-plane-line','drafts'=>'ri-draft-line','trash'=>'ri-delete-bin-line','spam'=>'ri-spam-line','archive'=>'ri-archive-line'];
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><i class="ri-mail-line me-2"></i> Email</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('email/settings') ?>" class="btn btn-soft-secondary"><i class="ri-settings-3-line me-1"></i> Cài đặt</a>
        <form method="POST" action="<?= url('email/sync') ?>" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="account_id" value="<?= $accountId ?>">
            <button class="btn btn-soft-info"><i class="ri-refresh-line me-1"></i> Đồng bộ</button>
        </form>
        <a href="<?= url('email/compose') ?>" class="btn btn-primary"><i class="ri-edit-line me-1"></i> Soạn email</a>
    </div>
</div>

<?php if (empty($accounts)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="ri-mail-settings-line fs-1 text-muted d-block mb-3"></i>
        <h5>Chưa cấu hình tài khoản email</h5>
        <p class="text-muted">Thêm tài khoản IMAP/SMTP để bắt đầu gửi và nhận email.</p>
        <a href="<?= url('email/settings') ?>" class="btn btn-primary"><i class="ri-settings-3-line me-1"></i> Cấu hình ngay</a>
    </div>
</div>
<?php else: ?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-lg-3">
        <div class="card">
            <div class="card-body p-3">
                <!-- Account selector -->
                <?php if (count($accounts) > 1): ?>
                <select class="form-select mb-3" onchange="location.href='<?= url('email') ?>?account='+this.value">
                    <?php foreach ($accounts as $acc): ?>
                    <option value="<?= $acc['id'] ?>" <?= $acc['id'] == $accountId ? 'selected' : '' ?>><?= e($acc['email']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                <p class="fw-medium mb-3"><?= e($accounts[0]['email']) ?></p>
                <?php endif; ?>

                <!-- Folders -->
                <ul class="list-group list-group-flush">
                    <?php
                    $folderMap = [];
                    foreach ($folders ?? [] as $f) $folderMap[$f['folder']] = $f;
                    foreach (['inbox','sent','drafts','trash','spam','archive'] as $f):
                        $cnt = $folderMap[$f]['cnt'] ?? 0;
                        $unread = $folderMap[$f]['unread'] ?? 0;
                        if ($cnt == 0 && !in_array($f, ['inbox','sent','trash'])) continue;
                    ?>
                    <li class="list-group-item d-flex align-items-center px-0 <?= $folder === $f ? 'text-primary fw-medium' : '' ?>">
                        <a href="<?= url('email?account=' . $accountId . '&folder=' . $f) ?>" class="flex-grow-1 text-decoration-none <?= $folder === $f ? 'text-primary' : 'text-body' ?>">
                            <i class="<?= $folderIcons[$f] ?? 'ri-folder-line' ?> me-2"></i> <?= $folderLabels[$f] ?? ucfirst($f) ?>
                        </a>
                        <?php if ($unread > 0): ?><span class="badge bg-primary"><?= $unread ?></span><?php elseif ($cnt > 0): ?><span class="badge bg-secondary-subtle text-secondary"><?= $cnt ?></span><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <div class="col-lg-9">
        <!-- Search -->
        <div class="card mb-2">
            <div class="card-body py-2">
                <form method="GET" action="<?= url('email') ?>" class="d-flex gap-2">
                    <input type="hidden" name="account" value="<?= $accountId ?>">
                    <input type="hidden" name="folder" value="<?= e($folder) ?>">
                    <div class="search-box flex-grow-1">
                        <input type="text" class="form-control" name="search" placeholder="Tìm email..." value="<?= e($search) ?>">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                    <button class="btn btn-primary"><i class="ri-search-line"></i></button>
                </form>
            </div>
        </div>

        <!-- Bulk bar -->
        <div class="card mb-2 d-none" id="bulkBar" style="position:sticky;top:70px;z-index:100">
            <div class="card-body py-2">
                <form method="POST" action="<?= url('email/bulk') ?>" class="d-flex align-items-center gap-2" id="bulkForm">
                    <?= csrf_field() ?>
                    <div id="bulkIds"></div>
                    <span class="fw-medium"><span id="bulkCount">0</span> đã chọn</span>
                    <button type="submit" name="action" value="read" class="btn btn-soft-primary"><i class="ri-mail-open-line me-1"></i> Đã đọc</button>
                    <button type="submit" name="action" value="unread" class="btn btn-soft-info"><i class="ri-mail-line me-1"></i> Chưa đọc</button>
                    <button type="submit" name="action" value="trash" class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header py-2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" class="form-check-input" id="checkAll">
                        <span class="fw-medium"><?= $folderLabels[$folder] ?? ucfirst($folder) ?> <span class="text-muted">(<?= $total ?>)</span></span>
                    </div>
                    <?php if ($unreadCount > 0): ?><span class="badge bg-primary"><?= $unreadCount ?> chưa đọc</span><?php endif; ?>
                </div>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($messages as $m): ?>
                <div class="list-group-item py-3 <?= !$m['is_read'] ? 'bg-light' : '' ?>">
                    <div class="d-flex align-items-start">
                        <input type="checkbox" class="form-check-input me-2 mt-1 row-check" value="<?= $m['id'] ?>">
                        <button class="btn btn-link p-0 me-2 mt-1 star-btn" data-id="<?= $m['id'] ?>" style="font-size:16px">
                            <i class="ri-star-<?= $m['is_starred'] ? 'fill text-warning' : 'line text-muted' ?>"></i>
                        </button>
                        <a href="<?= url('email/' . $m['id']) ?>" class="flex-grow-1 text-decoration-none text-body">
                            <div class="d-flex align-items-center mb-1">
                                <span class="fw-<?= !$m['is_read'] ? 'bold' : 'medium' ?> me-2">
                                    <?php if ($folder === 'sent'): ?>
                                        <?= e($m['to_emails']) ?>
                                    <?php else: ?>
                                        <?= e($m['from_name'] ?: $m['from_email']) ?>
                                    <?php endif; ?>
                                </span>
                                <?php if ($m['has_attachments']): ?><i class="ri-attachment-line text-muted me-1"></i><?php endif; ?>
                                <?php if ($m['contact_id']): ?><span class="badge bg-success-subtle text-success fs-10">KH</span><?php endif; ?>
                                <span class="text-muted fs-12 ms-auto"><?= $m['sent_at'] ? created_ago($m['sent_at']) : '' ?></span>
                            </div>
                            <p class="mb-0 <?= !$m['is_read'] ? 'fw-medium' : 'text-muted' ?>"><?= e(mb_substr($m['subject'] ?: '(Không tiêu đề)', 0, 80)) ?></p>
                            <?php if ($m['body_text']): ?><small class="text-muted"><?= e(mb_substr(strip_tags($m['body_text']), 0, 100)) ?>...</small><?php endif; ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                <div class="list-group-item text-center py-5 text-muted">
                    <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                    Không có email trong <?= $folderLabels[$folder] ?? $folder ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($totalPages > 1): ?>
            <div class="card-footer">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted fs-12"><?= $total ?> email</span>
                    <ul class="pagination pagination-separated mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= url("email?account={$accountId}&folder={$folder}&page={$i}") ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function(){
    var checkAll = document.getElementById('checkAll');
    var bulkBar = document.getElementById('bulkBar');
    if (!checkAll || !bulkBar) return;
    function updateBulk() {
        var checked = document.querySelectorAll('.row-check:checked');
        if (checked.length > 0) {
            bulkBar.classList.remove('d-none');
            document.getElementById('bulkCount').textContent = checked.length;
            var div = document.getElementById('bulkIds'); div.innerHTML = '';
            checked.forEach(function(cb) { var inp = document.createElement('input'); inp.type='hidden'; inp.name='email_ids[]'; inp.value=cb.value; div.appendChild(inp); });
        } else { bulkBar.classList.add('d-none'); }
    }
    checkAll.addEventListener('change', function() { document.querySelectorAll('.row-check').forEach(function(cb){cb.checked=checkAll.checked}); updateBulk(); });
    document.querySelectorAll('.row-check').forEach(function(cb){cb.addEventListener('change', updateBulk);});

    document.querySelectorAll('.star-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault(); e.stopPropagation();
            var id = this.dataset.id, icon = this.querySelector('i');
            fetch('<?= url("email/") ?>' + id + '/star', {
                method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                body: '_token=<?= csrf_token() ?>'
            }).then(function() { icon.className = icon.classList.contains('ri-star-fill') ? 'ri-star-line text-muted' : 'ri-star-fill text-warning'; });
        });
    });
})();
</script>
