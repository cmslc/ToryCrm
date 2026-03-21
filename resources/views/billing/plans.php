<?php $pageTitle = 'Chọn gói dịch vụ'; ?>

<!-- Page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Chọn gói dịch vụ</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">ToryCRM</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('billing') ?>">Thanh toán</a></li>
                    <li class="breadcrumb-item active">Chọn gói</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Billing cycle toggle -->
<div class="row justify-content-center mb-4">
    <div class="col-auto">
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="billingToggle" id="monthlyToggle" value="monthly" checked>
            <label class="btn btn-outline-primary" for="monthlyToggle">Hàng tháng</label>
            <input type="radio" class="btn-check" name="billingToggle" id="yearlyToggle" value="yearly">
            <label class="btn btn-outline-primary" for="yearlyToggle">Hàng năm <span class="badge bg-success ms-1">-20%</span></label>
        </div>
    </div>
</div>

<!-- Plans -->
<div class="row justify-content-center">
    <?php
    $currentPlanId = $currentSubscription['plan_id'] ?? null;
    $planIcons = ['ri-rocket-line', 'ri-briefcase-line', 'ri-building-2-line'];
    $planColors = ['primary', 'info', 'warning'];
    $isPopular = [false, false, true]; // Enterprise is most popular
    ?>
    <?php foreach ($plans as $i => $plan): ?>
        <?php
            $icon = $planIcons[$i] ?? 'ri-star-line';
            $color = $planColors[$i] ?? 'primary';
            $popular = $isPopular[$i] ?? false;
            $isCurrent = ($currentPlanId == $plan['id']);
            $features = !empty($plan['features']) ? json_decode($plan['features'], true) : [];
        ?>
        <div class="col-lg-4 col-md-6">
            <div class="card <?= $popular ? 'border-warning shadow' : '' ?> <?= $isCurrent ? 'border-success' : '' ?>">
                <?php if ($popular): ?>
                    <div class="card-header bg-warning text-dark text-center py-2">
                        <i class="ri-star-fill me-1"></i> Phổ biến nhất
                    </div>
                <?php endif; ?>
                <?php if ($isCurrent): ?>
                    <div class="card-header bg-success text-white text-center py-2">
                        <i class="ri-check-line me-1"></i> Gói hiện tại
                    </div>
                <?php endif; ?>
                <div class="card-body text-center p-4">
                    <div class="avatar-md mx-auto mb-3">
                        <span class="avatar-title bg-<?= $color ?>-subtle rounded-circle fs-1">
                            <i class="<?= $icon ?> text-<?= $color ?>"></i>
                        </span>
                    </div>
                    <h4 class="mb-2"><?= e($plan['name']) ?></h4>
                    <p class="text-muted mb-4"><?= e($plan['description'] ?? '') ?></p>

                    <div class="mb-4">
                        <h2 class="mb-0">
                            <span class="plan-price-monthly"><?= number_format($plan['price_monthly'] ?? 0, 0, ',', '.') ?></span>
                            <span class="plan-price-yearly d-none"><?= number_format($plan['price_yearly'] ?? 0, 0, ',', '.') ?></span>
                            <small class="text-muted fs-6"> d/<span class="plan-cycle-label">tháng</span></small>
                        </h2>
                    </div>

                    <ul class="list-unstyled text-start mb-4">
                        <li class="mb-2">
                            <i class="ri-check-line text-success me-2"></i>
                            <?= (int)($plan['max_users'] ?? 0) > 0 ? $plan['max_users'] : 'Không giới hạn' ?> người dùng
                        </li>
                        <li class="mb-2">
                            <i class="ri-check-line text-success me-2"></i>
                            <?= (int)($plan['max_contacts'] ?? 0) > 0 ? number_format($plan['max_contacts']) : 'Không giới hạn' ?> liên hệ
                        </li>
                        <li class="mb-2">
                            <i class="ri-check-line text-success me-2"></i>
                            <?= (int)($plan['max_deals'] ?? 0) > 0 ? number_format($plan['max_deals']) : 'Không giới hạn' ?> cơ hội
                        </li>
                        <li class="mb-2">
                            <i class="ri-check-line text-success me-2"></i>
                            <?= (int)($plan['max_storage_mb'] ?? 0) > 0 ? round($plan['max_storage_mb']/1024, 1) . ' GB' : 'Không giới hạn' ?> lưu trữ
                        </li>
                        <?php if (is_array($features)): ?>
                            <?php foreach ($features as $feature): ?>
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    <?= e($feature) ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>

                    <?php if ($isCurrent): ?>
                        <button class="btn btn-success w-100" disabled>
                            <i class="ri-check-double-line me-1"></i> Gói hiện tại
                        </button>
                    <?php else: ?>
                        <form method="POST" action="<?= url('billing/subscribe') ?>">
                            <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                            <input type="hidden" name="billing_cycle" value="monthly" class="billing-cycle-input">
                            <button type="submit" class="btn btn-<?= $popular ? 'warning' : 'primary' ?> w-100">
                                <i class="ri-shopping-cart-line me-1"></i> Chọn gói này
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyToggle = document.getElementById('monthlyToggle');
    const yearlyToggle = document.getElementById('yearlyToggle');
    const monthlyPrices = document.querySelectorAll('.plan-price-monthly');
    const yearlyPrices = document.querySelectorAll('.plan-price-yearly');
    const cycleLabels = document.querySelectorAll('.plan-cycle-label');
    const cycleInputs = document.querySelectorAll('.billing-cycle-input');

    function updatePricing(cycle) {
        monthlyPrices.forEach(el => el.classList.toggle('d-none', cycle === 'yearly'));
        yearlyPrices.forEach(el => el.classList.toggle('d-none', cycle === 'monthly'));
        cycleLabels.forEach(el => el.textContent = cycle === 'yearly' ? 'năm' : 'tháng');
        cycleInputs.forEach(el => el.value = cycle);
    }

    monthlyToggle.addEventListener('change', () => updatePricing('monthly'));
    yearlyToggle.addEventListener('change', () => updatePricing('yearly'));
});
</script>
