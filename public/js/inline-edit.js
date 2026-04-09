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
            input = document.createElement('select');
            input.className = 'form-select';
            input.style.minWidth = '140px';

            var emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.textContent = '-- Chọn --';
            input.appendChild(emptyOpt);

            getUsers(function (users) {
                users.forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    if (String(u.id) === String(currentValue)) opt.selected = true;
                    input.appendChild(opt);
                });
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
                if (el.querySelector('input, select')) return;
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
                    var newValue = input.value;
                    if (newValue === originalValue) {
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
                }

                input.addEventListener('blur', function () {
                    // Small delay to allow change event to fire first
                    setTimeout(function () {
                        if (el.querySelector('input, select')) {
                            cancel();
                        }
                    }, 200);
                });
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
