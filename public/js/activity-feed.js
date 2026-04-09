/**
 * ToryCRM Activity Feed Sidebar
 * Floating button with slide-in activity panel
 */
(function() {
    'use strict';

    var feedPanel = null;
    var feedList = null;
    var badge = null;
    var isOpen = false;
    var refreshInterval = null;
    var lastCount = 0;

    function init() {
        var style = document.createElement('style');
        style.textContent =
            '#af-btn{position:fixed;bottom:24px;right:24px;width:52px;height:52px;border-radius:50%;background:var(--vz-primary,#405189);color:#fff;border:none;box-shadow:0 4px 16px rgba(0,0,0,.2);cursor:pointer;z-index:10400;display:flex;align-items:center;justify-content:center;transition:transform .2s}' +
            '#af-btn:hover{transform:scale(1.08)}' +
            '#af-btn i{font-size:24px}' +
            '#af-badge{position:absolute;top:-2px;right:-2px;min-width:20px;height:20px;border-radius:10px;background:#f06548;color:#fff;font-size:11px;font-weight:600;display:none;align-items:center;justify-content:center;padding:0 5px}' +
            '#af-badge.show{display:flex}' +
            '#af-panel{position:fixed;top:0;right:0;width:380px;max-width:100%;height:100%;background:var(--vz-card-bg,#fff);box-shadow:-4px 0 24px rgba(0,0,0,.15);z-index:10500;transform:translateX(100%);transition:transform .25s ease;display:flex;flex-direction:column}' +
            '#af-panel.open{transform:translateX(0)}' +
            '#af-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.3);z-index:10499;display:none}' +
            '#af-overlay.open{display:block}' +
            '.af-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--vz-border-color,#e9ebec)}' +
            '.af-header h5{margin:0;font-size:16px;font-weight:600}' +
            '.af-body{flex:1;overflow-y:auto;padding:0}' +
            '.af-item{display:flex;gap:12px;padding:14px 20px;border-bottom:1px solid var(--vz-border-color,#e9ebec);cursor:pointer;transition:background .15s}' +
            '.af-item:hover{background:var(--vz-light,#f3f6f9)}' +
            '.af-avatar{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:14px;flex-shrink:0;color:#fff}' +
            '.af-content{flex:1;min-width:0}' +
            '.af-user{font-weight:500;font-size:13px;color:var(--vz-body-color,#212529)}' +
            '.af-desc{font-size:13px;color:var(--vz-secondary-color,#878a99);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}' +
            '.af-time{font-size:11px;color:var(--vz-secondary-color,#878a99);margin-top:2px}' +
            '.af-empty{text-align:center;padding:48px 20px;color:var(--vz-secondary-color,#878a99)}.af-empty i{font-size:36px;display:block;margin-bottom:8px}' +
            '.af-loading{text-align:center;padding:48px 20px}';

        document.head.appendChild(style);

        // Floating button
        var btn = document.createElement('button');
        btn.id = 'af-btn';
        btn.title = 'Hoạt động';
        btn.innerHTML = '<i class="ri-pulse-line"></i><span id="af-badge">0</span>';
        document.body.appendChild(btn);
        badge = document.getElementById('af-badge');

        // Overlay
        var overlayEl = document.createElement('div');
        overlayEl.id = 'af-overlay';
        overlayEl.addEventListener('click', closePanel);
        document.body.appendChild(overlayEl);

        // Panel
        feedPanel = document.createElement('div');
        feedPanel.id = 'af-panel';
        feedPanel.innerHTML =
            '<div class="af-header">' +
                '<h5>Hoạt động</h5>' +
                '<button type="button" class="btn-close" id="af-close"></button>' +
            '</div>' +
            '<div class="af-body" id="af-list">' +
                '<div class="af-loading"><div class="spinner-border text-primary"></div></div>' +
            '</div>';
        document.body.appendChild(feedPanel);
        feedList = document.getElementById('af-list');

        btn.addEventListener('click', togglePanel);
        document.getElementById('af-close').addEventListener('click', closePanel);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isOpen) closePanel();
        });

        // Initial load + auto refresh
        loadFeed();
        refreshInterval = setInterval(loadFeed, 60000);
    }

    var avatarColors = ['#405189','#0ab39c','#f06548','#f7b84b','#299cdb','#6559cc'];

    function getColor(name) {
        var sum = 0;
        for (var i = 0; i < (name || '').length; i++) sum += name.charCodeAt(i);
        return avatarColors[sum % avatarColors.length];
    }

    function loadFeed() {
        fetch('/activities/feed?format=json')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var activities = data.activities || [];
                lastCount = data.new_count || activities.length;
                updateBadge(lastCount);
                renderFeed(activities);
            })
            .catch(function() {});
    }

    function updateBadge(count) {
        if (count > 0 && !isOpen) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.add('show');
        } else {
            badge.classList.remove('show');
        }
    }

    function renderFeed(activities) {
        if (!activities.length) {
            feedList.innerHTML = '<div class="af-empty"><i class="ri-history-line"></i>Chưa có hoạt động nào</div>';
            return;
        }

        var html = '';
        activities.forEach(function(a) {
            var initial = (a.user_name || 'U').charAt(0).toUpperCase();
            var color = getColor(a.user_name);
            var link = '#';
            if (a.contact_id) link = '/contacts/' + a.contact_id;
            else if (a.deal_id) link = '/deals/' + a.deal_id;
            else if (a.company_id) link = '/companies/' + a.company_id;

            html += '<div class="af-item" data-href="' + link + '">' +
                '<div class="af-avatar" style="background:' + color + '">' + initial + '</div>' +
                '<div class="af-content">' +
                    '<div class="af-user">' + escapeHtml(a.user_name || 'Hệ thống') + '</div>' +
                    '<div class="af-desc">' + escapeHtml(a.title || '') + '</div>' +
                    '<div class="af-time">' + escapeHtml(a.time_ago || a.created_at || '') + '</div>' +
                '</div>' +
            '</div>';
        });

        feedList.innerHTML = html;

        feedList.querySelectorAll('.af-item').forEach(function(el) {
            el.addEventListener('click', function() {
                var href = el.getAttribute('data-href');
                if (href && href !== '#') {
                    window.location.href = href;
                }
            });
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    function togglePanel() {
        if (isOpen) closePanel();
        else openPanel();
    }

    function openPanel() {
        feedPanel.classList.add('open');
        document.getElementById('af-overlay').classList.add('open');
        isOpen = true;
        badge.classList.remove('show');
        loadFeed();
    }

    function closePanel() {
        feedPanel.classList.remove('open');
        document.getElementById('af-overlay').classList.remove('open');
        isOpen = false;
    }

    init();

})();
