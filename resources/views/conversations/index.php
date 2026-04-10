<?php $pageTitle = 'Hộp thư'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Hộp thư</h4>
            <div>
                <a href="<?= url('conversations/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo cuộc hội thoại</a>
            </div>
        </div>

        <?php
            $channelColors = ['email' => 'info', 'zalo' => 'success', 'facebook' => 'primary', 'sms' => 'warning', 'livechat' => 'danger'];
            $channelLabels = ['email' => 'Email', 'zalo' => 'Zalo', 'facebook' => 'Facebook', 'sms' => 'SMS', 'livechat' => 'Live Chat'];
            $statusColors = ['open' => 'info', 'pending' => 'warning', 'resolved' => 'success', 'closed' => 'secondary'];
            $statusLabels = ['open' => 'Mở', 'pending' => 'Chờ', 'resolved' => 'Đã xử lý', 'closed' => 'Đóng'];
            $currentFilter = $filters['filter'] ?? '';
            $activeId = $activeConversation['id'] ?? 0;
        ?>

        <div class="row">
            <!-- Left sidebar: conversation list -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body p-0">
                        <!-- Search -->
                        <div class="p-3 border-bottom">
                            <form method="GET" action="<?= url('conversations') ?>">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Tìm kiếm cuộc hội thoại..." value="<?= e($filters['search'] ?? '') ?>">
                                    <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                                </div>
                                <?php if ($currentFilter): ?>
                                    <input type="hidden" name="filter" value="<?= e($currentFilter) ?>">
                                <?php endif; ?>
                            </form>
                        </div>

                        <!-- Filter buttons -->
                        <div class="p-3 border-bottom d-flex gap-2 flex-wrap">
                            <a href="<?= url('conversations') ?>" class="btn <?= !$currentFilter ? 'btn-primary' : 'btn-soft-primary' ?>">Tất cả</a>
                            <a href="<?= url('conversations?filter=unread') ?>" class="btn <?= $currentFilter === 'unread' ? 'btn-primary' : 'btn-soft-primary' ?>">Chưa đọc</a>
                            <a href="<?= url('conversations?filter=mine') ?>" class="btn <?= $currentFilter === 'mine' ? 'btn-primary' : 'btn-soft-primary' ?>">Đã gán cho tôi</a>
                            <a href="<?= url('conversations?filter=starred') ?>" class="btn <?= $currentFilter === 'starred' ? 'btn-primary' : 'btn-soft-primary' ?>">Được đánh dấu</a>
                        </div>

                        <!-- Conversation list -->
                        <div data-simplebar style="max-height: 600px;">
                            <!-- AI Trợ lý - pinned -->
                            <a href="<?= url('conversations?active=ai') ?>"
                               class="d-flex align-items-start p-3 border-bottom text-decoration-none <?= ($activeId ?? '') === 'ai' ? 'bg-light' : '' ?>"
                               style="cursor:pointer;">
                                <div class="flex-shrink-0 me-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width:40px;height:40px;font-size:18px">
                                        <i class="ri-robot-line"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 text-dark">AI Trợ lý</h6>
                                        <span class="badge bg-primary-subtle text-primary">AI</span>
                                    </div>
                                    <p class="text-muted mb-0 text-truncate fs-13">Hỏi bất kỳ điều gì về CRM...</p>
                                </div>
                            </a>

                            <?php if (!empty($conversations)): ?>
                                <?php foreach ($conversations as $conv): ?>
                                    <?php
                                        $contactName = trim($conv['contact_name'] ?? '');
                                        if (empty($contactName)) $contactName = 'Không rõ';
                                        $firstLetter = mb_strtoupper(mb_substr($contactName, 0, 1));
                                        $isActive = ($conv['id'] == $activeId);
                                        $ch = $conv['channel'] ?? 'email';
                                    ?>
                                    <a href="<?= url('conversations?active=' . $conv['id'] . ($currentFilter ? '&filter=' . e($currentFilter) : '') . ($filters['search'] ? '&search=' . urlencode($filters['search']) : '')) ?>"
                                       class="d-flex align-items-start p-3 border-bottom text-decoration-none <?= $isActive ? 'bg-light' : '' ?>"
                                       style="cursor:pointer;">
                                        <!-- Avatar -->
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-title rounded-circle bg-<?= $channelColors[$ch] ?? 'secondary' ?>-subtle text-<?= $channelColors[$ch] ?? 'secondary' ?>" style="width:40px;height:40px;font-size:16px;display:flex;align-items:center;justify-content:center;">
                                                <?= $firstLetter ?>
                                            </div>
                                        </div>
                                        <!-- Content -->
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 text-truncate text-dark <?= $conv['unread_count'] > 0 ? 'fw-bold' : '' ?>">
                                                    <?= e($contactName) ?>
                                                </h6>
                                                <small class="text-muted flex-shrink-0 ms-2">
                                                    <?= $conv['last_message_at'] ? time_ago($conv['last_message_at']) : '' ?>
                                                </small>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center gap-1 overflow-hidden">
                                                    <span class="badge bg-<?= $channelColors[$ch] ?? 'secondary' ?>" style="font-size:10px;"><?= $channelLabels[$ch] ?? $ch ?></span>
                                                    <span class="text-muted text-truncate" style="font-size:13px;"><?= e(mb_substr($conv['last_message_preview'] ?? '', 0, 50)) ?></span>
                                                </div>
                                                <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-2">
                                                    <?php if ($conv['unread_count'] > 0): ?>
                                                        <span class="badge bg-danger rounded-pill"><?= $conv['unread_count'] ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($conv['is_starred']): ?>
                                                        <i class="ri-star-fill text-warning"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="ri-chat-1-line fs-1 d-block mb-2"></i>
                                    Chưa có cuộc hội thoại
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <div class="p-3 border-top">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><?= count($conversations) ?> / <?= $pagination['total'] ?></small>
                                    <nav><ul class="pagination pagination mb-0">
                                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                            <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                                                <a class="page-link" href="<?= url('conversations?page=' . $i . ($currentFilter ? '&filter=' . e($currentFilter) : '') . ($filters['search'] ? '&search=' . urlencode($filters['search']) : '')) ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul></nav>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right content: conversation detail -->
            <div class="col-xl-8">
                <?php if (($this->input('active') ?? '') === 'ai'): ?>
                    <!-- AI Chat Panel -->
                    <div class="card">
                        <div class="card-header p-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width:36px;height:36px">
                                    <i class="ri-robot-line"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">AI Trợ lý</h5>
                                    <small class="text-muted">Hỏi bất kỳ điều gì về CRM</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="aiChatMessages" style="height:400px;overflow-y:auto;padding:16px">
                                <!-- Suggested prompts -->
                                <div id="aiSuggestions" class="text-center py-4">
                                    <i class="ri-robot-line text-primary" style="font-size:48px"></i>
                                    <p class="text-muted mt-2">Tôi có thể giúp gì cho bạn?</p>
                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                        <button class="btn btn-soft-primary ai-suggest" data-msg="Doanh thu tháng này">💰 Doanh thu</button>
                                        <button class="btn btn-soft-warning ai-suggest" data-msg="Công việc quá hạn">⚠️ Task quá hạn</button>
                                        <button class="btn btn-soft-info ai-suggest" data-msg="Khách hàng cần liên hệ">📞 KH cần liên hệ</button>
                                        <button class="btn btn-soft-success ai-suggest" data-msg="Pipeline">📊 Pipeline</button>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 border-top">
                                <div class="d-flex gap-2">
                                    <input type="text" class="form-control" id="aiInput" placeholder="Nhập tin nhắn... (VD: Doanh thu tháng này)" autocomplete="off">
                                    <button class="btn btn-primary" id="aiSendBtn"><i class="ri-send-plane-fill"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                    (function() {
                        var chatBox = document.getElementById('aiChatMessages');
                        var input = document.getElementById('aiInput');
                        var sendBtn = document.getElementById('aiSendBtn');
                        var token = '<?= $_SESSION["csrf_token"] ?? "" ?>';

                        function addMsg(role, text) {
                            var suggestions = document.getElementById('aiSuggestions');
                            if (suggestions) suggestions.remove();

                            var div = document.createElement('div');
                            div.className = 'd-flex mb-3 ' + (role === 'user' ? 'justify-content-end' : '');
                            var bubble = role === 'user'
                                ? '<div class="bg-primary text-white rounded p-2 px-3" style="max-width:80%">' + text.replace(/\n/g,'<br>') + '</div>'
                                : '<div class="d-flex gap-2"><div class="flex-shrink-0"><div class="d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:32px;height:32px"><i class="ri-robot-line"></i></div></div><div class="bg-light rounded p-2 px-3" style="max-width:80%">' + text.replace(/\n/g,'<br>') + '</div></div>';
                            div.innerHTML = bubble;
                            chatBox.appendChild(div);
                            chatBox.scrollTop = chatBox.scrollHeight;
                        }

                        function send(msg) {
                            if (!msg.trim()) return;
                            addMsg('user', msg);
                            input.value = '';

                            var typing = document.createElement('div');
                            typing.id = 'aiTyping';
                            typing.className = 'd-flex mb-3';
                            typing.innerHTML = '<div class="bg-light rounded p-2 px-3"><span class="spinner-border spinner-border-sm me-1"></span> Đang suy nghĩ...</div>';
                            chatBox.appendChild(typing);
                            chatBox.scrollTop = chatBox.scrollHeight;

                            fetch('/ai-chat/send', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                body: '_token=' + token + '&message=' + encodeURIComponent(msg)
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(d) {
                                var t = document.getElementById('aiTyping');
                                if (t) t.remove();
                                addMsg('assistant', d.message || d.error || 'Lỗi');
                            })
                            .catch(function() {
                                var t = document.getElementById('aiTyping');
                                if (t) t.remove();
                                addMsg('assistant', 'Lỗi kết nối');
                            });
                        }

                        sendBtn.addEventListener('click', function() { send(input.value); });
                        input.addEventListener('keydown', function(e) { if (e.key === 'Enter') send(input.value); });
                        document.querySelectorAll('.ai-suggest').forEach(function(btn) {
                            btn.addEventListener('click', function() { send(this.dataset.msg); });
                        });

                        // Load history
                        fetch('/ai-chat/history').then(function(r){return r.json()}).then(function(d) {
                            if (d.messages && d.messages.length > 0) {
                                document.getElementById('aiSuggestions')?.remove();
                                d.messages.forEach(function(m) { addMsg(m.role, m.message || m.content); });
                            }
                        }).catch(function(){});
                    })();
                    </script>

                <?php elseif ($activeConversation): ?>
                    <div class="card">
                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="mb-0"><?= e(trim($activeConversation['contact_name'])) ?></h5>
                                    <span class="badge bg-<?= $statusColors[$activeConversation['status']] ?? 'secondary' ?>">
                                        <?= $statusLabels[$activeConversation['status']] ?? $activeConversation['status'] ?>
                                    </span>
                                    <span class="badge bg-<?= $channelColors[$activeConversation['channel']] ?? 'secondary' ?>">
                                        <?= $channelLabels[$activeConversation['channel']] ?? $activeConversation['channel'] ?>
                                    </span>
                                    <?php if (!empty($activeConversation['subject'])): ?>
                                        <span class="text-muted">- <?= e($activeConversation['subject']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <!-- Star toggle -->
                                    <form method="POST" action="<?= url('conversations/' . $activeConversation['id'] . '/star') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-ghost-warning p-1" title="Đánh dấu">
                                            <i class="<?= $activeConversation['is_starred'] ? 'ri-star-fill' : 'ri-star-line' ?> fs-5"></i>
                                        </button>
                                    </form>

                                    <!-- Assign dropdown -->
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="ri-user-line me-1"></i><?= e($activeConversation['assigned_name'] ?? 'Chưa gán') ?>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form method="POST" action="<?= url('conversations/' . $activeConversation['id'] . '/assign') ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="assigned_to" value="">
                                                    <button class="dropdown-item">Bỏ gán</button>
                                                </form>
                                            </li>
                                            <?php foreach ($users as $u): ?>
                                                <li>
                                                    <form method="POST" action="<?= url('conversations/' . $activeConversation['id'] . '/assign') ?>">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="assigned_to" value="<?= $u['id'] ?>">
                                                        <button class="dropdown-item <?= ($activeConversation['assigned_to'] ?? 0) == $u['id'] ? 'active' : '' ?>"><?= e($u['name']) ?></button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Status dropdown -->
                                    <div class="dropdown">
                                        <button class="btn btn-soft-<?= $statusColors[$activeConversation['status']] ?? 'secondary' ?> dropdown-toggle" data-bs-toggle="dropdown">
                                            <?= $statusLabels[$activeConversation['status']] ?? $activeConversation['status'] ?>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <?php foreach ($statusLabels as $sv => $sl): ?>
                                                <li>
                                                    <form method="POST" action="<?= url('conversations/' . $activeConversation['id'] . '/status') ?>">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="status" value="<?= $sv ?>">
                                                        <button class="dropdown-item <?= $activeConversation['status'] === $sv ? 'active' : '' ?>"><?= $sl ?></button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- View full page -->
                                    <a href="<?= url('conversations/' . $activeConversation['id']) ?>" class="btn btn-soft-info" title="Xem trang đầy đủ">
                                        <i class="ri-external-link-line me-1"></i> Xem
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Message area -->
                        <div class="card-body p-0">
                            <div data-simplebar style="max-height: 500px; padding: 1rem;" id="messageArea">
                                <?php if (!empty($messages)): ?>
                                    <?php foreach ($messages as $msg): ?>
                                        <?php $isOutbound = ($msg['direction'] === 'outbound'); ?>
                                        <div class="d-flex mb-3 <?= $isOutbound ? 'justify-content-end' : 'justify-content-start' ?>">
                                            <div class="<?= $isOutbound ? 'bg-primary-subtle' : 'bg-light' ?> rounded p-3" style="max-width:70%;">
                                                <div class="mb-1">
                                                    <small class="fw-medium <?= $isOutbound ? 'text-primary' : 'text-dark' ?>">
                                                        <?= $isOutbound ? e($msg['sender_name'] ?? 'Bạn') : e(trim($activeConversation['contact_name']) ?: 'Khách hàng') ?>
                                                    </small>
                                                </div>
                                                <div style="white-space: pre-wrap;"><?= e($msg['content']) ?></div>
                                                <div class="mt-1">
                                                    <small class="text-muted"><?= time_ago($msg['created_at']) ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="ri-chat-3-line fs-1 d-block mb-2"></i>
                                        Chưa có tin nhắn
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Reply form -->
                        <div class="card-footer">
                            <form method="POST" action="<?= url('conversations/' . $activeConversation['id'] . '/reply') ?>">
                                <?= csrf_field() ?>
                                <div class="mb-2">
                                    <textarea name="content" class="form-control" rows="3" placeholder="Nhập tin nhắn..." required id="replyContent"></textarea>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($cannedResponses)): ?>
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-soft-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="ri-file-text-line me-1"></i> Mẫu trả lời
                                                </button>
                                                <ul class="dropdown-menu" style="max-height:300px;overflow-y:auto;">
                                                    <?php foreach ($cannedResponses as $cr): ?>
                                                        <li>
                                                            <a class="dropdown-item canned-response-item" href="javascript:void(0)" data-content="<?= e($cr['content']) ?>">
                                                                <?= e($cr['title']) ?>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-send-plane-line me-1"></i> Gửi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="ri-chat-1-line display-4 text-muted d-block mb-3"></i>
                            <h5 class="text-muted">Chọn một cuộc hội thoại</h5>
                            <p class="text-muted mb-0">Chọn cuộc hội thoại từ danh sách bên trái hoặc tạo cuộc hội thoại mới</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll message area to bottom
            var msgArea = document.getElementById('messageArea');
            if (msgArea) {
                var simplebar = SimpleBar.instances.get(msgArea);
                if (simplebar) {
                    simplebar.getScrollElement().scrollTop = simplebar.getScrollElement().scrollHeight;
                }
            }

            // Canned responses
            document.querySelectorAll('.canned-response-item').forEach(function(el) {
                el.addEventListener('click', function() {
                    var textarea = document.getElementById('replyContent');
                    if (textarea) {
                        textarea.value = this.getAttribute('data-content');
                        textarea.focus();
                    }
                });
            });
        });
        </script>
