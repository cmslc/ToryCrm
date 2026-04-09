<?php $pageTitle = 'Check-in ngay'; ?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Check-in ngay</h4>
    <a href="<?= url('checkins') ?>" class="btn btn-soft-secondary"><i class="ri-arrow-left-line me-1"></i> Quay lại</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= url('checkins/store') ?>" enctype="multipart/form-data" id="checkinForm">
                    <?= csrf_field() ?>

                    <!-- Location Status -->
                    <div class="alert alert-info d-flex align-items-center mb-4" id="locationStatus">
                        <div class="spinner-border spinner-border-sm me-2" role="status" id="locationSpinner">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span id="locationText">Đang lấy vị trí...</span>
                    </div>

                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="address" id="address" class="form-control" placeholder="Tự động điền khi lấy được vị trí...">
                            <div class="form-text" id="coordsDisplay"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Khách hàng</label>
                            <select name="contact_id" class="form-select">
                                <option value="">-- Chọn khách hàng --</option>
                                <?php foreach ($contacts ?? [] as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['first_name'] . ' ' . ($c['last_name'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Doanh nghiệp</label>
                            <select name="company_id" class="form-select">
                                <option value="">-- Chọn doanh nghiệp --</option>
                                <?php foreach ($companies ?? [] as $comp): ?>
                                    <option value="<?= $comp['id'] ?>"><?= e($comp['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại check-in</label>
                            <select name="check_type" class="form-select">
                                <option value="visit">Thăm KH</option>
                                <option value="meeting">Họp</option>
                                <option value="delivery">Giao hàng</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ảnh chụp</label>
                            <input type="file" name="photo" class="form-control" accept="image/*" capture="environment">
                            <div class="form-text">Chụp ảnh hoặc chọn từ thư viện</div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Nội dung check-in, ghi chú..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
                            <i class="ri-map-pin-add-line me-1"></i> Check-in
                        </button>
                        <a href="<?= url('checkins') ?>" class="btn btn-soft-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="ri-information-line me-1"></i> Hướng dẫn</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled vstack gap-2 mb-0">
                    <li><i class="ri-checkbox-circle-fill text-success me-1"></i> Cho phép trình duyệt truy cập vị trí</li>
                    <li><i class="ri-checkbox-circle-fill text-success me-1"></i> Chọn khách hàng hoặc doanh nghiệp (nếu có)</li>
                    <li><i class="ri-checkbox-circle-fill text-success me-1"></i> Chọn loại check-in phù hợp</li>
                    <li><i class="ri-checkbox-circle-fill text-success me-1"></i> Chụp ảnh minh chứng (tùy chọn)</li>
                    <li><i class="ri-checkbox-circle-fill text-success me-1"></i> Thêm ghi chú và nhấn Check-in</li>
                </ul>
            </div>
        </div>

        <!-- Mini map placeholder -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="ri-map-pin-line me-1"></i> Vị trí hiện tại</h6>
            </div>
            <div class="card-body p-0">
                <div id="miniMap" class="rounded-bottom bg-light d-flex align-items-center justify-content-center" style="height:200px">
                    <div class="text-center text-muted">
                        <i class="ri-map-pin-line fs-1"></i>
                        <p class="mb-0 fs-12" id="miniMapText">Đang xác định vị trí...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var statusEl = document.getElementById('locationStatus');
    var textEl = document.getElementById('locationText');
    var spinnerEl = document.getElementById('locationSpinner');
    var latInput = document.getElementById('latitude');
    var lngInput = document.getElementById('longitude');
    var addressInput = document.getElementById('address');
    var coordsDisplay = document.getElementById('coordsDisplay');
    var btnSubmit = document.getElementById('btnSubmit');
    var miniMapText = document.getElementById('miniMapText');

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                latInput.value = lat;
                lngInput.value = lng;

                coordsDisplay.textContent = 'Tọa độ: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
                miniMapText.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);

                // Update address field with coordinates as placeholder
                if (!addressInput.value) {
                    addressInput.value = lat.toFixed(6) + ', ' + lng.toFixed(6);
                }

                // Update status
                spinnerEl.style.display = 'none';
                statusEl.classList.remove('alert-info');
                statusEl.classList.add('alert-success');
                textEl.innerHTML = '<i class="ri-checkbox-circle-fill me-1"></i> Đã xác định vị trí thành công!';

                // Enable submit button
                btnSubmit.disabled = false;
            },
            function(error) {
                spinnerEl.style.display = 'none';
                statusEl.classList.remove('alert-info');
                statusEl.classList.add('alert-danger');

                var errorMsg = 'Không thể lấy vị trí.';
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = 'Bạn đã từ chối quyền truy cập vị trí. Vui lòng cấp quyền trong cài đặt trình duyệt.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = 'Thông tin vị trí không khả dụng.';
                        break;
                    case error.TIMEOUT:
                        errorMsg = 'Hết thời gian chờ lấy vị trí.';
                        break;
                }
                textEl.innerHTML = '<i class="ri-error-warning-fill me-1"></i> ' + errorMsg;
                miniMapText.textContent = 'Không thể xác định vị trí';
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    } else {
        spinnerEl.style.display = 'none';
        statusEl.classList.remove('alert-info');
        statusEl.classList.add('alert-warning');
        textEl.innerHTML = '<i class="ri-error-warning-fill me-1"></i> Trình duyệt không hỗ trợ Geolocation.';
        miniMapText.textContent = 'Không hỗ trợ';
    }
});
</script>
