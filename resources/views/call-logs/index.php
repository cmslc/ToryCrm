<?php $pageTitle = 'Tổng đài'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Tổng đài - Lịch sử cuộc gọi</h4>
            <a href="<?= url('call-logs/create') ?>" class="btn btn-primary"><i class="ri-phone-line me-1"></i> Ghi nhận cuộc gọi</a>
        </div>

        <!-- Stats -->
        <div class="row mb-0">
            <div class="col-xl-2 col-md-4">
                <div class="card card-animate">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Tổng cuộc gọi</p>
                        <h4 class="mb-0"><?= $stats['total_calls'] ?? 0 ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4">
                <div class="card card-animate">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Gọi đến</p>
                        <h4 class="mb-0 text-info"><?= $stats['inbound'] ?? 0 ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4">
                <div class="card card-animate">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Gọi đi</p>
                        <h4 class="mb-0 text-primary"><?= $stats['outbound'] ?? 0 ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4">
                <div class="card card-animate">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Đã nghe</p>
                        <h4 class="mb-0 text-success"><?= $stats['answered'] ?? 0 ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4">
                <div class="card card-animate">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">Nhỡ</p>
                        <h4 class="mb-0 text-danger"><?= $stats['missed'] ?? 0 ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4">
                <div class="card card-animate">
                    <div class="card-body py-3">
                        <p class="text-muted mb-1 small">TB thời lượng</p>
                        <h4 class="mb-0"><?= round($stats['avg_duration'] ?? 0) ?>s</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= url('call-logs') ?>" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="search" placeholder="SĐT, tên KH..." value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="call_type" class="form-select">
                            <option value="">Loại</option>
                            <option value="inbound" <?= ($filters['call_type'] ?? '') === 'inbound' ? 'selected' : '' ?>>Gọi đến</option>
                            <option value="outbound" <?= ($filters['call_type'] ?? '') === 'outbound' ? 'selected' : '' ?>>Gọi đi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Trạng thái</option>
                            <option value="answered" <?= ($filters['status'] ?? '') === 'answered' ? 'selected' : '' ?>>Đã nghe</option>
                            <option value="missed" <?= ($filters['status'] ?? '') === 'missed' ? 'selected' : '' ?>>Nhỡ</option>
                            <option value="busy" <?= ($filters['status'] ?? '') === 'busy' ? 'selected' : '' ?>>Bận</option>
                            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Lỗi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" placeholder="Từ">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" placeholder="Đến">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                        <a href="<?= url('call-logs') ?>" class="btn btn-soft-secondary">Xóa</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sticky mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Loại</th>
                                <th>Số gọi</th>
                                <th>Số nhận</th>
                                <th>Khách hàng</th>
                                <th>Nhân viên</th>
                                <th>Thời lượng</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($callLogs['items'])): ?>
                                <?php foreach ($callLogs['items'] as $cl): ?>
                                <tr>
                                    <td>
                                        <?= $cl['call_type'] === 'inbound'
                                            ? '<span class="badge bg-info-subtle text-info"><i class="ri-phone-fill me-1"></i>Đến</span>'
                                            : '<span class="badge bg-primary-subtle text-primary"><i class="ri-phone-line me-1"></i>Đi</span>' ?>
                                    </td>
                                    <td class="fw-medium"><?= e($cl['caller_number']) ?></td>
                                    <td><?= e($cl['callee_number'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($cl['contact_first_name']): ?>
                                            <a href="<?= url('contacts/' . $cl['contact_id']) ?>"><?= e($cl['contact_first_name'] . ' ' . ($cl['contact_last_name'] ?? '')) ?></a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= user_avatar($cl['user_name'] ?? null) ?></td>
                                    <td>
                                        <?php
                                        $dur = (int)$cl['duration'];
                                        echo $dur >= 60 ? floor($dur/60) . 'm ' . ($dur%60) . 's' : $dur . 's';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $sc = ['answered'=>'success','missed'=>'danger','busy'=>'warning','failed'=>'secondary','voicemail'=>'info'];
                                        $sl = ['answered'=>'Đã nghe','missed'=>'Nhỡ','busy'=>'Bận','failed'=>'Lỗi','voicemail'=>'Voicemail'];
                                        ?>
                                        <span class="badge bg-<?= $sc[$cl['status']] ?? 'secondary' ?>"><?= $sl[$cl['status']] ?? $cl['status'] ?></span>
                                    </td>
                                    <td class="small"><?= format_datetime($cl['started_at']) ?></td>
                                    <td>
                                        <form method="POST" action="<?= url('call-logs/' . $cl['id'] . '/delete') ?>" data-confirm="Xóa?">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-phone-line fs-1 d-block mb-2"></i>Chưa có cuộc gọi</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($callLogs['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">Trang <?= $callLogs['page'] ?> / <?= $callLogs['total_pages'] ?></div>
                        <nav><ul class="pagination mb-0">
                            <?php for ($i = 1; $i <= $callLogs['total_pages']; $i++): ?>
                                <li class="page-item <?= $i === $callLogs['page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('call-logs?page=' . $i . '&' . http_build_query(array_filter($filters ?? []))) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul></nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
