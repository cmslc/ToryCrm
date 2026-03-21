<?php $pageTitle = e($company['name']); ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($company['name']) ?></h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('companies') ?>">Doanh nghiệp</a></li>
                <li class="breadcrumb-item active"><?= e($company['name']) ?></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title rounded-circle bg-info-subtle text-info fs-24">
                                <?= strtoupper(substr($company['name'], 0, 1)) ?>
                            </div>
                        </div>
                        <h5 class="mb-1"><?= e($company['name']) ?></h5>
                        <p class="text-muted"><?= e($company['industry'] ?? '') ?></p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="<?= url('companies/' . $company['id'] . '/edit') ?>" class="btn btn-primary btn-sm"><i class="ri-pencil-line me-1"></i> Sửa</a>
                            <form method="POST" action="<?= url('companies/' . $company['id'] . '/delete') ?>" onsubmit="return confirm('Xác nhận xóa?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-danger btn-sm"><i class="ri-delete-bin-line me-1"></i> Xóa</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h5 class="card-title mb-0">Thông tin</h5></div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr><th class="text-muted" width="35%">Email</th><td><?= e($company['email'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Điện thoại</th><td><?= e($company['phone'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Website</th><td><?= $company['website'] ? '<a href="' . e($company['website']) . '" target="_blank">' . e($company['website']) . '</a>' : '-' ?></td></tr>
                            <tr><th class="text-muted">MST</th><td><?= e($company['tax_code'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Quy mô</th><td><?= e($company['company_size'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Địa chỉ</th><td><?= e($company['address'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Thành phố</th><td><?= e($company['city'] ?? '-') ?></td></tr>
                            <tr><th class="text-muted">Phụ trách</th><td><?= e($company['owner_name'] ?? '-') ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <?php if ($company['description']): ?>
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Mô tả</h5></div>
                        <div class="card-body"><p><?= nl2br(e($company['description'])) ?></p></div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex">
                        <h5 class="card-title mb-0 flex-grow-1">Liên hệ (<?= count($contacts ?? []) ?>)</h5>
                        <a href="<?= url('contacts/create?company_id=' . $company['id']) ?>" class="btn btn-sm btn-soft-primary">Thêm liên hệ</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($contacts)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr><th>Tên</th><th>Email</th><th>Điện thoại</th><th>Trạng thái</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contacts as $c): ?>
                                            <tr>
                                                <td><a href="<?= url('contacts/' . $c['id']) ?>"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></a></td>
                                                <td><?= e($c['email'] ?? '-') ?></td>
                                                <td><?= e($c['phone'] ?? '-') ?></td>
                                                <td>
                                                    <?php $sc = ['new'=>'info','contacted'=>'primary','qualified'=>'warning','converted'=>'success','lost'=>'danger']; ?>
                                                    <?php $sl = ['new'=>'Mới','contacted'=>'Đã liên hệ','qualified'=>'Tiềm năng','converted'=>'Chuyển đổi','lost'=>'Mất']; ?>
                                                    <span class="badge bg-<?= $sc[$c['status']] ?? 'secondary' ?>-subtle text-<?= $sc[$c['status']] ?? 'secondary' ?>"><?= $sl[$c['status']] ?? $c['status'] ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Chưa có liên hệ</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex">
                        <h5 class="card-title mb-0 flex-grow-1">Cơ hội kinh doanh</h5>
                        <a href="<?= url('deals/create?company_id=' . $company['id']) ?>" class="btn btn-sm btn-soft-primary">Thêm cơ hội</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($deals)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr><th>Tên</th><th>Giá trị</th><th>Giai đoạn</th><th>Trạng thái</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($deals as $d): ?>
                                            <tr>
                                                <td><a href="<?= url('deals/' . $d['id']) ?>"><?= e($d['title']) ?></a></td>
                                                <td><?= format_money($d['value']) ?></td>
                                                <td><span class="badge" style="background-color:<?= safe_color($d['stage_color'] ?? null) ?>"><?= e($d['stage_name'] ?? '') ?></span></td>
                                                <td><?php $dc=['open'=>'primary','won'=>'success','lost'=>'danger']; ?><span class="badge bg-<?= $dc[$d['status']] ?? 'secondary' ?>"><?= $d['status'] ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Chưa có cơ hội</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
