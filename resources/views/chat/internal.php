<?php $pageTitle = 'Chat'; ?>
<?php $myId = $_SESSION['user']['id'] ?? 0; ?>
<?php
$activeRaw = $_GET['active'] ?? '';
$isAi = ($activeRaw === 'ai');

// Load groups the user is a member of
$groups = \Core\Database::fetchAll(
    "SELECT cv.id, cv.name, cv.last_message_at, cv.last_message_preview,
            cm.unread_count as my_unread,
            (SELECT COUNT(*) FROM conversation_members WHERE conversation_id = cv.id) as member_count
     FROM conversations cv
     JOIN conversation_members cm ON cm.conversation_id = cv.id AND cm.user_id = ?
     WHERE cv.tenant_id = ? AND cv.channel = 'group'
     ORDER BY cv.last_message_at DESC",
    [$myId, $_SESSION['tenant_id'] ?? 1]
);

// If active id loads as group, override $active/$messages
if ($isAi) {
    $isGroup = false;
    $active = null;
} elseif (!empty($active) && ($active['channel'] ?? '') === 'internal') {
    $isGroup = false;
} else {
    $activeId = (int) $activeRaw;
    if ($activeId) {
        $group = \Core\Database::fetch(
            "SELECT cv.* FROM conversations cv
             JOIN conversation_members cm ON cm.conversation_id = cv.id AND cm.user_id = ?
             WHERE cv.id = ? AND cv.tenant_id = ? AND cv.channel = 'group'",
            [$myId, $activeId, $_SESSION['tenant_id'] ?? 1]
        );
        if ($group) {
            $active = $group;
            $messages = \Core\Database::fetchAll(
                "SELECT m.*, u.name as sender_name, u.avatar as sender_avatar
                 FROM messages m LEFT JOIN users u ON m.sender_id = u.id
                 WHERE m.conversation_id = ? ORDER BY m.created_at ASC",
                [$activeId]
            );
            \Core\Database::query("UPDATE conversation_members SET unread_count = 0, last_read_at = NOW() WHERE conversation_id = ? AND user_id = ?", [$activeId, $myId]);
            $isGroup = true;
        }
    }
    $isGroup = $isGroup ?? false;
}
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Chat</h4>
    <div>
        <button type="button" class="btn btn-soft-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal"><i class="ri-group-line me-1"></i>Tạo nhóm</button>
    </div>
</div>

<div class="card" style="height: calc(100vh - 180px)">
    <div class="card-body p-0 d-flex h-100">

        <!-- Sidebar -->
        <div class="border-end d-flex flex-column" style="width:320px;min-width:320px">
            <div class="p-3 border-bottom">
                <div class="input-group mb-2">
                    <input type="text" id="globalSearch" class="form-control" placeholder="Tìm tin nhắn...">
                    <span class="input-group-text"><i class="ri-search-line"></i></span>
                </div>
                <div id="searchResults" class="mt-1" style="max-height:250px;overflow-y:auto;display:none;background:#fff;border:1px solid #e5e5e5;border-radius:6px"></div>

                <div class="input-group mt-2">
                    <input type="text" id="userSearch" class="form-control" placeholder="Tìm nhân viên để nhắn...">
                </div>
                <div id="userPickerResults" class="mt-1" style="max-height:200px;overflow-y:auto;display:none;"></div>
            </div>

            <div class="flex-grow-1" style="overflow-y:auto">
                <!-- AI Trợ lý pinned row -->
                <a href="<?= url('chat?active=ai') ?>" class="d-block px-3 py-2 border-bottom text-decoration-none text-dark <?= $isAi ? 'bg-primary-subtle' : '' ?>" style="<?= $isAi ? '' : 'background:#f8f9fa' ?>">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                            <div class="avatar-title rounded-circle bg-primary text-white"><i class="ri-robot-2-line"></i></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">AI Trợ lý</span>
                                <i class="ri-pushpin-2-fill text-primary fs-14" title="Đã ghim"></i>
                            </div>
                            <small class="text-muted">Hỏi bất cứ điều gì về CRM</small>
                        </div>
                    </div>
                </a>

                <?php if (empty($dms) && empty($groups)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="ri-chat-3-line fs-48 d-block mb-2"></i>
                        Chưa có cuộc chat nào.<br>
                        <small>Tìm nhân viên ở ô trên để bắt đầu.</small>
                    </div>
                <?php endif; ?>

                <!-- Groups -->
                <?php foreach ($groups as $g):
                    $isActive = ($active['id'] ?? 0) == $g['id'] && ($isGroup ?? false);
                    $hasUnread = (int)$g['my_unread'] > 0;
                ?>
                <a href="<?= url('chat?active=' . $g['id']) ?>"
                   class="d-block px-3 py-2 border-bottom text-decoration-none text-dark <?= $isActive ? 'bg-primary-subtle' : '' ?>">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                            <div class="avatar-title rounded-circle bg-info-subtle text-info"><i class="ri-group-line"></i></div>
                        </div>
                        <div class="flex-grow-1 text-truncate">
                            <div class="d-flex justify-content-between">
                                <span class="<?= $hasUnread ? 'fw-bold' : 'fw-medium' ?>"><?= e($g['name'] ?? 'Group') ?></span>
                                <?php if ($hasUnread): ?><span class="badge bg-danger rounded-pill"><?= $g['my_unread'] ?></span><?php endif; ?>
                            </div>
                            <small class="text-muted text-truncate d-block"><?= (int)$g['member_count'] ?> thành viên · <?= e($g['last_message_preview'] ?? '—') ?></small>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>

                <!-- DMs -->
                <?php foreach ($dms as $dm):
                    $isActive = ($active['id'] ?? 0) == $dm['id'] && !($isGroup ?? false);
                    $hasUnread = (int)$dm['my_unread'] > 0;
                ?>
                <a href="<?= url('chat?active=' . $dm['id']) ?>"
                   class="d-block px-3 py-2 border-bottom text-decoration-none text-dark <?= $isActive ? 'bg-primary-subtle' : '' ?>">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                            <?php if (!empty($dm['peer_avatar'])): ?>
                                <img src="<?= asset($dm['peer_avatar']) ?>" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                            <?php else: ?>
                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary"><?= strtoupper(mb_substr($dm['peer_name'] ?? '?', 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1 text-truncate">
                            <div class="d-flex justify-content-between">
                                <span class="<?= $hasUnread ? 'fw-bold' : 'fw-medium' ?>"><?= e($dm['peer_name'] ?? '') ?></span>
                                <?php if ($hasUnread): ?><span class="badge bg-danger rounded-pill"><?= $dm['my_unread'] ?></span><?php endif; ?>
                            </div>
                            <small class="text-muted text-truncate d-block"><?= e($dm['last_message_preview'] ?? '') ?></small>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Thread -->
        <div class="flex-grow-1 d-flex flex-column">
            <?php if ($isAi): ?>
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                            <div class="avatar-title rounded-circle bg-primary text-white"><i class="ri-robot-2-line"></i></div>
                        </div>
                        <div>
                            <div class="fw-semibold">AI Trợ lý</div>
                            <small class="text-muted">Hỏi bất cứ điều gì về CRM</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-soft-danger" id="aiClearBtn"><i class="ri-delete-bin-line me-1"></i>Xóa lịch sử</button>
                </div>

                <div id="aiMsgArea" class="flex-grow-1 px-3 py-2" style="overflow-y:auto;background:#fafafa">
                    <div id="aiWelcome" class="text-center py-5 text-muted">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title rounded-circle bg-primary-subtle text-primary" style="width:72px;height:72px;font-size:32px"><i class="ri-robot-2-line"></i></div>
                        </div>
                        <h6 class="mb-1">Xin chào! Tôi là AI Trợ lý của ToryCRM</h6>
                        <p class="small mb-3">Gợi ý câu hỏi:</p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <button class="btn btn-soft-primary ai-suggest">Doanh thu tháng này</button>
                            <button class="btn btn-soft-warning ai-suggest">Công việc quá hạn</button>
                            <button class="btn btn-soft-info ai-suggest">Khách hàng cần liên hệ</button>
                            <button class="btn btn-soft-success ai-suggest">Thống kê pipeline</button>
                        </div>
                    </div>
                </div>

                <form id="aiForm" class="p-3 border-top d-flex gap-2">
                    <?= csrf_field() ?>
                    <input type="text" id="aiInput" class="form-control" placeholder="Nhập câu hỏi cho AI..." autocomplete="off">
                    <button type="submit" class="btn btn-primary" id="aiSendBtn"><i class="ri-send-plane-line"></i></button>
                </form>

                <script>
                (function(){
                    var area = document.getElementById('aiMsgArea');
                    var welcome = document.getElementById('aiWelcome');
                    var form = document.getElementById('aiForm');
                    var input = document.getElementById('aiInput');
                    var sendBtn = document.getElementById('aiSendBtn');
                    var clearBtn = document.getElementById('aiClearBtn');
                    var token = '<?= csrf_token() ?>';

                    function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
                    function fmtTime(s){ var d = s ? new Date(s.replace(' ','T')) : new Date(); return d.getHours().toString().padStart(2,'0')+':'+d.getMinutes().toString().padStart(2,'0'); }
                    function appendMsg(role, content, ts){
                        if (welcome) { welcome.remove(); welcome = null; }
                        var mine = (role === 'user');
                        var html = '<div class="d-flex '+(mine?'justify-content-end':'')+' mb-2">'
                                 + '<div class="'+(mine?'bg-primary text-white':'bg-white border')+' rounded px-3 py-2" style="max-width:75%">'
                                 + '<div style="white-space:pre-wrap">'+esc(content)+'</div>'
                                 + '<small class="'+(mine?'text-white-50':'text-muted')+' fs-11 d-block mt-1">'+fmtTime(ts)+'</small>'
                                 + '</div></div>';
                        area.insertAdjacentHTML('beforeend', html);
                        area.scrollTop = area.scrollHeight;
                    }

                    fetch('<?= url('ai-chat/history') ?>').then(r=>r.json()).then(function(d){
                        (d.messages||[]).forEach(function(m){ appendMsg(m.role, m.message, m.created_at); });
                    }).catch(function(){});

                    function sendMessage(text){
                        if (!text.trim()) return;
                        appendMsg('user', text);
                        input.value = '';
                        sendBtn.disabled = true;
                        var loadingId = 'ai-loading-' + Date.now();
                        area.insertAdjacentHTML('beforeend', '<div id="'+loadingId+'" class="d-flex mb-2"><div class="bg-white border rounded px-3 py-2"><i class="ri-loader-4-line spin"></i> Đang suy nghĩ...</div></div>');
                        area.scrollTop = area.scrollHeight;
                        var fd = new FormData(); fd.append('_token', token); fd.append('message', text);
                        fetch('<?= url('ai-chat/send') ?>', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
                            .then(r=>r.json()).then(function(d){
                                var el = document.getElementById(loadingId); if (el) el.remove();
                                appendMsg('assistant', d.message || d.error || 'Lỗi không xác định');
                            }).catch(function(){
                                var el = document.getElementById(loadingId); if (el) el.remove();
                                appendMsg('assistant', 'Lỗi kết nối. Vui lòng thử lại.');
                            }).finally(function(){ sendBtn.disabled = false; input.focus(); });
                    }

                    form.addEventListener('submit', function(e){ e.preventDefault(); sendMessage(input.value); });
                    document.querySelectorAll('.ai-suggest').forEach(function(btn){
                        btn.addEventListener('click', function(){ sendMessage(this.textContent); });
                    });
                    clearBtn.addEventListener('click', function(){
                        if (!confirm('Xóa toàn bộ lịch sử chat với AI?')) return;
                        var fd = new FormData(); fd.append('_token', token);
                        fetch('<?= url('ai-chat/clear') ?>', {method:'POST', body:fd}).then(function(){ location.reload(); });
                    });
                })();
                </script>
            <?php elseif ($active): ?>
                <?php
                $isActiveGroup = ($active['channel'] ?? '') === 'group';
                if ($isActiveGroup) {
                    $headerName = $active['name'] ?? 'Group';
                    $headerSub = \Core\Database::fetch("SELECT COUNT(*) as c FROM conversation_members WHERE conversation_id = ?", [$active['id']])['c'] ?? 0;
                    $headerSub .= ' thành viên';
                    $replyPath = url('chat/group/' . $active['id'] . '/reply');
                    $pollPath = url('chat/group/' . $active['id'] . '/poll');
                } else {
                    $peerId = ($active['user_a_id'] == $myId) ? $active['user_b_id'] : $active['user_a_id'];
                    $peer = \Core\Database::fetch("SELECT name, avatar, email FROM users WHERE id = ?", [$peerId]);
                    $headerName = $peer['name'] ?? '';
                    $headerSub = $peer['email'] ?? '';
                    $replyPath = url('chat/internal/' . $active['id'] . '/reply');
                    $pollPath = url('chat/internal/' . $active['id'] . '/poll');
                }
                ?>
                <div class="p-3 border-bottom d-flex align-items-center">
                    <div class="avatar-sm me-2">
                        <?php if ($isActiveGroup): ?>
                            <div class="avatar-title rounded-circle bg-info-subtle text-info"><i class="ri-group-line"></i></div>
                        <?php elseif (!empty($peer['avatar'])): ?>
                            <img src="<?= asset($peer['avatar']) ?>" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                        <?php else: ?>
                            <div class="avatar-title rounded-circle bg-primary-subtle text-primary"><?= strtoupper(mb_substr($headerName ?: '?', 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="fw-semibold"><?= e($headerName) ?></div>
                        <small class="text-muted"><?= e($headerSub) ?></small>
                    </div>
                </div>

                <div id="msgArea" class="flex-grow-1 px-3 py-2" style="overflow-y:auto">
                    <?php foreach ($messages as $m):
                        $mine = ($m['sender_id'] == $myId);
                        $atts = [];
                        if (!empty($m['attachments'])) $atts = json_decode($m['attachments'], true) ?: [];
                    ?>
                    <div class="d-flex <?= $mine ? 'justify-content-end' : '' ?> mb-2 msg-row" data-msg-id="<?= $m['id'] ?>">
                        <div class="<?= $mine ? 'bg-primary text-white' : 'bg-light' ?> rounded px-3 py-2 position-relative" style="max-width:70%">
                            <?php if ($isActiveGroup && !$mine): ?>
                                <small class="d-block fw-semibold text-info"><?= e($m['sender_name'] ?? '') ?></small>
                            <?php endif; ?>
                            <?php if (!empty($m['content'])): ?>
                                <div style="white-space:pre-wrap"><?= e($m['content']) ?></div>
                            <?php endif; ?>
                            <?php foreach ($atts as $att): ?>
                                <?php if (!empty($att['is_image'])): ?>
                                    <img src="<?= e($att['url']) ?>" style="max-width:200px;max-height:200px;display:block;margin-top:4px;border-radius:4px">
                                <?php else: ?>
                                    <a href="<?= e($att['url']) ?>" target="_blank" class="<?= $mine ? 'text-white' : '' ?> d-block"><i class="ri-attachment-2 me-1"></i><?= e($att['name']) ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <small class="<?= $mine ? 'text-white-50' : 'text-muted' ?> fs-11 d-flex align-items-center gap-1">
                                <span><?= date('H:i d/m', strtotime($m['created_at'])) ?></span>
                                <?php if ($m['is_pinned']): ?><i class="ri-pushpin-2-fill"></i><?php endif; ?>
                                <button type="button" class="btn btn-sm p-0 pin-btn <?= $mine ? 'text-white-50' : 'text-muted' ?>" data-msg-id="<?= $m['id'] ?>" title="Ghim/bỏ ghim" style="font-size:11px"><i class="ri-pushpin-line"></i></button>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form id="dmForm" class="p-3 border-top d-flex gap-2 align-items-center" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" id="dmId" value="<?= $active['id'] ?>">
                    <label class="btn btn-soft-secondary btn-icon mb-0" title="Đính kèm">
                        <i class="ri-attachment-2"></i>
                        <input type="file" id="dmFile" style="display:none">
                    </label>
                    <span id="fileName" class="text-muted small"></span>
                    <input type="text" id="dmContent" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off">
                    <button type="submit" class="btn btn-primary"><i class="ri-send-plane-line"></i></button>
                </form>

                <script>
                (function(){
                    var dmId = <?= (int)$active['id'] ?>;
                    var myId = <?= (int)$myId ?>;
                    var isGroup = <?= $isActiveGroup ? 'true' : 'false' ?>;
                    var replyPath = <?= json_encode($replyPath) ?>;
                    var pollPath = <?= json_encode($pollPath) ?>;
                    var area = document.getElementById('msgArea');
                    var form = document.getElementById('dmForm');
                    var input = document.getElementById('dmContent');
                    var fileInput = document.getElementById('dmFile');
                    var fileName = document.getElementById('fileName');
                    var lastId = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
                    area.scrollTop = area.scrollHeight;

                    fileInput.addEventListener('change', function(){
                        fileName.textContent = this.files[0] ? this.files[0].name : '';
                    });

                    function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
                    function formatTime(s){ var d=new Date(s); return d.getHours().toString().padStart(2,'0')+':'+d.getMinutes().toString().padStart(2,'0')+' '+d.getDate().toString().padStart(2,'0')+'/'+(d.getMonth()+1).toString().padStart(2,'0'); }
                    function appendMsg(m){
                        if (document.querySelector('[data-msg-id="'+m.id+'"]')) return;
                        var mine = m.sender_id == myId;
                        var atts = m.attachments ? (typeof m.attachments === 'string' ? JSON.parse(m.attachments) : m.attachments) : [];
                        var attHtml = '';
                        (atts||[]).forEach(function(a){
                            if (a.is_image) attHtml += '<img src="'+a.url+'" style="max-width:200px;max-height:200px;display:block;margin-top:4px;border-radius:4px">';
                            else attHtml += '<a href="'+a.url+'" target="_blank" class="'+(mine?'text-white':'')+' d-block"><i class="ri-attachment-2 me-1"></i>'+esc(a.name)+'</a>';
                        });
                        var senderTag = (isGroup && !mine) ? '<small class="d-block fw-semibold text-info">'+esc(m.sender_name||'')+'</small>' : '';
                        var html = '<div class="d-flex '+(mine?'justify-content-end':'')+' mb-2 msg-row" data-msg-id="'+m.id+'">'
                                 + '<div class="'+(mine?'bg-primary text-white':'bg-light')+' rounded px-3 py-2 position-relative" style="max-width:70%">'
                                 + senderTag
                                 + (m.content ? '<div style="white-space:pre-wrap">'+esc(m.content)+'</div>' : '')
                                 + attHtml
                                 + '<small class="'+(mine?'text-white-50':'text-muted')+' fs-11">'+formatTime(m.created_at)+'</small>'
                                 + '</div></div>';
                        area.insertAdjacentHTML('beforeend', html);
                        area.scrollTop = area.scrollHeight;
                        if (m.id > lastId) lastId = m.id;
                    }

                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        var content = input.value.trim();
                        if (!content && !fileInput.files[0]) return;
                        var fd = new FormData();
                        fd.append('_token', '<?= csrf_token() ?>');
                        fd.append('content', content);
                        if (fileInput.files[0]) fd.append('attachment', fileInput.files[0]);
                        fetch(replyPath, {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
                            .then(r=>r.json()).then(function(){
                                input.value = '';
                                fileInput.value = '';
                                fileName.textContent = '';
                                poll();
                            });
                    });

                    function poll(){
                        fetch(pollPath + '?after='+lastId).then(r=>r.json()).then(function(d){
                            (d.messages||[]).forEach(appendMsg);
                        }).catch(function(){});
                    }
                    setInterval(poll, 5000);

                    // Pin
                    document.addEventListener('click', function(e){
                        var btn = e.target.closest('.pin-btn');
                        if (!btn) return;
                        e.preventDefault();
                        var mid = btn.dataset.msgId;
                        var fd = new FormData(); fd.append('_token', '<?= csrf_token() ?>');
                        fetch('<?= url('chat/message/') ?>'+mid+'/pin', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(d){
                            if (d.success) location.reload();
                        });
                    });
                })();
                </script>
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                    <div class="text-center">
                        <i class="ri-chat-smile-2-line fs-48 d-block mb-2"></i>
                        Chọn một cuộc chat bên trái hoặc tìm nhân viên / tạo nhóm.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Create Group Modal -->
<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="<?= url('chat/group/create') ?>" id="createGroupForm">
            <?= csrf_field() ?>
            <div class="modal-header"><h5 class="modal-title"><i class="ri-group-line me-1"></i>Tạo nhóm chat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tên nhóm <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required maxlength="100" placeholder="VD: Team Sales">
                </div>
                <div class="mb-2">
                    <label class="form-label d-flex justify-content-between align-items-center">
                        <span>Thành viên <span class="text-danger">*</span></span>
                        <small class="text-muted" id="memberCount">0 đã chọn</small>
                    </label>
                    <div class="input-group mb-2">
                        <span class="input-group-text bg-white"><i class="ri-search-line text-muted"></i></span>
                        <input type="text" id="memberSearch" class="form-control border-start-0" placeholder="Tìm theo tên, chức vụ hoặc email...">
                    </div>
                    <div id="selectedChips" class="d-flex flex-wrap gap-1 mb-2" style="min-height:0"></div>
                    <div id="memberList" class="border rounded" style="max-height:280px;overflow-y:auto">
                        <?php foreach ($users as $u):
                            $ini = mb_strtoupper(mb_substr($u['name'] ?? '?', 0, 1));
                            $search = mb_strtolower(($u['name'] ?? '') . ' ' . ($u['email'] ?? '') . ' ' . ($u['position_name'] ?? ''));
                        ?>
                        <label class="member-item d-flex align-items-center px-3 py-2 border-bottom mb-0" style="cursor:pointer" data-search="<?= e($search) ?>">
                            <input type="checkbox" name="members[]" value="<?= $u['id'] ?>" class="form-check-input me-3 member-checkbox" data-name="<?= e($u['name']) ?>">
                            <?php if (!empty($u['avatar'])): ?>
                                <img src="<?= asset($u['avatar']) ?>" class="rounded-circle me-2" width="36" height="36" style="object-fit:cover">
                            <?php else: ?>
                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary me-2" style="width:36px;height:36px"><?= $ini ?></div>
                            <?php endif; ?>
                            <div class="flex-grow-1 text-truncate">
                                <div class="fw-medium text-dark"><?= e($u['name']) ?></div>
                                <small class="text-muted text-truncate d-block"><?= e($u['position_name'] ?? '') ?><?= !empty($u['position_name']) && !empty($u['email']) ? ' · ' : '' ?><?= e($u['email'] ?? '') ?></small>
                            </div>
                        </label>
                        <?php endforeach; ?>
                        <div id="memberEmpty" class="text-center text-muted py-3" style="display:none">Không tìm thấy nhân viên phù hợp</div>
                    </div>
                    <small class="text-muted d-block mt-1">Chọn tối thiểu 2 thành viên.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary" id="createGroupSubmit" disabled><i class="ri-group-line me-1"></i> Tạo nhóm</button>
            </div>
        </form>
    </div>
</div>
<style>
    .member-item:hover { background:#f8f9fa; }
    .member-item:last-child { border-bottom:none !important; }
    .member-item input:checked ~ * { }
    .member-item.is-checked { background:#e7f1ff; }
    .selected-chip { background:#e7f1ff; color:#0d6efd; padding:2px 8px; border-radius:12px; font-size:12px; display:inline-flex; align-items:center; gap:4px; }
    .selected-chip .chip-x { cursor:pointer; opacity:.6; }
    .selected-chip .chip-x:hover { opacity:1; }
</style>
<script>
(function(){
    var modal = document.getElementById('createGroupModal');
    if (!modal) return;
    var search = modal.querySelector('#memberSearch');
    var list = modal.querySelector('#memberList');
    var empty = modal.querySelector('#memberEmpty');
    var chips = modal.querySelector('#selectedChips');
    var count = modal.querySelector('#memberCount');
    var submit = modal.querySelector('#createGroupSubmit');
    var items = modal.querySelectorAll('.member-item');
    var checkboxes = modal.querySelectorAll('.member-checkbox');

    function updateState(){
        var selected = Array.from(checkboxes).filter(c => c.checked);
        count.textContent = selected.length + ' đã chọn';
        submit.disabled = selected.length < 2;
        chips.innerHTML = selected.map(c =>
            '<span class="selected-chip">'+c.dataset.name+'<span class="chip-x" data-id="'+c.value+'">×</span></span>'
        ).join('');
        items.forEach(function(it){
            var cb = it.querySelector('.member-checkbox');
            it.classList.toggle('is-checked', cb.checked);
        });
    }

    checkboxes.forEach(function(cb){ cb.addEventListener('change', updateState); });
    chips.addEventListener('click', function(e){
        var x = e.target.closest('.chip-x'); if (!x) return;
        var cb = modal.querySelector('.member-checkbox[value="'+x.dataset.id+'"]');
        if (cb) { cb.checked = false; updateState(); }
    });

    search.addEventListener('input', function(){
        var q = this.value.trim().toLowerCase();
        var visible = 0;
        items.forEach(function(it){
            var show = !q || (it.dataset.search || '').includes(q);
            it.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        empty.style.display = visible ? 'none' : 'block';
    });

    modal.addEventListener('hidden.bs.modal', function(){
        modal.querySelector('#createGroupForm').reset();
        checkboxes.forEach(c => c.checked = false);
        search.value = '';
        items.forEach(it => it.style.display = '');
        updateState();
    });

    updateState();
})();
</script>

<script>
(function(){
    var searchInput = document.getElementById('userSearch');
    var results = document.getElementById('userPickerResults');
    var allUsers = <?= json_encode(array_map(fn($u) => ['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'avatar'=>$u['avatar']??'','position'=>$u['position_name']??''], $users), JSON_UNESCAPED_UNICODE) ?>;
    var assetBase = '<?= rtrim(url(''), '/') ?>/';

    function userAvatarHtml(u){
        if (u.avatar) return '<img src="'+assetBase+u.avatar+'" class="rounded-circle" width="32" height="32" style="object-fit:cover">';
        var ini = (u.name||'?').trim().charAt(0).toUpperCase();
        return '<div class="avatar-xs"><div class="avatar-title rounded-circle bg-primary-subtle text-primary" style="width:32px;height:32px">'+ini+'</div></div>';
    }

    searchInput.addEventListener('input', function(){
        var q = this.value.trim().toLowerCase();
        if (!q) { results.style.display = 'none'; return; }
        var matched = allUsers.filter(u => (u.name||'').toLowerCase().includes(q) || (u.email||'').toLowerCase().includes(q) || (u.position||'').toLowerCase().includes(q)).slice(0, 10);
        if (!matched.length) { results.innerHTML = '<div class="text-muted text-center py-2">Không tìm thấy</div>'; results.style.display='block'; return; }
        results.innerHTML = matched.map(u =>
            '<form method="POST" action="<?= url('chat/internal/start/') ?>'+u.id+'" class="d-block">'
            + '<?= csrf_field() ?>'
            + '<button type="submit" class="btn btn-link text-start w-100 px-2 py-2 text-decoration-none border-bottom d-flex align-items-center gap-2">'
            + userAvatarHtml(u)
            + '<div class="flex-grow-1 text-truncate">'
            + '<div class="fw-semibold text-dark">'+u.name+'</div>'
            + '<small class="text-muted d-block text-truncate">'+(u.position ? u.position+' · ' : '')+(u.email||'')+'</small>'
            + '</div>'
            + '</button></form>'
        ).join('');
        results.style.display = 'block';
    });

    // Global message search
    var gsInput = document.getElementById('globalSearch');
    var gsResults = document.getElementById('searchResults');
    var gsTimer = null;
    gsInput.addEventListener('input', function(){
        var q = this.value.trim();
        clearTimeout(gsTimer);
        if (q.length < 2) { gsResults.style.display='none'; return; }
        gsTimer = setTimeout(function(){
            fetch('<?= url('chat/search') ?>?q=' + encodeURIComponent(q)).then(r=>r.json()).then(function(d){
                var rows = d.results || [];
                if (!rows.length) { gsResults.innerHTML = '<div class="text-muted text-center py-2">Không có kết quả</div>'; gsResults.style.display='block'; return; }
                gsResults.innerHTML = rows.map(function(r){
                    return '<a href="<?= url('chat?active=') ?>'+r.conversation_id+'#msg-'+r.id+'" class="d-block px-2 py-2 border-bottom text-decoration-none text-dark">'
                        + '<div class="small text-muted">'+(r.group_name || r.sender_name || '')+'</div>'
                        + '<div>'+(r.content||'').substring(0, 120)+'</div>'
                        + '</a>';
                }).join('');
                gsResults.style.display = 'block';
            });
        }, 250);
    });
})();
</script>
