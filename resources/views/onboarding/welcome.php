<?php $pageTitle = 'Chào mừng đến ToryCRM!'; ?>

<!-- Page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0"><?= e($pageTitle) ?></h4>
        </div>
    </div>
</div>

<!-- Progress Steps -->
<div class="row justify-content-center mb-4">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center position-relative" id="step-indicators">
            <div class="position-absolute" style="top: 50%; left: 0; right: 0; height: 2px; background: #e9ecef; z-index: 0;"></div>
            <div class="step-indicator active" data-step="1">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white position-relative" style="width: 40px; height: 40px; z-index: 1;">
                    <i class="ri-building-line"></i>
                </div>
                <small class="d-block text-center mt-1">Công ty</small>
            </div>
            <div class="step-indicator" data-step="2">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-muted position-relative" style="width: 40px; height: 40px; z-index: 1;">
                    <i class="ri-team-line"></i>
                </div>
                <small class="d-block text-center mt-1">Thành viên</small>
            </div>
            <div class="step-indicator" data-step="3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-muted position-relative" style="width: 40px; height: 40px; z-index: 1;">
                    <i class="ri-upload-cloud-line"></i>
                </div>
                <small class="d-block text-center mt-1">Import</small>
            </div>
            <div class="step-indicator" data-step="4">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-muted position-relative" style="width: 40px; height: 40px; z-index: 1;">
                    <i class="ri-check-double-line"></i>
                </div>
                <small class="d-block text-center mt-1">Hoàn tất</small>
            </div>
        </div>
    </div>
</div>

<!-- Step Content -->
<div class="row justify-content-center">
    <div class="col-lg-8">

        <!-- Step 1: Thiết lập công ty -->
        <div class="card shadow-sm border-0 step-content" id="step-1">
            <div class="card-body p-4">
                <h5 class="card-title mb-3"><i class="ri-building-line me-1"></i> Thiết lập công ty</h5>
                <p class="text-muted mb-4">Hãy cung cấp thông tin cơ bản về công ty của bạn để bắt đầu.</p>

                <div class="mb-3">
                    <label for="company_name" class="form-label">Tên công ty <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="company_name" name="company_name" placeholder="VD: Công ty TNHH ABC" required>
                </div>

                <div class="mb-3">
                    <label for="company_phone" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control" id="company_phone" name="company_phone" placeholder="VD: 028 1234 5678">
                </div>

                <div class="mb-3">
                    <label for="company_email" class="form-label">Email công ty</label>
                    <input type="email" class="form-control" id="company_email" name="company_email" placeholder="VD: info@congty.vn">
                </div>

                <div class="mb-3">
                    <label for="industry" class="form-label">Ngành nghề</label>
                    <select class="form-select" id="industry" name="industry">
                        <option value="">-- Chọn ngành nghề --</option>
                        <option value="technology">Công nghệ thông tin</option>
                        <option value="retail">Bán lẻ</option>
                        <option value="manufacturing">Sản xuất</option>
                        <option value="services">Dịch vụ</option>
                        <option value="education">Giáo dục</option>
                        <option value="healthcare">Y tế</option>
                        <option value="finance">Tài chính</option>
                        <option value="realestate">Bất động sản</option>
                        <option value="other">Khác</option>
                    </select>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" onclick="goToStep(2)">
                        Tiếp theo <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Mời thành viên -->
        <div class="card shadow-sm border-0 step-content d-none" id="step-2">
            <div class="card-body p-4">
                <h5 class="card-title mb-3"><i class="ri-team-line me-1"></i> Mời thành viên</h5>
                <p class="text-muted mb-4">Mời đồng nghiệp cùng sử dụng ToryCRM. Bạn có thể bỏ qua bước này và mời sau.</p>

                <div class="mb-3">
                    <label for="invite_email_1" class="form-label">Email thành viên 1</label>
                    <input type="email" class="form-control" id="invite_email_1" name="invite_emails[]" placeholder="email@congty.vn">
                </div>

                <div class="mb-3">
                    <label for="invite_email_2" class="form-label">Email thành viên 2</label>
                    <input type="email" class="form-control" id="invite_email_2" name="invite_emails[]" placeholder="email@congty.vn">
                </div>

                <div class="mb-3">
                    <label for="invite_email_3" class="form-label">Email thành viên 3</label>
                    <input type="email" class="form-control" id="invite_email_3" name="invite_emails[]" placeholder="email@congty.vn">
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-light" onclick="goToStep(1)">
                        <i class="ri-arrow-left-line me-1"></i> Quay lại
                    </button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(3)">
                        Tiếp theo <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 3: Import khách hàng -->
        <div class="card shadow-sm border-0 step-content d-none" id="step-3">
            <div class="card-body p-4">
                <h5 class="card-title mb-3"><i class="ri-upload-cloud-line me-1"></i> Import khách hàng</h5>
                <p class="text-muted mb-4">Bạn có thể import danh sách khách hàng từ file Excel hoặc CSV, hoặc bỏ qua và làm sau.</p>

                <div class="text-center py-4">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-title bg-soft-primary text-primary rounded-circle" style="width: 80px; height: 80px; font-size: 2rem; display: flex; align-items: center; justify-content: center; background: rgba(64,81,137,0.1);">
                            <i class="ri-file-excel-2-line"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-3">Import dữ liệu khách hàng từ file Excel/CSV để bắt đầu nhanh hơn.</p>
                    <a href="<?= url('import') ?>" class="btn btn-outline-primary me-2">
                        <i class="ri-upload-2-line me-1"></i> Đi đến trang Import
                    </a>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-light" onclick="goToStep(2)">
                        <i class="ri-arrow-left-line me-1"></i> Quay lại
                    </button>
                    <button type="button" class="btn btn-primary" onclick="goToStep(4)">
                        Bỏ qua & Tiếp theo <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 4: Hoàn tất -->
        <div class="card shadow-sm border-0 step-content d-none" id="step-4">
            <div class="card-body p-4">
                <h5 class="card-title mb-3"><i class="ri-check-double-line me-1"></i> Hoàn tất thiết lập</h5>
                <p class="text-muted mb-4">Tuyệt vời! Bạn đã sẵn sàng sử dụng ToryCRM.</p>

                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="ri-checkbox-circle-line text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-success">Mọi thứ đã sẵn sàng!</h4>
                    <p class="text-muted mb-4">Hệ thống đã được thiết lập. Bạn có thể bắt đầu quản lý khách hàng, tạo deal và theo dõi công việc ngay bây giờ.</p>

                    <div id="setup-summary" class="text-start bg-light rounded p-3 mb-4" style="max-width: 400px; margin: 0 auto;">
                        <h6 class="fw-semibold mb-2">Tóm tắt thiết lập:</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-1"><i class="ri-check-line text-success me-1"></i> Công ty: <span id="summary-company" class="fw-medium">--</span></li>
                            <li class="mb-1"><i class="ri-check-line text-success me-1"></i> Ngành nghề: <span id="summary-industry" class="fw-medium">--</span></li>
                            <li><i class="ri-check-line text-success me-1"></i> Thành viên mời: <span id="summary-invites" class="fw-medium">0</span></li>
                        </ul>
                    </div>
                </div>

                <form action="<?= url('onboarding/complete') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="company_name" id="hidden_company_name">
                    <input type="hidden" name="company_phone" id="hidden_company_phone">
                    <input type="hidden" name="company_email" id="hidden_company_email">
                    <input type="hidden" name="industry" id="hidden_industry">
                    <input type="hidden" name="invite_email_1" id="hidden_invite_1">
                    <input type="hidden" name="invite_email_2" id="hidden_invite_2">
                    <input type="hidden" name="invite_email_3" id="hidden_invite_3">

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-light" onclick="goToStep(3)">
                            <i class="ri-arrow-left-line me-1"></i> Quay lại
                        </button>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-rocket-line me-1"></i> Bắt đầu sử dụng
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    let currentStep = 1;

    function goToStep(step) {
        // Validate step 1 before proceeding
        if (currentStep === 1 && step > 1) {
            const companyName = document.getElementById('company_name').value.trim();
            if (!companyName) {
                document.getElementById('company_name').classList.add('is-invalid');
                document.getElementById('company_name').focus();
                return;
            }
            document.getElementById('company_name').classList.remove('is-invalid');
        }

        // Hide all steps
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('d-none'));

        // Show target step
        document.getElementById('step-' + step).classList.remove('d-none');

        // Update indicators
        document.querySelectorAll('.step-indicator').forEach(el => {
            const s = parseInt(el.dataset.step);
            const circle = el.querySelector('.rounded-circle');
            if (s <= step) {
                circle.classList.remove('bg-light', 'text-muted');
                circle.classList.add('bg-primary', 'text-white');
            } else {
                circle.classList.remove('bg-primary', 'text-white');
                circle.classList.add('bg-light', 'text-muted');
            }
        });

        // If going to step 4, populate summary and hidden fields
        if (step === 4) {
            populateSummary();
        }

        currentStep = step;
    }

    function populateSummary() {
        const companyName = document.getElementById('company_name').value.trim();
        const companyPhone = document.getElementById('company_phone').value.trim();
        const companyEmail = document.getElementById('company_email').value.trim();
        const industry = document.getElementById('industry');
        const industryText = industry.options[industry.selectedIndex]?.text || '--';

        document.getElementById('summary-company').textContent = companyName || '--';
        document.getElementById('summary-industry').textContent = industryText;

        // Count invites
        let inviteCount = 0;
        for (let i = 1; i <= 3; i++) {
            const email = document.getElementById('invite_email_' + i).value.trim();
            if (email) inviteCount++;
        }
        document.getElementById('summary-invites').textContent = inviteCount;

        // Populate hidden fields
        document.getElementById('hidden_company_name').value = companyName;
        document.getElementById('hidden_company_phone').value = companyPhone;
        document.getElementById('hidden_company_email').value = companyEmail;
        document.getElementById('hidden_industry').value = industry.value;
        document.getElementById('hidden_invite_1').value = document.getElementById('invite_email_1').value.trim();
        document.getElementById('hidden_invite_2').value = document.getElementById('invite_email_2').value.trim();
        document.getElementById('hidden_invite_3').value = document.getElementById('invite_email_3').value.trim();
    }
</script>
