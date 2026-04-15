<?php
$pageTitle = 'Báo giá';
$currentStatus = $filters['status'] ?? '';
$qsc = ['pending'=>'warning','approved'=>'primary','has_order'=>'success','no_order'=>'info','deleted'=>'danger'];
$qsl = ['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','has_order'=>'Đã tạo ĐH','no_order'=>'Chưa tạo ĐH','deleted'=>'Đã xóa'];
$totalAll = 0;
foreach ($stats as $v) $totalAll += (int)$v;
?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Báo giá</h4>
            <div class="d-flex gap-2">
                <a href="<?= url('quotations/create') ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i> Tạo báo giá</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header p-2">
                <form method="GET" action="<?= url('quotations') ?>" class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="search-box" style="min-width:200px;max-width:300px">
                        <input type="text" class="form-control" name="search" placeholder="Tìm mã BG, tiêu đề..." value="<?= e($filters['search'] ?? '') ?>">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                    <select name="contact_id" class="form-select searchable-select" style="width:auto;min-width:140px;max-width:200px" onchange="this.form.submit()">
                        <option value="">Khách hàng</option>
                        <?php foreach ($contacts ?? [] as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filters['contact_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php $dp = $filters['date_period'] ?? ''; ?>
                    <select name="date_period" class="form-select" style="width:auto;min-width:140px" onchange="if(this.value==='custom'){document.getElementById('qCustomDate').classList.remove('d-none')}else{this.form.submit()}">
                        <option value="">Thời gian</option>
                        <option value="today" <?= $dp === 'today' ? 'selected' : '' ?>>Hôm nay</option>
                        <option value="yesterday" <?= $dp === 'yesterday' ? 'selected' : '' ?>>Hôm qua</option>
                        <option value="this_week" <?= $dp === 'this_week' ? 'selected' : '' ?>>Tuần này</option>
                        <option value="this_month" <?= $dp === 'this_month' ? 'selected' : '' ?>>Tháng này</option>
                        <option value="last_month" <?= $dp === 'last_month' ? 'selected' : '' ?>>Tháng trước</option>
                        <option value="this_year" <?= $dp === 'this_year' ? 'selected' : '' ?>>Năm nay</option>
                        <option value="custom" <?= $dp === 'custom' ? 'selected' : '' ?>>Thời gian khác</option>
                    </select>
                    <div id="qCustomDate" class="d-flex gap-1 <?= $dp === 'custom' ? '' : 'd-none' ?>">
                        <input type="date" name="date_from" class="form-control" style="width:auto" value="<?= e($filters['date_from'] ?? '') ?>">
                        <input type="date" name="date_to" class="form-control" style="width:auto" value="<?= e($filters['date_to'] ?? '') ?>">
                    </div>
                    <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
                    <button type="submit" class="btn btn-primary"><i class="ri-search-line me-1"></i> Tìm</button>
                    <?php if (!empty(array_filter($filters ?? []))): ?>
                        <a href="<?= url('quotations') ?>" class="btn btn-soft-danger"><i class="ri-refresh-line me-1"></i> Xóa lọc</a>
                    <?php endif; ?>
                    <select name="per_page" class="form-select ms-auto" style="width:auto;min-width:90px" onchange="this.form.submit()">
                        <?php foreach ([10,20,50,100] as $pp): ?>
                        <option value="<?= $pp ?>" <?= ($filters['per_page'] ?? 10) == $pp ? 'selected' : '' ?>><?= $pp ?> dòng</option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="card-body py-2 px-3 d-flex align-items-center gap-1 border-top">
                <div class="flex-grow-1 d-flex" style="overflow-x:auto;scrollbar-width:none;-webkit-overflow-scrolling:touch">
                    <div class="d-flex gap-1 flex-nowrap">
                        <a href="<?= url('quotations?' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>" class="btn <?= !$currentStatus ? 'btn-dark' : 'btn-soft-dark' ?> btn-label right rounded-pill text-nowrap waves-effect">
                            Tất cả <span class="label-icon align-middle rounded-pill fs-12 ms-2"><?= number_format($totalAll) ?></span>
                        </a>
                        <?php foreach ($qsl as $key => $label):
                            $count = (int)($stats[$key] ?? 0);
                            $color = $qsc[$key] ?? 'secondary';
                            $isActive = $currentStatus === $key;
                        ?>
                        <a href="<?= url('quotations?status=' . $key . '&' . http_build_query(array_diff_key($filters ?? [], ['status'=>'','page'=>'']))) ?>"
                           class="btn <?= $isActive ? "btn-{$color}" : "btn-soft-{$color}" ?> btn-label right rounded-pill text-nowrap waves-effect">
                            <?= $label ?> <span class="label-icon align-middle rounded-pill fs-12 ms-2"><?= number_format($count) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $sc = ['draft'=>'secondary','sent'=>'info','accepted'=>'success','rejected'=>'danger','expired'=>'warning'];
        $sl = ['draft'=>'Nháp','sent'=>'Đã gửi','accepted'=>'Chấp nhận','rejected'=>'Từ chối','expired'=>'Hết hạn'];
        ?>

        <div class="card">
            <div class="card-body p-0">
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
                                <th>PDF</th>
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
                                        <td><a href="<?= url('quotations/' . $q['id'] . '/pdf') ?>" target="_blank" class="btn btn-soft-danger btn-icon" title="Xem PDF"><i class="ri-file-pdf-2-line"></i></a></td>
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
                                <tr><td colspan="9" class="text-center py-4 text-muted"><i class="ri-file-text-line fs-1 d-block mb-2"></i>Chưa có báo giá</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (($quotations['total_pages'] ?? 0) > 1): ?>
                    <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                        <div class="text-muted fs-13">Hiển thị <strong><?= (($quotations['page'] - 1) * ($filters['per_page'] ?? 10)) + 1 ?> - <?= min($quotations['page'] * ($filters['per_page'] ?? 10), $quotations['total']) ?></strong> / <strong><?= number_format($quotations['total']) ?></strong></div>
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
