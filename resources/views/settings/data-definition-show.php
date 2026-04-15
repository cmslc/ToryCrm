<?php $pageTitle = 'Định nghĩa dữ liệu - ' . $moduleInfo['label']; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('settings/data-definition') ?>" class="text-primary"><i class="ri-arrow-left-line fs-18"></i></a>
        <div>
            <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="<?= url('settings/data-definition') ?>">Định nghĩa dữ liệu</a></li><li class="breadcrumb-item active"><?= e($moduleInfo['label']) ?></li></ol></nav>
            <h4 class="mb-0"><?= e($moduleInfo['label']) ?></h4>
        </div>
    </div>
    <a href="<?= url('custom-fields?module=' . $module) ?>" class="btn btn-primary"><i class="ri-add-line me-1"></i>Thêm trường</a>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabActive">Đang sử dụng <span class="badge bg-primary-subtle text-primary ms-1" id="activeCount">0</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabSystem">Hệ thống <span class="badge bg-secondary-subtle text-secondary ms-1" id="systemCount">0</span></a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabCustom">Tùy chỉnh <span class="badge bg-info-subtle text-info ms-1" id="customCount">0</span></a></li>
        </ul>
    </div>
    <div class="card-body p-0">
        <div class="tab-content">
            <!-- Active fields -->
            <div class="tab-pane active" id="tabActive">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Tên thuộc tính</th>
                                <th>Mã thuộc tính</th>
                                <th>Kiểu dữ liệu</th>
                                <th class="text-center">Bắt buộc</th>
                                <th>Giá trị mặc định</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $activeCount = 0; $systemCount = 0; $customCount = 0;
                            $idx = 0;
                            foreach ($fields as $f):
                                if ($f['is_system']) { $systemCount++; continue; }
                                if ($f['is_custom']) { $customCount++; }
                                $activeCount++;
                                $idx++;
                            ?>
                            <tr>
                                <td class="text-muted"><?= $idx ?></td>
                                <td>
                                    <span class="fw-medium"><?= e($f['label']) ?></span>
                                    <?php if ($f['is_custom']): ?><span class="badge bg-info-subtle text-info ms-1">Tùy chỉnh</span><?php endif; ?>
                                </td>
                                <td><code class="fs-12"><?= e($f['name']) ?></code></td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= e($f['type']) ?></span>
                                    <span class="text-muted fs-11 ms-1"><?= e($f['raw_type']) ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($f['required']): ?>
                                    <span class="badge bg-danger">Có</span>
                                    <?php else: ?>
                                    <span class="text-muted">Không</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted fs-13"><?= $f['default'] !== null ? e($f['default']) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- System fields -->
            <div class="tab-pane" id="tabSystem">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Tên thuộc tính</th>
                                <th>Mã thuộc tính</th>
                                <th>Kiểu dữ liệu</th>
                                <th>Giá trị mặc định</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sidx = 0; foreach ($fields as $f):
                                if (!$f['is_system']) continue;
                                $sidx++;
                            ?>
                            <tr>
                                <td class="text-muted"><?= $sidx ?></td>
                                <td><span class="fw-medium"><?= e($f['label']) ?></span></td>
                                <td><code class="fs-12"><?= e($f['name']) ?></code></td>
                                <td><span class="badge bg-light text-dark"><?= e($f['type']) ?></span></td>
                                <td class="text-muted fs-13"><?= $f['default'] !== null ? e($f['default']) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Custom fields -->
            <div class="tab-pane" id="tabCustom">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px">#</th>
                                <th>Tên thuộc tính</th>
                                <th>Mã thuộc tính</th>
                                <th>Kiểu dữ liệu</th>
                                <th class="text-center">Bắt buộc</th>
                                <th>Giá trị mặc định</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $cidx = 0; foreach ($fields as $f):
                                if (!$f['is_custom']) continue;
                                $cidx++;
                            ?>
                            <tr>
                                <td class="text-muted"><?= $cidx ?></td>
                                <td><span class="fw-medium"><?= e($f['label']) ?></span></td>
                                <td><code class="fs-12"><?= e($f['name']) ?></code></td>
                                <td><span class="badge bg-info-subtle text-info"><?= e($f['type']) ?></span></td>
                                <td class="text-center">
                                    <?= $f['required'] ? '<span class="badge bg-danger">Có</span>' : '<span class="text-muted">Không</span>' ?>
                                </td>
                                <td class="text-muted fs-13"><?= $f['default'] !== null ? e($f['default']) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ($cidx === 0): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có trường tùy chỉnh. <a href="<?= url('custom-fields?module=' . $module) ?>">Thêm mới</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('activeCount').textContent = <?= $activeCount ?>;
document.getElementById('systemCount').textContent = <?= $systemCount ?>;
document.getElementById('customCount').textContent = <?= $customCount ?>;
</script>
