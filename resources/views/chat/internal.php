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
            $messages = (new \App\Controllers\ChatController())->enrichMessages($messages, $myId);
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
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
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
                    <?php if ($isActiveGroup): ?>
                    <button type="button" class="btn btn-soft-secondary btn-icon" id="groupSettingsBtn" title="Quản lý nhóm" data-bs-toggle="modal" data-bs-target="#groupSettingsModal"><i class="ri-settings-3-line"></i></button>
                    <?php endif; ?>
                </div>

                <div id="msgArea" class="flex-grow-1 px-3 py-2" style="overflow-y:auto">
                    <?php
                    // Build member id->name map (for group mentions autocomplete) once
                    $groupMembers = [];
                    if ($isActiveGroup) {
                        $groupMembers = \Core\Database::fetchAll(
                            "SELECT u.id, u.name, u.avatar FROM conversation_members cm JOIN users u ON u.id = cm.user_id WHERE cm.conversation_id = ? ORDER BY u.name",
                            [$active['id']]
                        );
                    }
                    foreach ($messages as $m):
                        $isBot = (($m['sender_type'] ?? '') === 'system' && (int)$m['sender_id'] === 0);
                        $mine = !$isBot && ($m['sender_id'] == $myId);
                        $atts = [];
                        if (!empty($m['attachments'])) $atts = json_decode($m['attachments'], true) ?: [];
                        $deleted = !empty($m['deleted_at']);
                        $edited = !empty($m['edited_at']);
                        $replyTo = $m['reply_to'] ?? null;
                        $reactions = $m['reactions'] ?? [];
                        $isMentioned = !empty($m['is_mentioned']);
                        $bubbleCls = $isBot ? 'bg-warning-subtle border border-warning' : ($mine ? 'bg-primary text-white' : 'bg-light');
                    ?>
                    <div class="d-flex <?= $mine ? 'justify-content-end' : '' ?> mb-2 msg-row <?= $isMentioned ? 'mentioned' : '' ?>" data-msg-id="<?= $m['id'] ?>" data-created-at="<?= e($m['created_at']) ?>" data-mine="<?= $mine ? '1' : '0' ?>" data-sender-id="<?= (int)$m['sender_id'] ?>" data-bot="<?= $isBot ? '1' : '0' ?>">
                        <div class="msg-bubble <?= $bubbleCls ?> rounded px-3 py-2 position-relative" style="max-width:70%">
                            <?php if ($isBot): ?>
                                <small class="d-block fw-semibold text-warning"><i class="ri-robot-2-line me-1"></i>AI Trợ lý</small>
                            <?php elseif ($isActiveGroup && !$mine && !$deleted): ?>
                                <small class="d-block fw-semibold text-info"><?= e($m['sender_name'] ?? '') ?></small>
                            <?php endif; ?>
                            <?php if ($replyTo): ?>
                                <div class="reply-snapshot border-start border-3 ps-2 mb-1 small <?= $mine ? 'border-light opacity-75' : 'border-primary text-muted' ?>">
                                    <div class="fw-semibold"><?= e($replyTo['sender_name']) ?></div>
                                    <div class="text-truncate" style="max-width:250px"><?= e($replyTo['preview']) ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if ($deleted): ?>
                                <div class="fst-italic <?= $mine ? 'text-white-50' : 'text-muted' ?>"><i class="ri-delete-bin-line me-1"></i>Tin nhắn đã được thu hồi</div>
                            <?php else: ?>
                                <?php if (!empty($m['content'])): ?>
                                    <div class="msg-content" style="white-space:pre-wrap"><?= e($m['content']) ?></div>
                                <?php endif; ?>
                                <?php foreach ($atts as $att): ?>
                                    <?php if (!empty($att['is_image'])): ?>
                                        <img src="<?= e($att['url']) ?>" style="max-width:200px;max-height:200px;display:block;margin-top:4px;border-radius:4px">
                                    <?php else: ?>
                                        <a href="<?= e($att['url']) ?>" target="_blank" class="<?= $mine ? 'text-white' : '' ?> d-block"><i class="ri-attachment-2 me-1"></i><?= e($att['name']) ?></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (!empty($reactions)): ?>
                                <div class="reactions-row d-flex flex-wrap gap-1 mt-1">
                                    <?php foreach ($reactions as $rx): ?>
                                        <button type="button" class="btn btn-light py-0 px-1 reaction-pill <?= $rx['mine'] ? 'border-primary' : '' ?>" data-msg-id="<?= $m['id'] ?>" data-emoji="<?= e($rx['emoji']) ?>" style="font-size:11px;line-height:1.4">
                                            <span><?= e($rx['emoji']) ?></span> <span><?= $rx['count'] ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <small class="<?= $mine ? 'text-white-50' : 'text-muted' ?> fs-11 d-flex align-items-center gap-1 mt-1">
                                <span><?= date('H:i d/m', strtotime($m['created_at'])) ?></span>
                                <?php if ($edited && !$deleted): ?><span class="fst-italic">(đã sửa)</span><?php endif; ?>
                                <?php if ($m['is_pinned']): ?><i class="ri-pushpin-2-fill"></i><?php endif; ?>
                            </small>
                            <?php if (!$deleted): ?>
                            <div class="msg-actions" data-msg-id="<?= $m['id'] ?>" data-mine="<?= $mine ? '1' : '0' ?>">
                                <button type="button" class="act-btn react-btn" title="Thả cảm xúc"><i class="ri-emotion-line"></i></button>
                                <button type="button" class="act-btn reply-btn" title="Trả lời"><i class="ri-reply-line"></i></button>
                                <button type="button" class="act-btn pin-btn" title="Ghim"><i class="ri-pushpin-line"></i></button>
                                <?php if ($mine): ?>
                                <button type="button" class="act-btn edit-btn" title="Sửa"><i class="ri-edit-line"></i></button>
                                <button type="button" class="act-btn delete-btn" title="Thu hồi"><i class="ri-delete-bin-line"></i></button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <style>
                    .msg-row { position:relative; }
                    .msg-row.mentioned .msg-bubble { box-shadow:0 0 0 2px rgba(255, 193, 7, 0.4); }
                    .msg-bubble { position:relative; }
                    .msg-actions { position:absolute; top:-18px; right:-4px; display:none; gap:2px; background:#fff; border:1px solid #e5e5e5; border-radius:16px; padding:2px 4px; box-shadow:0 1px 3px rgba(0,0,0,.08); white-space:nowrap; z-index:2; }
                    .msg-row[data-mine="1"] .msg-actions { right:auto; left:-4px; }
                    .msg-bubble:hover .msg-actions { display:inline-flex; }
                    .msg-actions .act-btn { background:none; border:none; color:#666; padding:2px 6px; font-size:13px; cursor:pointer; border-radius:12px; }
                    .msg-actions .act-btn:hover { background:#f0f0f0; color:#0d6efd; }
                    .reaction-pill { background:#f0f0f0 !important; border:1px solid transparent !important; }
                    .reaction-pill.border-primary { background:#e7f1ff !important; }
                    .emoji-picker { position:absolute; background:#fff; border:1px solid #e5e5e5; border-radius:8px; padding:6px; box-shadow:0 2px 8px rgba(0,0,0,.1); display:flex; gap:4px; z-index:100; }
                    .emoji-picker button { background:none; border:none; font-size:20px; cursor:pointer; padding:2px 6px; border-radius:6px; }
                    .emoji-picker button:hover { background:#f0f0f0; }
                    .mention-tag { background:#fff3cd; color:#856404; padding:0 4px; border-radius:4px; font-weight:500; }
                    #mentionPicker { position:absolute; bottom:100%; left:0; background:#fff; border:1px solid #e5e5e5; border-radius:6px; box-shadow:0 -2px 8px rgba(0,0,0,.08); max-height:200px; overflow-y:auto; min-width:260px; z-index:200; display:none; }
                    #mentionPicker .mention-item { padding:6px 10px; cursor:pointer; display:flex; align-items:center; gap:8px; border-bottom:1px solid #f5f5f5; }
                    #mentionPicker .mention-item.active, #mentionPicker .mention-item:hover { background:#e7f1ff; }
                    .reply-banner { background:#f0f4ff; border-left:3px solid #0d6efd; padding:4px 8px; border-radius:4px; font-size:12px; display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; }
                </style>

                <div class="p-3 border-top position-relative">
                    <div id="replyBanner" class="reply-banner" style="display:none">
                        <div><i class="ri-reply-line me-1"></i>Trả lời <strong id="replyBannerName"></strong>: <span id="replyBannerText" class="text-muted"></span></div>
                        <button type="button" class="btn-close" style="font-size:10px" id="replyBannerClose"></button>
                    </div>
                    <div id="mentionPicker"></div>
                    <form id="dmForm" class="d-flex gap-2 align-items-end" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" id="dmReplyTo" value="">
                        <input type="hidden" id="dmMentions" value="">
                        <input type="hidden" id="dmId" value="<?= $active['id'] ?>">
                        <label class="btn btn-soft-secondary btn-icon mb-0" title="Đính kèm">
                            <i class="ri-attachment-2"></i>
                            <input type="file" id="dmFile" style="display:none">
                        </label>
                        <div class="flex-grow-1">
                            <span id="fileName" class="text-muted small d-block" style="min-height:0"></span>
                            <textarea id="dmContent" class="form-control" placeholder="Nhập tin nhắn... (Enter gửi, Shift+Enter xuống dòng<?= $isActiveGroup ? ', @ để tag' : '' ?>)" rows="1" style="resize:none;max-height:140px;overflow-y:auto"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="ri-send-plane-line"></i></button>
                    </form>
                </div>

                <script>
                (function(){
                    var dmId = <?= (int)$active['id'] ?>;
                    var myId = <?= (int)$myId ?>;
                    var isGroup = <?= $isActiveGroup ? 'true' : 'false' ?>;
                    var replyPath = <?= json_encode($replyPath) ?>;
                    var pollPath = <?= json_encode($pollPath) ?>;
                    var groupMembers = <?= $isActiveGroup ? json_encode(array_map(fn($u) => ['id'=>(int)$u['id'],'name'=>$u['name'],'avatar'=>$u['avatar']??''], $groupMembers), JSON_UNESCAPED_UNICODE) : '[]' ?>;
                    var assetBaseUrl = '<?= rtrim(url(''), '/') ?>/';
                    var csrfTok = '<?= csrf_token() ?>';
                    var area = document.getElementById('msgArea');
                    var form = document.getElementById('dmForm');
                    var input = document.getElementById('dmContent');
                    var fileInput = document.getElementById('dmFile');
                    var fileName = document.getElementById('fileName');
                    var replyBanner = document.getElementById('replyBanner');
                    var replyBannerName = document.getElementById('replyBannerName');
                    var replyBannerText = document.getElementById('replyBannerText');
                    var replyToField = document.getElementById('dmReplyTo');
                    var mentionsField = document.getElementById('dmMentions');
                    var mentionPicker = document.getElementById('mentionPicker');
                    var pickedMentions = []; // [{id, name}]
                    var lastId = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
                    var peerLastRead = <?= !$isActiveGroup ? json_encode(($active['user_a_id'] == $myId ? ($active['last_read_b_at'] ?? null) : ($active['last_read_a_at'] ?? null))) : 'null' ?>;
                    var baseTitle = document.title;
                    var unseenCount = 0;
                    var notifReady = ('Notification' in window) && Notification.permission === 'granted';
                    if ('Notification' in window && Notification.permission === 'default') {
                        Notification.requestPermission().then(function(p){ notifReady = (p === 'granted'); });
                    }
                    area.scrollTop = area.scrollHeight;

                    // Auto-grow textarea
                    function autogrow(){ input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 140) + 'px'; }
                    input.addEventListener('input', autogrow);

                    fileInput.addEventListener('change', function(){
                        fileName.textContent = this.files[0] ? '📎 ' + this.files[0].name : '';
                    });

                    function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
                    function formatTime(s){ var d=new Date(s.replace(' ','T')); return d.getHours().toString().padStart(2,'0')+':'+d.getMinutes().toString().padStart(2,'0')+' '+d.getDate().toString().padStart(2,'0')+'/'+(d.getMonth()+1).toString().padStart(2,'0'); }
                    function renderReactions(m){
                        if (!m.reactions || !m.reactions.length) return '';
                        return '<div class="reactions-row d-flex flex-wrap gap-1 mt-1">' + m.reactions.map(function(r){
                            return '<button type="button" class="btn btn-light py-0 px-1 reaction-pill '+(r.mine?'border-primary':'')+'" data-msg-id="'+m.id+'" data-emoji="'+esc(r.emoji)+'" style="font-size:11px;line-height:1.4"><span>'+esc(r.emoji)+'</span> <span>'+r.count+'</span></button>';
                        }).join('') + '</div>';
                    }
                    function renderReplyTo(m, mine){
                        if (!m.reply_to) return '';
                        return '<div class="reply-snapshot border-start border-3 ps-2 mb-1 small '+(mine?'border-light opacity-75':'border-primary text-muted')+'"><div class="fw-semibold">'+esc(m.reply_to.sender_name||'')+'</div><div class="text-truncate" style="max-width:250px">'+esc(m.reply_to.preview||'')+'</div></div>';
                    }
                    function renderActions(m, mine, isDeleted){
                        if (isDeleted) return '';
                        var base = '<button type="button" class="act-btn react-btn" title="Thả cảm xúc"><i class="ri-emotion-line"></i></button>'
                                 + '<button type="button" class="act-btn reply-btn" title="Trả lời"><i class="ri-reply-line"></i></button>'
                                 + '<button type="button" class="act-btn pin-btn" title="Ghim"><i class="ri-pushpin-line"></i></button>'
                                 + '<button type="button" class="act-btn totask-btn" title="Tạo task"><i class="ri-task-line"></i></button>';
                        if (mine) base += '<button type="button" class="act-btn edit-btn" title="Sửa"><i class="ri-edit-line"></i></button>'
                                        + '<button type="button" class="act-btn delete-btn" title="Thu hồi"><i class="ri-delete-bin-line"></i></button>';
                        return '<div class="msg-actions" data-msg-id="'+m.id+'" data-mine="'+(mine?'1':'0')+'">'+base+'</div>';
                    }
                    function appendMsg(m){
                        var existing = document.querySelector('[data-msg-id="'+m.id+'"]');
                        if (existing) { updateMsgRow(existing, m); return; }
                        var isBot = (m.sender_type === 'system' && (m.sender_id|0) === 0);
                        var mine = !isBot && m.sender_id == myId;
                        var isDeleted = !!m.deleted_at;
                        var atts = m.attachments ? (typeof m.attachments === 'string' ? JSON.parse(m.attachments) : m.attachments) : [];
                        var attHtml = '';
                        (atts||[]).forEach(function(a){
                            if (a.is_image) attHtml += '<img src="'+a.url+'" style="max-width:200px;max-height:200px;display:block;margin-top:4px;border-radius:4px">';
                            else attHtml += '<a href="'+a.url+'" target="_blank" class="'+(mine?'text-white':'')+' d-block"><i class="ri-attachment-2 me-1"></i>'+esc(a.name)+'</a>';
                        });
                        var bubbleCls = isBot ? 'bg-warning-subtle border border-warning' : (mine?'bg-primary text-white':'bg-light');
                        var senderTag = isBot ? '<small class="d-block fw-semibold text-warning"><i class="ri-robot-2-line me-1"></i>AI Trợ lý</small>'
                            : ((isGroup && !mine && !isDeleted) ? '<small class="d-block fw-semibold text-info">'+esc(m.sender_name||'')+'</small>' : '');
                        var body = isDeleted
                            ? '<div class="fst-italic '+(mine?'text-white-50':'text-muted')+'"><i class="ri-delete-bin-line me-1"></i>Tin nhắn đã được thu hồi</div>'
                            : ((m.content ? '<div class="msg-content" style="white-space:pre-wrap">'+esc(m.content)+'</div>' : '') + attHtml);
                        var html = '<div class="d-flex '+(mine?'justify-content-end':'')+' mb-2 msg-row '+(m.is_mentioned?'mentioned':'')+'" data-msg-id="'+m.id+'" data-created-at="'+esc(m.created_at||'')+'" data-mine="'+(mine?'1':'0')+'" data-sender-id="'+(m.sender_id||0)+'" data-bot="'+(isBot?'1':'0')+'">'
                                 + '<div class="msg-bubble '+bubbleCls+' rounded px-3 py-2 position-relative" style="max-width:70%">'
                                 + senderTag
                                 + renderReplyTo(m, mine)
                                 + body
                                 + renderReactions(m)
                                 + '<small class="'+(mine?'text-white-50':'text-muted')+' fs-11 d-flex align-items-center gap-1 mt-1">'
                                 + '<span>'+formatTime(m.created_at)+'</span>'
                                 + (m.edited_at && !isDeleted ? '<span class="fst-italic">(đã sửa)</span>' : '')
                                 + '</small>'
                                 + renderActions(m, mine, isDeleted)
                                 + '</div></div>';
                        area.insertAdjacentHTML('beforeend', html);
                        area.scrollTop = area.scrollHeight;
                        if (m.id > lastId) lastId = m.id;

                        if (!mine && document.hidden) {
                            unseenCount++;
                            document.title = '(' + unseenCount + ') ' + baseTitle;
                            if (notifReady) {
                                try {
                                    var n = new Notification((m.sender_name || 'Tin nhắn mới') + (m.is_mentioned ? ' @bạn' : ''), {
                                        body: (m.content || '[Tệp đính kèm]').substring(0, 120),
                                        icon: '/favicon.ico',
                                        tag: 'chat-' + m.id
                                    });
                                    n.onclick = function(){ window.focus(); this.close(); };
                                } catch(e){}
                            }
                        }
                    }
                    function updateMsgRow(row, m){
                        // Used for edits/delete/reaction updates coming from poll on a msg we already rendered
                        var bubble = row.querySelector('.msg-bubble'); if (!bubble) return;
                        var mine = row.dataset.mine === '1';
                        var isDeleted = !!m.deleted_at;
                        // Replace content
                        var content = bubble.querySelector('.msg-content');
                        if (isDeleted) {
                            if (content) content.remove();
                            if (!bubble.querySelector('.deleted-marker')) {
                                bubble.insertAdjacentHTML('afterbegin', '<div class="deleted-marker fst-italic '+(mine?'text-white-50':'text-muted')+'"><i class="ri-delete-bin-line me-1"></i>Tin nhắn đã được thu hồi</div>');
                            }
                            var act = bubble.querySelector('.msg-actions'); if (act) act.remove();
                        } else if (content && m.content !== undefined) {
                            content.textContent = m.content;
                        }
                        // Reactions
                        var rr = bubble.querySelector('.reactions-row'); if (rr) rr.remove();
                        var rxHtml = renderReactions(m);
                        if (rxHtml) {
                            var footer = bubble.querySelector('small');
                            if (footer) footer.insertAdjacentHTML('beforebegin', rxHtml);
                            else bubble.insertAdjacentHTML('beforeend', rxHtml);
                        }
                        // Edited label
                        if (m.edited_at && !isDeleted) {
                            var footer2 = bubble.querySelector('small');
                            if (footer2 && !footer2.querySelector('.fst-italic')) {
                                var sp = document.createElement('span'); sp.className='fst-italic'; sp.textContent='(đã sửa)';
                                footer2.appendChild(document.createTextNode(' '));
                                footer2.appendChild(sp);
                            }
                        }
                    }

                    // Read-receipt renderer: show "Đã xem" under last mine-msg <= peerLastRead
                    function renderReadReceipt(){
                        document.querySelectorAll('.read-receipt').forEach(el => el.remove());
                        if (isGroup || !peerLastRead) return;
                        var mineRows = area.querySelectorAll('.msg-row[data-mine="1"]');
                        var lastSeen = null;
                        mineRows.forEach(function(r){
                            var ca = r.dataset.createdAt;
                            if (ca && ca <= peerLastRead) lastSeen = r;
                        });
                        if (lastSeen) {
                            var bubble = lastSeen.querySelector('div');
                            if (bubble) {
                                var rr = document.createElement('small');
                                rr.className = 'read-receipt text-muted d-block text-end mt-1';
                                rr.style.fontSize = '11px';
                                rr.innerHTML = '<i class="ri-check-double-line text-info"></i> Đã xem';
                                bubble.parentNode.appendChild(rr);
                            }
                        }
                    }
                    renderReadReceipt();

                    function sendMessage(){
                        var content = input.value.trim();
                        if (!content && !fileInput.files[0]) return;
                        var fd = new FormData();
                        fd.append('_token', csrfTok);
                        fd.append('content', content);
                        if (fileInput.files[0]) fd.append('attachment', fileInput.files[0]);
                        if (replyToField.value) fd.append('reply_to_id', replyToField.value);
                        // Mentions: filter to only those whose name still appears in content
                        pickedMentions.filter(function(p){ return content.indexOf('@' + p.name) !== -1; })
                            .forEach(function(p){ fd.append('mentions[]', p.id); });
                        fetch(replyPath, {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
                            .then(r=>r.json()).then(function(){
                                input.value = '';
                                autogrow();
                                fileInput.value = '';
                                fileName.textContent = '';
                                cancelReply();
                                pickedMentions = [];
                                poll();
                            });
                    }
                    function cancelReply(){ replyToField.value = ''; replyBanner.style.display='none'; }
                    document.getElementById('replyBannerClose').addEventListener('click', cancelReply);

                    // --- Hover action delegation: reply / edit / delete / react ---
                    area.addEventListener('click', function(e){
                        var row = e.target.closest('.msg-row'); if (!row) return;
                        var mid = row.dataset.msgId;

                        if (e.target.closest('.reply-btn')) {
                            var bubble = row.querySelector('.msg-bubble');
                            var senderEl = row.querySelector('.fw-semibold') || {textContent: row.dataset.mine==='1' ? 'Bạn' : 'Người gửi'};
                            var contentEl = bubble.querySelector('.msg-content');
                            replyToField.value = mid;
                            replyBannerName.textContent = senderEl.textContent || '';
                            replyBannerText.textContent = ((contentEl && contentEl.textContent) || '[Tệp]').substring(0, 80);
                            replyBanner.style.display = 'flex';
                            input.focus();
                            return;
                        }
                        if (e.target.closest('.edit-btn')) {
                            var bubble = row.querySelector('.msg-bubble');
                            var cEl = bubble.querySelector('.msg-content');
                            if (!cEl) return;
                            var current = cEl.textContent;
                            var ta = document.createElement('textarea');
                            ta.className = 'form-control';
                            ta.value = current;
                            ta.rows = 2;
                            cEl.replaceWith(ta);
                            ta.focus();
                            function finish(save){
                                var next = save ? ta.value.trim() : current;
                                var span = document.createElement('div');
                                span.className = 'msg-content';
                                span.style.whiteSpace = 'pre-wrap';
                                span.textContent = next;
                                ta.replaceWith(span);
                                if (save && next && next !== current) {
                                    var fd = new FormData(); fd.append('_token', csrfTok); fd.append('content', next);
                                    fetch('<?= url('chat/message/') ?>'+mid+'/edit', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
                                        .then(r=>r.json()).then(function(d){
                                            if (d.error) { alert(d.message || 'Không thể sửa.'); span.textContent = current; }
                                            else { poll(); }
                                        });
                                }
                            }
                            ta.addEventListener('keydown', function(ev){
                                if (ev.key === 'Enter' && !ev.shiftKey) { ev.preventDefault(); finish(true); }
                                if (ev.key === 'Escape') { finish(false); }
                            });
                            ta.addEventListener('blur', function(){ finish(true); });
                            return;
                        }
                        if (e.target.closest('.delete-btn')) {
                            if (!confirm('Thu hồi tin nhắn này?')) return;
                            var fd = new FormData(); fd.append('_token', csrfTok);
                            fetch('<?= url('chat/message/') ?>'+mid+'/delete', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(){ location.reload(); });
                            return;
                        }
                        if (e.target.closest('.react-btn')) {
                            // Show emoji picker under bubble
                            var bubble = row.querySelector('.msg-bubble');
                            document.querySelectorAll('.emoji-picker').forEach(el => el.remove());
                            var pk = document.createElement('div');
                            pk.className = 'emoji-picker';
                            pk.innerHTML = ['👍','❤️','😂','😮','😢','🎉'].map(em => '<button type="button" data-em="'+em+'">'+em+'</button>').join('');
                            bubble.appendChild(pk);
                            pk.addEventListener('click', function(ev){
                                var b = ev.target.closest('button[data-em]'); if (!b) return;
                                var em = b.dataset.em;
                                var fd = new FormData(); fd.append('_token', csrfTok); fd.append('emoji', em);
                                fetch('<?= url('chat/message/') ?>'+mid+'/react', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
                                    .then(r=>r.json()).then(function(){ pk.remove(); poll(); });
                            });
                            setTimeout(function(){ document.addEventListener('click', function out(ev2){ if (!pk.contains(ev2.target) && !ev2.target.closest('.react-btn')) { pk.remove(); document.removeEventListener('click', out); } }); }, 50);
                            return;
                        }
                        if (e.target.closest('.reaction-pill')) {
                            var pill = e.target.closest('.reaction-pill');
                            var em = pill.dataset.emoji;
                            var fd = new FormData(); fd.append('_token', csrfTok); fd.append('emoji', em);
                            fetch('<?= url('chat/message/') ?>'+mid+'/react', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(){ poll(); });
                            return;
                        }
                        if (e.target.closest('.pin-btn')) {
                            var fd = new FormData(); fd.append('_token', csrfTok);
                            fetch('<?= url('chat/message/') ?>'+mid+'/pin', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(d){
                                if (d.success) location.reload();
                            });
                            return;
                        }
                        if (e.target.closest('.totask-btn')) {
                            var bubble = row.querySelector('.msg-bubble');
                            var cEl = bubble.querySelector('.msg-content');
                            var suggested = (cEl && cEl.textContent) ? cEl.textContent.substring(0, 180) : '';
                            var title = prompt('Tiêu đề task:', suggested);
                            if (!title) return;
                            var fd = new FormData(); fd.append('_token', csrfTok); fd.append('title', title);
                            fetch('<?= url('chat/message/') ?>'+mid+'/to-task', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(d){
                                if (d.success) {
                                    if (confirm('Đã tạo task. Mở task ngay?')) window.open(d.url, '_blank');
                                } else alert(d.error || 'Lỗi');
                            });
                            return;
                        }
                    });

                    // --- @mention autocomplete (groups only) ---
                    var mentionActiveIdx = 0;
                    var mentionMatches = [];
                    function hideMentionPicker(){ mentionPicker.style.display='none'; mentionMatches = []; }
                    function showMentionPicker(q){
                        if (!isGroup) return;
                        var needle = q.toLowerCase();
                        var withAi = [{id: -1, name: 'AI', avatar: '', isAi: true}].concat(groupMembers.filter(function(m){ return m.id !== myId; }));
                        mentionMatches = withAi.filter(function(m){ return m.name.toLowerCase().includes(needle); }).slice(0, 6);
                        if (!mentionMatches.length) { hideMentionPicker(); return; }
                        mentionActiveIdx = 0;
                        mentionPicker.innerHTML = mentionMatches.map(function(m, i){
                            var ava;
                            if (m.isAi) ava = '<div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:24px;height:24px"><i class="ri-robot-2-line" style="font-size:14px"></i></div>';
                            else if (m.avatar) ava = '<img src="'+assetBaseUrl+m.avatar+'" class="rounded-circle" width="24" height="24" style="object-fit:cover">';
                            else ava = '<div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:24px;height:24px;font-size:11px">'+esc((m.name||'?').charAt(0).toUpperCase())+'</div>';
                            var tag = m.isAi ? '<span class="badge bg-warning-subtle text-warning ms-auto">Bot</span>' : '';
                            return '<div class="mention-item '+(i===0?'active':'')+'" data-idx="'+i+'">'+ava+'<span>'+esc(m.name)+'</span>'+tag+'</div>';
                        }).join('');
                        mentionPicker.style.display = 'block';
                    }
                    function insertMention(m){
                        var val = input.value;
                        var caret = input.selectionStart;
                        var before = val.substring(0, caret);
                        var at = before.lastIndexOf('@');
                        if (at < 0) return;
                        var after = val.substring(caret);
                        var newVal = val.substring(0, at) + '@' + m.name + ' ' + after;
                        input.value = newVal;
                        input.selectionStart = input.selectionEnd = at + 1 + m.name.length + 1;
                        // AI is handled server-side (content regex); don't push to pickedMentions
                        if (!m.isAi && !pickedMentions.find(x => x.id === m.id)) pickedMentions.push(m);
                        hideMentionPicker();
                        autogrow();
                    }
                    input.addEventListener('input', function(){
                        if (!isGroup) return;
                        var caret = input.selectionStart;
                        var before = input.value.substring(0, caret);
                        var at = before.lastIndexOf('@');
                        if (at < 0) { hideMentionPicker(); return; }
                        // @ must be at start or preceded by whitespace
                        if (at > 0 && !/\s/.test(before.charAt(at - 1))) { hideMentionPicker(); return; }
                        var query = before.substring(at + 1);
                        if (query.length > 20 || /\s/.test(query)) { hideMentionPicker(); return; }
                        showMentionPicker(query);
                    });
                    mentionPicker.addEventListener('mousedown', function(e){
                        var it = e.target.closest('.mention-item'); if (!it) return;
                        e.preventDefault();
                        insertMention(mentionMatches[+it.dataset.idx]);
                    });

                    form.addEventListener('submit', function(e){ e.preventDefault(); sendMessage(); });

                    // Enter to send (with mention handling), Shift+Enter = newline
                    input.addEventListener('keydown', function(e){
                        if (mentionPicker.style.display === 'block' && mentionMatches.length) {
                            if (e.key === 'ArrowDown') { e.preventDefault(); mentionActiveIdx = (mentionActiveIdx + 1) % mentionMatches.length; mentionPicker.querySelectorAll('.mention-item').forEach((it,i)=>it.classList.toggle('active', i===mentionActiveIdx)); return; }
                            if (e.key === 'ArrowUp') { e.preventDefault(); mentionActiveIdx = (mentionActiveIdx - 1 + mentionMatches.length) % mentionMatches.length; mentionPicker.querySelectorAll('.mention-item').forEach((it,i)=>it.classList.toggle('active', i===mentionActiveIdx)); return; }
                            if (e.key === 'Enter' || e.key === 'Tab') { e.preventDefault(); insertMention(mentionMatches[mentionActiveIdx]); return; }
                            if (e.key === 'Escape') { e.preventDefault(); hideMentionPicker(); return; }
                        }
                        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); sendMessage(); }
                    });

                    // Clipboard paste image
                    input.addEventListener('paste', function(e){
                        var items = (e.clipboardData || {}).items || [];
                        for (var i = 0; i < items.length; i++) {
                            if (items[i].type && items[i].type.startsWith('image/')) {
                                var file = items[i].getAsFile();
                                if (file) {
                                    var dt = new DataTransfer();
                                    dt.items.add(file);
                                    fileInput.files = dt.files;
                                    fileName.textContent = '📎 ' + (file.name || 'clipboard-image.png');
                                    e.preventDefault();
                                }
                                break;
                            }
                        }
                    });

                    // Drag-drop file onto message area
                    ['dragenter','dragover'].forEach(function(ev){
                        area.addEventListener(ev, function(e){ e.preventDefault(); area.style.background = '#e7f1ff'; });
                    });
                    ['dragleave','drop'].forEach(function(ev){
                        area.addEventListener(ev, function(e){ e.preventDefault(); area.style.background = ''; });
                    });
                    area.addEventListener('drop', function(e){
                        var f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                        if (f) {
                            var dt = new DataTransfer(); dt.items.add(f);
                            fileInput.files = dt.files;
                            fileName.textContent = '📎 ' + f.name;
                            input.focus();
                        }
                    });

                    // Title reset on focus
                    window.addEventListener('focus', function(){
                        unseenCount = 0;
                        document.title = baseTitle;
                    });

                    function poll(){
                        fetch(pollPath + '?after='+lastId).then(r=>r.json()).then(function(d){
                            (d.messages||[]).forEach(appendMsg);
                            if (d.peer_last_read_at && d.peer_last_read_at !== peerLastRead) {
                                peerLastRead = d.peer_last_read_at;
                                renderReadReceipt();
                            }
                        }).catch(function(){});
                    }
                    // Polling backoff: 5s when tab is visible, 30s when hidden
                    var pollTimer = null;
                    function schedulePoll(){
                        if (pollTimer) clearTimeout(pollTimer);
                        var delay = document.hidden ? 30000 : 5000;
                        pollTimer = setTimeout(function(){ poll(); schedulePoll(); }, delay);
                    }
                    schedulePoll();
                    document.addEventListener('visibilitychange', function(){
                        if (!document.hidden) { poll(); schedulePoll(); } // catch up immediately on focus
                        else schedulePoll();
                    });

                    input.focus();
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

<?php if ($isActiveGroup ?? false): ?>
<!-- Group Settings Modal -->
<div class="modal fade" id="groupSettingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ri-settings-3-line me-1"></i>Quản lý nhóm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#gsInfo">Thông tin</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#gsMembers">Thành viên</button></li>
                    <li class="nav-item"><button class="nav-link text-danger" data-bs-toggle="tab" data-bs-target="#gsDanger">Hành động</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="gsInfo">
                        <div class="mb-3">
                            <label class="form-label">Tên nhóm</label>
                            <div class="input-group">
                                <input type="text" id="gsName" class="form-control" value="<?= e($active['name'] ?? '') ?>" maxlength="100">
                                <button type="button" class="btn btn-primary" id="gsSaveName">Lưu</button>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="gsMembers">
                        <div class="mb-2" id="gsAddArea" style="display:none">
                            <label class="form-label">Thêm thành viên</label>
                            <div class="input-group mb-2">
                                <input type="text" id="gsAddSearch" class="form-control" placeholder="Tìm nhân viên để thêm...">
                                <button type="button" class="btn btn-soft-primary" id="gsAddBtn" disabled>Thêm <span id="gsAddCount">0</span></button>
                            </div>
                            <div id="gsAddResults" class="border rounded" style="max-height:180px;overflow-y:auto;display:none"></div>
                        </div>
                        <div id="gsMemberList" class="border rounded" style="max-height:320px;overflow-y:auto">
                            <div class="text-center text-muted py-3">Đang tải...</div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="gsDanger">
                        <p class="text-muted small">Rời nhóm sẽ xóa bạn khỏi danh sách thành viên. Bạn sẽ không thấy tin nhắn mới.</p>
                        <button type="button" class="btn btn-outline-danger" id="gsLeaveBtn"><i class="ri-logout-box-line me-1"></i>Rời nhóm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(function(){
    var gid = <?= (int)$active['id'] ?>;
    var myId = <?= (int)$myId ?>;
    var tok = '<?= csrf_token() ?>';
    var assetBase = '<?= rtrim(url(''), '/') ?>/';
    var modal = document.getElementById('groupSettingsModal');
    if (!modal) return;
    var nameInput = document.getElementById('gsName');
    var memberList = document.getElementById('gsMemberList');
    var addArea = document.getElementById('gsAddArea');
    var addSearch = document.getElementById('gsAddSearch');
    var addResults = document.getElementById('gsAddResults');
    var addBtn = document.getElementById('gsAddBtn');
    var addCount = document.getElementById('gsAddCount');
    var addSelected = {}; // id -> name
    var allUsers = <?= json_encode(array_map(fn($u) => ['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'avatar'=>$u['avatar']??'','position'=>$u['position_name']??''], $users), JSON_UNESCAPED_UNICODE) ?>;
    var isAdmin = false;

    function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
    function avatar(u, size){ size = size||32; return u.avatar ? '<img src="'+assetBase+u.avatar+'" class="rounded-circle" width="'+size+'" height="'+size+'" style="object-fit:cover">' : '<div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:'+size+'px;height:'+size+'px">'+esc((u.name||'?').charAt(0).toUpperCase())+'</div>'; }

    function loadMembers(){
        fetch('<?= url('chat/group/') ?>'+gid+'/members').then(r=>r.json()).then(function(d){
            isAdmin = !!d.is_admin;
            addArea.style.display = isAdmin ? '' : 'none';
            nameInput.disabled = !isAdmin;
            document.getElementById('gsSaveName').style.display = isAdmin ? '' : 'none';
            memberList.innerHTML = (d.members||[]).map(function(m){
                var canRemove = isAdmin && m.id != myId;
                return '<div class="d-flex align-items-center px-3 py-2 border-bottom">'
                     + avatar(m, 36)
                     + '<div class="flex-grow-1 ms-2 text-truncate">'
                     + '<div class="fw-medium text-dark">'+esc(m.name)+' '+(m.role==='admin'?'<span class="badge bg-info-subtle text-info ms-1">Admin</span>':'')+(m.id==myId?'<span class="badge bg-secondary-subtle text-secondary ms-1">Bạn</span>':'')+'</div>'
                     + '<small class="text-muted text-truncate d-block">'+esc(m.position_name||'')+(m.position_name&&m.email?' · ':'')+esc(m.email||'')+'</small>'
                     + '</div>'
                     + (canRemove ? '<button class="btn btn-light btn-icon text-danger remove-mem-btn" data-uid="'+m.id+'" data-name="'+esc(m.name)+'" title="Xóa khỏi nhóm"><i class="ri-close-line"></i></button>' : '')
                     + '</div>';
            }).join('');
        });
    }

    modal.addEventListener('show.bs.modal', loadMembers);

    document.getElementById('gsSaveName').addEventListener('click', function(){
        var n = nameInput.value.trim(); if (!n) return;
        var fd = new FormData(); fd.append('_token', tok); fd.append('name', n);
        fetch('<?= url('chat/group/') ?>'+gid+'/rename', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(d){
            if (d.success) { alert('Đã đổi tên nhóm.'); location.reload(); }
            else alert(d.error || 'Lỗi');
        });
    });

    memberList.addEventListener('click', function(e){
        var b = e.target.closest('.remove-mem-btn'); if (!b) return;
        if (!confirm('Xóa ' + b.dataset.name + ' khỏi nhóm?')) return;
        var fd = new FormData(); fd.append('_token', tok);
        fetch('<?= url('chat/group/') ?>'+gid+'/members/'+b.dataset.uid+'/remove', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(d){
            if (d.success) loadMembers();
            else alert(d.error || 'Lỗi');
        });
    });

    function refreshAddCount(){ var n = Object.keys(addSelected).length; addCount.textContent = n; addBtn.disabled = n === 0; }
    addSearch.addEventListener('input', function(){
        var q = this.value.trim().toLowerCase();
        if (!q) { addResults.style.display='none'; return; }
        var matched = allUsers.filter(u => u.name.toLowerCase().includes(q) || (u.email||'').toLowerCase().includes(q)).slice(0, 10);
        addResults.innerHTML = matched.map(u =>
            '<div class="d-flex align-items-center px-2 py-2 border-bottom" data-uid="'+u.id+'" data-name="'+esc(u.name)+'" style="cursor:pointer">'
            + '<input type="checkbox" class="form-check-input me-2" '+(addSelected[u.id]?'checked':'')+'>'
            + avatar(u, 28)
            + '<div class="ms-2 text-truncate"><div class="fw-medium text-dark">'+esc(u.name)+'</div><small class="text-muted">'+esc(u.position||'')+(u.position&&u.email?' · ':'')+esc(u.email||'')+'</small></div>'
            + '</div>'
        ).join('');
        addResults.style.display = matched.length ? 'block' : 'none';
    });
    addResults.addEventListener('click', function(e){
        var row = e.target.closest('[data-uid]'); if (!row) return;
        var uid = row.dataset.uid;
        if (addSelected[uid]) { delete addSelected[uid]; } else { addSelected[uid] = row.dataset.name; }
        row.querySelector('input').checked = !!addSelected[uid];
        refreshAddCount();
    });
    addBtn.addEventListener('click', function(){
        var ids = Object.keys(addSelected);
        if (!ids.length) return;
        var fd = new FormData(); fd.append('_token', tok);
        ids.forEach(id => fd.append('members[]', id));
        fetch('<?= url('chat/group/') ?>'+gid+'/members/add', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(d){
            if (d.success) { addSelected = {}; addSearch.value=''; addResults.style.display='none'; refreshAddCount(); loadMembers(); }
            else alert(d.error || 'Lỗi');
        });
    });

    document.getElementById('gsLeaveBtn').addEventListener('click', function(){
        if (!confirm('Rời nhóm "<?= e($active['name'] ?? '') ?>"?')) return;
        var fd = new FormData(); fd.append('_token', tok);
        fetch('<?= url('chat/group/') ?>'+gid+'/leave', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(function(d){
            if (d.success) window.location.href = '<?= url('chat') ?>';
            else alert(d.error || 'Lỗi');
        });
    });
})();
</script>
<?php endif; ?>

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
