<?php $pageTitle = 'Quản lý người dùng'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Quản lý người dùng</h4>
            <div>
                <a href="<?= url('users/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm người dùng</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('users') ?>" class="d-flex align-items-center gap-2 flex-wrap mb-4">
                    <div class="search-box" style="min-width:180px;max-width:280px">
                        <input type="text" class="form-control" name="search" placeholder="Tên, email, SĐT..." value="<?= e($filters['search'] ?? '') ?>">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                    <select name="role" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                        <option value="">Tất cả vai trò</option>
                        <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="manager" <?= ($filters['role'] ?? '') === 'manager' ? 'selected' : '' ?>>Manager</option>
                        <option value="staff" <?= ($filters['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
                    </select>
                    <select name="status" class="form-select" style="width:auto;min-width:130px" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Bị khóa</option>
                    </select>
                    <?php if (!empty($departments)): ?>
                    <select name="department" class="form-select" style="width:auto;min-width:140px" onchange="this.form.submit()">
                        <option value="">Tất cả phòng ban</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= ($filters['department'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                    <?php if (($filters['search'] ?? '') || ($filters['role'] ?? '') || ($filters['status'] ?? '') !== '' && ($filters['status'] ?? '') !== null || ($filters['department'] ?? '')): ?>
                    <a href="<?= url('users') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
                    <?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Phòng ban</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Đăng nhập cuối</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users['items'])): ?>
                                <?php
                                $rc = ['admin'=>'danger','manager'=>'warning','staff'=>'info'];
                                $rl = ['admin'=>'Admin','manager'=>'Manager','staff'=>'Staff'];
                                ?>
                                <?php foreach ($users['items'] as $user): ?>
                                    <tr>
                                        <td class="fw-medium"><?= e($user['name']) ?></td>
                                        <td><?= e($user['email']) ?></td>
                                        <td><?= e($user['phone'] ?? '-') ?></td>
                                        <td><?= e($user['department'] ?? '-') ?></td>
                                        <td><span class="badge bg-<?= $rc[$user['role']] ?? 'secondary' ?>"><?= $rl[$user['role']] ?? '' ?></span></td>
                                        <td>
                                            <?= ($user['is_active'] ?? false)
                                                ? '<span class="badge bg-success-subtle text-success">Hoạt động</span>'
                                                : '<span class="badge bg-danger-subtle text-danger">Bị khóa</span>' ?>
                                        </td>
                                        <td><?= !empty($user['last_login']) ? time_ago($user['last_login']) : '-' ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="<?= url('users/' . $user['id'] . '/edit') ?>" class="btn btn btn-soft-primary" title="Sửa"><i class="ri-pencil-line"></i></a>
                                                <form method="POST" action="<?= url('users/' . $user['id'] . '/toggle-active') ?>" class="d-inline" data-confirm="<?= $user['is_active'] ? 'Khóa người dùng này?' : 'Mở khóa người dùng này?' ?>">
                                                    <?= csrf_field() ?>
                                                    <?php if ($user['is_active'] ?? false): ?>
                                                        <button class="btn btn btn-soft-danger" title="Khóa"><i class="ri-lock-line"></i></button>
                                                    <?php else: ?>
                                                        <button class="btn btn btn-soft-success" title="Mở khóa"><i class="ri-lock-unlock-line"></i></button>
                                                    <?php endif; ?>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted"><i class="ri-user-line fs-1 d-block mb-2"></i>Chưa có người dùng</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($users['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($users['items']) ?> / <?= $users['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $users['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $users['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('users?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
