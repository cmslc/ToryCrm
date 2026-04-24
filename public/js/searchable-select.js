/**
 * ToryCRM Searchable Select
 *
 * Auto-converts any <select> with class "searchable-select" into
 * a dropdown with search input. Supports optgroup.
 *
 * Usage: <select name="owner_id" class="form-select searchable-select">
 */
(function() {
    'use strict';

    function init() {
        document.querySelectorAll('select.searchable-select').forEach(function(sel) {
            if (sel.dataset.searchInit) return;
            sel.dataset.searchInit = '1';
            convert(sel);
        });
    }

    function convert(sel) {
        // Collect options with groups
        var items = [];
        var selectedValue = sel.value;
        var selectedText = '';
        var placeholderText = '';

        function addOption(opt, group) {
            var item = { value: opt.value, text: opt.textContent.trim(), group: group || null, avatar: opt.dataset.avatar || '' };
            items.push(item);
            if (opt.value === '' && !placeholderText) placeholderText = opt.textContent.trim();
            if (opt.value === selectedValue && opt.value !== '') { selectedText = opt.textContent.trim(); }
        }

        for (var i = 0; i < sel.children.length; i++) {
            var child = sel.children[i];
            if (child.tagName.toUpperCase() === 'OPTGROUP') {
                var groupName = child.label || '';
                for (var j = 0; j < child.children.length; j++) {
                    addOption(child.children[j], groupName);
                }
            } else if (child.tagName.toUpperCase() === 'OPTION') {
                addOption(child, null);
            }
        }

        // Hide original select
        sel.style.display = 'none';

        // Create wrapper - match native select sizing
        var wrapper = document.createElement('div');
        var hasAutoWidth = sel.style.width === 'auto';
        wrapper.className = 'position-relative searchable-select-wrapper' + (hasAutoWidth ? ' d-inline-block' : '');
        if (sel.style.width && sel.style.width !== 'auto') {
            wrapper.style.width = sel.style.width;
        }
        if (sel.style.maxWidth) wrapper.style.maxWidth = sel.style.maxWidth;
        sel.parentNode.insertBefore(wrapper, sel.nextSibling);

        function avatarHtml(avatar, name, size) {
            size = size || 22;
            if (avatar) return '<img src="/' + avatar + '" class="rounded-circle" width="' + size + '" height="' + size + '" style="object-fit:cover">';
            if (!name) return '';
            return '<span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:' + size + 'px;height:' + size + 'px;font-size:' + (size * 0.45) + 'px">' + name.charAt(0).toUpperCase() + '</span>';
        }

        function btnContent(text, avatar, name) {
            var av = avatar ? avatarHtml(avatar, name) : '';
            if (!text) return '<span class="text-muted">' + (placeholderText || 'Chọn...') + '</span>';
            return (av ? '<span class="me-2 flex-shrink-0">' + av + '</span>' : '') + '<span class="flex-grow-1 text-truncate">' + text + '</span>';
        }

        var selectedAvatar = '';
        items.forEach(function(o) { if (o.value === selectedValue && o.value !== '') selectedAvatar = o.avatar; });

        // Display button
        var btn = document.createElement('div');
        btn.className = 'form-select d-flex align-items-center text-nowrap';
        btn.style.cursor = 'pointer';
        btn.innerHTML = btnContent(selectedText, selectedAvatar, selectedText);
        wrapper.appendChild(btn);

        // Dropdown
        var dd = document.createElement('div');
        dd.className = 'border rounded bg-white shadow';
        dd.style.cssText = 'position:absolute;z-index:1060;min-width:220px;width:100%;display:none;top:100%;left:0;margin-top:2px';
        wrapper.appendChild(dd);

        // Search input
        var searchWrap = document.createElement('div');
        searchWrap.className = 'p-2 border-bottom';
        var searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'form-control';
        searchInput.placeholder = 'Tìm kiếm...';
        searchInput.autocomplete = 'off';
        searchWrap.appendChild(searchInput);
        dd.appendChild(searchWrap);

        // Options list
        var listEl = document.createElement('div');
        listEl.style.cssText = 'max-height:200px;overflow-y:auto';
        dd.appendChild(listEl);

        function renderOptions(q) {
            listEl.innerHTML = '';
            var filtered = items.filter(function(o) {
                if (o.value === '' && !q) return true;
                if (o.value === '' && q) return false;
                if (!q) return true;
                var text = o.text.toLowerCase();
                var words = q.toLowerCase().trim().split(/\s+/);
                return words.every(function(w) { return text.indexOf(w) !== -1; });
            });

            if (filtered.length === 0) {
                listEl.innerHTML = '<div class="px-3 py-2 text-muted fs-13">Không tìm thấy</div>';
                return;
            }

            var lastGroup = null;
            filtered.forEach(function(o) {
                // Group header
                if (o.group && o.group !== lastGroup) {
                    var groupEl = document.createElement('div');
                    groupEl.className = 'px-3 py-1 text-muted fw-medium fs-11 text-uppercase bg-light border-bottom';
                    groupEl.textContent = o.group;
                    listEl.appendChild(groupEl);
                    lastGroup = o.group;
                }

                var item = document.createElement('div');
                item.className = 'px-3 py-2 fs-13 d-flex align-items-center gap-2' + (o.value === selectedValue ? ' bg-primary text-white' : '');
                item.style.cursor = 'pointer';
                var hasAvatars = items.some(function(i) { return i.avatar; });
                var avEl = (hasAvatars && o.value) ? avatarHtml(o.avatar, o.text, 24) : '';
                item.innerHTML = (avEl ? '<span class="flex-shrink-0">' + avEl + '</span>' : '') + '<span>' + (o.text || placeholderText || 'Chọn...') + '</span>';
                item.dataset.value = o.value;

                item.addEventListener('mouseenter', function() {
                    if (o.value !== selectedValue) this.style.backgroundColor = '#f3f6f9';
                });
                item.addEventListener('mouseleave', function() {
                    if (o.value !== selectedValue) this.style.backgroundColor = '';
                });
                item.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    sel.value = o.value;
                    selectedValue = o.value;
                    selectedText = o.value ? o.text : '';
                    selectedAvatar = o.avatar || '';
                    btn.innerHTML = btnContent(selectedText, selectedAvatar, selectedText);
                    close();
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                });
                listEl.appendChild(item);
            });
        }

        // Sync the button label when external JS sets sel.value programmatically
        // (e.g. edit modal pre-filling the user). Caller should dispatch
        // `sel.dispatchEvent(new Event('change'))`, or we can detect on open.
        function syncFromSelect() {
            if (selectedValue === sel.value) return;
            selectedValue = sel.value;
            var match = null;
            for (var i = 0; i < items.length; i++) {
                if (items[i].value === sel.value) { match = items[i]; break; }
            }
            selectedText = match && match.value !== '' ? match.text : '';
            selectedAvatar = match ? (match.avatar || '') : '';
            btn.innerHTML = btnContent(selectedText, selectedAvatar, selectedText);
        }
        sel.addEventListener('change', syncFromSelect);

        function open() {
            syncFromSelect(); // refresh in case value was changed externally without event
            dd.style.display = 'block';
            searchInput.value = '';
            renderOptions('');
            setTimeout(function() { searchInput.focus(); }, 50);
        }

        function close() {
            dd.style.display = 'none';
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (dd.style.display === 'none') open(); else close();
        });

        var isComposing = false;
        searchInput.addEventListener('compositionstart', function() { isComposing = true; });
        searchInput.addEventListener('compositionend', function() {
            isComposing = false;
            renderOptions(this.value);
        });

        searchInput.addEventListener('input', function() {
            if (!isComposing) renderOptions(this.value);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') close();
        });

        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) close();
        });

        // Expose refresh for dynamic updates
        sel._searchable = {
            refresh: function() {
                items = [];
                selectedValue = sel.value;
                selectedText = '';
                placeholderText = '';
                for (var i = 0; i < sel.children.length; i++) {
                    var child = sel.children[i];
                    if (child.tagName.toUpperCase() === 'OPTGROUP') {
                        var gn = child.label || '';
                        for (var j = 0; j < child.children.length; j++) { addOption(child.children[j], gn); }
                    } else if (child.tagName.toUpperCase() === 'OPTION') {
                        addOption(child, null);
                    }
                }
                btn.innerHTML = '<span class="flex-grow-1 text-truncate">' + (selectedText || '<span class="text-muted">' + (placeholderText || 'Chọn...') + '</span>') + '</span>';
            }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window._initSearchableSelect = init;
})();
