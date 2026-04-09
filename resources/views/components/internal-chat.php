<?php
/**
 * Internal Chat Component
 * Usage: include this file with $chatEntityType and $chatEntityId set
 * e.g. $chatEntityType = 'deal'; $chatEntityId = $deal['id'];
 */
$chatEntityType = $chatEntityType ?? '';
$chatEntityId = $chatEntityId ?? 0;
?>

<div class="card" id="internal-chat-widget">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0"><i class="ri-chat-3-line me-1"></i> Trao đổi nội bộ</h5>
        <span class="badge bg-primary-subtle text-primary" id="chat-count">0</span>
    </div>
    <div class="card-body p-0">
        <!-- Chat Messages Area -->
        <div id="chat-messages" style="max-height: 400px; overflow-y: auto; padding: 16px;">
            <div id="chat-loading" class="text-center py-4 text-muted">
                <i class="ri-loader-4-line ri-spin fs-4"></i>
            </div>
            <div id="chat-empty" class="text-center py-4 text-muted" style="display:none;">
                <i class="ri-chat-3-line fs-1 d-block mb-2"></i>
                Chưa có tin nhắn nào
            </div>
            <div id="chat-list"></div>
        </div>

        <!-- Chat Input -->
        <div class="border-top p-3">
            <div class="position-relative">
                <div class="d-flex gap-2">
                    <div class="flex-grow-1 position-relative">
                        <textarea id="chat-input" class="form-control" rows="2" placeholder="Nhập tin nhắn... (dùng @ để nhắc người dùng)" style="resize: none;"></textarea>
                        <!-- @mention dropdown -->
                        <div id="mention-dropdown" class="dropdown-menu shadow" style="display:none; position:absolute; z-index:1050; max-height:200px; overflow-y:auto;"></div>
                    </div>
                    <button type="button" id="chat-send-btn" class="btn btn-primary align-self-end">
                        <i class="ri-send-plane-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const entityType = '<?= e($chatEntityType) ?>';
    const entityId = '<?= (int) $chatEntityId ?>';
    const currentUserId = <?= (int) ($_SESSION['user']['id'] ?? 0) ?>;
    const currentUserRole = '<?= e($_SESSION['user']['role'] ?? '') ?>';
    const csrfToken = '<?= $_SESSION['csrf_token'] ?? '' ?>';

    const chatList = document.getElementById('chat-list');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('chat-send-btn');
    const chatLoading = document.getElementById('chat-loading');
    const chatEmpty = document.getElementById('chat-empty');
    const chatCount = document.getElementById('chat-count');
    const mentionDropdown = document.getElementById('mention-dropdown');

    let mentionMode = false;
    let mentionQuery = '';
    let mentionStartPos = 0;
    let usersCache = [];

    // Load messages
    function loadMessages() {
        fetch('/' + 'chat/' + entityType + '/' + entityId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            chatLoading.style.display = 'none';
            const messages = data.messages || [];
            chatCount.textContent = messages.length;

            if (messages.length === 0) {
                chatEmpty.style.display = '';
                chatList.innerHTML = '';
                return;
            }

            chatEmpty.style.display = 'none';
            chatList.innerHTML = messages.map(renderMessage).join('');
            scrollToBottom();
        })
        .catch(() => {
            chatLoading.innerHTML = '<span class="text-danger">Lỗi tải tin nhắn</span>';
        });
    }

    function renderMessage(msg) {
        const canDelete = msg.user_id == currentUserId || currentUserRole === 'admin';
        const pinnedClass = msg.is_pinned == 1 ? 'border-start border-3 border-warning ps-2' : '';
        const pinnedIcon = msg.is_pinned == 1 ? 'ri-pushpin-fill text-warning' : 'ri-pushpin-line text-muted';

        return `
        <div class="d-flex mb-3 chat-message ${pinnedClass}" data-id="${msg.id}">
            <div class="flex-shrink-0">
                <div class="avatar-xs">
                    <div class="avatar-title rounded-circle bg-primary-subtle text-primary">${msg.avatar_initial}</div>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h6 class="mb-0 fs-13">${escHtml(msg.user_name || '')}</h6>
                    <small class="text-muted">${escHtml(msg.time_ago || '')}</small>
                    <div class="ms-auto d-flex gap-1">
                        <button class="btn btn-link p-0 chat-pin-btn" data-id="${msg.id}" title="Ghim"><i class="${pinnedIcon}"></i></button>
                        ${canDelete ? `<button class="btn btn-link p-0 text-danger chat-delete-btn" data-id="${msg.id}" title="Xoá"><i class="ri-delete-bin-line"></i></button>` : ''}
                    </div>
                </div>
                <div class="fs-13">${msg.content_html || escHtml(msg.content)}</div>
            </div>
        </div>`;
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function scrollToBottom() {
        setTimeout(() => { chatMessages.scrollTop = chatMessages.scrollHeight; }, 50);
    }

    // Send message
    function sendMessage() {
        const content = chatInput.value.trim();
        if (!content) return;

        sendBtn.disabled = true;
        const formData = new FormData();
        formData.append('content', content);
        formData.append('csrf_token', csrfToken);

        fetch('/' + 'chat/' + entityType + '/' + entityId, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            sendBtn.disabled = false;
            if (data.success && data.message) {
                chatEmpty.style.display = 'none';
                chatList.insertAdjacentHTML('beforeend', renderMessage(data.message));
                chatInput.value = '';
                chatCount.textContent = parseInt(chatCount.textContent || 0) + 1;
                scrollToBottom();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(() => {
            sendBtn.disabled = false;
            alert('Lỗi gửi tin nhắn');
        });
    }

    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!mentionMode) sendMessage();
        }
    });

    // @mention autocomplete
    chatInput.addEventListener('input', function() {
        const val = this.value;
        const cursorPos = this.selectionStart;

        // Find @ before cursor
        const textBeforeCursor = val.substring(0, cursorPos);
        const atIndex = textBeforeCursor.lastIndexOf('@');

        if (atIndex >= 0 && (atIndex === 0 || textBeforeCursor[atIndex - 1] === ' ' || textBeforeCursor[atIndex - 1] === '\n')) {
            const query = textBeforeCursor.substring(atIndex + 1);
            if (query.length >= 0 && !/\s/.test(query)) {
                mentionMode = true;
                mentionQuery = query;
                mentionStartPos = atIndex;
                showMentionDropdown(query);
                return;
            }
        }

        hideMentionDropdown();
    });

    function showMentionDropdown(query) {
        fetch('/' + 'api-internal/users?q=' + encodeURIComponent(query), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(users => {
            if (!users.length) {
                hideMentionDropdown();
                return;
            }

            usersCache = users;
            mentionDropdown.innerHTML = users.map((u, i) =>
                `<a class="dropdown-item mention-item" href="#" data-index="${i}" data-name="${escHtml(u.name)}">
                    <div class="avatar-xs d-inline-flex me-2">
                        <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-12">${escHtml(u.name.charAt(0).toUpperCase())}</div>
                    </div>
                    ${escHtml(u.name)}
                </a>`
            ).join('');
            mentionDropdown.style.display = 'block';
            mentionDropdown.style.bottom = '100%';
            mentionDropdown.style.left = '0';
        });
    }

    function hideMentionDropdown() {
        mentionMode = false;
        mentionDropdown.style.display = 'none';
    }

    mentionDropdown.addEventListener('click', function(e) {
        e.preventDefault();
        const item = e.target.closest('.mention-item');
        if (!item) return;

        const name = item.dataset.name.replace(/\s+/g, '');
        const val = chatInput.value;
        const before = val.substring(0, mentionStartPos);
        const after = val.substring(chatInput.selectionStart);
        chatInput.value = before + '@' + name + ' ' + after;
        chatInput.focus();
        const newPos = mentionStartPos + name.length + 2;
        chatInput.setSelectionRange(newPos, newPos);
        hideMentionDropdown();
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!mentionDropdown.contains(e.target) && e.target !== chatInput) {
            hideMentionDropdown();
        }
    });

    // Pin / Delete handlers (delegated)
    chatList.addEventListener('click', function(e) {
        const pinBtn = e.target.closest('.chat-pin-btn');
        const delBtn = e.target.closest('.chat-delete-btn');

        if (pinBtn) {
            e.preventDefault();
            const id = pinBtn.dataset.id;
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);

            fetch('/' + 'chat/' + id + '/pin', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) loadMessages();
            });
        }

        if (delBtn) {
            e.preventDefault();
            if (!confirm('Xác nhận xóa tin nhắn?')) return;
            const id = delBtn.dataset.id;
            const formData = new FormData();
            formData.append('csrf_token', csrfToken);

            fetch('/' + 'chat/' + id + '/delete', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const msgEl = chatList.querySelector(`.chat-message[data-id="${id}"]`);
                    if (msgEl) msgEl.remove();
                    chatCount.textContent = Math.max(0, parseInt(chatCount.textContent || 0) - 1);
                    if (!chatList.children.length) chatEmpty.style.display = '';
                }
            });
        }
    });

    // Initialize
    loadMessages();
})();
</script>
