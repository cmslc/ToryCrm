/**
 * ToryCRM AI Chat Widget
 * Floating chat popup - bottom right
 */
(function() {
    'use strict';

    var isOpen = false;
    var csrfToken = '';
    var historyLoaded = false;

    // Find CSRF token from page
    function getCsrfToken() {
        var el = document.querySelector('[name=csrf_token]') || document.querySelector('[name=_token]');
        return el ? el.value : '';
    }

    // Create widget HTML
    function createWidget() {
        // Floating button
        var btn = document.createElement('div');
        btn.id = 'aiChatWidgetBtn';
        btn.innerHTML = '<i class="ri-robot-line"></i>';
        btn.style.cssText = 'position:fixed;bottom:24px;right:24px;width:56px;height:56px;border-radius:50%;background:var(--vz-primary,#405189);color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.25);z-index:9998;transition:transform .2s';
        btn.onmouseenter = function() { btn.style.transform = 'scale(1.1)'; };
        btn.onmouseleave = function() { btn.style.transform = 'scale(1)'; };
        btn.onclick = toggleWidget;
        document.body.appendChild(btn);

        // Chat popup
        var popup = document.createElement('div');
        popup.id = 'aiChatWidgetPopup';
        popup.style.cssText = 'position:fixed;bottom:90px;right:24px;width:370px;height:520px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,0.2);z-index:9999;display:none;flex-direction:column;overflow:hidden';
        popup.innerHTML =
            '<div style="background:var(--vz-primary,#405189);color:#fff;padding:14px 16px;display:flex;align-items:center;justify-content:space-between">' +
                '<div style="display:flex;align-items:center;gap:10px">' +
                    '<i class="ri-robot-line" style="font-size:22px"></i>' +
                    '<div><div style="font-weight:600;font-size:15px">AI Trợ lý</div><small style="opacity:.8;font-size:12px">ToryCRM</small></div>' +
                '</div>' +
                '<div style="display:flex;gap:8px">' +
                    '<button onclick="window._aiWidget.clear()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:16px;padding:4px" title="Xóa lịch sử"><i class="ri-delete-bin-line"></i></button>' +
                    '<button onclick="window._aiWidget.toggle()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:18px;padding:4px" title="Đóng"><i class="ri-close-line"></i></button>' +
                '</div>' +
            '</div>' +
            '<div id="aiWidgetMessages" style="flex:1;overflow-y:auto;padding:12px;display:flex;flex-direction:column;gap:8px">' +
                '<div id="aiWidgetWelcome" style="text-align:center;padding:20px 10px">' +
                    '<i class="ri-robot-line" style="font-size:40px;color:var(--vz-primary,#405189)"></i>' +
                    '<p style="color:#878a99;margin:10px 0 12px;font-size:13px">Xin chào! Tôi có thể giúp gì?</p>' +
                    '<div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center">' +
                        '<button class="ai-widget-suggest" onclick="window._aiWidget.send(\'Doanh thu tháng này\')">Doanh thu tháng này</button>' +
                        '<button class="ai-widget-suggest" onclick="window._aiWidget.send(\'Công việc quá hạn\')">Công việc quá hạn</button>' +
                        '<button class="ai-widget-suggest" onclick="window._aiWidget.send(\'Thống kê pipeline\')">Thống kê pipeline</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div id="aiWidgetTyping" style="display:none;padding:8px 12px">' +
                '<div style="display:flex;align-items:center;gap:6px">' +
                    '<div style="display:flex;gap:3px"><span class="ai-dot"></span><span class="ai-dot"></span><span class="ai-dot"></span></div>' +
                    '<small style="color:#878a99">Đang trả lời...</small>' +
                '</div>' +
            '</div>' +
            '<div style="padding:10px 12px;border-top:1px solid #e9ebec;display:flex;gap:8px">' +
                '<input type="text" id="aiWidgetInput" placeholder="Nhập tin nhắn..." style="flex:1;border:1px solid #e9ebec;border-radius:8px;padding:8px 12px;font-size:14px;outline:none">' +
                '<button onclick="window._aiWidget.sendFromInput()" style="background:var(--vz-primary,#405189);color:#fff;border:none;border-radius:8px;padding:8px 14px;cursor:pointer;font-size:16px"><i class="ri-send-plane-fill"></i></button>' +
            '</div>';
        document.body.appendChild(popup);

        // Enter key
        document.getElementById('aiWidgetInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                window._aiWidget.sendFromInput();
            }
        });

        // Add styles
        var style = document.createElement('style');
        style.textContent =
            '.ai-widget-suggest{background:#f3f6f9;border:1px solid #e9ebec;border-radius:16px;padding:5px 12px;font-size:12px;cursor:pointer;color:#405189;transition:all .2s}' +
            '.ai-widget-suggest:hover{background:#405189;color:#fff;border-color:#405189}' +
            '.ai-dot{width:6px;height:6px;background:#878a99;border-radius:50%;animation:aiDotAnim 1.4s infinite}' +
            '.ai-dot:nth-child(2){animation-delay:.2s}' +
            '.ai-dot:nth-child(3){animation-delay:.4s}' +
            '@keyframes aiDotAnim{0%,60%,100%{opacity:.3;transform:translateY(0)}30%{opacity:1;transform:translateY(-4px)}}' +
            '.ai-msg-user{align-self:flex-end;background:var(--vz-primary,#405189);color:#fff;padding:8px 12px;border-radius:12px 12px 0 12px;max-width:85%;font-size:13px;white-space:pre-wrap;word-wrap:break-word}' +
            '.ai-msg-bot{align-self:flex-start;background:#f3f6f9;padding:8px 12px;border-radius:12px 12px 12px 0;max-width:85%;font-size:13px;white-space:pre-wrap;word-wrap:break-word}';
        document.head.appendChild(style);
    }

    function toggleWidget() {
        isOpen = !isOpen;
        var popup = document.getElementById('aiChatWidgetPopup');
        var btn = document.getElementById('aiChatWidgetBtn');
        if (isOpen) {
            popup.style.display = 'flex';
            btn.innerHTML = '<i class="ri-close-line"></i>';
            document.getElementById('aiWidgetInput').focus();
            csrfToken = getCsrfToken();
            if (!historyLoaded) {
                historyLoaded = true;
                fetch('/ai-chat/history').then(function(r){return r.json()}).then(function(d) {
                    if (d.messages && d.messages.length > 0) {
                        d.messages.forEach(function(m) { appendMsg(m.role, m.message || m.content); });
                    }
                }).catch(function(){});
            }
        } else {
            popup.style.display = 'none';
            btn.innerHTML = '<i class="ri-robot-line"></i>';
        }
    }

    function appendMsg(role, text) {
        var container = document.getElementById('aiWidgetMessages');
        var welcome = document.getElementById('aiWidgetWelcome');
        if (welcome) welcome.style.display = 'none';

        var div = document.createElement('div');
        div.className = role === 'user' ? 'ai-msg-user' : 'ai-msg-bot';
        div.textContent = text;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function sendMessage(text) {
        if (!text || !text.trim()) return;
        text = text.trim();
        appendMsg('user', text);

        var typing = document.getElementById('aiWidgetTyping');
        typing.style.display = 'block';

        var formData = new FormData();
        formData.append('message', text);
        var tk = csrfToken || getCsrfToken();
        formData.append('csrf_token', tk);
        formData.append('_token', tk);

        fetch('/ai-chat/send', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                typing.style.display = 'none';
                appendMsg('assistant', data.message || data.error || 'Lỗi không xác định');
            })
            .catch(function() {
                typing.style.display = 'none';
                appendMsg('assistant', 'Có lỗi xảy ra. Vui lòng thử lại.');
            });
    }

    function sendFromInput() {
        var input = document.getElementById('aiWidgetInput');
        var text = input.value;
        input.value = '';
        sendMessage(text);
    }

    function clearChat() {
        if (!confirm('Xóa lịch sử trò chuyện?')) return;
        var formData = new FormData();
        var tk = csrfToken || getCsrfToken();
        formData.append('csrf_token', tk);
        formData.append('_token', tk);

        fetch('/ai-chat/clear', { method: 'POST', body: formData })
            .then(function() {
                var container = document.getElementById('aiWidgetMessages');
                container.innerHTML =
                    '<div id="aiWidgetWelcome" style="text-align:center;padding:20px 10px">' +
                        '<i class="ri-robot-line" style="font-size:40px;color:var(--vz-primary,#405189)"></i>' +
                        '<p style="color:#878a99;margin:10px 0 12px;font-size:13px">Xin chào! Tôi có thể giúp gì?</p>' +
                    '</div>';
            });
    }

    // Public API
    window._aiWidget = {
        toggle: toggleWidget,
        send: sendMessage,
        sendFromInput: sendFromInput,
        clear: clearChat
    };

    // Init when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createWidget);
    } else {
        createWidget();
    }
})();
