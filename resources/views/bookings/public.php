<?php $noLayout = true; ?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lịch hẹn - <?= e($link['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root { --accent: #4f46e5; --accent-light: #eef2ff; --accent-hover: #4338ca; }
        * { box-sizing: border-box; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        .booking-page { display: flex; min-height: 100vh; align-items: center; justify-content: center; padding: 20px; }
        .booking-card { background: #fff; border-radius: 20px; box-shadow: 0 25px 60px rgba(0,0,0,.15); max-width: 520px; width: 100%; overflow: hidden; }
        .booking-header { background: linear-gradient(135deg, var(--accent), #7c3aed); padding: 32px 32px 28px; color: #fff; text-align: center; position: relative; }
        .booking-header::after { content: ''; position: absolute; bottom: -20px; left: 0; right: 0; height: 40px; background: #fff; border-radius: 20px 20px 0 0; }
        .host-avatar { width: 72px; height: 72px; border-radius: 50%; background: rgba(255,255,255,.2); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; margin: 0 auto 14px; border: 3px solid rgba(255,255,255,.3); }
        .host-name { font-size: 15px; opacity: .9; margin-bottom: 4px; }
        .booking-title { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .booking-meta { display: flex; align-items: center; justify-content: center; gap: 16px; font-size: 13px; opacity: .85; }
        .booking-meta i { font-size: 16px; }
        .booking-body { padding: 12px 32px 32px; position: relative; z-index: 1; }

        /* Steps indicator */
        .steps-bar { display: flex; align-items: center; justify-content: center; gap: 0; margin-bottom: 28px; }
        .step-dot { width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; color: #9ca3af; transition: all .3s; }
        .step-dot.active { background: var(--accent); color: #fff; }
        .step-dot.done { background: #10b981; color: #fff; }
        .step-line { width: 40px; height: 2px; background: #e5e7eb; transition: all .3s; }
        .step-line.done { background: #10b981; }

        /* Calendar */
        .cal-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .cal-nav h6 { margin: 0; font-weight: 600; font-size: 15px; }
        .cal-nav button { border: none; background: none; color: var(--accent); font-size: 20px; cursor: pointer; padding: 4px 8px; border-radius: 8px; transition: background .2s; }
        .cal-nav button:hover { background: var(--accent-light); }
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
        .cal-head { text-align: center; font-size: 12px; font-weight: 600; color: #9ca3af; padding: 6px 0; }
        .cal-cell { text-align: center; padding: 10px 4px; border-radius: 10px; font-size: 14px; cursor: pointer; transition: all .2s; color: #374151; }
        .cal-cell:hover:not(.off) { background: var(--accent-light); color: var(--accent); }
        .cal-cell.sel { background: var(--accent); color: #fff !important; font-weight: 600; }
        .cal-cell.today:not(.sel) { border: 2px solid var(--accent); font-weight: 600; }
        .cal-cell.off { color: #d1d5db; cursor: default; pointer-events: none; }
        .cal-cell.empty { pointer-events: none; }

        /* Time slots */
        .slot-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .slot-btn { padding: 10px 8px; border: 2px solid #e5e7eb; border-radius: 10px; background: #fff; font-size: 14px; font-weight: 500; color: #374151; cursor: pointer; transition: all .2s; text-align: center; }
        .slot-btn:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }
        .slot-btn.sel { border-color: var(--accent); background: var(--accent); color: #fff; }

        /* Form */
        .form-control { border-radius: 10px; border: 2px solid #e5e7eb; padding: 10px 14px; transition: border-color .2s; }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
        .btn-book { background: var(--accent); color: #fff; border: none; border-radius: 12px; padding: 14px; font-size: 16px; font-weight: 600; width: 100%; transition: all .2s; }
        .btn-book:hover { background: var(--accent-hover); color: #fff; }
        .btn-book:disabled { opacity: .6; }

        /* Success */
        .success-icon { width: 80px; height: 80px; border-radius: 50%; background: #d1fae5; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .success-icon i { font-size: 40px; color: #10b981; }
        .confirm-card { background: #f9fafb; border-radius: 12px; padding: 20px; }
        .confirm-card p { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: 14px; }
        .confirm-card p:last-child { margin-bottom: 0; }
        .confirm-card i { color: var(--accent); font-size: 18px; width: 20px; }

        .step { display: none; } .step.active { display: block; }
        .back-btn { border: none; background: none; color: #6b7280; font-size: 20px; cursor: pointer; padding: 4px; border-radius: 8px; }
        .back-btn:hover { background: #f3f4f6; }
        .section-label { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .section-label h6 { margin: 0; font-weight: 600; }
        .badge-time { background: rgba(255,255,255,.2); padding: 4px 12px; border-radius: 20px; font-size: 13px; }
    </style>
</head>
<body>
<div class="booking-page">
    <div class="booking-card">
        <!-- Header -->
        <div class="booking-header">
            <div class="host-avatar"><?= strtoupper(mb_substr($link['user_name'] ?? 'U', 0, 1)) ?></div>
            <div class="host-name"><?= e($link['user_name'] ?? '') ?></div>
            <div class="booking-title"><?= e($link['title']) ?></div>
            <?php if (!empty($link['description'])): ?>
                <p style="opacity:.85;font-size:14px;margin-bottom:10px"><?= e($link['description']) ?></p>
            <?php endif; ?>
            <div class="booking-meta">
                <span><i class="ri-time-line"></i> <?= (int)$link['duration_minutes'] ?> phút</span>
                <span><i class="ri-video-chat-line"></i> Online</span>
            </div>
        </div>

        <div class="booking-body">
            <!-- Steps -->
            <div class="steps-bar">
                <div class="step-dot active" id="dot-1">1</div>
                <div class="step-line" id="line-1"></div>
                <div class="step-dot" id="dot-2">2</div>
                <div class="step-line" id="line-2"></div>
                <div class="step-dot" id="dot-3">3</div>
            </div>

            <!-- Step 1: Calendar -->
            <div class="step active" id="step1">
                <div class="cal-nav">
                    <button type="button" onclick="changeMonth(-1)"><i class="ri-arrow-left-s-line"></i></button>
                    <h6 id="calMonthYear"></h6>
                    <button type="button" onclick="changeMonth(1)"><i class="ri-arrow-right-s-line"></i></button>
                </div>
                <div class="cal-grid" id="calendarGrid"></div>
            </div>

            <!-- Step 2: Time Slots -->
            <div class="step" id="step2">
                <div class="section-label">
                    <button class="back-btn" onclick="goToStep(1)"><i class="ri-arrow-left-s-line"></i></button>
                    <h6 id="selectedDateLabel"></h6>
                </div>
                <div id="slotsLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem"></div>
                    <p class="mt-2 text-muted" style="font-size:14px">Đang tải khung giờ...</p>
                </div>
                <div class="slot-grid" id="slotsContainer"></div>
                <div id="noSlots" class="text-center py-4 d-none">
                    <i class="ri-calendar-close-line" style="font-size:40px;color:#d1d5db"></i>
                    <p class="text-muted mt-2" style="font-size:14px">Không có khung giờ khả dụng.</p>
                </div>
            </div>

            <!-- Step 3: Form -->
            <div class="step" id="step3">
                <div class="section-label">
                    <button class="back-btn" onclick="goToStep(2)"><i class="ri-arrow-left-s-line"></i></button>
                    <h6>Thông tin của bạn</h6>
                </div>
                <div style="background:var(--accent-light);border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:14px;color:var(--accent)">
                    <i class="ri-calendar-check-line me-1"></i> <span id="bookingSummary"></span>
                </div>
                <form id="bookingForm" onsubmit="submitBooking(event)">
                    <div class="mb-3">
                        <label class="form-label fw-medium" style="font-size:14px">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="guest_name" class="form-control" required placeholder="Nguyễn Văn A">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium" style="font-size:14px">Email <span class="text-danger">*</span></label>
                        <input type="email" name="guest_email" class="form-control" required placeholder="email@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium" style="font-size:14px">Số điện thoại</label>
                        <input type="tel" name="guest_phone" class="form-control" placeholder="0912 345 678">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium" style="font-size:14px">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Nội dung muốn trao đổi..."></textarea>
                    </div>
                    <button type="submit" class="btn-book" id="submitBtn">
                        <i class="ri-calendar-check-line me-1"></i> Xác nhận đặt lịch
                    </button>
                </form>
            </div>

            <!-- Step 4: Success -->
            <div class="step" id="step4">
                <div class="text-center py-3">
                    <div class="success-icon"><i class="ri-check-line"></i></div>
                    <h4 style="font-weight:700;color:#065f46">Đặt lịch thành công!</h4>
                    <p class="text-muted mb-4" style="font-size:14px">Cuộc hẹn của bạn đã được xác nhận.</p>
                    <div class="confirm-card text-start" id="confirmCard"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
var currentMonth = new Date().getMonth();
var currentYear = new Date().getFullYear();
var selectedDate = null;
var selectedSlot = null;
var slug = '<?= e($link['slug']) ?>';
var dayNameToNum = {"mon":1,"tue":2,"wed":3,"thu":4,"fri":5,"sat":6,"sun":7};
var rawDays = <?= $link['available_days'] ?: '[]' ?>;
var availableDays = rawDays.map(function(d) { return typeof d === 'number' ? d : (dayNameToNum[d.toLowerCase()] || 0); });
var maxAdvanceDays = <?= (int)$link['max_advance_days'] ?>;
var todayStr = new Date().toISOString().slice(0,10);

var monthNames = ['Tháng 1','Tháng 2','Tháng 3','Tháng 4','Tháng 5','Tháng 6','Tháng 7','Tháng 8','Tháng 9','Tháng 10','Tháng 11','Tháng 12'];
var dayNames = ['T2','T3','T4','T5','T6','T7','CN'];

function renderCalendar() {
    var grid = document.getElementById('calendarGrid');
    document.getElementById('calMonthYear').textContent = monthNames[currentMonth] + ' ' + currentYear;
    var html = '';
    dayNames.forEach(function(d) { html += '<div class="cal-head">' + d + '</div>'; });
    var firstDay = new Date(currentYear, currentMonth, 1).getDay();
    firstDay = firstDay === 0 ? 6 : firstDay - 1;
    var daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    var today = new Date(); today.setHours(0,0,0,0);
    var maxDate = new Date(); maxDate.setDate(maxDate.getDate() + maxAdvanceDays);
    for (var i = 0; i < firstDay; i++) html += '<div class="cal-cell empty"></div>';
    for (var d = 1; d <= daysInMonth; d++) {
        var date = new Date(currentYear, currentMonth, d);
        var dow = date.getDay() === 0 ? 7 : date.getDay();
        var ds = currentYear + '-' + String(currentMonth+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
        var ok = availableDays.includes(dow) && date >= today && date <= maxDate;
        var cls = 'cal-cell';
        if (!ok) cls += ' off';
        if (selectedDate === ds) cls += ' sel';
        if (ds === todayStr && selectedDate !== ds) cls += ' today';
        html += '<div class="' + cls + '" onclick="selectDate(\'' + ds + '\')">' + d + '</div>';
    }
    grid.innerHTML = html;
}

function changeMonth(off) {
    currentMonth += off;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    if (currentMonth < 0) { currentMonth = 11; currentYear--; }
    renderCalendar();
}

function selectDate(ds) {
    selectedDate = ds;
    renderCalendar();
    var d = new Date(ds);
    var dl = ['Chủ nhật','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'];
    document.getElementById('selectedDateLabel').textContent = dl[d.getDay()] + ', ' + ds.split('-').reverse().join('/');
    goToStep(2);
    loadSlots(ds);
}

function loadSlots(date) {
    document.getElementById('slotsLoading').classList.remove('d-none');
    document.getElementById('slotsContainer').innerHTML = '';
    document.getElementById('noSlots').classList.add('d-none');
    fetch('/book/' + slug + '/slots?date=' + date)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('slotsLoading').classList.add('d-none');
            if (!data.slots || data.slots.length === 0) { document.getElementById('noSlots').classList.remove('d-none'); return; }
            var html = '';
            data.slots.forEach(function(s) {
                html += '<div class="slot-btn" data-start="' + s.start + '" data-end="' + s.end + '" onclick="selectSlot(this)">' + s.label + '</div>';
            });
            document.getElementById('slotsContainer').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('slotsLoading').classList.add('d-none');
            document.getElementById('noSlots').classList.remove('d-none');
        });
}

function selectSlot(el) {
    document.querySelectorAll('.slot-btn').forEach(function(b) { b.classList.remove('sel'); });
    el.classList.add('sel');
    selectedSlot = { start: el.dataset.start, end: el.dataset.end };
    var df = selectedDate.split('-').reverse().join('/');
    document.getElementById('bookingSummary').textContent = df + '  |  ' + selectedSlot.start + ' - ' + selectedSlot.end + '  (<?= (int)$link['duration_minutes'] ?> phút)';
    goToStep(3);
}

function goToStep(n) {
    document.querySelectorAll('.step').forEach(function(s) { s.classList.remove('active'); });
    document.getElementById('step' + n).classList.add('active');
    for (var i = 1; i <= 3; i++) {
        var dot = document.getElementById('dot-' + i);
        dot.className = 'step-dot' + (i < n ? ' done' : (i === n ? ' active' : ''));
        dot.textContent = i < n ? '\u2713' : i;
        if (i < 3) document.getElementById('line-' + i).className = 'step-line' + (i < n ? ' done' : '');
    }
}

function submitBooking(e) {
    e.preventDefault();
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';
    var form = document.getElementById('bookingForm');
    var fd = new FormData(form);
    fd.append('booking_date', selectedDate);
    fd.append('start_time', selectedSlot.start);
    fetch('/book/' + slug, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('confirmCard').innerHTML =
                    '<p><i class="ri-calendar-line"></i><strong>Ngày:</strong> ' + data.booking.date + '</p>' +
                    '<p><i class="ri-time-line"></i><strong>Giờ:</strong> ' + data.booking.time + '</p>' +
                    '<p><i class="ri-timer-line"></i><strong>Thời lượng:</strong> ' + data.booking.duration + '</p>';
                goToStep(4);
                document.querySelector('.steps-bar').style.display = 'none';
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
