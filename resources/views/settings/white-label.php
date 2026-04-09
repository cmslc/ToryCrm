<?php $pageTitle = 'White-label'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Cài đặt thương hiệu (White-label)</h4>
        </div>

        <form method="POST" action="<?= url('settings/white-label') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row">
                <!-- Settings Form -->
                <div class="col-lg-7">
                    <!-- Company Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="ri-building-line me-2"></i>Thông tin công ty</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Tên công ty / Thương hiệu</label>
                                <input type="text" class="form-control" name="name" id="brandName"
                                       value="<?= e($branding['name'] ?? 'ToryCRM') ?>"
                                       placeholder="ToryCRM">
                                <div class="form-text">Tên này sẽ hiển thị trên sidebar, tiêu đề trang và các báo cáo.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Logo & Favicon -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="ri-image-line me-2"></i>Logo & Favicon</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Logo (Sidebar & Trang đăng nhập)</label>
                                        <?php if (!empty($branding['logo_url'])): ?>
                                            <div class="mb-2">
                                                <img src="<?= e($branding['logo_url']) ?>" alt="Logo" class="img-thumbnail" style="max-height:60px">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" name="logo" accept="image/*">
                                        <div class="form-text">Khuyến nghị: PNG, kích thước 200x50px</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Favicon</label>
                                        <?php if (!empty($branding['favicon_url'])): ?>
                                            <div class="mb-2">
                                                <img src="<?= e($branding['favicon_url']) ?>" alt="Favicon" class="img-thumbnail" style="max-height:40px">
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" name="favicon" accept="image/*,.ico">
                                        <div class="form-text">Khuyến nghị: ICO hoặc PNG, 32x32px</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Colors -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="ri-palette-line me-2"></i>Màu sắc</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Màu chính (Primary)</label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="color" class="form-control form-control-color" name="primary_color" id="primaryColor"
                                                   value="<?= e($branding['primary_color'] ?? '#405189') ?>">
                                            <input type="text" class="form-control" id="primaryColorText"
                                                   value="<?= e($branding['primary_color'] ?? '#405189') ?>"
                                                   style="max-width:120px" readonly>
                                        </div>
                                        <div class="form-text">Thay đổi CSS variable --vz-primary</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Màu sidebar</label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="color" class="form-control form-control-color" name="sidebar_color" id="sidebarColor"
                                                   value="<?= e($branding['sidebar_color'] ?: '#405189') ?>">
                                            <input type="text" class="form-control" id="sidebarColorText"
                                                   value="<?= e($branding['sidebar_color'] ?? '') ?>"
                                                   style="max-width:120px" readonly>
                                        </div>
                                        <div class="form-text">Để trống để dùng mặc định</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Nền trang đăng nhập</label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="color" class="form-control form-control-color" name="login_bg" id="loginBgColor"
                                                   value="<?= e($branding['login_bg'] ?: '#405189') ?>">
                                            <input type="text" class="form-control" id="loginBgText"
                                                   value="<?= e($branding['login_bg'] ?? '') ?>"
                                                   style="max-width:120px" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Custom CSS -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="ri-code-s-slash-line me-2"></i>CSS tùy chỉnh (Nâng cao)</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <textarea class="form-control font-monospace" name="custom_css" rows="6"
                                          placeholder="/* Thêm CSS tùy chỉnh tại đây */"><?= e($branding['custom_css'] ?? '') ?></textarea>
                                <div class="form-text">CSS này sẽ được thêm vào cuối trang. Sử dụng cẩn thận.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cài đặt</button>
                        <a href="<?= url('settings') ?>" class="btn btn-light ms-2">Hủy</a>
                    </div>
                </div>

                <!-- Live Preview -->
                <div class="col-lg-5">
                    <div class="card position-sticky" style="top:80px">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="ri-eye-line me-2"></i>Xem trước</h5>
                        </div>
                        <div class="card-body p-0">
                            <!-- Sidebar Preview -->
                            <div id="previewSidebar" style="background:#405189; color:#fff; padding:20px; border-radius:4px 4px 0 0; min-height:200px">
                                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-light border-opacity-25">
                                    <i class="ri-customer-service-2-fill fs-22 me-2"></i>
                                    <span class="fw-bold fs-17" id="previewBrandName"><?= e($branding['name'] ?? 'ToryCRM') ?></span>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:rgba(255,255,255,0.1)">
                                        <i class="ri-dashboard-2-line"></i> <span>Dashboard</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 p-2 rounded">
                                        <i class="ri-contacts-line"></i> <span>Khách hàng</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 p-2 rounded">
                                        <i class="ri-hand-coin-line"></i> <span>Cơ hội</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 p-2 rounded">
                                        <i class="ri-settings-3-line"></i> <span>Cài đặt</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Header Preview -->
                            <div id="previewHeader" style="background:#fff; padding:12px 20px; border-bottom:1px solid #e9ebec">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-muted">Trang chủ</span>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge rounded-pill" id="previewBadge" style="background:#405189; color:#fff">3</span>
                                        <div class="avatar-xs">
                                            <div class="avatar-title rounded-circle" id="previewAvatar" style="background:#405189; color:#fff; font-size:14px">A</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Button Preview -->
                            <div style="padding:20px;">
                                <p class="text-muted mb-2">Nút bấm mẫu:</p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="btn" id="previewBtnPrimary" style="background:#405189; color:#fff">Nút chính</button>
                                    <button type="button" class="btn btn-light">Nút phụ</button>
                                    <button type="button" class="btn btn-soft-primary">Soft</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var brandNameInput = document.getElementById('brandName');
            var primaryColorInput = document.getElementById('primaryColor');
            var primaryColorText = document.getElementById('primaryColorText');
            var sidebarColorInput = document.getElementById('sidebarColor');
            var sidebarColorText = document.getElementById('sidebarColorText');
            var loginBgInput = document.getElementById('loginBgColor');
            var loginBgText = document.getElementById('loginBgText');

            function updatePreview() {
                var name = brandNameInput.value || 'ToryCRM';
                var primary = primaryColorInput.value;
                var sidebar = sidebarColorInput.value;

                document.getElementById('previewBrandName').textContent = name;
                document.getElementById('previewSidebar').style.backgroundColor = sidebar || primary;
                document.getElementById('previewBadge').style.backgroundColor = primary;
                document.getElementById('previewAvatar').style.backgroundColor = primary;
                document.getElementById('previewBtnPrimary').style.backgroundColor = primary;

                primaryColorText.value = primary;
                sidebarColorText.value = sidebar;
                loginBgText.value = loginBgInput.value;
            }

            brandNameInput.addEventListener('input', updatePreview);
            primaryColorInput.addEventListener('input', updatePreview);
            sidebarColorInput.addEventListener('input', updatePreview);
            loginBgInput.addEventListener('input', updatePreview);
        });
        </script>
