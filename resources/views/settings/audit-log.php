<?php $pageTitle = 'Audit Log'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Audit Log</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
                <li class="breadcrumb-item active">Audit Log</li>
            </ol>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('settings/audit-log') ?>" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <select name="module" class="form-select">
                            <option value="">Tất cả module</option>
                            <?php foreach (['contacts','companies','deals','tasks','orders','products','tickets','campaigns','fund','users'] as $m): ?>
                                <option value="<?= $m ?>" <?= ($filters['module'] ?? '') === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Thời gian</th>
                                <th>Người dùng</th>
                                <th>Hành động</th>
                                <th>Module</th>
                                <th>ID</th>
                                <th>Thay đổi</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($logs['items'])): ?>
                                <?php foreach ($logs['items'] as $log): ?>
                                <tr>
                                    <td class="small"><?= format_datetime($log['created_at']) ?></td>
                                    <td><?= e($log['user_name'] ?? 'System') ?></td>
                                    <td>
                                        <?php
                                        $ac = ['create'=>'success','update'=>'warning','delete'=>'danger','login'=>'info','export'=>'primary'];
                                        ?>
                                        <span class="badge bg-<?= $ac[$log['action']] ?? 'secondary' ?>"><?= e($log['action']) ?></span>
                                    </td>
                                    <td><?= e($log['module']) ?></td>
                                    <td><?= $log['entity_id'] ?: '-' ?></td>
                                    <td>
                                        <?php if ($log['old_values'] || $log['new_values']): ?>
                                            <?php
                                            $old = json_decode($log['old_values'] ?? '{}', true) ?: [];
                                            $new = json_decode($log['new_values'] ?? '{}', true) ?: [];
                                            $changes = array_keys(array_merge($old, $new));
                                            ?>
                                            <small class="text-muted">
                                                <?php foreach (array_slice($changes, 0, 3) as $field): ?>
                                                    <span class="d-block">
                                                        <strong><?= e($field) ?></strong>:
                                                        <?php if (isset($old[$field])): ?>
                                                            <del class="text-danger"><?= e(substr((string)($old[$field] ?? ''), 0, 30)) ?></del>
                                                        <?php endif; ?>
                                                        <?php if (isset($new[$field])): ?>
                                                            <ins class="text-success"><?= e(substr((string)($new[$field] ?? ''), 0, 30)) ?></ins>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                <?php if (count($changes) > 3): ?>
                                                    <span class="text-muted">+<?= count($changes) - 3 ?> fields</span>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted"><?= e($log['ip_address'] ?? '') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted py-3">Chưa có log</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($logs['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Trang <?= $logs['page'] ?> / <?= $logs['total_pages'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= min($logs['total_pages'], 10); $i++): ?>
                                <li class="page-item <?= $i === $logs['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('settings/audit-log?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
