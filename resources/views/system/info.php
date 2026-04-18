<?php $pageTitle = 'Thông tin hệ thống'; ?>

<div class="page-title-box"><h4 class="mb-0"><i class="ri-server-line me-2"></i> Thông tin hệ thống</h4></div>

<!-- Resource Cards -->
<div class="row mb-1">
    <div class="col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0 me-3"><span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20"><i class="ri-database-2-line"></i></span></div>
                    <div>
                        <p class="text-muted mb-0 fs-12">Database</p>
                        <h5 class="mb-0"><?= $dbStats['size'] ?></h5>
                        <small class="text-muted"><?= $dbStats['tables'] ?> bảng</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0 me-3"><span class="avatar-title bg-<?= $resources['ram_percent'] > 80 ? 'danger' : 'success' ?>-subtle text-<?= $resources['ram_percent'] > 80 ? 'danger' : 'success' ?> rounded-circle fs-20"><i class="ri-cpu-line"></i></span></div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-0 fs-12">RAM</p>
                        <h5 class="mb-0"><?= $resources['ram_used'] ?> / <?= $resources['ram_total'] ?></h5>
                        <div class="progress mt-1" style="height:4px"><div class="progress-bar bg-<?= $resources['ram_percent'] > 80 ? 'danger' : 'success' ?>" style="width:<?= $resources['ram_percent'] ?>%"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0 me-3"><span class="avatar-title bg-<?= $resources['disk_percent'] > 80 ? 'danger' : 'info' ?>-subtle text-<?= $resources['disk_percent'] > 80 ? 'danger' : 'info' ?> rounded-circle fs-20"><i class="ri-hard-drive-2-line"></i></span></div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-0 fs-12">Ổ đĩa</p>
                        <h5 class="mb-0"><?= $resources['disk_used'] ?> / <?= $resources['disk_total'] ?></h5>
                        <div class="progress mt-1" style="height:4px"><div class="progress-bar bg-<?= $resources['disk_percent'] > 80 ? 'danger' : 'info' ?>" style="width:<?= $resources['disk_percent'] ?>%"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm flex-shrink-0 me-3"><span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-20"><i class="ri-time-line"></i></span></div>
                    <div>
                        <p class="text-muted mb-0 fs-12">Uptime</p>
                        <h5 class="mb-0"><?= $server['uptime'] ?></h5>
                        <small class="text-muted">ToryCRM v<?= $version ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Server Info -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-server-line me-2"></i> Server</h5></div>
            <div class="card-body p-2">
                <table class="table table-hover mb-0">
                    <tbody>
                        <tr><td class="text-muted" style="width:40%">PHP</td><td class="fw-medium"><?= $server['php_version'] ?></td></tr>
                        <tr><td class="text-muted">MySQL</td><td class="fw-medium"><?= $server['mysql_version'] ?></td></tr>
                        <tr><td class="text-muted">OS</td><td><?= $server['os'] ?></td></tr>
                        <tr><td class="text-muted">Web Server</td><td><?= $server['server_software'] ?></td></tr>
                        <tr><td class="text-muted">Hostname</td><td><?= $server['hostname'] ?></td></tr>
                        <tr><td class="text-muted">IP</td><td><code><?= $server['server_ip'] ?></code></td></tr>
                        <tr><td class="text-muted">Timezone</td><td><?= $server['timezone'] ?></td></tr>
                        <tr><td class="text-muted">Memory Limit</td><td><?= $server['php_memory_limit'] ?></td></tr>
                        <tr><td class="text-muted">Upload Max</td><td><?= $server['php_max_upload'] ?></td></tr>
                        <tr><td class="text-muted">Post Max</td><td><?= $server['php_max_post'] ?></td></tr>
                        <tr><td class="text-muted">Max Execution</td><td><?= $server['php_max_execution'] ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Plugins -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-plug-line me-2"></i> Plugins (<?= count($plugins) ?>)</h5></div>
            <div class="card-body p-2">
                <table class="table table-hover mb-0">
                    <tbody>
                        <?php foreach ($plugins as $p): ?>
                        <tr>
                            <td><?= e($p['name']) ?></td>
                            <td><code class="fs-11"><?= e($p['slug']) ?></code></td>
                            <td class="text-end">
                                <?php if ($p['is_active']): ?>
                                <span class="badge bg-success-subtle text-success">Bật</span>
                                <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary">Tắt</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Database -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-database-2-line me-2"></i> Database — <?= $dbStats['size'] ?></h5></div>
            <div class="card-body p-2">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Bảng</th><th class="text-end">Rows</th><th class="text-end">Dung lượng</th></tr></thead>
                    <tbody>
                        <?php foreach ($dbStats['top_tables'] as $t): ?>
                        <tr>
                            <td><code class="fs-12"><?= e($t['TABLE_NAME'] ?? $t['table_name'] ?? '') ?></code></td>
                            <td class="text-end"><?= number_format($t['TABLE_ROWS'] ?? $t['table_rows'] ?? 0) ?></td>
                            <td class="text-end"><?= $t['size_mb'] ?> MB</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Record Counts -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-bar-chart-line me-2"></i> Số liệu</h5></div>
            <div class="card-body p-2">
                <?php
                $countLabels = ['contacts'=>'Khách hàng','deals'=>'Cơ hội','tasks'=>'Công việc','orders'=>'Đơn hàng','tickets'=>'Ticket','quotations'=>'Báo giá','contracts'=>'Hợp đồng','fund_transactions'=>'Giao dịch quỹ','debts'=>'Công nợ','email_messages'=>'Email','activities'=>'Hoạt động','users'=>'Người dùng','lead_form_submissions'=>'Lead Forms'];
                ?>
                <table class="table table-hover mb-0">
                    <tbody>
                        <?php foreach ($counts as $table => $count): ?>
                        <tr>
                            <td><?= $countLabels[$table] ?? $table ?></td>
                            <td class="text-end fw-medium"><?= number_format($count) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
