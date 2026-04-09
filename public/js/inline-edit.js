/**
 * ToryCRM Inline Edit
 *
 * Usage:
 *   <span data-inline-edit data-url="/contacts/5/quick-update" data-field="status"
 *         data-type="select" data-options='{"new":"Mới","contacted":"Đã liên hệ"}'
 *         data-value="new">
 *       <span class="badge bg-info-subtle text-info">Mới</span>
 *   </span>
 *
 * Supported types: text, select, user
 */
(function () {
    'use strict';

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    // Preload users for user-type fields
    var usersCache = null;
    function getUsers(callback) {
        if (usersCache) return callback(usersCache);
        // Users are injected as a global if available, otherwise empty
        if (window.__inlineEditUsers) {
            usersCache = window.__inlineEditUsers;
            return callback(usersCache);
        }
        callback([]);
    }

    function createInput(el) {
        var type = el.dataset.type || 'text';
        var currentValue = el.dataset.value || '';
        var input;

        if (type === 'select') {
            input = document.createElement('select');
            input.className = 'form-select';
            input.style.minWidth = '140px';
            input.style.maxWidth = '200px';

            try {
                var options = JSON.parse(el.dataset.options || '{}');
                Object.keys(options).forEach(function (key) {
                    var opt = document.createElement('option');
                    opt.value = key;
                    opt.textContent = options[key];
                    if (key === currentValue) opt.selected = true;
                    input.appendChild(opt);
                });
            } catch (e) { /* ignore parse errors */ }

        } else if (type === 'user') {
            // Searchable user dropdown
            input = document.createElement('div');
            input.className = 'position-relative';
            input.style.minWidth = '180px';
            input.style.maxWidth = '220px';
            input._isCustom = true;

            var searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.className = 'form-control';
            searchInput.placeholder = 'Tìm người...';
            searchInput.autocomplete = 'off';
            input.appendChild(searchInput);

            var dropdown = document.createElement('div');
            dropdown.className = 'border rounded bg-white shadow-sm mt-1';
            dropdown.style.cssText = 'position:absolute;z-index:1060;width:100%;max-height:200px;overflow-y:auto;display:none';
            input.appendChild(dropdown);

            getUsers(function (users) {
                input._users = users;
                input._selectedValue = '';

                function renderList(q) {
                    dropdown.innerHTML = '';
                    var filtered = users.filter(function(u) {
                        return !q || u.name.toLowerCase().indexOf(q.toLowerCase()) !== -1;
                    });
                    if (filtered.length === 0) {
                        dropdown.innerHTML = '<div class="px-3 py-2 text-muted">Không tìm thấy</div>';
                    }
                    filtered.forEach(function(u) {
                        var item = document.createElement('div');
                        item.className = 'px-3 py-2';
                        item.style.cursor = 'pointer';
                        item.textContent = u.name;
                        item.dataset.value = u.id;
                        item.addEventListener('mouseenter', function() { this.style.backgroundColor = '#f3f6f9'; });
                        item.addEventListener('mouseleave', function() { this.style.backgroundColor = ''; });
                        item.addEventListener('mousedown', function(e) {
                            e.preventDefault();
                            input._selectedValue = u.id;
                            searchInput.value = u.name;
                            dropdown.style.display = 'none';
                            // Trigger change
                            var evt = new Event('change', {bubbles: true});
                            searchInput.dispatchEvent(evt);
                        });
                        dropdown.appendChild(item);
                    });
                    dropdown.style.display = filtered.length > 0 || q ? 'block' : 'none';
                }

                searchInput.addEventListener('focus', function() { renderList(this.value); });
                searchInput.addEventListener('input', function() { renderList(this.value); });
                searchInput.addEventListener('blur', function() { setTimeout(function() { dropdown.style.display = 'none'; }, 200); });

                // Set initial value
                var current = users.find(function(u) { return String(u.id) === String(currentValue); });
                if (current) {
                    searchInput.value = current.name;
                    input._selectedValue = current.id;
                }

                setTimeout(function() { searchInput.focus(); }, 50);
            });

        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control';
            input.value = currentValue;
            input.style.minWidth = '120px';
        }

        return input;
    }

    function flashSuccess(el) {
        el.style.transition = 'background-color 0.3s';
        el.style.backgroundColor = 'rgba(25, 135, 84, 0.15)';
        setTimeout(function () {
            el.style.backgroundColor = '';
        }, 800);
    }

    function showToastError(message) {
        if (typeof Toastify !== 'undefined') {
            Toastify({
                text: message || 'Lỗi cập nhật',
                duration: 3000,
                gravity: 'top',
                position: 'right',
                className: 'bg-danger'
            }).showToast();
        } else {
            console.error(message);
        }
    }

    function initInlineEdit() {
        document.querySelectorAll('[data-inline-edit]').forEach(function (el) {
            if (el._inlineEditBound) return;
            el._inlineEditBound = true;

            el.style.cursor = 'pointer';

            el.addEventListener('click', function (e) {
                // Don't trigger if already editing
                if (el.querySelector('input, select, [data-value]')) return;
                e.preventDefault();
                e.stopPropagation();

                var originalHTML = el.innerHTML;
                var originalValue = el.dataset.value || '';

                var input = createInput(el);

                el.innerHTML = '';
                el.appendChild(input);
                input.focus();

                function cancel() {
                    el.innerHTML = originalHTML;
                }

                function save() {
                    var newValue = input._isCustom ? (input._selectedValue || '') : input.value;
                    if (newValue === originalValue || newValue === '') {
                        cancel();
                        return;
                    }

                    // Show spinner
                    el.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                    var url = el.dataset.url;
                    var field = el.dataset.field;

                    var body = new FormData();
                    body.append('_token', getCsrfToken());
                    body.append('field', field);
                    body.append('value', newValue);

                    fetch(url, {
                        method: 'POST',
                        body: body
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            el.dataset.value = data.value || newValue;

                            if (data.display) {
                                el.innerHTML = data.display;
                            } else {
                                cancel();
                            }

                            flashSuccess(el);
                        } else {
                            showToastError(data.error || 'Lỗi cập nhật');
                            cancel();
                        }
                    })
                    .catch(function () {
                        showToastError('Lỗi kết nối');
                        cancel();
                    });
                }

                input.addEventListener('keydown', function (evt) {
                    if (evt.key === 'Escape') {
                        cancel();
                    } else if (evt.key === 'Enter' && input.tagName === 'INPUT') {
                        save();
                    }
                });

                if (input.tagName === 'SELECT') {
                    input.addEventListener('change', save);
                } else if (input._isCustom) {
                    // Custom searchable dropdown - listen for change on inner input
                    var innerInput = input.querySelector('input');
                    if (innerInput) {
                        innerInput.addEventListener('change', save);
                        innerInput.addEventListener('keydown', function(evt) {
                            if (evt.key === 'Escape') cancel();
                        });
                        innerInput.addEventListener('blur', function() {
                            setTimeout(function() { if (!input._selectedValue || input._selectedValue === originalValue) cancel(); }, 300);
                        });
                    }
                }

                if (!input._isCustom) {
                    input.addEventListener('blur', function () {
                        setTimeout(function () {
                            if (el.querySelector('input, select')) {
                                cancel();
                            }
                        }, 200);
                    });
                }
            });
        });
    }

    // Init on DOMContentLoaded and also expose for dynamic content
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initInlineEdit);
    } else {
        initInlineEdit();
    }

    window.initInlineEdit = initInlineEdit;
})();
