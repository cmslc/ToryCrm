<?php $pageTitle = 'Đặt lịch hẹn'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Đặt lịch hẹn</h4>
    <div>
        <a href="<?= url('bookings/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo liên kết mới</a>
    </div>
</div>

<?php if (empty($links)): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <div class="avatar-lg mx-auto mb-3">
            <div class="avatar-title rounded-circle bg-primary-subtle text-primary" style="width:80px;height:80px;font-size:36px">
                <i class="ri-calendar-check-line"></i>
            </div>
        </div>
        <h5>Chưa có liên kết đặt lịch nào</h5>
        <p class="text-muted">Tạo liên kết đặt lịch để khách hàng có thể tự đặt lịch hẹn với bạn.</p>
        <a href="<?= url('bookings/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo liên kết đầu tiên</a>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Thời lượng</th>
                        <th>Liên kết</th>
                        <th class="text-center">Lịch hẹn</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($links as $link): ?>
                    <tr>
                        <td>
                            <h6 class="mb-1"><?= e($link['title']) ?></h6>
                            <?php if (!empty($link['description'])): ?>
                                <small class="text-muted"><?= e(mb_substr($link['description'], 0, 60)) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= (int)($link['duration_minutes'] ?? 0) ?> phút</td>
                        <td>
                            <div class="input-group" style="max-width:320px">
                                <input type="text" class="form-control bg-light" value="<?= url('book/' . e($link['slug'])) ?>" readonly id="link-<?= $link['id'] ?>">
                                <button class="btn btn-soft-primary" onclick="copyLink(<?= $link['id'] ?>)" title="Sao chép">
                                    <i class="ri-file-copy-line me-1"></i> Sao chép
                                </button>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info-subtle text-info fs-6"><?= (int)($link['booking_count'] ?? 0) ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($link['is_active']): ?>
                                <span class="badge bg-success-subtle text-success">Hoạt động</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary">Tắt</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="<?= url('book/' . e($link['slug'])) ?>" target="_blank" class="btn btn-soft-info" title="Xem trang công khai">
                                    <i class="ri-external-link-line me-1"></i> Xem
                                </a>
                                <a href="<?= url('bookings/' . $link['id'] . '/edit') ?>" class="btn btn-soft-warning" title="Sửa">
                                    <i class="ri-edit-line me-1"></i> Sửa
                                </a>
                                <form method="POST" action="<?= url('bookings/' . $link['id'] . '/delete') ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xóa liên kết này?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button class="btn btn-soft-danger" title="Xóa"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function copyLink(id) {
    var input = document.getElementById('link-' + id);
    navigator.clipboard.writeText(input.value).then(function() {
        var btn = input.nextElementSibling;
        btn.innerHTML = '<i class="ri-check-line"></i>';
        setTimeout(function() { btn.innerHTML = '<i class="ri-file-copy-line"></i>'; }, 2000);
    });
}
</script>
