<?php
/**
 * Tag Input Component (form-mode)
 * Không sync AJAX - tag IDs nằm trong hidden inputs `tag_ids[]` để submit cùng form.
 * Usage: include với $selectedTags (array) — default empty.
 */
$selectedTags = $selectedTags ?? [];
$componentId = 'tagInputForm_' . uniqid();
?>

<div class="tag-input-wrapper" id="<?= $componentId ?>">
    <div class="d-flex flex-wrap gap-1 mb-2" id="<?= $componentId ?>_tags">
        <?php foreach ($selectedTags as $tag): ?>
            <span class="badge fs-12 d-inline-flex align-items-center gap-1 tag-badge" style="background-color: <?= e($tag['color']) ?>; color: #fff;" data-tag-id="<?= $tag['id'] ?>">
                <?= e($tag['name']) ?>
                <input type="hidden" name="tag_ids[]" value="<?= (int)$tag['id'] ?>">
                <i class="ri-close-line" style="cursor:pointer;" data-remove="<?= (int)$tag['id'] ?>"></i>
            </span>
        <?php endforeach; ?>
    </div>
    <div class="position-relative">
        <input type="text" class="form-control" placeholder="Nhập tên nhãn rồi nhấn Enter..." id="<?= $componentId ?>_search" autocomplete="off">
        <div class="dropdown-menu w-100" id="<?= $componentId ?>_dropdown" style="display:none; position:absolute; z-index:1050; max-height:200px; overflow-y:auto"></div>
    </div>
</div>

<script>
(function() {
    var cid = '<?= $componentId ?>';
    var selectedIds = [<?= implode(',', array_column($selectedTags, 'id')) ?>];
    var searchInput = document.getElementById(cid + '_search');
    var dropdown = document.getElementById(cid + '_dropdown');
    var tagsContainer = document.getElementById(cid + '_tags');
    var searchTimer = null;

    function escapeHtml(str) { var d = document.createElement('div'); d.textContent = str; return d.innerHTML; }

    function addTagBadge(tag) {
        if (selectedIds.indexOf(parseInt(tag.id)) !== -1) return;
        selectedIds.push(parseInt(tag.id));
        var badge = document.createElement('span');
        badge.className = 'badge fs-12 d-inline-flex align-items-center gap-1 tag-badge';
        badge.style.backgroundColor = tag.color;
        badge.style.color = '#fff';
        badge.dataset.tagId = tag.id;
        badge.innerHTML = escapeHtml(tag.name) +
            '<input type="hidden" name="tag_ids[]" value="' + tag.id + '">' +
            '<i class="ri-close-line" style="cursor:pointer" data-remove="' + tag.id + '"></i>';
        badge.querySelector('[data-remove]').addEventListener('click', function() {
            selectedIds = selectedIds.filter(function(id) { return id !== parseInt(tag.id); });
            badge.remove();
        });
        tagsContainer.appendChild(badge);
    }

    // Wire up existing badges
    tagsContainer.querySelectorAll('[data-remove]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var rid = parseInt(this.dataset.remove);
            selectedIds = selectedIds.filter(function(id) { return id !== rid; });
            this.closest('.tag-badge').remove();
        });
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        var q = this.value.trim();
        if (!q) { dropdown.style.display = 'none'; return; }
        searchTimer = setTimeout(function() {
            fetch('<?= url('tags/search') ?>?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                dropdown.innerHTML = '';
                (data.tags || []).forEach(function(tag) {
                    if (selectedIds.indexOf(parseInt(tag.id)) !== -1) return;
                    var item = document.createElement('a');
                    item.className = 'dropdown-item d-flex align-items-center gap-2';
                    item.href = '#';
                    item.innerHTML = '<span class="rounded-circle d-inline-block" style="width:12px;height:12px;background:' + escapeHtml(tag.color) + '"></span> ' + escapeHtml(tag.name);
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        addTagBadge(tag);
                        searchInput.value = '';
                        dropdown.style.display = 'none';
                    });
                    dropdown.appendChild(item);
                });

                // Create new tag option
                var createItem = document.createElement('a');
                createItem.className = 'dropdown-item text-primary';
                createItem.href = '#';
                createItem.innerHTML = '<i class="ri-add-line me-1"></i> Tạo nhãn "' + escapeHtml(q) + '"';
                createItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    var fd = new FormData();
                    fd.append('name', q);
                    fd.append('color', '#405189');
                    fd.append('_token', document.querySelector('input[name=_token]')?.value || '');
                    fetch('<?= url('tags/store') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    }).then(r => r.json()).then(d => {
                        if (d.success && d.tag) {
                            addTagBadge(d.tag);
                            searchInput.value = '';
                            dropdown.style.display = 'none';
                        }
                    });
                });
                dropdown.appendChild(createItem);
                dropdown.style.display = 'block';
            });
        }, 250);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var firstItem = dropdown.querySelector('.dropdown-item');
            if (firstItem) firstItem.click();
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#' + cid)) dropdown.style.display = 'none';
    });
})();
</script>
