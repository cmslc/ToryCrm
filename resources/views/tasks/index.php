<?php $pageTitle = 'Công việc'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Công việc</h4>
            <div>
                <a href="<?= url('tasks/kanban') ?>" class="btn btn-soft-info me-1"><i class="ri-layout-masonry-line me-1"></i> Kanban</a>
                <a href="<?= url('tasks/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Thêm công việc</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('tasks') ?>" class="row g-3 mb-4">
                    <div class="col-md-3"><input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= e($filters['search'] ?? '') ?>"></div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="todo" <?= ($filters['status'] ?? '') === 'todo' ? 'selected' : '' ?>>Cần làm</option>
                            <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>Đang làm</option>
                            <option value="review" <?= ($filters['status'] ?? '') === 'review' ? 'selected' : '' ?>>Review</option>
                            <option value="done" <?= ($filters['status'] ?? '') === 'done' ? 'selected' : '' ?>>Hoàn thành</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="priority" class="form-select">
                            <option value="">Ưu tiên</option>
                            <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Khẩn cấp</option>
                            <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Cao</option>
                            <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>TB</option>
                            <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Thấp</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('tasks') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Công việc</th><th>Trạng thái</th><th>Ưu tiên</th><th>Phụ trách</th><th>Hạn</th><th>Liên quan</th><th>Thao tác</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tasks['items'])): foreach ($tasks['items'] as $task): ?>
                                <tr>
                                    <td><a href="<?= url('tasks/' . $task['id']) ?>" class="fw-medium text-dark"><?= e($task['title']) ?></a></td>
                                    <td>
                                        <?php $sc=['todo'=>'secondary','in_progress'=>'primary','review'=>'warning','done'=>'success']; $sl=['todo'=>'Cần làm','in_progress'=>'Đang làm','review'=>'Review','done'=>'Xong']; ?>
                                        <span class="badge bg-<?= $sc[$task['status']] ?? 'secondary' ?>"><?= $sl[$task['status']] ?? '' ?></span>
                                    </td>
                                    <td>
                                        <?php $pc=['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger']; $pl=['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn']; ?>
                                        <span class="badge bg-<?= $pc[$task['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$task['priority']] ?? 'secondary' ?>"><?= $pl[$task['priority']] ?? '' ?></span>
                                    </td>
                                    <td><?= e($task['assigned_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($task['due_date']): ?>
                                            <?php $isOverdue = strtotime($task['due_date']) < time() && $task['status'] !== 'done'; ?>
                                            <span class="<?= $isOverdue ? 'text-danger fw-medium' : 'text-muted' ?>"><?= format_datetime($task['due_date']) ?></span>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $task['contact_first_name'] ? e($task['contact_first_name']) : '' ?>
                                        <?= $task['deal_title'] ? '<br><small class="text-muted">' . e($task['deal_title']) . '</small>' : '' ?>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="<?= url('tasks/' . $task['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                <li><form method="POST" action="<?= url('tasks/' . $task['id'] . '/delete') ?>" onsubmit="return confirm('Xóa?')"><?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button></form></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted"><i class="ri-task-line fs-1 d-block mb-2"></i>Chưa có công việc</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($tasks['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($tasks['items']) ?> / <?= $tasks['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $tasks['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $tasks['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= url('tasks?page=' . $i) ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
