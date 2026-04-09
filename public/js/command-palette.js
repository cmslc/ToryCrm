/**
 * ToryCRM Command Palette (Ctrl+K)
 * Spotlight-style search overlay with fuzzy matching
 */
(function() {
    'use strict';

    var RECENT_KEY = 'torycrm_recent_searches';
    var MAX_RECENT = 5;
    var overlay = null;
    var input = null;
    var resultsList = null;
    var selectedIndex = -1;
    var results = [];
    var debounceTimer = null;

    // Static navigation items
    var navItems = [
        { title: 'Dashboard', url: '/dashboard', icon: 'ri-dashboard-2-line', category: 'Điều hướng' },
        { title: 'Hộp thư', url: '/conversations', icon: 'ri-chat-1-line', category: 'Điều hướng' },
        { title: 'Khách hàng', url: '/contacts', icon: 'ri-contacts-line', category: 'Điều hướng' },
        { title: 'Doanh nghiệp', url: '/companies', icon: 'ri-building-line', category: 'Điều hướng' },
        { title: 'Cơ hội', url: '/deals', icon: 'ri-hand-coin-line', category: 'Điều hướng' },
        { title: 'Pipeline', url: '/deals/pipeline', icon: 'ri-git-branch-line', category: 'Điều hướng' },
        { title: 'Đơn hàng bán', url: '/orders', icon: 'ri-file-list-3-line', category: 'Điều hướng' },
        { title: 'Đơn hàng mua', url: '/purchase-orders', icon: 'ri-shopping-cart-line', category: 'Điều hướng' },
        { title: 'Sản phẩm', url: '/products', icon: 'ri-shopping-bag-line', category: 'Điều hướng' },
        { title: 'Công việc', url: '/tasks', icon: 'ri-task-line', category: 'Điều hướng' },
        { title: 'Lịch hẹn', url: '/calendar', icon: 'ri-calendar-2-line', category: 'Điều hướng' },
        { title: 'Ticket', url: '/tickets', icon: 'ri-customer-service-line', category: 'Điều hướng' },
        { title: 'Chiến dịch', url: '/campaigns', icon: 'ri-megaphone-line', category: 'Điều hướng' },
        { title: 'Hoạt động', url: '/activities', icon: 'ri-history-line', category: 'Điều hướng' },
        { title: 'Báo cáo', url: '/reports', icon: 'ri-bar-chart-box-line', category: 'Điều hướng' },
        { title: 'Quỹ', url: '/fund', icon: 'ri-wallet-3-line', category: 'Điều hướng' },
        { title: 'Cài đặt', url: '/settings', icon: 'ri-settings-3-line', category: 'Điều hướng' },
        { title: 'Check-in', url: '/checkins', icon: 'ri-map-pin-user-line', category: 'Điều hướng' },
        { title: 'Workflow', url: '/workflows', icon: 'ri-flow-chart', category: 'Điều hướng' },
        { title: 'Người dùng', url: '/users', icon: 'ri-group-line', category: 'Điều hướng' },
    ];

    var actionItems = [
        { title: 'Tạo khách hàng', url: '/contacts/create', icon: 'ri-user-add-line', category: 'Hành động' },
        { title: 'Tạo cơ hội', url: '/deals/create', icon: 'ri-add-circle-line', category: 'Hành động' },
        { title: 'Tạo công việc', url: '/tasks/create', icon: 'ri-add-box-line', category: 'Hành động' },
        { title: 'Tạo đơn hàng', url: '/orders/create', icon: 'ri-file-add-line', category: 'Hành động' },
        { title: 'Tạo ticket', url: '/tickets/create', icon: 'ri-coupon-line', category: 'Hành động' },
        { title: 'Tạo chiến dịch', url: '/campaigns/create', icon: 'ri-megaphone-line', category: 'Hành động' },
        { title: 'Tạo lịch hẹn', url: '/calendar/create', icon: 'ri-calendar-event-line', category: 'Hành động' },
        { title: 'Import / Export', url: '/import-export', icon: 'ri-upload-cloud-line', category: 'Hành động' },
    ];

    function fuzzyMatch(text, query) {
        text = text.toLowerCase();
        query = query.toLowerCase();
        if (text.indexOf(query) !== -1) return true;
        var qi = 0;
        for (var i = 0; i < text.length && qi < query.length; i++) {
            if (text[i] === query[qi]) qi++;
        }
        return qi === query.length;
    }

    function getRecent() {
        try {
            return JSON.parse(localStorage.getItem(RECENT_KEY)) || [];
        } catch(e) { return []; }
    }

    function saveRecent(item) {
        var recent = getRecent().filter(function(r) { return r.url !== item.url; });
        recent.unshift({ title: item.title, url: item.url, icon: item.icon || 'ri-time-line' });
        if (recent.length > MAX_RECENT) recent = recent.slice(0, MAX_RECENT);
        localStorage.setItem(RECENT_KEY, JSON.stringify(recent));
    }

    function createOverlay() {
        if (overlay) return;

        overlay = document.createElement('div');
        overlay.id = 'command-palette-overlay';
        overlay.innerHTML =
            '<div id="command-palette-modal">' +
                '<div id="command-palette-search">' +
                    '<i class="ri-search-line"></i>' +
                    '<input type="text" id="command-palette-input" placeholder="Tìm kiếm trang, hành động, khách hàng, cơ hội..." autocomplete="off">' +
                    '<kbd>ESC</kbd>' +
                '</div>' +
                '<div id="command-palette-results"></div>' +
            '</div>';

        var style = document.createElement('style');
        style.textContent =
            '#command-palette-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:99999;display:flex;align-items:flex-start;justify-content:center;padding-top:15vh;backdrop-filter:blur(4px);opacity:0;transition:opacity .15s}' +
            '#command-palette-overlay.show{opacity:1}' +
            '#command-palette-modal{background:var(--vz-card-bg,#fff);border-radius:16px;width:100%;max-width:640px;box-shadow:0 25px 60px rgba(0,0,0,.3);overflow:hidden;transform:translateY(-10px);transition:transform .15s}' +
            '#command-palette-overlay.show #command-palette-modal{transform:translateY(0)}' +
            '#command-palette-search{display:flex;align-items:center;padding:16px 20px;border-bottom:1px solid var(--vz-border-color,#e9ebec);gap:12px}' +
            '#command-palette-search i{font-size:22px;color:var(--vz-secondary-color,#878a99);flex-shrink:0}' +
            '#command-palette-input{flex:1;border:none;outline:none;font-size:16px;background:transparent;color:var(--vz-body-color,#212529)}' +
            '#command-palette-input::placeholder{color:var(--vz-secondary-color,#878a99)}' +
            '#command-palette-search kbd{background:var(--vz-light,#f3f6f9);border:1px solid var(--vz-border-color,#e9ebec);border-radius:4px;padding:2px 8px;font-size:12px;color:var(--vz-secondary-color,#878a99);flex-shrink:0}' +
            '#command-palette-results{max-height:400px;overflow-y:auto;padding:8px 0}' +
            '#command-palette-results:empty{display:none}' +
            '.cp-group-title{padding:8px 20px 4px;font-size:11px;font-weight:600;text-transform:uppercase;color:var(--vz-secondary-color,#878a99);letter-spacing:.5px}' +
            '.cp-item{display:flex;align-items:center;padding:10px 20px;cursor:pointer;gap:12px;transition:background .1s}' +
            '.cp-item:hover,.cp-item.active{background:var(--vz-light,#f3f6f9)}' +
            '.cp-item i{font-size:18px;color:var(--vz-primary,#405189);width:24px;text-align:center;flex-shrink:0}' +
            '.cp-item-content{flex:1;min-width:0}' +
            '.cp-item-title{font-size:14px;font-weight:500;color:var(--vz-body-color,#212529);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}' +
            '.cp-item-subtitle{font-size:12px;color:var(--vz-secondary-color,#878a99);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}' +
            '.cp-badge{font-size:10px;padding:2px 8px;border-radius:4px;background:var(--vz-primary-bg-subtle,#dae1f3);color:var(--vz-primary,#405189);flex-shrink:0;font-weight:500}' +
            '.cp-empty{text-align:center;padding:32px 20px;color:var(--vz-secondary-color,#878a99)}.cp-empty i{font-size:36px;display:block;margin-bottom:8px}' +
            '.cp-hint{display:flex;gap:16px;justify-content:center;padding:10px;border-top:1px solid var(--vz-border-color,#e9ebec);font-size:12px;color:var(--vz-secondary-color,#878a99)}' +
            '.cp-hint kbd{background:var(--vz-light,#f3f6f9);border:1px solid var(--vz-border-color,#e9ebec);border-radius:3px;padding:1px 5px;font-size:11px;margin:0 2px}';

        document.head.appendChild(style);
        document.body.appendChild(overlay);

        input = document.getElementById('command-palette-input');
        resultsList = document.getElementById('command-palette-results');

        // Events
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closePalette();
        });

        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            var q = input.value.trim();
            if (q.length === 0) {
                showRecent();
                return;
            }
            // Immediate local results
            renderResults(q, [], []);
            // Debounced AJAX
            if (q.length >= 2) {
                debounceTimer = setTimeout(function() { fetchRemote(q); }, 300);
            }
        });

        input.addEventListener('keydown', function(e) {
            var items = resultsList.querySelectorAll('.cp-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, 0);
                updateSelection(items);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    items[selectedIndex].click();
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closePalette();
            }
        });
    }

    function updateSelection(items) {
        for (var i = 0; i < items.length; i++) {
            items[i].classList.toggle('active', i === selectedIndex);
        }
        if (items[selectedIndex]) {
            items[selectedIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    function showRecent() {
        var recent = getRecent();
        results = [];
        selectedIndex = -1;
        var html = '';
        if (recent.length > 0) {
            html += '<div class="cp-group-title">Tìm kiếm gần đây</div>';
            recent.forEach(function(item, idx) {
                results.push(item);
                html += buildItem(item, idx);
            });
        } else {
            html = '<div class="cp-empty"><i class="ri-search-line"></i>Nhập để tìm kiếm trang, hành động, khách hàng...</div>';
        }
        resultsList.innerHTML = html;
        bindClicks();
    }

    function fetchRemote(q) {
        fetch('/search?q=' + encodeURIComponent(q) + '&format=json')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var contacts = (data.contacts || []).map(function(c) {
                    return {
                        title: (c.first_name || '') + ' ' + (c.last_name || ''),
                        subtitle: [c.email, c.phone].filter(Boolean).join(' · '),
                        url: '/contacts/' + c.id,
                        icon: 'ri-user-line',
                        category: 'Khách hàng'
                    };
                });
                var deals = (data.deals || []).map(function(d) {
                    return {
                        title: d.title,
                        subtitle: d.stage_name || '',
                        url: '/deals/' + d.id,
                        icon: 'ri-hand-coin-line',
                        category: 'Cơ hội'
                    };
                });
                renderResults(q, contacts, deals);
            })
            .catch(function() {});
    }

    function renderResults(q, remoteContacts, remoteDeals) {
        results = [];
        selectedIndex = -1;
        var html = '';
        var idx = 0;

        // Navigation
        var navMatches = navItems.filter(function(n) { return fuzzyMatch(n.title, q); });
        if (navMatches.length > 0) {
            html += '<div class="cp-group-title">Điều hướng</div>';
            navMatches.slice(0, 5).forEach(function(item) {
                results.push(item);
                html += buildItem(item, idx++);
            });
        }

        // Actions
        var actMatches = actionItems.filter(function(a) { return fuzzyMatch(a.title, q); });
        if (actMatches.length > 0) {
            html += '<div class="cp-group-title">Hành động</div>';
            actMatches.slice(0, 5).forEach(function(item) {
                results.push(item);
                html += buildItem(item, idx++);
            });
        }

        // Remote contacts
        if (remoteContacts && remoteContacts.length > 0) {
            html += '<div class="cp-group-title">Khách hàng</div>';
            remoteContacts.slice(0, 5).forEach(function(item) {
                results.push(item);
                html += buildItem(item, idx++);
            });
        }

        // Remote deals
        if (remoteDeals && remoteDeals.length > 0) {
            html += '<div class="cp-group-title">Cơ hội</div>';
            remoteDeals.slice(0, 5).forEach(function(item) {
                results.push(item);
                html += buildItem(item, idx++);
            });
        }

        if (html === '') {
            html = '<div class="cp-empty"><i class="ri-search-line"></i>Không tìm thấy kết quả cho "' + escapeHtml(q) + '"</div>';
        }

        resultsList.innerHTML = html;
        bindClicks();
    }

    function buildItem(item, idx) {
        return '<div class="cp-item" data-index="' + idx + '">' +
            '<i class="' + (item.icon || 'ri-arrow-right-line') + '"></i>' +
            '<div class="cp-item-content">' +
                '<div class="cp-item-title">' + escapeHtml(item.title) + '</div>' +
                (item.subtitle ? '<div class="cp-item-subtitle">' + escapeHtml(item.subtitle) + '</div>' : '') +
            '</div>' +
            (item.category ? '<span class="cp-badge">' + escapeHtml(item.category) + '</span>' : '') +
        '</div>';
    }

    function bindClicks() {
        var items = resultsList.querySelectorAll('.cp-item');
        items.forEach(function(el) {
            el.addEventListener('click', function() {
                var i = parseInt(el.getAttribute('data-index'));
                var item = results[i];
                if (item && item.url) {
                    saveRecent(item);
                    window.location.href = item.url;
                }
            });
            el.addEventListener('mouseenter', function() {
                selectedIndex = parseInt(el.getAttribute('data-index'));
                updateSelection(items);
            });
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    function openPalette() {
        createOverlay();
        overlay.style.display = 'flex';
        requestAnimationFrame(function() {
            overlay.classList.add('show');
        });
        input.value = '';
        showRecent();
        setTimeout(function() { input.focus(); }, 50);
    }

    function closePalette() {
        if (!overlay) return;
        overlay.classList.remove('show');
        setTimeout(function() {
            overlay.style.display = 'none';
        }, 150);
    }

    // Global Ctrl+K listener
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (overlay && overlay.style.display === 'flex') {
                closePalette();
            } else {
                openPalette();
            }
        }
    });

    // Intercept search bar click in header
    document.addEventListener('click', function(e) {
        var searchInput = e.target.closest('.app-search input');
        if (searchInput) {
            e.preventDefault();
            searchInput.blur();
            openPalette();
        }
    });

    // Intercept search form submit
    document.addEventListener('submit', function(e) {
        var form = e.target.closest('.app-search');
        if (form) {
            e.preventDefault();
            openPalette();
        }
    });

})();
