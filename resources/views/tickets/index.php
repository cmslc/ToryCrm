<?php $pageTitle = 'Ticket hỗ trợ'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Ticket hỗ trợ</h4>
            <div>
                <a href="<?= url('tickets/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo ticket</a>
            </div>
        </div>

        <?php
            $sc = ['open'=>'info','in_progress'=>'primary','waiting'=>'warning','resolved'=>'success','closed'=>'secondary'];
            $sl = ['open'=>'Mở','in_progress'=>'Đang xử lý','waiting'=>'Chờ phản hồi','resolved'=>'Đã xử lý','closed'=>'Đóng'];
            $si = ['open'=>'ri-inbox-line','in_progress'=>'ri-loader-4-line','waiting'=>'ri-time-line','resolved'=>'ri-check-double-line','closed'=>'ri-lock-line'];
        ?>
        <div class="row">
            <?php foreach (['open','in_progress','waiting','resolved','closed'] as $st): ?>
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-3">
                                    <div class="avatar-title rounded-circle bg-<?= $sc[$st] ?>-subtle text-<?= $sc[$st] ?>">
                                        <i class="<?= $si[$st] ?> fs-5"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-muted mb-1"><?= $sl[$st] ?></p>
                                    <h4 class="mb-0"><?= $statusStats[$st] ?? 0 ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('tickets') ?>" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="search" placeholder="Tìm kiếm..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <?php foreach ($sl as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="priority" class="form-select">
                            <option value="">Ưu tiên</option>
                            <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Thấp</option>
                            <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Trung bình</option>
                            <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Cao</option>
                            <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Khẩn cấp</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="category_id" class="form-select">
                            <option value="">Danh mục</option>
                            <?php foreach ($categories ?? [] as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('tickets') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã ticket</th>
                                <th>Tiêu đề</th>
                                <th>Danh mục</th>
                                <th>Khách hàng</th>
                                <th>Ưu tiên</th>
                                <th>Trạng thái</th>
                                <th>Phụ trách</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tickets['items'])): ?>
                                <?php
                                    $pc = ['low'=>'info','medium'=>'warning','high'=>'danger','urgent'=>'danger'];
                                    $pl = ['low'=>'Thấp','medium'=>'TB','high'=>'Cao','urgent'=>'Khẩn'];
                                ?>
                                <?php foreach ($tickets['items'] as $ticket): ?>
                                    <tr>
                                        <td><span class="fw-medium text-muted"><?= e($ticket['ticket_code']) ?></span></td>
                                        <td><a href="<?= url('tickets/' . $ticket['id']) ?>" class="fw-medium text-dark"><?= e($ticket['title']) ?></a></td>
                                        <td>
                                            <?php if (!empty($ticket['category_name'])): ?>
                                                <span class="badge" style="background-color:<?= safe_color($ticket['category_color'] ?? null) ?>"><?= e($ticket['category_name']) ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($ticket['contact_name'] ?? '-') ?></td>
                                        <td><span class="badge bg-<?= $pc[$ticket['priority']] ?? 'secondary' ?>-subtle text-<?= $pc[$ticket['priority']] ?? 'secondary' ?>"><?= $pl[$ticket['priority']] ?? '' ?></span></td>
                                        <td><span class="badge bg-<?= $sc[$ticket['status']] ?? 'secondary' ?>"><?= $sl[$ticket['status']] ?? $ticket['status'] ?></span></td>
                                        <td><?= e($ticket['assigned_name'] ?? '-') ?></td>
                                        <td><?= format_date($ticket['created_at']) ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('tickets/' . $ticket['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('tickets/' . $ticket['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('tickets/' . $ticket['id'] . '/delete') ?>" onsubmit="return confirm('Xác nhận xóa?')">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-customer-service-2-line fs-1 d-block mb-2"></i>Chưa có ticket</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($tickets['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($tickets['items']) ?> / <?= $tickets['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $tickets['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $tickets['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('tickets?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
