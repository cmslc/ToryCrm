<?php $pageTitle = 'Leads - ' . e($form['name']); ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0"><?= e($form['name']) ?> <span class="badge bg-primary"><?= $total ?> leads</span></h4>
    <a href="<?= url('lead-forms') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="card">
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <?php foreach ($form['fields'] as $f): ?>
                        <th><?= e($f['label']) ?></th>
                        <?php endforeach; ?>
                        <th>KH</th>
                        <th>Nguồn</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($submissions as $s):
                    $data = json_decode($s['data'], true) ?: [];
                ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <?php foreach ($form['fields'] as $f): ?>
                    <td><?= e($data[$f['name']] ?? '-') ?></td>
                    <?php endforeach; ?>
                    <td>
                        <?php if ($s['contact_id']): ?>
                        <a href="<?= url('contacts/' . $s['contact_id']) ?>" class="badge bg-success-subtle text-success"><?= e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? ''))) ?: 'KH #' . $s['contact_id'] ?></a>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <td class="text-muted fs-12"><?= e($s['source_url'] ? parse_url($s['source_url'], PHP_URL_HOST) : '-') ?></td>
                    <td class="text-muted fs-12"><?= created_ago($s['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($submissions)): ?>
                <tr><td colspan="<?= count($form['fields']) + 3 ?>" class="text-center text-muted py-4">Chưa có leads nào</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
