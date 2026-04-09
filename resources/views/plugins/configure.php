<?php $pageTitle = 'Cấu hình ' . e($plugin['name']); ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">
                <i class="<?= e($plugin['icon']) ?> me-2"></i>
                Cấu hình: <?= e($plugin['name']) ?>
            </h4>
            <a href="<?= url('plugins') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thiết lập cấu hình</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $fields = $configSchema['fields'] ?? [];
                        if (empty($fields)):
                        ?>
                            <div class="text-center py-4 text-muted">
                                <i class="ri-settings-3-line fs-1 d-block mb-2"></i>
                                Plugin này không có cấu hình nào.
                            </div>
                        <?php else: ?>
                            <form method="POST" action="<?= url('plugins/' . $plugin['id'] . '/configure') ?>">
                                <?= csrf_field() ?>

                                <?php foreach ($fields as $field):
                                    $key = $field['key'];
                                    $value = $tenantConfig[$key] ?? ($field['default'] ?? '');
                                    $required = !empty($field['required']);
                                    $readonly = !empty($field['readonly']);
                                ?>
                                    <div class="mb-3">
                                        <label class="form-label" for="field_<?= e($key) ?>">
                                            <?= e($field['label']) ?>
                                            <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
                                        </label>

                                        <?php if ($field['type'] === 'text'): ?>
                                            <input type="text"
                                                   class="form-control"
                                                   id="field_<?= e($key) ?>"
                                                   name="<?= e($key) ?>"
                                                   value="<?= e($value) ?>"
                                                   placeholder="<?= e($field['placeholder'] ?? '') ?>"
                                                   <?= $required ? 'required' : '' ?>
                                                   <?= $readonly ? 'readonly' : '' ?>>

                                        <?php elseif ($field['type'] === 'number'): ?>
                                            <input type="number"
                                                   class="form-control"
                                                   id="field_<?= e($key) ?>"
                                                   name="<?= e($key) ?>"
                                                   value="<?= e($value) ?>"
                                                   <?= $required ? 'required' : '' ?>>

                                        <?php elseif ($field['type'] === 'textarea'): ?>
                                            <textarea class="form-control"
                                                      id="field_<?= e($key) ?>"
                                                      name="<?= e($key) ?>"
                                                      rows="3"
                                                      placeholder="<?= e($field['placeholder'] ?? '') ?>"
                                                      <?= $required ? 'required' : '' ?>><?= e($value) ?></textarea>

                                        <?php elseif ($field['type'] === 'select'): ?>
                                            <select class="form-select"
                                                    id="field_<?= e($key) ?>"
                                                    name="<?= e($key) ?>"
                                                    <?= $required ? 'required' : '' ?>>
                                                <option value="">-- Chọn --</option>
                                                <?php foreach (($field['options'] ?? []) as $opt): ?>
                                                    <option value="<?= e($opt) ?>" <?= $value === $opt ? 'selected' : '' ?>>
                                                        <?= e(ucfirst($opt)) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                        <?php elseif ($field['type'] === 'checkbox'): ?>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input"
                                                       type="checkbox"
                                                       id="field_<?= e($key) ?>"
                                                       name="<?= e($key) ?>"
                                                       value="1"
                                                       <?= $value ? 'checked' : '' ?>>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cấu hình</button>
                                    <a href="<?= url('plugins') ?>" class="btn btn-light">Hủy</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Thông tin plugin</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg mx-auto">
                                <div class="avatar-title bg-primary-subtle text-primary rounded fs-28">
                                    <i class="<?= e($plugin['icon']) ?>"></i>
                                </div>
                            </div>
                        </div>
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Tên</td>
                                <td class="fw-medium"><?= e($plugin['name']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Phiên bản</td>
                                <td>v<?= e($plugin['version']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tác giả</td>
                                <td><?= e($plugin['author']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Danh mục</td>
                                <td><span class="badge bg-info-subtle text-info"><?= e(ucfirst($plugin['category'])) ?></span></td>
                            </tr>
                        </table>
                        <p class="text-muted mt-3 mb-0"><?= e($plugin['description']) ?></p>
                    </div>
                </div>
            </div>
        </div>
