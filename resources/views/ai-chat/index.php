<?php $pageTitle = 'AI Trợ lý'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">AI Trợ lý</h4>
    <div>
        <button class="btn btn-soft-danger" onclick="clearChat()"><i class="ri-delete-bin-line me-1"></i> Xóa lịch sử</button>
    </div>
</div>

<div class="row">
    <!-- Chat History Sidebar -->
    <div class="col-lg-3">
        <div class="card" style="height: calc(100vh - 220px)">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="ri-history-line me-1"></i> Lịch sử trò chuyện</h6>
            </div>
            <div class="card-body p-2" data-simplebar style="max-height: calc(100vh - 300px)">
                <div id="chatHistoryList" class="list-group list-group-flush">
                    <div class="text-center text-muted p-3">
                        <i class="ri-chat-3-line" style="font-size: 40px"></i>
                        <p class="mt-2 mb-0">Bắt đầu cuộc trò chuyện mới</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="col-lg-9">
        <div class="card" style="height: calc(100vh - 220px)">
            <div class="card-header bg-primary-subtle">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title rounded-circle bg-primary text-white">
                            <i class="ri-robot-line fs-4"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-0">ToryCRM AI</h6>
                        <small class="text-muted">Trợ lý thông minh - Hỗ trợ quản lý CRM</small>
                    </div>
                </div>
            </div>

            <div class="card-body" id="chatMessages" data-simplebar style="max-height: calc(100vh - 400px); overflow-y: auto;">
                <!-- Welcome message -->
                <div id="welcomeArea" class="text-center py-5">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-title rounded-circle bg-primary-subtle text-primary" style="width:80px;height:80px;font-size:36px">
                            <i class="ri-robot-line"></i>
                        </div>
                    </div>
                    <h5>Xin chào! Tôi là AI Trợ lý</h5>
                    <p class="text-muted mb-4">Tôi có thể giúp bạn quản lý CRM hiệu quả hơn. Hãy thử các gợi ý bên dưới:</p>
                    <div class="row g-2 justify-content-center">
                        <div class="col-auto">
                            <button class="btn btn-soft-primary" onclick="sendSuggestion('Doanh thu tháng này')">
                                <i class="ri-money-dollar-circle-line me-1"></i> Doanh thu tháng này
                            </button>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-soft-warning" onclick="sendSuggestion('Công việc quá hạn')">
                                <i class="ri-alarm-warning-line me-1"></i> Công việc quá hạn
                            </button>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-soft-info" onclick="sendSuggestion('Khách hàng cần liên hệ')">
                                <i class="ri-phone-line me-1"></i> Khách hàng cần liên hệ
                            </button>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-soft-success" onclick="sendSuggestion('Thống kê pipeline')">
                                <i class="ri-bar-chart-box-line me-1"></i> Thống kê pipeline
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages will be appended here -->
                <div id="messageArea"></div>

                <!-- Typing indicator -->
                <div id="typingIndicator" class="d-none mb-3">
                    <div class="d-flex align-items-start">
                        <div class="avatar-xs me-2 flex-shrink-0">
                            <div class="avatar-title rounded-circle bg-primary text-white"><i class="ri-robot-line"></i></div>
                        </div>
                        <div class="bg-light rounded p-3">
                            <div class="typing-dots">
                                <span></span><span></span><span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="card-footer bg-light">
                <form id="chatForm" onsubmit="sendMessage(event)" class="d-flex gap-2">
                    <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                    <input type="text" id="chatInput" class="form-control" placeholder="Nhập tin nhắn... (VD: Doanh thu tháng này, Tạo task...)" autocomplete="off">
                    <button type="submit" class="btn btn-primary" id="sendBtn">
                        <i class="ri-send-plane-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.typing-dots {
    display: flex;
    gap: 4px;
    padding: 4px 0;
}
.typing-dots span {
    width: 8px;
    height: 8px;
    background: #878a99;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}
.typing-dots span:nth-child(2) { animation-delay: 0.2s; }
.typing-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-6px); opacity: 1; }
}
.chat-bubble {
    max-width: 80%;
    white-space: pre-wrap;
    word-wrap: break-word;
}
.chat-bubble-user {
    background-color: var(--vz-primary);
    color: #fff;
    border-radius: 12px 12px 0 12px;
}
.chat-bubble-ai {
    background-color: var(--vz-light);
    border-radius: 12px 12px 12px 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadHistory();
    document.getElementById('chatInput').focus();
});

function loadHistory() {
    fetch('<?= url("ai-chat/history") ?>')
        .then(r => r.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                document.getElementById('welcomeArea').classList.add('d-none');
                data.messages.forEach(m => {
                    appendMessage(m.role, m.message, m.created_at);
                });
                scrollToBottom();
                buildHistoryList(data.messages);
            }
        })
        .catch(() => {});
}

function buildHistoryList(messages) {
    var list = document.getElementById('chatHistoryList');
    var userMsgs = messages.filter(m => m.role === 'user');
    if (userMsgs.length === 0) return;

    var grouped = {};
    userMsgs.forEach(m => {
        var date = m.created_at ? m.created_at.substring(0, 10) : 'Hôm nay';
        if (!grouped[date]) grouped[date] = [];
        grouped[date].push(m);
    });

    var html = '';
    for (var date in grouped) {
        html += '<div class="p-2 bg-light"><small class="text-muted fw-medium">' + date + '</small></div>';
        grouped[date].forEach(m => {
            var preview = m.message.substring(0, 40) + (m.message.length > 40 ? '...' : '');
            html += '<a href="#" class="list-group-item list-group-item-action py-2 px-3"><small>' + escapeHtml(preview) + '</small></a>';
        });
    }
    list.innerHTML = html;
}

function sendSuggestion(text) {
    document.getElementById('chatInput').value = text;
    sendMessage(new Event('submit'));
}

function sendMessage(e) {
    e.preventDefault();
    var input = document.getElementById('chatInput');
    var message = input.value.trim();
    if (!message) return;

    document.getElementById('welcomeArea').classList.add('d-none');
    appendMessage('user', message);
    input.value = '';

    document.getElementById('typingIndicator').classList.remove('d-none');
    scrollToBottom();

    var formData = new FormData();
    formData.append('message', message);
    formData.append('_token', document.querySelector('[name=_token]').value);

    fetch('<?= url("ai-chat/send") ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('typingIndicator').classList.add('d-none');
        if (data.message) {
            appendMessage('assistant', data.message);
        } else if (data.error) {
            appendMessage('assistant', 'Lỗi: ' + data.error);
        }
        scrollToBottom();
    })
    .catch(() => {
        document.getElementById('typingIndicator').classList.add('d-none');
        appendMessage('assistant', 'Có lỗi xảy ra. Vui lòng thử lại.');
        scrollToBottom();
    });
}

function appendMessage(role, text, time) {
    var area = document.getElementById('messageArea');
    var timeStr = time ? new Date(time).toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'}) : new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});

    if (role === 'user') {
        area.innerHTML += '<div class="d-flex justify-content-end mb-3">' +
            '<div class="chat-bubble chat-bubble-user p-3">' + escapeHtml(text) +
            '<div class="mt-1"><small style="opacity:0.7">' + timeStr + '</small></div></div></div>';
    } else {
        area.innerHTML += '<div class="d-flex align-items-start mb-3">' +
            '<div class="avatar-xs me-2 flex-shrink-0"><div class="avatar-title rounded-circle bg-primary text-white"><i class="ri-robot-line"></i></div></div>' +
            '<div class="chat-bubble chat-bubble-ai p-3">' + escapeHtml(text) +
            '<div class="mt-1"><small class="text-muted">' + timeStr + '</small></div></div></div>';
    }
}

function clearChat() {
    if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ lịch sử trò chuyện?')) return;

    var formData = new FormData();
    formData.append('_token', document.querySelector('[name=_token]').value);

    fetch('<?= url("ai-chat/clear") ?>', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(() => {
            document.getElementById('messageArea').innerHTML = '';
            document.getElementById('welcomeArea').classList.remove('d-none');
            document.getElementById('chatHistoryList').innerHTML =
                '<div class="text-center text-muted p-3"><i class="ri-chat-3-line" style="font-size:40px"></i><p class="mt-2 mb-0">Bắt đầu cuộc trò chuyện mới</p></div>';
        });
}

function scrollToBottom() {
    var el = document.getElementById('chatMessages');
    var sb = el.querySelector('.simplebar-content-wrapper');
    if (sb) sb.scrollTop = sb.scrollHeight;
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
}
</script>
