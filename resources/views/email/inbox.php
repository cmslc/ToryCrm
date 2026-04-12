<?php
$pageTitle = 'Email';
$folderLabels = ['inbox'=>'Hộp thư đến','sent'=>'Đã gửi','drafts'=>'Nháp','trash'=>'Thùng rác','spam'=>'Spam','archive'=>'Lưu trữ'];
$folderIcons = ['inbox'=>'ri-inbox-line','sent'=>'ri-send-plane-line','drafts'=>'ri-draft-line','trash'=>'ri-delete-bin-line','spam'=>'ri-spam-line','archive'=>'ri-archive-line'];
?>

<?php if (empty($accounts)): ?>
<div class="page-title-box"><h4 class="mb-0"><i class="ri-mail-line me-2"></i> Email</h4></div>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="ri-mail-settings-line fs-1 text-muted d-block mb-3"></i>
        <h5>Chưa cấu hình tài khoản email</h5>
        <p class="text-muted">Thêm tài khoản để bắt đầu gửi và nhận email.</p>
        <a href="<?= url('email/settings') ?>" class="btn btn-primary"><i class="ri-settings-3-line me-1"></i> Cấu hình ngay</a>
    </div>
</div>
<?php else: ?>

<div class="page-title-box"><h4 class="mb-0"><i class="ri-mail-line me-2"></i> Email</h4></div>

<div class="d-flex" style="min-height:calc(100vh - 140px)">
    <!-- Sidebar Gmail style -->
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
                if ($cnt == 0 && !in_array($f, ['inbox','sent','drafts','trash'])) continue;
                $isActive = ($folder === $f);
            ?>
            <a href="<?= url('email?account=' . $accountId . '&folder=' . $f) ?>"
               class="d-flex align-items-center px-3 py-2 rounded text-decoration-none mb-1 <?= $isActive ? 'bg-primary-subtle text-primary fw-medium' : 'text-body' ?>"
               style="<?= $isActive ? '' : '' ?>">
                <i class="<?= $folderIcons[$f] ?> me-2 fs-16"></i>
                <span class="flex-grow-1"><?= $folderLabels[$f] ?></span>
                <?php if ($unread > 0): ?><span class="fw-bold fs-12"><?= $unread ?></span>
                <?php elseif ($cnt > 0 && $f !== 'inbox'): ?><span class="text-muted fs-12"><?= $cnt ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <hr class="my-3">
        <div class="px-2">
            <?php if (count($accounts) > 1): ?>
            <select class="form-select mb-2" onchange="location.href='<?= url('email') ?>?account='+this.value">
                <?php foreach ($accounts as $acc): ?>
                <option value="<?= $acc['id'] ?>" <?= $acc['id'] == $accountId ? 'selected' : '' ?>><?= e($acc['email']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php else: ?>
            <small class="text-muted d-block mb-2"><?= e($accounts[0]['email']) ?></small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main content -->
    <div class="flex-grow-1 ms-3">
        <!-- Toolbar -->
        <div class="bg-white rounded-top border-bottom px-3 py-2 d-flex align-items-center gap-3">
            <input type="checkbox" class="form-check-input" id="checkAll">
            <form method="POST" action="<?= url('email/sync') ?>" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="account_id" value="<?= $accountId ?>">
                <button class="btn btn-link p-0 text-muted shadow-none" title="Đồng bộ"><i class="ri-refresh-line fs-18"></i></button>
            </form>
            <form method="GET" action="<?= url('email') ?>" class="flex-grow-1 d-flex">
                <input type="hidden" name="account" value="<?= $accountId ?>">
                <input type="hidden" name="folder" value="<?= e($folder) ?>">
                <div class="search-box w-100">
                    <input type="text" class="form-control border-0 bg-light" name="search" placeholder="Tìm kiếm email..." value="<?= e($search) ?>" style="border-radius:8px">
                    <i class="ri-search-line search-icon"></i>
                </div>
            </form>
            <span class="text-muted fs-12 flex-shrink-0"><?= $total ?> email</span>
        </div>

        <!-- Bulk bar -->
        <div class="bg-primary-subtle px-3 py-2 d-none" id="bulkBar">
            <form method="POST" action="<?= url('email/bulk') ?>" class="d-flex align-items-center gap-2" id="bulkForm">
                <?= csrf_field() ?>
                <div id="bulkIds"></div>
                <span class="fw-medium fs-13"><span id="bulkCount">0</span> đã chọn</span>
                <button type="submit" name="action" value="read" class="btn btn-light"><i class="ri-mail-open-line"></i></button>
                <button type="submit" name="action" value="unread" class="btn btn-light"><i class="ri-mail-unread-line"></i></button>
                <button type="submit" name="action" value="trash" class="btn btn-light text-danger"><i class="ri-delete-bin-line"></i></button>
            </form>
        </div>

        <!-- Email list (Gmail style) -->
        <div class="bg-white rounded-bottom border">
            <?php foreach ($messages as $m):
                $sender = $folder === 'sent' ? e($m['to_emails']) : e($m['from_name'] ?: $m['from_email']);
                $subj = e(mb_substr($m['subject'] ?: '(Không tiêu đề)', 0, 60));
                $preview = $m['body_text'] ? ' - ' . e(mb_substr(strip_tags($m['body_text']), 0, 80)) : '';
                $time = $m['sent_at'] ? (date('Y-m-d', strtotime($m['sent_at'])) === date('Y-m-d') ? date('H:i', strtotime($m['sent_at'])) : date('d/m', strtotime($m['sent_at']))) : '';
            ?>
            <?php $emailUrl = ($folder === 'drafts') ? url('email/compose?draft=' . $m['id']) : url('email/' . $m['id']); ?>
            <div class="d-flex align-items-center px-3 py-3 border-bottom email-row <?= !$m['is_read'] ? 'bg-light fw-medium' : '' ?>" style="cursor:pointer" onclick="if(!event.target.closest('.no-nav'))location.href='<?= $emailUrl ?>'">
                <input type="checkbox" class="form-check-input me-2 row-check no-nav" value="<?= $m['id'] ?>">
                <button class="btn btn-link p-0 me-2 star-btn no-nav" data-id="<?= $m['id'] ?>" style="font-size:14px;line-height:1">
                    <i class="ri-star-<?= $m['is_starred'] ? 'fill text-warning' : 'line text-muted' ?>"></i>
                </button>
                <span class="flex-shrink-0 text-truncate <?= !$m['is_read'] ? 'fw-bold' : '' ?>" style="width:180px"><?= $sender ?></span>
                <span class="flex-grow-1 text-truncate ms-2">
                    <span class="<?= !$m['is_read'] ? '' : 'text-body' ?>"><?= $subj ?></span>
                    <span class="text-muted fw-normal"><?= $preview ?></span>
                </span>
                <?php if ($m['has_attachments']): ?><i class="ri-attachment-line text-muted ms-2 flex-shrink-0"></i><?php endif; ?>
                <?php if ($m['contact_id']): ?><span class="badge bg-success-subtle text-success ms-1 flex-shrink-0 fs-10">KH</span><?php endif; ?>
                <span class="text-muted fs-12 ms-2 flex-shrink-0" style="min-width:40px;text-align:right"><?= $time ?></span>
            </div>
            <?php endforeach; ?>

            <?php if (empty($messages)): ?>
            <div class="text-center py-5 text-muted">
                <i class="ri-inbox-line fs-1 d-block mb-2"></i>
                Không có email trong <?= $folderLabels[$folder] ?? $folder ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="d-flex align-items-center justify-content-between mt-2">
            <span class="text-muted fs-12"><?= $total ?> email</span>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="<?= url("email?account={$accountId}&folder={$folder}&page={$i}") ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.email-row:hover { background-color: #f8f9fa !important; }
.email-row .star-btn:hover i { color: #f7b84b !important; }
</style>

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
