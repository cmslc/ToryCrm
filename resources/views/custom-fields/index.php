<?php $pageTitle = 'Trường tùy chỉnh'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Trường tùy chỉnh</h4>
    <div>
        <a href="<?= url('custom-fields/create?module=' . ($activeModule ?? 'contacts')) ?>" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> Thêm trường
        </a>
    </div>
</div>

<!-- Module Tabs -->
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <?php foreach ($modules as $key => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeModule ?? 'contacts') === $key ? 'active' : '' ?>"
                       href="<?= url('custom-fields?module=' . $key) ?>">
                        <?= $label ?>
                        <?php if (!empty($fieldsByModule[$key])): ?>
                            <span class="badge bg-primary-subtle text-primary ms-1"><?= count($fieldsByModule[$key]) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-body">
        <?php
            $activeFields = $fieldsByModule[$activeModule ?? 'contacts'] ?? [];
            $typeLabels = [
                'text' => 'Văn bản', 'number' => 'Số', 'email' => 'Email', 'phone' => 'Điện thoại',
                'url' => 'URL', 'textarea' => 'Văn bản dài', 'select' => 'Danh sách', 'multi_select' => 'Chọn nhiều',
                'checkbox' => 'Checkbox', 'radio' => 'Radio', 'date' => 'Ngày', 'datetime' => 'Ngày giờ',
                'file' => 'Tệp tin', 'color' => 'Màu sắc', 'currency' => 'Tiền tệ',
            ];
            $typeColors = [
                'text' => 'primary', 'number' => 'info', 'email' => 'warning', 'phone' => 'success',
                'url' => 'secondary', 'textarea' => 'primary', 'select' => 'info', 'multi_select' => 'info',
                'checkbox' => 'success', 'radio' => 'success', 'date' => 'warning', 'datetime' => 'warning',
                'file' => 'danger', 'color' => 'dark', 'currency' => 'success',
            ];
        ?>

        <?php if (!empty($activeFields)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="fieldTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th>Tên trường</th>
                            <th>Khóa</th>
                            <th>Loại</th>
                            <th>Bắt buộc</th>
                            <th>Hiển thị danh sách</th>
                            <th>Có thể lọc</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="sortableFields">
                        <?php foreach ($activeFields as $field): ?>
                            <tr data-id="<?= $field['id'] ?>">
                                <td><i class="ri-drag-move-2-line text-muted" style="cursor:grab"></i></td>
                                <td class="fw-medium"><?= e($field['field_label']) ?></td>
                                <td><code><?= e($field['field_key']) ?></code></td>
                                <td>
                                    <span class="badge bg-<?= $typeColors[$field['field_type']] ?? 'secondary' ?>-subtle text-<?= $typeColors[$field['field_type']] ?? 'secondary' ?>">
                                        <?= $typeLabels[$field['field_type']] ?? $field['field_type'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($field['is_required']): ?>
                                        <span class="badge bg-danger-subtle text-danger">Bắt buộc</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($field['show_in_list']): ?>
                                        <i class="ri-eye-line text-success"></i>
                                    <?php else: ?>
                                        <i class="ri-eye-off-line text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($field['is_filterable']): ?>
                                        <i class="ri-filter-line text-primary"></i>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= url('custom-fields/' . $field['id'] . '/edit') ?>" class="btn btn-soft-primary">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        <form method="POST" action="<?= url('custom-fields/' . $field['id'] . '/delete') ?>" data-confirm="Xác nhận xóa trường này? Tất cả dữ liệu liên quan sẽ bị mất.">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="ri-input-method-line" style="font-size:48px"></i>
                <p class="mt-3 mb-0">Chưa có trường tùy chỉnh nào cho module này</p>
                <a href="<?= url('custom-fields/create?module=' . ($activeModule ?? 'contacts')) ?>" class="btn btn-primary mt-3">
                    <i class="ri-add-line me-1"></i> Thêm trường đầu tiên
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Simple drag & drop reorder via native HTML5
    var tbody = document.getElementById('sortableFields');
    if (!tbody) return;

    var dragItem = null;

    tbody.querySelectorAll('tr').forEach(function(row) {
        row.setAttribute('draggable', 'true');

        row.addEventListener('dragstart', function(e) {
            dragItem = this;
            this.style.opacity = '0.5';
        });

        row.addEventListener('dragend', function() {
            this.style.opacity = '1';
            dragItem = null;
            saveOrder();
        });

        row.addEventListener('dragover', function(e) {
            e.preventDefault();
        });

        row.addEventListener('drop', function(e) {
            e.preventDefault();
            if (dragItem !== this) {
                var allRows = Array.from(tbody.querySelectorAll('tr'));
                var fromIndex = allRows.indexOf(dragItem);
                var toIndex = allRows.indexOf(this);
                if (fromIndex < toIndex) {
                    tbody.insertBefore(dragItem, this.nextSibling);
                } else {
                    tbody.insertBefore(dragItem, this);
                }
            }
        });
    });

    function saveOrder() {
        var ids = [];
        tbody.querySelectorAll('tr').forEach(function(row) {
            ids.push(row.getAttribute('data-id'));
        });

        var fd = new FormData();
        ids.forEach(function(id) { fd.append('ids[]', id); });
        fd.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '<?= csrf_token() ?>');

        fetch('<?= url('custom-fields/reorder') ?>', {
            method: 'POST',
            body: fd
        });
    }
});
</script>
