<?php $pageTitle = 'Bảng xếp hạng'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Bảng xếp hạng</h4>
    <div>
        <a href="<?= url('achievements') ?>" class="btn btn-soft-warning"><i class="ri-award-line me-1"></i> Thành tựu</a>
    </div>
</div>

<!-- Month Selector -->
<div class="row mb-4">
    <div class="col-md-3">
        <form method="GET" action="<?= url('leaderboard') ?>">
            <input type="month" name="period" class="form-control" value="<?= e($period) ?>" onchange="this.form.submit()">
        </form>
    </div>
</div>

<?php
$top3 = array_slice($leaderboard, 0, 3);
$rest = array_slice($leaderboard, 3);
$podiumColors = ['warning', 'secondary', 'danger'];
$podiumIcons = ['ri-vip-crown-fill', 'ri-medal-line', 'ri-medal-line'];
$podiumLabels = ['1st', '2nd', '3rd'];
$podiumOrder = [1, 0, 2]; // Display 2nd, 1st, 3rd
?>

<!-- Top 3 Podium -->
<?php if (count($top3) >= 1): ?>
<div class="row justify-content-center mb-4">
    <?php foreach ($podiumOrder as $idx): ?>
        <?php if (isset($top3[$idx])): ?>
            <?php $entry = $top3[$idx]; $isFirst = ($idx === 0); ?>
            <div class="col-md-3 col-sm-4">
                <div class="card text-center border-<?= $podiumColors[$idx] ?> <?= $isFirst ? 'shadow-lg' : '' ?>" style="<?= $isFirst ? 'margin-top: -20px; z-index: 2;' : '' ?>">
                    <div class="card-body py-4">
                        <div class="position-relative d-inline-block mb-3">
                            <div class="avatar-lg mx-auto">
                                <div class="avatar-title rounded-circle bg-<?= $podiumColors[$idx] ?>-subtle text-<?= $podiumColors[$idx] ?>" style="width:<?= $isFirst ? '80' : '64' ?>px;height:<?= $isFirst ? '80' : '64' ?>px;font-size:<?= $isFirst ? '28' : '22' ?>px">
                                    <?= strtoupper(mb_substr($entry['name'] ?? 'U', 0, 1)) ?>
                                </div>
                            </div>
                            <span class="position-absolute top-0 end-0 badge bg-<?= $podiumColors[$idx] ?>" style="font-size:14px">
                                <i class="<?= $podiumIcons[$idx] ?>"></i> <?= $podiumLabels[$idx] ?>
                            </span>
                        </div>
                        <h5 class="mb-1"><?= e($entry['name'] ?? '') ?></h5>
                        <p class="text-<?= $podiumColors[$idx] ?> fw-bold mb-1" style="font-size:<?= $isFirst ? '24px' : '20px' ?>">
                            <?= number_format((float)($entry['revenue'] ?? 0), 0, ',', '.') ?> VNĐ
                        </p>
                        <div class="d-flex justify-content-center gap-3 text-muted">
                            <small><i class="ri-hand-coin-line me-1"></i><?= (int)($entry['deals_won'] ?? 0) ?> đơn</small>
                            <small><i class="ri-calendar-check-line me-1"></i><?= (int)($entry['activities_count'] ?? 0) ?> hoạt động</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Full Ranking Table -->
<div class="card">
    <div class="card-header">
        <h6 class="card-title mb-0"><i class="ri-bar-chart-horizontal-line me-1"></i> Bảng xếp hạng chi tiết</h6>
    </div>
    <div class="card-body">
        <?php if (empty($leaderboard)): ?>
            <div class="text-center py-5">
                <i class="ri-trophy-line text-muted" style="font-size:64px"></i>
                <p class="text-muted mt-3">Chưa có dữ liệu cho tháng này.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">Hạng</th>
                        <th>Nhân viên</th>
                        <th class="text-center">Đơn hàng</th>
                        <th class="text-end">Doanh thu</th>
                        <th class="text-center">Hoạt động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaderboard as $entry): ?>
                    <?php
                        $rankBadge = match((int)$entry['rank']) {
                            1 => '<span class="badge bg-warning text-dark"><i class="ri-vip-crown-fill me-1"></i>1</span>',
                            2 => '<span class="badge bg-secondary"><i class="ri-medal-line me-1"></i>2</span>',
                            3 => '<span class="badge bg-danger"><i class="ri-medal-line me-1"></i>3</span>',
                            default => '<span class="text-muted fw-bold">#' . $entry['rank'] . '</span>',
                        };
                        $isMe = ((int)($entry['user_id'] ?? 0)) === ($_SESSION['user']['id'] ?? 0);
                    ?>
                    <tr class="<?= $isMe ? 'table-primary' : '' ?>">
                        <td class="text-center"><?= $rankBadge ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-xs me-2">
                                    <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                        <?= strtoupper(mb_substr($entry['name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?= e($entry['name'] ?? '') ?> <?= $isMe ? '<span class="badge bg-primary-subtle text-primary">Bạn</span>' : '' ?></h6>
                                    <small class="text-muted"><?= e($entry['email'] ?? '') ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center"><span class="fw-medium"><?= (int)($entry['deals_won'] ?? 0) ?></span></td>
                        <td class="text-end fw-medium"><?= number_format((float)($entry['revenue'] ?? 0), 0, ',', '.') ?> VNĐ</td>
                        <td class="text-center"><?= (int)($entry['activities_count'] ?? 0) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
