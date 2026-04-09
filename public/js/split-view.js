/**
 * ToryCRM Split View for Contact List
 * Slide-in panel showing contact detail on row click
 */
(function() {
    'use strict';

    var SPLIT_KEY = 'torycrm_split_view_enabled';
    var panel = null;
    var panelContent = null;
    var isOpen = false;

    function isContactListPage() {
        var path = window.location.pathname.replace(/\/+$/, '');
        return path === '/contacts' || path === '/contacts/';
    }

    if (!isContactListPage()) return;

    function isSplitEnabled() {
        return localStorage.getItem(SPLIT_KEY) !== 'false';
    }

    function createPanel() {
        if (panel) return;

        var style = document.createElement('style');
        style.textContent =
            '#split-view-panel{position:fixed;top:0;right:0;width:50%;max-width:700px;height:100%;background:var(--vz-card-bg,#fff);box-shadow:-4px 0 24px rgba(0,0,0,.15);z-index:10500;transform:translateX(100%);transition:transform .25s ease;overflow-y:auto;display:flex;flex-direction:column}' +
            '#split-view-panel.open{transform:translateX(0)}' +
            '#split-view-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.3);z-index:10499;display:none}' +
            '#split-view-overlay.open{display:block}' +
            '#split-view-panel .sv-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--vz-border-color,#e9ebec);position:sticky;top:0;background:var(--vz-card-bg,#fff);z-index:1}' +
            '#split-view-panel .sv-body{flex:1;padding:20px;overflow-y:auto}' +
            '#split-view-panel .sv-loading{text-align:center;padding:60px 20px;color:var(--vz-secondary-color,#878a99)}' +
            '.split-view-toggle{display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;color:var(--vz-body-color,#212529);user-select:none;padding:6px 12px;border-radius:6px;border:1px solid var(--vz-border-color,#e9ebec);background:var(--vz-card-bg,#fff)}' +
            '.split-view-toggle:hover{background:var(--vz-light,#f3f6f9)}' +
            '.split-view-toggle .form-check-input{margin:0}';

        document.head.appendChild(style);

        // Overlay
        var overlayEl = document.createElement('div');
        overlayEl.id = 'split-view-overlay';
        overlayEl.addEventListener('click', closePanel);
        document.body.appendChild(overlayEl);

        // Panel
        panel = document.createElement('div');
        panel.id = 'split-view-panel';
        panel.innerHTML =
            '<div class="sv-header">' +
                '<h5 class="mb-0">Chi tiết khách hàng</h5>' +
                '<button type="button" class="btn-close" id="split-view-close"></button>' +
            '</div>' +
            '<div class="sv-body" id="split-view-body">' +
                '<div class="sv-loading"><div class="spinner-border text-primary"></div></div>' +
            '</div>';
        document.body.appendChild(panel);

        panelContent = document.getElementById('split-view-body');

        document.getElementById('split-view-close').addEventListener('click', closePanel);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isOpen) {
                closePanel();
            }
        });
    }

    function openPanel(contactId) {
        createPanel();
        panelContent.innerHTML = '<div class="sv-loading"><div class="spinner-border text-primary"></div></div>';
        panel.classList.add('open');
        document.getElementById('split-view-overlay').classList.add('open');
        isOpen = true;

        fetch('/contacts/' + contactId + '?partial=1')
            .then(function(r) { return r.text(); })
            .then(function(html) {
                panelContent.innerHTML = html;
            })
            .catch(function() {
                panelContent.innerHTML = '<div class="sv-loading text-danger"><i class="ri-error-warning-line" style="font-size:36px"></i><div class="mt-2">Không thể tải thông tin</div></div>';
            });
    }

    function closePanel() {
        if (!panel) return;
        panel.classList.remove('open');
        document.getElementById('split-view-overlay').classList.remove('open');
        isOpen = false;
    }

    function addToggleButton() {
        // Check if toggle already exists in HTML
        var existing = document.getElementById('split-view-check');
        if (existing) {
            existing.checked = isSplitEnabled();
            existing.addEventListener('change', function() {
                localStorage.setItem(SPLIT_KEY, this.checked ? 'true' : 'false');
                // Update row cursors
                document.querySelectorAll('table tbody tr[data-id]').forEach(function(r) {
                    r.style.cursor = isSplitEnabled() ? 'pointer' : '';
                });
            });
            return;
        }

        // Fallback: create toggle if not in HTML
        var titleBox = document.querySelector('.page-title-box');
        if (!titleBox) return;

        var toggle = document.createElement('label');
        toggle.className = 'd-flex align-items-center gap-1 mb-0';
        toggle.style.cursor = 'pointer';
        toggle.innerHTML =
            '<input type="checkbox" class="form-check-input m-0" id="split-view-check"' + (isSplitEnabled() ? ' checked' : '') + '>' +
            '<span class="fs-13">Split View</span>';

        var rightSide = titleBox.querySelector('.page-title-right, .d-flex.gap-2');
        if (rightSide) {
            rightSide.insertBefore(toggle, rightSide.firstChild);
        } else {
            titleBox.appendChild(toggle);
        }

        document.getElementById('split-view-check').addEventListener('change', function() {
            localStorage.setItem(SPLIT_KEY, this.checked ? 'true' : 'false');
        });
    }

    function bindRows() {
        var rows = document.querySelectorAll('table tbody tr[data-id], table tbody tr');
        rows.forEach(function(row) {
            row.addEventListener('click', function(e) {
                if (!isSplitEnabled()) return;
                // Don't trigger on action buttons, checkboxes, links
                if (e.target.closest('a, button, .form-check, .dropdown, input, select, [data-inline-edit], .form-select, .form-control')) return;

                var id = row.getAttribute('data-id');
                if (!id) {
                    // Try to find link in the row
                    var link = row.querySelector('a[href*="/contacts/"]');
                    if (link) {
                        var match = link.href.match(/\/contacts\/(\d+)/);
                        if (match) id = match[1];
                    }
                }
                if (id) {
                    e.preventDefault();
                    openPanel(id);
                }
            });

            if (isSplitEnabled()) {
                row.style.cursor = 'pointer';
            }
        });
    }

    addToggleButton();
    bindRows();

})();
