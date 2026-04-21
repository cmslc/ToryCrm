<?php $pageTitle = 'Gộp người liên hệ trùng'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Gộp người liên hệ trùng</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('contacts') ?>">Khách hàng</a></li>
        <li class="breadcrumb-item active">Trùng lặp</li>
    </ol>
</div>

<div class="alert alert-info mb-3 py-2">
    <i class="ri-information-line me-1"></i>
    Tìm thấy <strong><?= $totalGroups ?></strong> nhóm có SĐT trùng nhau. Chọn "Giữ" cho người chính và "Gộp vào" cho các bản sao — hệ thống sẽ chuyển mọi "nơi làm việc" về person được giữ, rồi xoá các bản sao.
</div>

<?php if (empty($groups)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <i class="ri-checkbox-circle-line fs-1 text-success d-block mb-2"></i>
        <h5 class="mb-0">Không có trùng lặp nào.</h5>
    </div>
</div>
<?php else: ?>

<?php foreach ($groups as $idx => $group): ?>
<div class="card mb-3">
    <div class="card-header d-flex align-items-center justify-content-between py-2">
        <div>
            <span class="badge bg-warning-subtle text-warning me-2"><i class="ri-phone-line me-1"></i><?= e($group['key']) ?></span>
            <span class="text-muted fs-13"><?= count($group['persons']) ?> người trùng SĐT</span>
        </div>
    </div>
    <form method="POST" action="<?= url('persons/merge') ?>" data-confirm="Gộp các person đã chọn? Hành động không thể hoàn tác.">
        <?= csrf_field() ?>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:80px" class="text-center">Giữ</th>
                        <th style="width:80px" class="text-center">Gộp vào</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Làm ở (công ty)</th>
                        <th class="text-center">Số nơi</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($group['persons'] as $pi => $p): ?>
                    <tr>
                        <td class="text-center">
                            <input type="radio" class="form-check-input" name="target_id" value="<?= $p['id'] ?>" <?= $pi === 0 ? 'checked' : '' ?> data-group="<?= $idx ?>">
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input merge-src" name="source_ids[]" value="<?= $p['id'] ?>" data-group="<?= $idx ?>" <?= $pi !== 0 ? 'checked' : '' ?>>
                        </td>
                        <td>
                            <a href="<?= url('persons/' . $p['id']) ?>" target="_blank" class="fw-medium"><?= e($p['full_name']) ?></a>
                            <div class="text-muted fs-12">#<?= $p['id'] ?></div>
                        </td>
                        <td class="text-muted fs-13"><?= e($p['email'] ?? '-') ?></td>
                        <td class="text-muted fs-13" style="max-width:350px"><?= e($p['companies'] ?? '-') ?></td>
                        <td class="text-center"><span class="badge bg-primary-subtle text-primary"><?= $p['emp_count'] ?></span></td>
                        <td><a href="<?= url('persons/' . $p['id']) ?>" target="_blank" class="btn btn-soft-primary btn-icon" title="Xem"><i class="ri-eye-line"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2 py-2">
            <button type="submit" class="btn btn-primary"><i class="ri-merge-cells-horizontal me-1"></i>Gộp</button>
        </div>
    </form>
</div>
<?php endforeach; ?>

<?php endif; ?>

<script>
// Prevent selecting the "keep" target as a merge source in the same group
document.querySelectorAll('input[name="target_id"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var g = this.dataset.group;
        document.querySelectorAll('.merge-src[data-group="' + g + '"]').forEach(function(cb) {
            if (cb.value === radio.value) { cb.checked = false; cb.disabled = true; }
            else { cb.disabled = false; }
        });
    });
    // Initialize
    radio.dispatchEvent(new Event('change'));
});
</script>
