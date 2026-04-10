<?php
/**
 * Tag Input Component
 * Usage: include with $entityType, $entityId, $selectedTags (array) params
 */
$entityType = $entityType ?? '';
$entityId = $entityId ?? 0;
$selectedTags = $selectedTags ?? [];
$componentId = 'tagInput_' . md5($entityType . $entityId);
?>

<div class="tag-input-wrapper mb-3" id="<?= $componentId ?>">
    <div class="d-flex flex-wrap gap-1 mb-2" id="<?= $componentId ?>_tags">
        <?php foreach ($selectedTags as $tag): ?>
            <span class="badge fs-12 d-inline-flex align-items-center gap-1 tag-badge" style="background-color: <?= e($tag['color']) ?>; color: #fff;" data-tag-id="<?= $tag['id'] ?>">
                <?= e($tag['name']) ?>
                <i class="ri-close-line" style="cursor:pointer;" onclick="removeTag_<?= $componentId ?>(<?= $tag['id'] ?>, this)"></i>
            </span>
        <?php endforeach; ?>
    </div>
    <div class="position-relative">
        <input type="text" class="form-control" placeholder="Nhập tên nhãn..." id="<?= $componentId ?>_search" autocomplete="off">
        <div class="dropdown-menu w-100 tag-autocomplete" id="<?= $componentId ?>_dropdown" style="display:none; position:absolute; z-index:1050;"></div>
    </div>
</div>

<script>
(function() {
    var cid = '<?= $componentId ?>';
    var entityType = '<?= e($entityType) ?>';
    var entityId = <?= (int) $entityId ?>;
    var selectedIds = [<?= implode(',', array_column($selectedTags, 'id')) ?>];
    var searchInput = document.getElementById(cid + '_search');
    var dropdown = document.getElementById(cid + '_dropdown');
    var tagsContainer = document.getElementById(cid + '_tags');
    var searchTimer = null;

    function syncTags() {
        var formData = new FormData();
        formData.append('entity_type', entityType);
        formData.append('entity_id', entityId);
        selectedIds.forEach(function(id) {
            formData.append('tag_ids[]', id);
        });
        formData.append('_token', (document.querySelector('input[name=_token]')?.value || '<?= $_SESSION['csrf_token'] ?? '' ?>'));

        fetch('/tags/assign', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
    }

    window['removeTag_' + cid] = function(tagId, el) {
        selectedIds = selectedIds.filter(function(id) { return id !== tagId; });
        el.closest('.tag-badge').remove();
        syncTags();
    };

    function addTagBadge(tag) {
        if (selectedIds.indexOf(tag.id) !== -1) return;
        selectedIds.push(tag.id);

        var badge = document.createElement('span');
        badge.className = 'badge fs-12 d-inline-flex align-items-center gap-1 tag-badge';
        badge.style.backgroundColor = tag.color;
        badge.style.color = '#fff';
        badge.dataset.tagId = tag.id;
        badge.innerHTML = escapeHtml(tag.name) + ' <i class="ri-close-line" style="cursor:pointer;"></i>';
        badge.querySelector('i').addEventListener('click', function() {
            selectedIds = selectedIds.filter(function(id) { return id !== tag.id; });
            badge.remove();
            syncTags();
        });
        tagsContainer.appendChild(badge);
        syncTags();
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        var q = this.value.trim();
        if (q.length < 1) {
            dropdown.style.display = 'none';
            return;
        }
        searchTimer = setTimeout(function() {
            fetch('/tags/search?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                dropdown.innerHTML = '';
                var tags = data.tags || [];
                tags.forEach(function(tag) {
                    if (selectedIds.indexOf(tag.id) !== -1) return;
                    var item = document.createElement('a');
                    item.className = 'dropdown-item d-flex align-items-center gap-2';
                    item.href = '#';
                    item.innerHTML = '<span class="rounded-circle d-inline-block" style="width:12px;height:12px;background:' + escapeHtml(tag.color) + ';"></span> ' + escapeHtml(tag.name);
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        addTagBadge(tag);
                        searchInput.value = '';
                        dropdown.style.display = 'none';
                    });
                    dropdown.appendChild(item);
                });

                // Option to create new tag
                var createItem = document.createElement('a');
                createItem.className = 'dropdown-item text-primary';
                createItem.href = '#';
                createItem.innerHTML = '<i class="ri-add-line me-1"></i> Tạo nhãn "' + escapeHtml(q) + '"';
                createItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    var formData = new FormData();
                    formData.append('name', q);
                    formData.append('color', '#405189');
                    formData.append('_token', (document.querySelector('input[name=_token]')?.value || '<?= $_SESSION['csrf_token'] ?? '' ?>'));

                    fetch('/tags/store', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success && data.tag) {
                            addTagBadge({id: data.tag.id, name: data.tag.name, color: data.tag.color});
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

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#' + cid)) {
            dropdown.style.display = 'none';
        }
    });
})();
</script>
