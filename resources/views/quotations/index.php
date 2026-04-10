<?php $pageTitle = 'Báo giá'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo giá</h4>
            <div>
                <a href="<?= url('quotations/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo báo giá</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col">
                <div class="card border-secondary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-secondary-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-draft-line text-secondary fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Nháp</p>
                                <h4 class="mb-0"><?= (int)($stats['draft'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-info-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-send-plane-line text-info fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Đã gửi</p>
                                <h4 class="mb-0"><?= (int)($stats['sent'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-success-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-check-double-line text-success fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Chấp nhận</p>
                                <h4 class="mb-0"><?= (int)($stats['accepted'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-danger-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-close-circle-line text-danger fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Từ chối</p>
                                <h4 class="mb-0"><?= (int)($stats['rejected'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-warning-subtle rounded me-3 d-flex align-items-center justify-content-center">
                                <i class="ri-time-line text-warning fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1">Hết hạn</p>
                                <h4 class="mb-0"><?= (int)($stats['expired'] ?? 0) ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('quotations') ?>" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search" placeholder="Tìm mã BG, tiêu đề..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                            <option value="sent" <?= ($filters['status'] ?? '') === 'sent' ? 'selected' : '' ?>>Đã gửi</option>
                            <option value="accepted" <?= ($filters['status'] ?? '') === 'accepted' ? 'selected' : '' ?>>Chấp nhận</option>
                            <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                            <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Hết hạn</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="contact_id" class="form-select searchable-select">
                            <option value="">Khách hàng</option>
                            <?php foreach ($contacts ?? [] as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($filters['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" placeholder="Từ ngày">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" placeholder="Đến ngày">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i> Lọc</button>
                        <a href="<?= url('quotations') ?>" class="btn btn-soft-secondary">Xóa lọc</a>
                    </div>
                </form>

                <?php
                $sc = ['draft'=>'secondary','sent'=>'info','accepted'=>'success','rejected'=>'danger','expired'=>'warning'];
                $sl = ['draft'=>'Nháp','sent'=>'Đã gửi','accepted'=>'Chấp nhận','rejected'=>'Từ chối','expired'=>'Hết hạn'];
                ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã BG</th>
                                <th>Tiêu đề</th>
                                <th>Khách hàng</th>
                                <th>Tổng tiền</th>
                                <th>Hiệu lực đến</th>
                                <th>Trạng thái</th>
                                <th>Lượt xem</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($quotations['items'])): ?>
                                <?php foreach ($quotations['items'] as $q): ?>
                                    <tr>
                                        <td><a href="<?= url('quotations/' . $q['id']) ?>" class="fw-medium"><?= e($q['quote_number']) ?></a></td>
                                        <td><?= e($q['title'] ?: '-') ?></td>
                                        <td><?= e(trim(($q['contact_first_name'] ?? '') . ' ' . ($q['contact_last_name'] ?? ''))) ?: '-' ?></td>
                                        <td class="fw-medium"><?= format_money($q['total'] ?? 0) ?></td>
                                        <td>
                                            <?php if ($q['valid_until']): ?>
                                                <?php $isExpired = $q['valid_until'] < date('Y-m-d'); ?>
                                                <span class="<?= $isExpired ? 'text-danger' : 'text-success' ?>"><?= format_date($q['valid_until']) ?></span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-<?= $sc[$q['status']] ?? 'secondary' ?>"><?= $sl[$q['status']] ?? '' ?></span></td>
                                        <td><i class="ri-eye-line me-1"></i><?= (int)($q['view_count'] ?? 0) ?></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn btn-soft-secondary" data-bs-toggle="dropdown"><i class="ri-more-fill"></i></button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= url('quotations/' . $q['id']) ?>"><i class="ri-eye-line me-2"></i>Xem</a></li>
                                                    <li><a class="dropdown-item" href="<?= url('quotations/' . $q['id'] . '/edit') ?>"><i class="ri-pencil-line me-2"></i>Sửa</a></li>
                                                    <?php if ($q['status'] === 'draft'): ?>
                                                    <li>
                                                        <form method="POST" action="<?= url('quotations/' . $q['id'] . '/send') ?>" data-confirm="Gửi báo giá này?">
                                                            <?= csrf_field() ?><button class="dropdown-item"><i class="ri-send-plane-line me-2"></i>Gửi</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <?php if (in_array($q['status'], ['accepted', 'sent'])): ?>
                                                    <li>
                                                        <form method="POST" action="<?= url('quotations/' . $q['id'] . '/convert') ?>" data-confirm="Chuyển thành đơn hàng?">
                                                            <?= csrf_field() ?><button class="dropdown-item"><i class="ri-swap-line me-2"></i>Chuyển đơn hàng</button>
                                                        </form>
                                                    </li>
                                                    <?php endif; ?>
                                                    <li><a class="dropdown-item" href="<?= url('quotations/' . $q['id'] . '/pdf') ?>" target="_blank"><i class="ri-printer-line me-2"></i>PDF</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?= url('quotations/' . $q['id'] . '/delete') ?>" data-confirm="Xác nhận xóa báo giá?">
                                                            <?= csrf_field() ?><button class="dropdown-item text-danger"><i class="ri-delete-bin-line me-2"></i>Xóa</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted"><i class="ri-file-text-line fs-1 d-block mb-2"></i>Chưa có báo giá</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($quotations['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Hiển thị <?= count($quotations['items']) ?> / <?= $quotations['total'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $quotations['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $quotations['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('quotations?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
