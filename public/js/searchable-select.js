/**
 * ToryCRM Searchable Select
 *
 * Auto-converts any <select> with class "searchable-select" into
 * a dropdown with search input.
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
        // Collect options
        var options = [];
        var selectedValue = sel.value;
        var selectedText = '';
        for (var i = 0; i < sel.options.length; i++) {
            var opt = sel.options[i];
            options.push({ value: opt.value, text: opt.textContent.trim() });
            if (opt.value === selectedValue && opt.value !== '') selectedText = opt.textContent.trim();
        }

        // Hide original select
        sel.style.display = 'none';

        // Create wrapper
        var wrapper = document.createElement('div');
        wrapper.className = 'position-relative searchable-select-wrapper';
        sel.parentNode.insertBefore(wrapper, sel.nextSibling);

        // Display button
        var btn = document.createElement('div');
        btn.className = 'form-select d-flex align-items-center';
        btn.style.cursor = 'pointer';
        btn.innerHTML = '<span class="flex-grow-1 text-truncate">' + (selectedText || '<span class="text-muted">Chọn...</span>') + '</span>';
        wrapper.appendChild(btn);

        // Dropdown
        var dd = document.createElement('div');
        dd.className = 'border rounded bg-white shadow';
        dd.style.cssText = 'position:absolute;z-index:1060;width:100%;display:none;top:100%;left:0;margin-top:2px';
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
            var filtered = options.filter(function(o) {
                if (o.value === '' && !q) return true;
                if (o.value === '' && q) return false;
                return !q || o.text.toLowerCase().indexOf(q.toLowerCase()) !== -1;
            });

            if (filtered.length === 0) {
                listEl.innerHTML = '<div class="px-3 py-2 text-muted fs-13">Không tìm thấy</div>';
                return;
            }

            filtered.forEach(function(o) {
                var item = document.createElement('div');
                item.className = 'px-3 py-2 fs-13' + (o.value === selectedValue ? ' bg-primary text-white' : '');
                item.style.cursor = 'pointer';
                item.textContent = o.text || 'Chọn...';
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
                    btn.innerHTML = '<span class="flex-grow-1 text-truncate">' + (selectedText || '<span class="text-muted">Chọn...</span>') + '</span>';
                    close();
                    // Trigger change event on original select
                    sel.dispatchEvent(new Event('change', { bubbles: true }));
                });
                listEl.appendChild(item);
            });
        }

        function open() {
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

        searchInput.addEventListener('input', function() {
            renderOptions(this.value);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') close();
        });

        // Close on click outside
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) close();
        });
    }

    // Init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-init on dynamic content
    window._initSearchableSelect = init;
})();
