<?php $pageTitle = 'Chat nội bộ'; ?>
<?php $myId = $_SESSION['user']['id'] ?? 0; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Chat</h4>
</div>

<!-- Tab switch -->
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link" href="<?= url('chat') ?>"><i class="ri-user-3-line me-1"></i>Khách hàng</a></li>
    <li class="nav-item"><a class="nav-link active" href="<?= url('chat/internal') ?>"><i class="ri-team-line me-1"></i>Nội bộ</a></li>
</ul>

<div class="card" style="height: calc(100vh - 200px)">
    <div class="card-body p-0 d-flex h-100">

        <!-- Sidebar: DM list + user picker -->
        <div class="border-end d-flex flex-column" style="width:320px;min-width:320px">
            <div class="p-3 border-bottom">
                <div class="input-group">
                    <input type="text" id="userSearch" class="form-control" placeholder="Tìm nhân viên để nhắn tin...">
                    <span class="input-group-text"><i class="ri-search-line"></i></span>
                </div>
                <div id="userPickerResults" class="mt-2" style="max-height:200px;overflow-y:auto;display:none;"></div>
            </div>
            <div class="flex-grow-1" style="overflow-y:auto">
                <?php if (empty($dms)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="ri-chat-3-line fs-48 d-block mb-2"></i>
                        Chưa có cuộc chat nào.<br>
                        <small>Tìm nhân viên ở ô trên để bắt đầu.</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($dms as $dm):
                        $isActive = ($active['id'] ?? 0) == $dm['id'];
                        $hasUnread = (int)$dm['my_unread'] > 0;
                    ?>
                    <a href="<?= url('chat/internal?active=' . $dm['id']) ?>"
                       class="d-block px-3 py-2 border-bottom text-decoration-none text-dark <?= $isActive ? 'bg-primary-subtle' : '' ?>">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2">
                                <?php if (!empty($dm['peer_avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $dm['peer_avatar'])): ?>
                                    <img src="<?= url('uploads/avatars/' . $dm['peer_avatar']) ?>" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                                <?php else: ?>
                                    <div class="avatar-title rounded-circle bg-primary-subtle text-primary"><?= strtoupper(mb_substr($dm['peer_name'] ?? '?', 0, 1)) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1 text-truncate">
                                <div class="d-flex justify-content-between">
                                    <span class="<?= $hasUnread ? 'fw-bold' : 'fw-medium' ?>"><?= e($dm['peer_name'] ?? '') ?></span>
                                    <?php if ($hasUnread): ?>
                                        <span class="badge bg-danger rounded-pill"><?= $dm['my_unread'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted text-truncate d-block"><?= e($dm['last_message_preview'] ?? '') ?></small>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Thread -->
        <div class="flex-grow-1 d-flex flex-column">
            <?php if ($active): ?>
                <?php
                $peerId = ($active['user_a_id'] == $myId) ? $active['user_b_id'] : $active['user_a_id'];
                $peer = \Core\Database::fetch("SELECT name, avatar, email FROM users WHERE id = ?", [$peerId]);
                ?>
                <div class="p-3 border-bottom d-flex align-items-center">
                    <div class="avatar-sm me-2">
                        <?php if (!empty($peer['avatar']) && file_exists(BASE_PATH . '/public/uploads/avatars/' . $peer['avatar'])): ?>
                            <img src="<?= url('uploads/avatars/' . $peer['avatar']) ?>" class="rounded-circle" width="36" height="36" style="object-fit:cover">
                        <?php else: ?>
                            <div class="avatar-title rounded-circle bg-primary-subtle text-primary"><?= strtoupper(mb_substr($peer['name'] ?? '?', 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="fw-semibold"><?= e($peer['name'] ?? '') ?></div>
                        <small class="text-muted"><?= e($peer['email'] ?? '') ?></small>
                    </div>
                </div>

                <div id="msgArea" class="flex-grow-1 px-3 py-2" style="overflow-y:auto">
                    <?php foreach ($messages as $m):
                        $mine = ($m['sender_id'] == $myId);
                    ?>
                    <div class="d-flex <?= $mine ? 'justify-content-end' : '' ?> mb-2" data-msg-id="<?= $m['id'] ?>">
                        <div class="<?= $mine ? 'bg-primary text-white' : 'bg-light' ?> rounded px-3 py-2" style="max-width:70%">
                            <div style="white-space:pre-wrap"><?= e($m['content']) ?></div>
                            <small class="<?= $mine ? 'text-white-50' : 'text-muted' ?> fs-11"><?= date('H:i d/m', strtotime($m['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form id="dmForm" class="p-3 border-top d-flex gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" id="dmId" value="<?= $active['id'] ?>">
                    <input type="text" id="dmContent" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off" required>
                    <button type="submit" class="btn btn-primary"><i class="ri-send-plane-line"></i></button>
                </form>

                <script>
                (function(){
                    var dmId = <?= (int)$active['id'] ?>;
                    var myId = <?= (int)$myId ?>;
                    var area = document.getElementById('msgArea');
                    var form = document.getElementById('dmForm');
                    var input = document.getElementById('dmContent');
                    var lastId = <?= !empty($messages) ? (int)end($messages)['id'] : 0 ?>;
                    area.scrollTop = area.scrollHeight;

                    function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
                    function formatTime(s){ var d=new Date(s); return d.getHours().toString().padStart(2,'0')+':'+d.getMinutes().toString().padStart(2,'0')+' '+d.getDate().toString().padStart(2,'0')+'/'+(d.getMonth()+1).toString().padStart(2,'0'); }
                    function appendMsg(m){
                        if (document.querySelector('[data-msg-id="'+m.id+'"]')) return;
                        var mine = m.sender_id == myId;
                        var html = '<div class="d-flex '+(mine?'justify-content-end':'')+' mb-2" data-msg-id="'+m.id+'">'
                                 + '<div class="'+(mine?'bg-primary text-white':'bg-light')+' rounded px-3 py-2" style="max-width:70%">'
                                 + '<div style="white-space:pre-wrap">'+esc(m.content)+'</div>'
                                 + '<small class="'+(mine?'text-white-50':'text-muted')+' fs-11">'+formatTime(m.created_at)+'</small>'
                                 + '</div></div>';
                        area.insertAdjacentHTML('beforeend', html);
                        area.scrollTop = area.scrollHeight;
                        if (m.id > lastId) lastId = m.id;
                    }

                    form.addEventListener('submit', function(e){
                        e.preventDefault();
                        var content = input.value.trim();
                        if (!content) return;
                        var fd = new FormData();
                        fd.append('_token', '<?= csrf_token() ?>');
                        fd.append('content', content);
                        fetch('<?= url('chat/internal/') ?>'+dmId+'/reply', {method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}})
                            .then(r=>r.json()).then(function(){
                                input.value = '';
                                poll();
                            });
                    });

                    function poll(){
                        fetch('<?= url('chat/internal/') ?>'+dmId+'/poll?after='+lastId)
                            .then(r=>r.json()).then(function(d){
                                (d.messages||[]).forEach(appendMsg);
                            }).catch(function(){});
                    }
                    setInterval(poll, 5000);
                })();
                </script>
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                    <div class="text-center">
                        <i class="ri-chat-smile-2-line fs-48 d-block mb-2"></i>
                        Chọn một cuộc chat bên trái hoặc tìm nhân viên để bắt đầu.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function(){
    var searchInput = document.getElementById('userSearch');
    var results = document.getElementById('userPickerResults');
    var allUsers = <?= json_encode(array_map(fn($u) => ['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email']], $users), JSON_UNESCAPED_UNICODE) ?>;

    searchInput.addEventListener('input', function(){
        var q = this.value.trim().toLowerCase();
        if (!q) { results.style.display = 'none'; return; }
        var matched = allUsers.filter(u => (u.name||'').toLowerCase().includes(q) || (u.email||'').toLowerCase().includes(q)).slice(0, 10);
        if (!matched.length) { results.innerHTML = '<div class="text-muted text-center py-2">Không tìm thấy</div>'; results.style.display='block'; return; }
        results.innerHTML = matched.map(u =>
            '<form method="POST" action="<?= url('chat/internal/start/') ?>'+u.id+'" class="d-block">'
            + '<?= csrf_field() ?>'
            + '<button type="submit" class="btn btn-link text-start w-100 px-2 py-1 text-decoration-none border-bottom">'
            + '<strong>'+u.name+'</strong> <small class="text-muted">'+(u.email||'')+'</small>'
            + '</button></form>'
        ).join('');
        results.style.display = 'block';
    });
})();
</script>
