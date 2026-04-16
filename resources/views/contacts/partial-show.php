<?php
$statusLabels = []; $statusColors = [];
foreach ($contactStatuses ?? [] as $_cs) { $statusLabels[$_cs['slug']] = $_cs['name']; $statusColors[$_cs['slug']] = $_cs['color']; }
if (empty($statusLabels)) { $statusLabels = ['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất']; $statusColors = ['new'=>'info','contacted'=>'primary','qualified'=>'warning','converted'=>'success','lost'=>'danger']; }
$st = $contact['status'] ?? 'new';
?>

<!-- Header: 2 columns - left avatar+name, right info -->
<div class="d-flex gap-3 mb-4 pb-3 border-bottom">
    <!-- Left: Avatar + Name -->
    <div class="text-center flex-shrink-0" style="min-width:120px">
        <div class="avatar-lg mx-auto mb-2">
            <div class="avatar-title rounded-circle bg-primary-subtle text-primary fs-24">
                <?= strtoupper(substr($contact['first_name'] ?? '', 0, 1)) ?>
            </div>
        </div>
        <h6 class="mb-1"><?= e(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')) ?></h6>
        <?php if ($contact['position']): ?><div class="text-muted fs-12 mb-1"><?= e($contact['position']) ?></div><?php endif; ?>
        <span class="badge bg-<?= $statusColors[$st] ?? 'secondary' ?>-subtle text-<?= $statusColors[$st] ?? 'secondary' ?>">
            <?= $statusLabels[$st] ?? $st ?>
        </span>
    </div>

    <!-- Right: Contact Info -->
    <div class="flex-grow-1">
        <table class="table table-borderless mb-0">
            <tbody>
                <?php if (!empty($contact['email'])): ?>
                <tr><td class="text-muted py-1 ps-0" style="width:90px"><i class="ri-mail-line me-1"></i>Email</td><td class="py-1"><?= e($contact['email']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($contact['phone'])): ?>
                <tr><td class="text-muted py-1 ps-0"><i class="ri-phone-line me-1"></i>SĐT</td><td class="py-1"><?= e($contact['phone']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($contact['company_name'])): ?>
                <tr><td class="text-muted py-1 ps-0"><i class="ri-building-line me-1"></i>Công ty</td><td class="py-1 fw-medium" style="word-break:break-word"><?= e($contact['company_name']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($contact['source_name'])): ?>
                <tr><td class="text-muted py-1 ps-0"><i class="ri-focus-line me-1"></i>Nguồn</td><td class="py-1"><?= e($contact['source_name']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($contact['owner_name'])): ?>
                <tr><td class="text-muted py-1 ps-0"><i class="ri-user-star-line me-1"></i>Phụ trách</td><td class="py-1"><?= user_avatar($contact['owner_name'] ?? null) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($contact['address'])): ?>
                <tr><td class="text-muted py-1 ps-0"><i class="ri-map-pin-line me-1"></i>Địa chỉ</td><td class="py-1"><?= e($contact['address']) ?><?= $contact['city'] ? ', ' . e($contact['city']) : '' ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Stats
$cid = $contact['id'];
$dealStats = \Core\Database::fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(value),0) as total FROM deals WHERE contact_id = ? AND status = 'open'", [$cid]);
$wonStats = \Core\Database::fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(value),0) as total FROM deals WHERE contact_id = ? AND status = 'won'", [$cid]);
$orderStats = \Core\Database::fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(total),0) as total FROM orders WHERE contact_id = ? AND is_deleted = 0", [$cid]);
$ticketStats = \Core\Database::fetch("SELECT COUNT(*) as cnt FROM tickets WHERE contact_id = ? AND status IN ('open','in_progress')", [$cid]);
$lastContact = \Core\Database::fetch("SELECT MAX(created_at) as last_at FROM activities WHERE contact_id = ?", [$cid]);
?>

<!-- Stats Grid -->
<div class="row g-2 mb-4">
    <div class="col-6">
        <div class="border rounded p-2 text-center">
            <div class="fw-semibold text-primary"><?= $dealStats['cnt'] ?? 0 ?></div>
            <div class="text-muted fs-12">Cơ hội mở</div>
        </div>
    </div>
    <div class="col-6">
        <div class="border rounded p-2 text-center">
            <div class="fw-semibold text-success"><?= $wonStats['cnt'] ?? 0 ?></div>
            <div class="text-muted fs-12">Đã chốt</div>
        </div>
    </div>
    <div class="col-6">
        <div class="border rounded p-2 text-center">
            <div class="fw-semibold text-info"><?= $orderStats['cnt'] ?? 0 ?></div>
            <div class="text-muted fs-12">Đơn hàng</div>
        </div>
    </div>
    <div class="col-6">
        <div class="border rounded p-2 text-center">
            <div class="fw-semibold text-warning"><?= $ticketStats['cnt'] ?? 0 ?></div>
            <div class="text-muted fs-12">Ticket mở</div>
        </div>
    </div>
</div>

<!-- Revenue Summary -->
<?php $totalRevenue = ($wonStats['total'] ?? 0) + ($orderStats['total'] ?? 0); ?>
<?php if ($totalRevenue > 0): ?>
<div class="border rounded p-3 mb-4 bg-success-subtle">
    <div class="d-flex justify-content-between align-items-center">
        <span class="text-muted fs-13"><i class="ri-money-dollar-circle-line me-1"></i>Tổng doanh thu</span>
        <span class="fw-semibold text-success"><?= format_money($totalRevenue) ?></span>
    </div>
    <?php if (($wonStats['total'] ?? 0) > 0): ?>
    <div class="d-flex justify-content-between mt-1">
        <small class="text-muted">Deal thắng</small>
        <small><?= format_money($wonStats['total']) ?></small>
    </div>
    <?php endif; ?>
    <?php if (($orderStats['total'] ?? 0) > 0): ?>
    <div class="d-flex justify-content-between mt-1">
        <small class="text-muted">Đơn hàng</small>
        <small><?= format_money($orderStats['total']) ?></small>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Last Contact -->
<?php if (!empty($lastContact['last_at'])): ?>
<div class="d-flex justify-content-between align-items-center mb-3 px-1">
    <span class="text-muted fs-13"><i class="ri-time-line me-1"></i>Liên hệ lần cuối</span>
    <span class="fs-13"><?= time_ago($lastContact['last_at']) ?></span>
</div>
<?php endif; ?>

<?php if (!empty($activities)): ?>
<h6 class="mb-3"><i class="ri-history-line me-1"></i> Hoạt động gần đây</h6>
<div class="vstack gap-2 mb-4">
    <?php foreach (array_slice($activities, 0, 5) as $act): ?>
    <div class="d-flex align-items-start gap-2 p-2 rounded" style="background:var(--vz-light,#f3f6f9)">
        <i class="ri-checkbox-blank-circle-fill text-primary mt-1" style="font-size:8px"></i>
        <div class="flex-1">
            <div class="fs-13"><?= e($act['title'] ?? '') ?></div>
            <small class="text-muted"><?= e($act['user_name'] ?? '') ?> · <?= e($act['created_at'] ?? '') ?></small>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="d-flex gap-2">
    <a href="<?= url('contacts/' . $contact['id'] . '/edit') ?>" class="btn btn-primary flex-fill">
        <i class="ri-edit-line me-1"></i> Sửa
    </a>
    <?php if (!empty($contact['phone'])): ?>
    <a href="tel:<?= e($contact['phone']) ?>" class="btn btn-success flex-fill">
        <i class="ri-phone-line me-1"></i> Gọi điện
    </a>
    <?php endif; ?>
    <?php if (!empty($contact['email'])): ?>
    <a href="mailto:<?= e($contact['email']) ?>" class="btn btn-info flex-fill">
        <i class="ri-mail-line me-1"></i> Gửi email
    </a>
    <?php endif; ?>
</div>
