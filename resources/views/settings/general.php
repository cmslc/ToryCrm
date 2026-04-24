<?php $pageTitle = 'Cài đặt chung'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Cài đặt chung</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Trang chủ</a></li>
        <li class="breadcrumb-item active">Cài đặt chung</li>
    </ol>
</div>

<form method="POST" action="<?= url('settings/general') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- Left: vertical nav pills -->
                <div class="col-lg-3">
                    <div class="nav flex-column nav-pills" id="settingsTab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active" id="tab-system" data-bs-toggle="pill" href="#pane-system" role="tab"><i class="ri-settings-4-line me-1"></i> Thông tin hệ thống</a>
                        <a class="nav-link" id="tab-region" data-bs-toggle="pill" href="#pane-region" role="tab"><i class="ri-global-line me-1"></i> Khu vực & Định dạng</a>
                        <a class="nav-link" id="tab-work" data-bs-toggle="pill" href="#pane-work" role="tab"><i class="ri-time-line me-1"></i> Giờ làm việc</a>
                        <a class="nav-link" id="tab-email" data-bs-toggle="pill" href="#pane-email" role="tab"><i class="ri-mail-send-line me-1"></i> Email gửi</a>
                        <a class="nav-link" id="tab-security" data-bs-toggle="pill" href="#pane-security" role="tab"><i class="ri-shield-keyhole-line me-1"></i> Bảo mật & Giới hạn</a>
                        <a class="nav-link" id="tab-brand" data-bs-toggle="pill" href="#pane-brand" role="tab"><i class="ri-palette-line me-1"></i> Thương hiệu</a>
                    </div>
                </div>

                <!-- Right: tab content -->
                <div class="col-lg-9">
                    <div class="tab-content">

                        <!-- 1. Thông tin hệ thống -->
                        <div class="tab-pane fade show active" id="pane-system" role="tabpanel">
                            <h5 class="mb-3"><i class="ri-settings-4-line me-1"></i> Thông tin hệ thống</h5>
                            <div class="mb-3">
                                <label class="form-label">Tên hệ thống</label>
                                <input type="text" class="form-control" name="system_name" maxlength="100"
                                       value="<?= e($g['system_name'] ?? '') ?>" placeholder="VD: ToryCRM Công ty ABC">
                                <small class="text-muted">Hiển thị trong tiêu đề email, báo cáo xuất PDF. Để trống sẽ dùng White-label brand.</small>
                            </div>
                        </div>

                        <!-- 2. Khu vực & Định dạng -->
                        <div class="tab-pane fade" id="pane-region" role="tabpanel">
                            <h5 class="mb-3"><i class="ri-global-line me-1"></i> Khu vực & Định dạng</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Múi giờ</label>
                                    <select class="form-select" name="timezone">
                                        <?php
                                        $tzCurrent = $g['timezone'] ?? 'Asia/Ho_Chi_Minh';
                                        $common = ['Asia/Ho_Chi_Minh','Asia/Bangkok','Asia/Singapore','Asia/Tokyo','Asia/Shanghai','Asia/Seoul','Asia/Jakarta','Asia/Manila','UTC','Europe/London','America/New_York','America/Los_Angeles'];
                                        foreach ($common as $tz): ?>
                                            <option value="<?= $tz ?>" <?= $tzCurrent === $tz ? 'selected' : '' ?>><?= $tz ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngôn ngữ</label>
                                    <select class="form-select" name="locale">
                                        <?php $loc = $g['locale'] ?? 'vi'; ?>
                                        <option value="vi" <?= $loc === 'vi' ? 'selected' : '' ?>>Tiếng Việt</option>
                                        <option value="en" <?= $loc === 'en' ? 'selected' : '' ?>>English</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tiền tệ mặc định</label>
                                    <select class="form-select" name="currency">
                                        <?php
                                        $cur = $g['currency'] ?? 'VND';
                                        $currencies = ['VND' => 'VND – Việt Nam Đồng', 'USD' => 'USD – US Dollar', 'EUR' => 'EUR – Euro', 'JPY' => 'JPY – Yên Nhật', 'CNY' => 'CNY – Nhân dân tệ'];
                                        foreach ($currencies as $code => $name): ?>
                                            <option value="<?= $code ?>" <?= $cur === $code ? 'selected' : '' ?>><?= $name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Định dạng ngày</label>
                                    <select class="form-select" name="date_format">
                                        <?php
                                        $df = $g['date_format'] ?? 'd/m/Y';
                                        $now = new DateTime();
                                        $formats = ['d/m/Y' => $now->format('d/m/Y'), 'Y-m-d' => $now->format('Y-m-d'), 'm/d/Y' => $now->format('m/d/Y'), 'd-m-Y' => $now->format('d-m-Y')];
                                        foreach ($formats as $fmt => $preview): ?>
                                            <option value="<?= $fmt ?>" <?= $df === $fmt ? 'selected' : '' ?>><?= $fmt ?> (<?= $preview ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <small class="text-muted">Thay đổi có hiệu lực ngay ở lần tải trang kế tiếp.</small>
                        </div>

                        <!-- 3. Giờ làm việc -->
                        <div class="tab-pane fade" id="pane-work" role="tabpanel">
                            <h5 class="mb-3"><i class="ri-time-line me-1"></i> Giờ làm việc</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giờ bắt đầu</label>
                                    <input type="time" class="form-control" name="work_start" value="<?= e($g['work_start'] ?? '08:00') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giờ kết thúc</label>
                                    <input type="time" class="form-control" name="work_end" value="<?= e($g['work_end'] ?? '17:30') ?>">
                                </div>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Ngày làm việc trong tuần</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <?php
                                    $days = ['2' => 'Thứ 2', '3' => 'Thứ 3', '4' => 'Thứ 4', '5' => 'Thứ 5', '6' => 'Thứ 6', '7' => 'Thứ 7', '1' => 'CN'];
                                    $selectedDays = $g['work_days'] ?? [2, 3, 4, 5, 6];
                                    foreach ($days as $val => $label):
                                        $checked = in_array((int)$val, (array)$selectedDays, true) ? 'checked' : '';
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="work_days[]" value="<?= $val ?>" id="wd<?= $val ?>" <?= $checked ?>>
                                            <label class="form-check-label" for="wd<?= $val ?>"><?= $label ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted">Dùng cho chấm công, SLA, nhắc hẹn.</small>
                            </div>
                        </div>

                        <!-- 4. Email gửi -->
                        <div class="tab-pane fade" id="pane-email" role="tabpanel">
                            <h5 class="mb-3"><i class="ri-mail-send-line me-1"></i> Email gửi</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên người gửi</label>
                                    <input type="text" class="form-control" name="email_from_name" value="<?= e($g['email_from_name'] ?? '') ?>" placeholder="VD: ToryCRM">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email gửi</label>
                                    <input type="email" class="form-control" name="email_from_email" value="<?= e($g['email_from_email'] ?? '') ?>" placeholder="noreply@domain.com">
                                </div>
                            </div>
                            <small class="text-muted">Địa chỉ From cho email tự động (thông báo, merge request, reset password).</small>
                        </div>

                        <!-- 5. Bảo mật & Giới hạn -->
                        <div class="tab-pane fade" id="pane-security" role="tabpanel">
                            <h5 class="mb-3"><i class="ri-shield-keyhole-line me-1"></i> Bảo mật & Giới hạn</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Session timeout (phút)</label>
                                    <input type="number" class="form-control" name="session_timeout" min="5" max="1440"
                                           value="<?= (int)($g['session_timeout'] ?? 120) ?>">
                                    <small class="text-muted">5–1440 phút. Hết hạn sẽ yêu cầu đăng nhập lại. Áp dụng cho phiên đăng nhập mới.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giới hạn upload (MB)</label>
                                    <input type="number" class="form-control" name="upload_limit" min="1" max="100"
                                           value="<?= (int)($g['upload_limit'] ?? 10) ?>">
                                    <small class="text-muted">1–100 MB/file. Áp dụng cho ảnh đại diện, file đính kèm.</small>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Thương hiệu -->
                        <div class="tab-pane fade" id="pane-brand" role="tabpanel">
                            <h5 class="mb-3"><i class="ri-palette-line me-1"></i> Thương hiệu</h5>
                            <div class="mb-3">
                                <label class="form-label">Tên thương hiệu <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="brand_name" value="<?= e($branding['name'] ?? 'ToryCRM') ?>" maxlength="100">
                                <small class="text-muted">Hiển thị trên sidebar và trang đăng nhập.</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Logo (sidebar & login)</label>
                                    <?php if (!empty($branding['logo_url'])): ?>
                                        <div class="mb-2"><img src="<?= asset($branding['logo_url']) ?>" alt="Logo" class="img-thumbnail" style="max-height:60px"></div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="logo" accept="image/*">
                                    <small class="text-muted">Khuyến nghị PNG, 200×50px.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Favicon</label>
                                    <?php if (!empty($branding['favicon_url'])): ?>
                                        <div class="mb-2"><img src="/<?= ltrim($branding['favicon_url'], '/') ?>" alt="Favicon" class="img-thumbnail" style="max-height:40px"></div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="favicon" accept="image/*,.ico">
                                    <small class="text-muted">ICO hoặc PNG, 32×32px.</small>
                                </div>
                            </div>
                            <?php
                            $colorFields = [
                                ['name'=>'primary_color', 'label'=>'Màu chính', 'value'=>$branding['primary_color'] ?? '#405189'],
                                ['name'=>'sidebar_color', 'label'=>'Màu sidebar', 'value'=>$branding['sidebar_color'] ?? ''],
                                ['name'=>'login_bg', 'label'=>'Nền trang đăng nhập', 'value'=>$branding['login_bg'] ?? ''],
                            ];
                            ?>
                            <div class="row">
                                <?php foreach ($colorFields as $cf): ?>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label"><?= $cf['label'] ?></label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" value="<?= e($cf['value'] ?: '#405189') ?>"
                                               onchange="this.nextElementSibling.value=this.value">
                                        <input type="text" class="form-control" name="<?= $cf['name'] ?>" value="<?= e($cf['value']) ?>"
                                               placeholder="#405189" maxlength="7"
                                               oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value))this.previousElementSibling.value=this.value">
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">CSS tùy chỉnh <small class="text-muted">(nâng cao)</small></label>
                                <textarea class="form-control font-monospace" name="custom_css" rows="4"
                                          placeholder="/* Thêm CSS tùy chỉnh ở đây */"><?= e($branding['custom_css'] ?? '') ?></textarea>
                                <small class="text-muted">CSS này được thêm vào cuối trang — dùng cẩn thận.</small>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cài đặt</button>
        </div>
    </div>
</form>
