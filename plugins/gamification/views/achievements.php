<?php $pageTitle = 'Thành tựu'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Thành tựu</h4>
    <div>
        <a href="<?= url('leaderboard') ?>" class="btn btn-soft-primary"><i class="ri-trophy-line me-1"></i> Bảng xếp hạng</a>
    </div>
</div>

<?php
$earned = array_filter($achievements, fn($a) => (int)$a['is_earned'] === 1);
$totalPoints = array_sum(array_map(fn($a) => (int)$a['points'], $earned));
?>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title rounded-circle bg-success-subtle text-success">
                            <i class="ri-trophy-line fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Đã đạt được</p>
                        <h4 class="mb-0"><?= count($earned) ?> / <?= count($achievements) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title rounded-circle bg-warning-subtle text-warning">
                            <i class="ri-star-line fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Tổng điểm thành tựu</p>
                        <h4 class="mb-0"><?= $totalPoints ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title rounded-circle bg-info-subtle text-info">
                            <i class="ri-percent-line fs-5"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Hoàn thành</p>
                        <h4 class="mb-0"><?= count($achievements) > 0 ? round(count($earned) / count($achievements) * 100) : 0 ?>%</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Achievement Cards -->
<div class="row">
    <?php foreach ($achievements as $ach):
        $isEarned = (int)$ach['is_earned'] === 1;
        $progress = (int)($ach['progress'] ?? 0);
    ?>
    <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card <?= $isEarned ? '' : 'opacity-75' ?>" style="<?= !$isEarned ? 'filter: grayscale(30%)' : '' ?>">
            <div class="card-body text-center py-4">
                <?php if ($isEarned): ?>
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-success rounded-pill"><i class="ri-check-line"></i></span>
                    </div>
                <?php endif; ?>

                <div class="avatar-lg mx-auto mb-3">
                    <div class="avatar-title rounded-circle bg-<?= e($ach['color'] ?? 'warning') ?>-subtle text-<?= e($ach['color'] ?? 'warning') ?>" style="width:72px;height:72px;font-size:32px">
                        <i class="<?= e($ach['icon'] ?? 'ri-trophy-line') ?>"></i>
                    </div>
                </div>

                <h6 class="mb-1"><?= e($ach['name']) ?></h6>
                <p class="text-muted mb-2" style="font-size:13px"><?= e($ach['description'] ?? '') ?></p>

                <span class="badge bg-<?= e($ach['color'] ?? 'warning') ?>-subtle text-<?= e($ach['color'] ?? 'warning') ?> mb-2">
                    +<?= (int)$ach['points'] ?> điểm
                </span>

                <?php if ($isEarned): ?>
                    <div class="mt-2">
                        <small class="text-success"><i class="ri-check-double-line me-1"></i>Đạt được <?= date('d/m/Y', strtotime($ach['earned_at'])) ?></small>
                    </div>
                <?php else: ?>
                    <div class="mt-2">
                        <div class="progress" style="height: 6px">
                            <div class="progress-bar bg-<?= e($ach['color'] ?? 'warning') ?>" style="width: <?= $progress ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $progress ?>% hoàn thành</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
