<?php $pageTitle = 'Thêm doanh nghiệp'; ?>

        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="mb-0">Thêm doanh nghiệp</h4>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="<?= url('companies') ?>">Doanh nghiệp</a></li>
                <li class="breadcrumb-item active">Thêm mới</li>
            </ol>
        </div>

        <form method="POST" action="<?= url('companies/store') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Thông tin doanh nghiệp</h5></div>
                        <div class="card-body">
                            <!-- Logo -->
                            <div class="mb-3 d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <div class="rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:64px;height:64px;font-size:24px" id="logoInitial"><i class="ri-building-line"></i></div>
                                    <img src="" class="rounded d-none" id="logoPreview" style="width:64px;height:64px;object-fit:cover">
                                    <label for="logoInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:24px;height:24px;cursor:pointer">
                                        <i class="ri-camera-line fs-12"></i>
                                    </label>
                                    <input type="file" name="logo" id="logoInput" accept="image/*" class="d-none">
                                </div>
                                <div class="text-muted fs-13">Logo doanh nghiệp<br><small>JPG, PNG tối đa 5MB</small></div>
                            </div>
                            <script>
                            document.getElementById('logoInput')?.addEventListener('change', function() {
                                if (this.files && this.files[0]) {
                                    var reader = new FileReader();
                                    reader.onload = function(e) {
                                        document.getElementById('logoPreview').src = e.target.result;
                                        document.getElementById('logoPreview').classList.remove('d-none');
                                        document.getElementById('logoInitial').classList.add('d-none');
                                    };
                                    reader.readAsDataURL(this.files[0]);
                                }
                            });
                            </script>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên doanh nghiệp <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mã số thuế</label>
                                    <input type="text" class="form-control" name="tax_code">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Điện thoại</label>
                                    <input type="text" class="form-control" name="phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Website</label>
                                    <input type="text" class="form-control" name="website">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngành nghề</label>
                                    <select name="industry" class="form-select">
                                        <option value="">Chọn ngành</option>
                                        <?php foreach (['Công nghệ', 'Tài chính', 'Giáo dục', 'Y tế', 'Bất động sản', 'Thương mại', 'Sản xuất', 'Dịch vụ', 'Khác'] as $ind): ?>
                                            <option value="<?= $ind ?>"><?= $ind ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Quy mô</label>
                                    <select name="company_size" class="form-select">
                                        <option value="">Chọn</option>
                                        <option value="1-10">1-10 người</option>
                                        <option value="11-50">11-50 người</option>
                                        <option value="51-200">51-200 người</option>
                                        <option value="201-500">201-500 người</option>
                                        <option value="500+">500+ người</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thành phố</label>
                                    <input type="text" class="form-control" name="city">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" class="form-control" name="address">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Quản lý</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Người phụ trách</label>
                                <?php
                                $deptGrouped = [];
                                foreach ($users ?? [] as $u) { $deptGrouped[$u['dept_name'] ?? 'Chưa phân phòng'][] = $u; }
                                ?>
                                <select name="owner_id" class="form-select searchable-select">
                                    <option value="">Chọn</option>
                                    <?php foreach ($deptGrouped as $dept => $dUsers): ?>
                                    <optgroup label="<?= e($dept) ?>">
                                        <?php foreach ($dUsers as $u): ?>
                                        <option value="<?= $u['id'] ?>"><?= e($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ri-save-line me-1"></i> Lưu</button>
                            <a href="<?= url('companies') ?>" class="btn btn-soft-secondary">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
