<?php $pageTitle = 'Quy tắc hoa hồng'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Quy tắc hoa hồng</h4>
            <div>
                <a href="<?= url('commissions') ?>" class="btn btn-soft-secondary me-1"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
                <a href="<?= url('commissions/rules/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo quy tắc</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên quy tắc</th>
                                <th>Loại</th>
                                <th>Giá trị</th>
                                <th>Áp dụng cho</th>
                                <th class="text-end">Giá trị tối thiểu</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rules)): ?>
                                <?php foreach ($rules as $rule): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($rule['name']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $rule['type'] === 'percent' ? 'primary' : 'info' ?>">
                                                <?= $rule['type'] === 'percent' ? 'Phần trăm (%)' : 'Cố định' ?>
                                            </span>
                                        </td>
                                        <td class="fw-medium">
                                            <?= $rule['type'] === 'percent' ? number_format($rule['value'], 1) . '%' : format_money($rule['value']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $rule['apply_to'] === 'deal' ? 'warning' : 'success' ?>">
                                                <?= $rule['apply_to'] === 'deal' ? 'Deal' : 'Đơn hàng' ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><?= $rule['min_value'] > 0 ? format_money($rule['min_value']) : '-' ?></td>
                                        <td>
                                            <?php if ($rule['is_active']): ?>
                                                <span class="badge bg-success">Đang hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tắt</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="<?= url('commissions/rules/' . $rule['id'] . '/edit') ?>" class="btn btn-soft-primary"><i class="ri-pencil-line"></i></a>
                                                <form method="POST" action="<?= url('commissions/rules/' . $rule['id'] . '/delete') ?>" data-confirm="Xác nhận xóa quy tắc này?">
                                                    <?= csrf_field() ?>
                                                    <button class="btn btn-soft-danger"><i class="ri-delete-bin-line"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-settings-3-line fs-1 d-block mb-2"></i>Chưa có quy tắc hoa hồng</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
