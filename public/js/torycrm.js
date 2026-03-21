/**
 * ToryCRM - Custom JavaScript
 * Notification polling, dropdown fixes, custom behaviors
 */

// Notification polling - every 30 seconds
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

            if (badge && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = '';
            } else if (badge) {
                badge.style.display = 'none';
            }

            if (countHeader) countHeader.textContent = data.count > 0 ? data.count : '';

            if (list && data.notifications && data.notifications.length > 0) {
                var html = '';
                data.notifications.forEach(function(n) {
                    html += '<a href="' + baseUrl + '/notifications/' + n.id + '/read" class="d-flex align-items-start p-3 border-bottom text-decoration-none">';
                    html += '  <div class="avatar-xs me-3 flex-shrink-0"><div class="avatar-title rounded-circle bg-light"><i class="ri-notification-3-line text-primary fs-16"></i></div></div>';
                    html += '  <div class="flex-grow-1"><h6 class="mb-1 text-dark fs-13">' + (n.title || '') + '</h6>';
                    html += '    <p class="mb-0 text-muted fs-12">' + (n.message || '') + '</p>';
                    html += '    <small class="text-muted">' + timeAgo(n.created_at) + '</small></div></a>';
                });
                list.innerHTML = html;
            } else if (list) {
                list.innerHTML = '<div class="text-center py-4 text-muted"><i class="ri-notification-off-line fs-24 d-block mb-2"></i>Không có thông báo mới</div>';
            }
        })
        .catch(function() {});
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
// Velzon Confirm Modal - replaces browser confirm()
// ============================================================
(function() {
    var pendingForm = null;

    function showConfirmModal(message, onConfirm) {
        var modalEl = document.getElementById('confirmModal');
        if (!modalEl) { if (window.confirm(message)) onConfirm(); return; }

        var icon = modalEl.querySelector('.modal-body > div:first-child i');
        var iconWrap = modalEl.querySelector('.modal-body > div:first-child');
        var title = document.getElementById('confirmTitle');
        var msg = document.getElementById('confirmMessage');
        var okBtn = document.getElementById('confirmOk');

        var lowerMsg = message.toLowerCase();
        var isDelete = lowerMsg.includes('xóa') || lowerMsg.includes('delete');
        var isLock = lowerMsg.includes('khóa') || lowerMsg.includes('lock');
        var isCancel = lowerMsg.includes('hủy') || lowerMsg.includes('cancel');
        var isRestore = lowerMsg.includes('khôi phục') || lowerMsg.includes('restore');
        var isConfirm = lowerMsg.includes('xác nhận') || lowerMsg.includes('duyệt');

        if (isDelete) {
            icon.className = 'ri-delete-bin-line'; iconWrap.className = 'text-danger mb-4';
            okBtn.className = 'btn w-sm btn-danger'; title.textContent = 'Xác nhận xóa';
        } else if (isLock) {
            icon.className = 'ri-lock-line'; iconWrap.className = 'text-warning mb-4';
            okBtn.className = 'btn w-sm btn-warning'; title.textContent = 'Xác nhận';
        } else if (isCancel) {
            icon.className = 'ri-close-circle-line'; iconWrap.className = 'text-danger mb-4';
            okBtn.className = 'btn w-sm btn-danger'; title.textContent = 'Xác nhận hủy';
        } else if (isRestore) {
            icon.className = 'ri-refresh-line'; iconWrap.className = 'text-success mb-4';
            okBtn.className = 'btn w-sm btn-success'; title.textContent = 'Khôi phục';
        } else if (isConfirm) {
            icon.className = 'ri-check-double-line'; iconWrap.className = 'text-success mb-4';
            okBtn.className = 'btn w-sm btn-success'; title.textContent = 'Xác nhận';
        } else {
            icon.className = 'ri-error-warning-line'; iconWrap.className = 'text-warning mb-4';
            okBtn.className = 'btn w-sm btn-primary'; title.textContent = 'Xác nhận';
        }

        msg.textContent = message;
        pendingForm = onConfirm;

        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    // Intercept form submit with confirm()
    document.addEventListener('submit', function(e) {
        var form = e.target;
        var onsubmit = form.getAttribute('onsubmit');
        if (!onsubmit || !onsubmit.includes('confirm(')) return;

        e.preventDefault();
        e.stopPropagation();

        var match = onsubmit.match(/confirm\(['"](.+?)['"]\)/);
        var message = match ? match[1] : 'Bạn có chắc chắn?';

        showConfirmModal(message, function() {
            form.removeAttribute('onsubmit');
            form.submit();
        });
    }, true);

    // Handle confirm OK click
    document.addEventListener('click', function(e) {
        if (e.target.id === 'confirmOk' || e.target.closest('#confirmOk')) {
            if (typeof pendingForm === 'function') {
                pendingForm();
                pendingForm = null;
            }
            var modalEl = document.getElementById('confirmModal');
            if (modalEl) {
                var inst = bootstrap.Modal.getInstance(modalEl);
                if (inst) inst.hide();
            }
        }
    });
})();

// Auto-dismiss flash alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
});
