/**
 * ToryCRM - Custom JavaScript
 */

// Notification polling
(function() {
    function getBaseUrl() {
        var link = document.querySelector('.navbar-nav a[href*="/dashboard"]');
        if (link) {
            var href = link.getAttribute('href');
            var idx = href.indexOf('/dashboard');
            if (idx > 0) return href.substring(0, idx);
        }
        return '';
    }

    function timeAgo(dateStr) {
        var now = new Date();
        var date = new Date(dateStr);
        var diff = Math.floor((now - date) / 1000);
        if (diff < 60) return 'Vừa xong';
        if (diff < 3600) return Math.floor(diff / 60) + ' phút trước';
        if (diff < 86400) return Math.floor(diff / 3600) + ' giờ trước';
        if (diff < 604800) return Math.floor(diff / 86400) + ' ngày trước';
        return date.toLocaleDateString('vi-VN');
    }

    function pollNotifications() {
        var baseUrl = getBaseUrl();
        fetch(baseUrl + '/notifications/unread', { headers: { 'Accept': 'application/json' } })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var badge = document.getElementById('notif-badge');
            var countHeader = document.getElementById('notif-count-header');
            var list = document.getElementById('notif-list');
            if (badge) { badge.textContent = data.count; badge.style.display = data.count > 0 ? '' : 'none'; }
            if (countHeader) countHeader.textContent = data.count > 0 ? data.count : '';
            if (list && data.notifications && data.notifications.length > 0) {
                var html = '';
                data.notifications.forEach(function(n) {
                    html += '<a href="' + baseUrl + '/notifications/' + n.id + '/read" class="d-flex align-items-start p-3 border-bottom text-decoration-none">';
                    html += '<div class="avatar-xs me-3 flex-shrink-0"><div class="avatar-title rounded-circle bg-light"><i class="ri-notification-3-line text-primary fs-16"></i></div></div>';
                    html += '<div class="flex-grow-1"><h6 class="mb-1 text-dark fs-13">' + (n.title || '') + '</h6>';
                    html += '<p class="mb-0 text-muted fs-12">' + (n.message || '') + '</p>';
                    html += '<small class="text-muted">' + timeAgo(n.created_at) + '</small></div></a>';
                });
                list.innerHTML = html;
            } else if (list) {
                list.innerHTML = '<div class="text-center py-4 text-muted"><i class="ri-notification-off-line fs-24 d-block mb-2"></i>Không có thông báo mới</div>';
            }
        }).catch(function() {});
    }
    setTimeout(pollNotifications, 2000);
    setInterval(pollNotifications, 30000);
})();

// Dropdown inside table-responsive fix
document.addEventListener('show.bs.dropdown', function(e) {
    var tableResp = e.target.closest('.table-responsive');
    if (!tableResp) return;
    var menu = e.target.nextElementSibling;
    if (!menu || !menu.classList.contains('dropdown-menu')) return;
    requestAnimationFrame(function() {
        var btnRect = e.target.getBoundingClientRect();
        var menuHeight = menu.scrollHeight || 150;
        var spaceBelow = window.innerHeight - btnRect.bottom;
        menu.style.position = 'fixed';
        menu.style.transform = 'none';
        menu.style.left = (btnRect.right - menu.offsetWidth) + 'px';
        menu.style.top = spaceBelow < menuHeight + 10
            ? (btnRect.top - menuHeight - 2) + 'px'
            : (btnRect.bottom + 2) + 'px';
    });
});

// Format money helper
function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN').format(amount) + ' đ';
}

// ============================================================
// Velzon Confirm Modal (data-confirm attribute on forms)
// ============================================================
(function() {
    var pendingForm = null;

    function getModalStyle(message) {
        var m = (message || '').toLowerCase();
        if (m.includes('xóa') || m.includes('delete'))
            return { icon: 'ri-delete-bin-line', color: 'danger', title: 'Xác nhận xóa' };
        if (m.includes('khóa') || m.includes('lock'))
            return { icon: 'ri-lock-line', color: 'warning', title: 'Xác nhận' };
        if (m.includes('hủy') || m.includes('cancel'))
            return { icon: 'ri-close-circle-line', color: 'danger', title: 'Xác nhận hủy' };
        if (m.includes('khôi phục') || m.includes('restore'))
            return { icon: 'ri-refresh-line', color: 'success', title: 'Khôi phục' };
        if (m.includes('duyệt') || m.includes('approve'))
            return { icon: 'ri-check-double-line', color: 'success', title: 'Xác nhận' };
        return { icon: 'ri-error-warning-line', color: 'warning', title: 'Xác nhận' };
    }

    // Intercept click on submit buttons inside forms with data-confirm
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('button[type="submit"], button:not([type])');
        if (!btn) return;

        var form = btn.closest('form[data-confirm]');
        if (!form) return;

        var message = form.getAttribute('data-confirm');
        if (!message) return;

        e.preventDefault();
        e.stopPropagation();

        var modalEl = document.getElementById('confirmModal');
        if (!modalEl) { form.removeAttribute('data-confirm'); form.submit(); return; }

        var style = getModalStyle(message);
        var iconEl = modalEl.querySelector('.modal-body > div:first-child i');
        var iconWrap = modalEl.querySelector('.modal-body > div:first-child');
        iconEl.className = style.icon;
        iconEl.style.fontSize = '80px';
        iconWrap.className = 'text-' + style.color + ' mb-4';
        document.getElementById('confirmTitle').textContent = style.title;
        document.getElementById('confirmMessage').textContent = message;
        var okBtn = document.getElementById('confirmOk');
        okBtn.className = 'btn w-sm btn-' + style.color;

        pendingForm = form;
        new bootstrap.Modal(modalEl).show();
    }, true);

    // OK button click
    var okBtn = document.getElementById('confirmOk');
    if (okBtn) {
        okBtn.addEventListener('click', function() {
            var modalEl = document.getElementById('confirmModal');
            if (modalEl) {
                var inst = bootstrap.Modal.getInstance(modalEl);
                if (inst) inst.hide();
            }
            if (pendingForm) {
                var form = pendingForm;
                pendingForm = null;
                form.removeAttribute('data-confirm');
                form.submit();
            }
        });
    }
})();

// Auto-dismiss flash alerts
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            try { bootstrap.Alert.getOrCreateInstance(alert).close(); } catch(e) {}
        }, 5000);
    });
});
