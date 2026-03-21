<?php $pageTitle = 'Phân quyền'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Phân quyền vai trò</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
                <li class="breadcrumb-item active">Phân quyền</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('settings/permissions') ?>">
            <?= csrf_field() ?>
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="ri-information-line me-1"></i> Admin luôn có toàn quyền. Chỉ cấu hình cho Manager và Staff.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Module</th>
                                    <th>Quyền</th>
                                    <th class="text-center">Admin</th>
                                    <th class="text-center">Manager</th>
                                    <th class="text-center">Staff</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $currentModule = '';
                                foreach ($permissions as $p):
                                    $isNewModule = $p['module'] !== $currentModule;
                                    $currentModule = $p['module'];
                                ?>
                                <tr <?= $isNewModule ? 'class="table-light"' : '' ?>>
                                    <td><?= $isNewModule ? '<strong>' . e(ucfirst($p['module'])) . '</strong>' : '' ?></td>
                                    <td><?= e($p['label']) ?></td>
                                    <td class="text-center"><input type="checkbox" checked disabled class="form-check-input"></td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" name="perms[manager][]" value="<?= $p['id'] ?>"
                                            <?= in_array($p['id'], $rolePerms['manager'] ?? []) ? 'checked' : '' ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" name="perms[staff][]" value="<?= $p['id'] ?>"
                                            <?= in_array($p['id'], $rolePerms['staff'] ?? []) ? 'checked' : '' ?>>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu phân quyền</button>
                    </div>
                </div>
            </div>
        </form>
