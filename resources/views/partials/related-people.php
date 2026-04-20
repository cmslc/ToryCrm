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
        <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
            <div class="avatar-xs me-2">
                <span class="avatar-title bg-primary text-white rounded-circle fs-12"><?= strtoupper(mb_substr($rpOwnerName, 0, 1)) ?></span>
            </div>
            <span class="fw-medium"><?= e($rpOwnerName) ?></span>
        </div>

        <!-- Người có quyền truy cập -->
        <label class="text-muted fs-12">Người có quyền truy cập</label>
        <div id="rpFollowerTags" class="d-flex flex-wrap gap-1 mb-2">
            <?php
            // Followers
            foreach ($rpFollowers as $f):
                if ($f['user_id'] == $rpOwnerId) continue;
            ?>
                <span class="badge bg-info-subtle text-info d-inline-flex align-items-center gap-1 py-1 px-2" data-uid="<?= $f['user_id'] ?>">
                    <?= e($f['name']) ?>
                    <i class="ri-close-line" style="cursor:pointer;font-size:14px" onclick="rpRemoveFollower(<?= $f['user_id'] ?>, this)"></i>
                </span>
            <?php endforeach; ?>

            <?php
            // Users with view_all or view permission
            $rpModule = $_entityType . 's'; // contacts, quotations, orders, contracts
            $rpShownIds = array_column($rpFollowers, 'user_id');
            $rpShownIds[] = $rpOwnerId;
            $rpShownPlaceholder = implode(',', array_map('intval', $rpShownIds));
            try {
                $rpPermUsers = \Core\Database::fetchAll(
                    "SELECT DISTINCT u.id, u.name FROM users u
                     JOIN user_groups ug ON u.id = ug.user_id
                     JOIN group_permissions gp ON ug.group_id = gp.group_id
                     JOIN permissions p ON gp.permission_id = p.id
                     WHERE p.module = ? AND p.action IN ('view_all','view')
                     AND u.is_active = 1 AND u.id NOT IN ({$rpShownPlaceholder})
                     ORDER BY u.name",
                    [$rpModule]
                );
                foreach ($rpPermUsers as $pu):
            ?>
                <span class="badge bg-soft-secondary text-secondary py-1 px-2"><?= e($pu['name']) ?></span>
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
                badge.className = 'badge bg-info-subtle text-info d-inline-flex align-items-center gap-1 py-1 px-2';
                badge.dataset.uid = uid;
                badge.innerHTML = name + ' <i class="ri-close-line" style="cursor:pointer;font-size:14px" onclick="rpRemoveFollower(' + uid + ', this)"></i>';
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
})();
</script>
