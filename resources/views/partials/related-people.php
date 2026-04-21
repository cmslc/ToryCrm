<?php
/**
 * Related People (Owner + Followers) - Reusable partial
 *
 * Variables:
 *   $rpEntityType  - 'contact', 'quotation', 'order', 'contract'
 *   $rpEntityId    - Entity ID
 *   $rpOwnerId     - Current owner user ID
 *   $rpOwnerName   - Current owner name
 */
$rpEntityType = $rpEntityType ?? '';
$rpEntityId = $rpEntityId ?? 0;
$rpOwnerId = $rpOwnerId ?? 0;
$rpOwnerName = $rpOwnerName ?? '-';
$rpUsers = \Core\Database::fetchAll("SELECT u.id, u.name, u.avatar, d.name as dept_name FROM users u LEFT JOIN departments d ON u.department_id = d.id WHERE u.is_active = 1 ORDER BY d.name, u.name");

// Load followers if table exists
$rpFollowers = [];
$rpFollowerTable = $rpEntityType . '_followers'; // contact_followers, quotation_followers, etc.
try {
    $rpFollowers = \Core\Database::fetchAll(
        "SELECT f.user_id, u.name FROM {$rpFollowerTable} f JOIN users u ON f.user_id = u.id WHERE f.{$rpEntityType}_id = ? ORDER BY f.created_at",
        [$rpEntityId]
    );
} catch (\Exception $e) {
    // Table might not exist yet
}
?>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0"><i class="ri-team-line me-1"></i> Người liên quan</h5></div>
    <div class="card-body py-2">
        <!-- Phụ trách chính -->
        <label class="text-muted fs-12">Phụ trách chính</label>
        <?php $rpOwnerAvatar = null; foreach ($rpUsers as $_u) { if ($_u['id'] == $rpOwnerId) { $rpOwnerAvatar = $_u['avatar'] ?? null; break; } } ?>
        <div class="d-flex align-items-center justify-content-between mb-3 p-2 bg-light rounded">
            <div class="d-flex align-items-center">
                <?php if ($rpOwnerAvatar): ?>
                <img src="<?= asset($rpOwnerAvatar) ?>" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover">
                <?php else: ?>
                <div class="avatar-xs me-2"><span class="avatar-title bg-primary text-white rounded-circle fs-12"><?= strtoupper(mb_substr($rpOwnerName, 0, 1)) ?></span></div>
                <?php endif; ?>
                <span class="fw-medium" id="rpOwnerName"><?= e($rpOwnerName) ?></span>
            </div>
            <div class="position-relative">
                <button type="button" class="btn btn-soft-primary py-0 px-2" id="rpChangeOwnerBtn">Đổi</button>
                <div id="rpOwnerSearchBox" class="position-absolute end-0 bg-white border rounded shadow p-2" style="display:none;width:250px;z-index:1060;top:100%;margin-top:4px">
                    <input type="text" class="form-control mb-1" id="rpOwnerSearchInput" placeholder="Tìm người..." autocomplete="off">
                    <div id="rpOwnerSearchResults" style="max-height:200px;overflow-y:auto"></div>
                </div>
            </div>
        </div>

        <!-- Người liên quan -->
        <label class="text-muted fs-12">Người liên quan</label>
        <div id="rpFollowerTags" class="d-flex flex-wrap gap-1 mb-2">
            <?php
            $rpShownIds = [$rpOwnerId];

            // Followers (thêm thủ công)
            foreach ($rpFollowers as $f):
                if (in_array($f['user_id'], $rpShownIds)) continue;
                $rpShownIds[] = $f['user_id'];
            ?>
                <?php $fAv = null; foreach ($rpUsers as $_u) { if ($_u['id'] == $f['user_id']) { $fAv = $_u['avatar'] ?? null; break; } } ?>
                <span class="badge bg-light text-dark d-inline-flex align-items-center gap-1 py-1 px-2 border fs-12 fw-normal" data-uid="<?= $f['user_id'] ?>">
                    <?php if ($fAv): ?><img src="<?= asset($fAv) ?>" class="rounded-circle" width="20" height="20" style="object-fit:cover">
                    <?php else: ?><span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:9px"><?= mb_strtoupper(mb_substr($f['name'], 0, 1)) ?></span><?php endif; ?>
                    <?= e($f['name']) ?>
                    <i class="ri-close-line text-muted" style="cursor:pointer;font-size:14px" onclick="rpRemoveFollower(<?= $f['user_id'] ?>, this)"></i>
                </span>
            <?php endforeach; ?>

            <?php
            // Trưởng/phó phòng của người phụ trách
            if ($rpOwnerId) {
                try {
                    $ownerDept = \Core\Database::fetch("SELECT department_id FROM users WHERE id = ?", [$rpOwnerId]);
                    if ($ownerDept && $ownerDept['department_id']) {
                        $deptManagers = \Core\Database::fetchAll(
                            "SELECT u.id, u.name, u.avatar, CASE WHEN d.manager_id = u.id THEN 'Trưởng phòng' ELSE 'Phó phòng' END as role_label
                             FROM departments d JOIN users u ON (u.id = d.manager_id OR u.id = d.vice_manager_id)
                             WHERE d.id = ? AND u.is_active = 1 AND u.id NOT IN ({$rpPlaceholders})",
                            [$ownerDept['department_id']]
                        );
                        foreach ($deptManagers as $dm):
                            $rpShownIds[] = $dm['id'];
                        ?>
                <span class="badge bg-light text-dark d-inline-flex align-items-center gap-1 py-1 px-2 border fs-12 fw-normal" title="<?= e($dm['role_label']) ?>">
                    <?php if ($dm['avatar'] ?? null): ?><img src="<?= asset($dm['avatar']) ?>" class="rounded-circle" width="20" height="20" style="object-fit:cover">
                    <?php else: ?><span class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:9px"><?= mb_strtoupper(mb_substr($dm['name'], 0, 1)) ?></span><?php endif; ?>
                    <?= e($dm['name']) ?>
                </span>
                        <?php endforeach;
                    }
                } catch (\Exception $e) {}
                $rpPlaceholders = implode(',', array_map('intval', $rpShownIds));
            }
            ?>

            <?php
            // Ban lãnh đạo + Người có quyền "Xem tất cả" module này
            $rpModule = $rpEntityType . 's';
            $rpPlaceholders = implode(',', array_map('intval', $rpShownIds));
            try {
                $rpAutoUsers = \Core\Database::fetchAll(
                    "SELECT DISTINCT u.id, u.name, u.avatar,
                        CASE WHEN pg.is_system = 1 THEN 'Ban lãnh đạo' ELSE 'Xem tất cả' END as role_label
                     FROM users u
                     JOIN user_permission_groups upg ON u.id = upg.user_id
                     JOIN permission_groups pg ON upg.group_id = pg.id
                     LEFT JOIN group_permissions gp ON pg.id = gp.group_id
                     LEFT JOIN permissions p ON gp.permission_id = p.id
                     WHERE u.is_active = 1 AND u.id NOT IN ({$rpPlaceholders})
                     AND (pg.is_system = 1 OR (p.module = ? AND p.action = 'view_all'))
                     ORDER BY u.name",
                    [$rpModule]
                );
                foreach ($rpAutoUsers as $au):
            ?>
                <span class="badge bg-light text-dark d-inline-flex align-items-center gap-1 py-1 px-2 border fs-12 fw-normal" title="<?= e($au['role_label']) ?>">
                    <?php if ($au['avatar'] ?? null): ?><img src="<?= asset($au['avatar']) ?>" class="rounded-circle" width="20" height="20" style="object-fit:cover">
                    <?php else: ?><span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:9px"><?= mb_strtoupper(mb_substr($au['name'], 0, 1)) ?></span><?php endif; ?>
                    <?= e($au['name']) ?>
                </span>
            <?php endforeach; } catch (\Exception $e) {} ?>
        </div>
        <div class="position-relative">
            <input type="text" class="form-control" id="rpFollowerInput" placeholder="Gõ tên để thêm..." autocomplete="off">
            <div id="rpFollowerDropdown" class="dropdown-menu w-100" style="display:none;max-height:200px;overflow-y:auto"></div>
        </div>
    </div>
</div>

<script>
(function() {
    var entityType = '<?= $rpEntityType ?>';
    var entityId = <?= (int)$rpEntityId ?>;
    var tok = '<?= csrf_token() ?>';
    var users = <?= json_encode($rpUsers) ?>;
    var existing = [<?= implode(',', array_column($rpFollowers, 'user_id')) ?>];

    var input = document.getElementById('rpFollowerInput');
    var dropdown = document.getElementById('rpFollowerDropdown');
    var tags = document.getElementById('rpFollowerTags');

    input?.addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        if (!q) { dropdown.style.display = 'none'; return; }
        var filtered = users.filter(function(u) { return u.name.toLowerCase().indexOf(q) !== -1 && existing.indexOf(u.id) === -1; }).slice(0, 8);
        if (!filtered.length) { dropdown.innerHTML = '<div class="px-3 py-2 text-muted">Không tìm thấy</div>'; dropdown.style.display = 'block'; return; }
        dropdown.innerHTML = filtered.map(function(u) {
            var av = u.avatar ? '<img src="/' + u.avatar + '" class="rounded-circle me-2" width="24" height="24" style="object-fit:cover">' : '<span class="avatar-title bg-primary text-white rounded-circle me-2" style="width:24px;height:24px;font-size:10px;display:inline-flex;align-items:center;justify-content:center">' + u.name.charAt(0).toUpperCase() + '</span>';
            return '<div class="d-flex align-items-center px-3 py-2" style="cursor:pointer" onmousedown="rpAddFollower(' + u.id + ',\'' + u.name.replace(/'/g, "\\'") + '\')">' + av + '<span>' + u.name + '</span>' + (u.dept_name ? '<small class="text-muted ms-auto">' + u.dept_name + '</small>' : '') + '</div>';
        }).join('');
        dropdown.style.display = 'block';
    });

    input?.addEventListener('blur', function() { setTimeout(function() { dropdown.style.display = 'none'; }, 200); });

    window.rpAddFollower = function(uid, name) {
        fetch('<?= url("") ?>' + entityType + 's/' + entityId + '/followers', {
            method: 'POST',
            headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': tok},
            body: JSON.stringify({ user_id: uid, action: 'add' })
        }).then(r => r.json()).then(function(data) {
            if (data.success) {
                existing.push(uid);
                var badge = document.createElement('span');
                badge.className = 'badge bg-light text-dark d-inline-flex align-items-center gap-1 py-1 px-2 border fs-12 fw-normal';
                badge.dataset.uid = uid;
                var av = '';
                users.forEach(function(u2) { if (u2.id === uid && u2.avatar) av = u2.avatar; });
                var avHtml = av ? '<img src="/' + av + '" class="rounded-circle" width="20" height="20" style="object-fit:cover">' : '<span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:9px">' + name.charAt(0).toUpperCase() + '</span>';
                badge.innerHTML = avHtml + ' ' + name + ' <i class="ri-close-line text-muted" style="cursor:pointer;font-size:14px" onclick="rpRemoveFollower(' + uid + ', this)"></i>';
                tags.appendChild(badge);
                input.value = '';
                dropdown.style.display = 'none';
            }
        });
    };

    window.rpRemoveFollower = function(uid, el) {
        fetch('<?= url("") ?>' + entityType + 's/' + entityId + '/followers', {
            method: 'POST',
            headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': tok},
            body: JSON.stringify({ user_id: uid, action: 'remove' })
        }).then(r => r.json()).then(function(data) {
            if (data.success) {
                existing = existing.filter(function(id) { return id !== uid; });
                el.closest('.badge').remove();
            }
        });
    };

    // Change owner
    var ownerBtn = document.getElementById('rpChangeOwnerBtn');
    var ownerBox = document.getElementById('rpOwnerSearchBox');
    var ownerInput = document.getElementById('rpOwnerSearchInput');
    var ownerResults = document.getElementById('rpOwnerSearchResults');

    ownerBtn?.addEventListener('click', function(e) {
        e.stopPropagation();
        ownerBox.style.display = ownerBox.style.display === 'none' ? 'block' : 'none';
        if (ownerBox.style.display === 'block') { ownerInput.value = ''; renderOwnerList(''); ownerInput.focus(); }
    });

    function renderOwnerList(q) {
        var filtered = users.filter(function(u) { return !q || u.name.toLowerCase().indexOf(q.toLowerCase()) !== -1; }).slice(0, 10);
        ownerResults.innerHTML = filtered.map(function(u) {
            var av = u.avatar ? '<img src="/' + u.avatar + '" class="rounded-circle me-2" width="24" height="24" style="object-fit:cover">' : '<span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center me-2" style="width:24px;height:24px;font-size:10px">' + u.name.charAt(0).toUpperCase() + '</span>';
            return '<div class="d-flex align-items-center px-2 py-1 rounded" style="cursor:pointer" onmousedown="rpChangeOwner(' + u.id + ',\'' + u.name.replace(/'/g, "\\'") + '\',\'' + (u.avatar || '').replace(/'/g, "\\'") + '\')">' + av + '<span>' + u.name + '</span></div>';
        }).join('');
    }

    ownerInput?.addEventListener('input', function() { renderOwnerList(this.value); });
    document.addEventListener('click', function(e) { if (ownerBox && !ownerBox.contains(e.target) && e.target !== ownerBtn) ownerBox.style.display = 'none'; });

    window.rpChangeOwner = function(uid, name, avatar) {
        fetch('<?= url("") ?>' + entityType + 's/' + entityId + '/change-owner', {
            method: 'POST',
            headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': tok},
            body: JSON.stringify({ owner_id: uid })
        }).then(r => r.json()).then(function(data) {
            if (data.success || data.owner_id) {
                var nameEl = document.getElementById('rpOwnerName');
                if (nameEl) nameEl.textContent = name;
                var imgContainer = nameEl?.closest('.d-flex')?.querySelector('img, .avatar-xs');
                if (imgContainer && avatar) {
                    imgContainer.outerHTML = '<img src="/' + avatar + '" class="rounded-circle me-2" width="32" height="32" style="object-fit:cover">';
                }
                ownerBox.style.display = 'none';
            }
        });
    };
})();
</script>
