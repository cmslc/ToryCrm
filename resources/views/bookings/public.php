<?php $noLayout = true; ?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch hẹn - <?= e($link['title']) ?></title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/icons.min.css" rel="stylesheet">
    <link href="/assets/css/app.min.css" rel="stylesheet">
    <style>
        body { background: #f3f3f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .booking-container { max-width: 800px; width: 100%; margin: 30px auto; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
        .cal-header { text-align: center; font-weight: 600; font-size: 13px; padding: 8px 0; color: #878a99; }
        .cal-day { text-align: center; padding: 10px 6px; border-radius: 8px; cursor: pointer; font-size: 14px; transition: all .2s; }
        .cal-day:hover { background: rgba(64,81,137,0.1); }
        .cal-day.selected { background: #405189; color: #fff; }
        .cal-day.disabled { color: #ccc; cursor: not-allowed; pointer-events: none; }
        .cal-day.empty { pointer-events: none; }
        .time-slot { cursor: pointer; transition: all .2s; }
        .time-slot:hover, .time-slot.selected { background: #405189; color: #fff; border-color: #405189; }
        .step { display: none; }
        .step.active { display: block; }
    </style>
</head>
<body>
    <div class="booking-container p-3">
        <div class="card shadow-lg border-0">
            <div class="card-body p-4 p-md-5">
                <!-- Header -->
                <div class="text-center mb-4">
                    <div class="avatar-lg mx-auto mb-3">
                        <div class="avatar-title rounded-circle bg-primary text-white" style="width:72px;height:72px;font-size:28px">
                            <?= strtoupper(mb_substr($link['user_name'] ?? 'U', 0, 1)) ?>
                        </div>
                    </div>
                    <h4 class="mb-1"><?= e($link['user_name'] ?? '') ?></h4>
                    <h5 class="text-primary mb-2"><?= e($link['title']) ?></h5>
                    <?php if (!empty($link['description'])): ?>
                        <p class="text-muted"><?= e($link['description']) ?></p>
                    <?php endif; ?>
                    <span class="badge bg-primary-subtle text-primary"><i class="ri-time-line me-1"></i><?= (int)$link['duration_minutes'] ?> phút</span>
                </div>

                <!-- Progress Steps -->
                <div class="d-flex justify-content-center mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill step-badge" id="badge-1">1. Chọn ngày</span>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                        <span class="badge rounded-pill bg-light text-muted step-badge" id="badge-2">2. Chọn giờ</span>
                        <i class="ri-arrow-right-s-line text-muted"></i>
                        <span class="badge rounded-pill bg-light text-muted step-badge" id="badge-3">3. Thông tin</span>
                    </div>
                </div>

                <!-- Step 1: Calendar -->
                <div class="step active" id="step1">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button class="btn btn-light" onclick="changeMonth(-1)"><i class="ri-arrow-left-s-line"></i></button>
                        <h5 class="mb-0" id="calMonthYear"></h5>
                        <button class="btn btn-light" onclick="changeMonth(1)"><i class="ri-arrow-right-s-line"></i></button>
                    </div>
                    <div class="calendar-grid" id="calendarGrid"></div>
                </div>

                <!-- Step 2: Time Slots -->
                <div class="step" id="step2">
                    <div class="d-flex align-items-center mb-3">
                        <button class="btn btn-light me-3" onclick="goToStep(1)"><i class="ri-arrow-left-s-line"></i></button>
                        <h5 class="mb-0" id="selectedDateLabel"></h5>
                    </div>
                    <div id="slotsLoading" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Đang tải khung giờ...</p>
                    </div>
                    <div id="slotsContainer" class="row g-2"></div>
                    <div id="noSlots" class="text-center py-4 d-none">
                        <i class="ri-calendar-close-line text-muted" style="font-size:48px"></i>
                        <p class="text-muted mt-2">Không có khung giờ khả dụng cho ngày này.</p>
                    </div>
                </div>

                <!-- Step 3: Booking Form -->
                <div class="step" id="step3">
                    <div class="d-flex align-items-center mb-3">
                        <button class="btn btn-light me-3" onclick="goToStep(2)"><i class="ri-arrow-left-s-line"></i></button>
                        <h5 class="mb-0">Điền thông tin</h5>
                    </div>
                    <div class="alert alert-primary-subtle border-0 mb-4">
                        <i class="ri-calendar-check-line me-1"></i>
                        <span id="bookingSummary"></span>
                    </div>
                    <form id="bookingForm" onsubmit="submitBooking(event)">
                        <div class="mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="guest_name" class="form-control" required placeholder="Nguyễn Văn A">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="guest_email" class="form-control" required placeholder="email@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="tel" name="guest_phone" class="form-control" placeholder="0912 345 678">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Nội dung muốn trao đổi..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                            <i class="ri-calendar-check-line me-1"></i> Xác nhận đặt lịch
                        </button>
                    </form>
                </div>

                <!-- Step 4: Confirmation -->
                <div class="step" id="step4">
                    <div class="text-center py-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title rounded-circle bg-success text-white" style="width:80px;height:80px;font-size:36px">
                                <i class="ri-check-line"></i>
                            </div>
                        </div>
                        <h4 class="text-success">Đặt lịch thành công!</h4>
                        <p class="text-muted mb-4" id="confirmDetails"></p>
                        <div class="card bg-light border-0">
                            <div class="card-body" id="confirmCard"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-center text-muted mt-3">Powered by <strong>ToryCRM</strong></p>
    </div>

    <script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    var currentMonth = new Date().getMonth();
    var currentYear = new Date().getFullYear();
    var selectedDate = null;
    var selectedSlot = null;
    var slug = '<?= e($link['slug']) ?>';
    var availableDays = [<?= e($link['available_days']) ?>];
    var maxAdvanceDays = <?= (int)$link['max_advance_days'] ?>;

    var monthNames = ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
    var dayNames = ['T2','T3','T4','T5','T6','T7','CN'];

    function renderCalendar() {
        var grid = document.getElementById('calendarGrid');
        document.getElementById('calMonthYear').textContent = monthNames[currentMonth] + ' ' + currentYear;

        var html = '';
        dayNames.forEach(function(d) { html += '<div class="cal-header">' + d + '</div>'; });

        var firstDay = new Date(currentYear, currentMonth, 1).getDay();
        firstDay = firstDay === 0 ? 6 : firstDay - 1; // Monday=0

        var daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        var today = new Date(); today.setHours(0,0,0,0);
        var maxDate = new Date(); maxDate.setDate(maxDate.getDate() + maxAdvanceDays);

        for (var i = 0; i < firstDay; i++) {
            html += '<div class="cal-day empty"></div>';
        }

        for (var d = 1; d <= daysInMonth; d++) {
            var date = new Date(currentYear, currentMonth, d);
            var dayOfWeek = date.getDay() === 0 ? 7 : date.getDay();
            var dateStr = currentYear + '-' + String(currentMonth+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
            var isAvailable = availableDays.includes(dayOfWeek) && date >= today && date <= maxDate;
            var isSelected = selectedDate === dateStr;

            html += '<div class="cal-day ' + (!isAvailable ? 'disabled' : '') + (isSelected ? ' selected' : '') + '" data-date="' + dateStr + '" onclick="selectDate(\'' + dateStr + '\')">' + d + '</div>';
        }

        grid.innerHTML = html;
    }

    function changeMonth(offset) {
        currentMonth += offset;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar();
    }

    function selectDate(dateStr) {
        selectedDate = dateStr;
        renderCalendar();

        var d = new Date(dateStr);
        var dayLabels = ['Chủ nhật','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'];
        document.getElementById('selectedDateLabel').textContent = dayLabels[d.getDay()] + ', ' + dateStr.split('-').reverse().join('/');

        goToStep(2);
        loadSlots(dateStr);
    }

    function loadSlots(date) {
        document.getElementById('slotsLoading').classList.remove('d-none');
        document.getElementById('slotsContainer').innerHTML = '';
        document.getElementById('noSlots').classList.add('d-none');

        fetch('/book/' + slug + '/slots?date=' + date)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('slotsLoading').classList.add('d-none');
                if (!data.slots || data.slots.length === 0) {
                    document.getElementById('noSlots').classList.remove('d-none');
                    return;
                }
                var html = '';
                data.slots.forEach(function(s) {
                    html += '<div class="col-4 col-md-3"><button class="btn btn-outline-primary w-100 time-slot" data-start="' + s.start + '" data-end="' + s.end + '" onclick="selectSlot(this)">' + s.label + '</button></div>';
                });
                document.getElementById('slotsContainer').innerHTML = html;
            })
            .catch(function() {
                document.getElementById('slotsLoading').classList.add('d-none');
                document.getElementById('noSlots').classList.remove('d-none');
            });
    }

    function selectSlot(el) {
        document.querySelectorAll('.time-slot').forEach(function(b) { b.classList.remove('selected'); });
        el.classList.add('selected');
        selectedSlot = { start: el.dataset.start, end: el.dataset.end };

        var dateFormatted = selectedDate.split('-').reverse().join('/');
        document.getElementById('bookingSummary').textContent = dateFormatted + ' | ' + selectedSlot.start + ' - ' + selectedSlot.end + ' (<?= (int)$link['duration_minutes'] ?> phút)';

        goToStep(3);
    }

    function goToStep(n) {
        document.querySelectorAll('.step').forEach(function(s) { s.classList.remove('active'); });
        document.getElementById('step' + n).classList.add('active');

        for (var i = 1; i <= 3; i++) {
            var badge = document.getElementById('badge-' + i);
            if (i <= n) {
                badge.className = 'badge rounded-pill bg-primary step-badge';
            } else {
                badge.className = 'badge rounded-pill bg-light text-muted step-badge';
            }
        }
    }

    function submitBooking(e) {
        e.preventDefault();
        var btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';

        var form = document.getElementById('bookingForm');
        var formData = new FormData(form);
        formData.append('booking_date', selectedDate);
        formData.append('start_time', selectedSlot.start);

        fetch('/book/' + slug, {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('confirmDetails').textContent = 'Cuộc hẹn của bạn đã được xác nhận.';
                document.getElementById('confirmCard').innerHTML =
                    '<p class="mb-2"><i class="ri-calendar-line me-2"></i><strong>Ngày:</strong> ' + data.booking.date + '</p>' +
                    '<p class="mb-2"><i class="ri-time-line me-2"></i><strong>Giờ:</strong> ' + data.booking.time + '</p>' +
                    '<p class="mb-0"><i class="ri-timer-line me-2"></i><strong>Thời lượng:</strong> ' + data.booking.duration + '</p>';
                goToStep(4);
            } else {
                alert(data.error || 'Có lỗi xảy ra');
                btn.disabled = false;
                btn.innerHTML = '<i class="ri-calendar-check-line me-1"></i> Xác nhận đặt lịch';
            }
        })
        .catch(function() {
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
            btn.disabled = false;
            btn.innerHTML = '<i class="ri-calendar-check-line me-1"></i> Xác nhận đặt lịch';
        });
    }

    renderCalendar();
    </script>
</body>
</html>
