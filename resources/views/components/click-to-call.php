<?php
/**
 * Click-to-Call component
 * Usage: <?php include BASE_PATH . '/resources/views/components/click-to-call.php'; ?>
 * Requires: $phone variable to be set before including
 */
$ctcPhone = $phone ?? $ctcPhone ?? '';
if (empty($ctcPhone)) return;
?>
<a href="javascript:void(0)" class="click-to-call-btn text-decoration-none" data-phone="<?= e($ctcPhone) ?>" title="Gọi <?= e($ctcPhone) ?>">
    <i class="ri-phone-line me-1"></i><?= e($ctcPhone) ?>
</a>

<!-- Click-to-Call Widget (injected once) -->
<div id="callWidget" class="call-widget d-none" style="position:fixed;bottom:24px;right:24px;z-index:9999;width:320px;">
    <div class="card shadow-lg border-0 mb-0">
        <div class="card-header bg-primary text-white d-flex align-items-center py-2">
            <i class="ri-phone-line me-2"></i>
            <span class="flex-grow-1 fw-medium" id="callWidgetTitle">Đang gọi...</span>
            <button type="button" class="btn-close btn-close-white" onclick="endCall()" style="font-size:10px"></button>
        </div>
        <div class="card-body text-center py-4">
            <div id="callDialing">
                <div class="avatar-md mx-auto mb-3">
                    <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-24 call-pulse">
                        <i class="ri-phone-line"></i>
                    </div>
                </div>
                <h6 id="callPhoneNumber" class="mb-1"></h6>
                <p class="text-muted mb-0" id="callStatus">Đang kết nối...</p>
                <p class="text-muted mb-0 d-none" id="callTimer">00:00</p>
            </div>
        </div>
        <div class="card-footer text-center border-top py-2">
            <button class="btn btn-danger rounded-circle" onclick="endCall()" style="width:48px;height:48px">
                <i class="ri-phone-fill fs-20"></i>
            </button>
        </div>
    </div>
</div>

<style>
.call-widget .call-pulse {
    animation: callPulse 1.5s ease-in-out infinite;
}
@keyframes callPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}
.click-to-call-btn {
    color: var(--vz-primary);
    cursor: pointer;
}
.click-to-call-btn:hover {
    color: var(--vz-primary);
    text-decoration: underline !important;
}
</style>

<script>
(function() {
    if (window._clickToCallInit) return;
    window._clickToCallInit = true;

    let callTimerInterval = null;
    let callStartTime = null;
    let stringeeClient = null;
    let currentCall = null;

    // Initialize Stringee client if SDK is loaded
    function initStringee() {
        if (typeof StringeeClient === 'undefined') return;

        fetch('<?= url("integrations/voip/token") ?>')
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.token) return;

                stringeeClient = new StringeeClient();
                stringeeClient.connect(data.token);

                stringeeClient.on('connect', function() {
                    console.log('Stringee connected');
                });

                stringeeClient.on('incomingcall2', function(incomingCall) {
                    // Handle incoming calls if needed
                    currentCall = incomingCall;
                    showCallWidget(incomingCall.fromNumber || 'Không rõ số', 'Cuộc gọi đến...');
                });
            })
            .catch(() => {});
    }

    window.startCall = function(phone) {
        const widget = document.getElementById('callWidget');
        const phoneEl = document.getElementById('callPhoneNumber');
        const statusEl = document.getElementById('callStatus');
        const timerEl = document.getElementById('callTimer');
        const titleEl = document.getElementById('callWidgetTitle');

        phoneEl.textContent = phone;
        statusEl.textContent = 'Đang kết nối...';
        statusEl.classList.remove('d-none');
        timerEl.classList.add('d-none');
        titleEl.textContent = 'Đang gọi...';
        widget.classList.remove('d-none');

        // Try browser-based calling via Stringee SDK
        if (stringeeClient && stringeeClient.isConnected) {
            try {
                currentCall = new StringeeCall2(stringeeClient, phone, true);
                currentCall.makeCall(function(res) {
                    if (res.r !== 0) {
                        fallbackRestCall(phone);
                    }
                });

                currentCall.on('addlocalstream', function(stream) {});
                currentCall.on('addremotestream', function(stream) {
                    var audio = document.createElement('audio');
                    audio.srcObject = stream;
                    audio.autoplay = true;
                    document.body.appendChild(audio);
                });

                currentCall.on('signalingstate', function(state) {
                    switch(state.code) {
                        case 3: // ringing
                            statusEl.textContent = 'Đang đổ chuông...';
                            break;
                        case 4: // answered
                            statusEl.classList.add('d-none');
                            timerEl.classList.remove('d-none');
                            titleEl.textContent = 'Đang nghe...';
                            startTimer();
                            break;
                        case 5: // busy
                            statusEl.textContent = 'Bận';
                            setTimeout(() => endCall(), 2000);
                            break;
                        case 6: // ended
                            endCall();
                            break;
                    }
                });

                return;
            } catch (e) {
                console.warn('Stringee SDK call failed, falling back to REST API');
            }
        }

        fallbackRestCall(phone);
    };

    function fallbackRestCall(phone) {
        // Fallback: use REST API call
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value || '';

        fetch('<?= url("integrations/voip/call") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: '_token=' + encodeURIComponent(csrfToken) + '&phone=' + encodeURIComponent(phone),
        })
        .then(r => r.json())
        .then(data => {
            const statusEl = document.getElementById('callStatus');
            if (data.success) {
                statusEl.textContent = 'Cuộc gọi đang được kết nối qua tổng đài...';
                startTimer();
            } else {
                statusEl.textContent = data.error || 'Không thể thực hiện cuộc gọi';
                setTimeout(() => endCall(), 3000);
            }
        })
        .catch(() => {
            document.getElementById('callStatus').textContent = 'Lỗi kết nối';
            setTimeout(() => endCall(), 2000);
        });
    }

    function showCallWidget(phone, status) {
        document.getElementById('callPhoneNumber').textContent = phone;
        document.getElementById('callStatus').textContent = status;
        document.getElementById('callWidget').classList.remove('d-none');
    }

    function startTimer() {
        callStartTime = Date.now();
        const timerEl = document.getElementById('callTimer');
        const statusEl = document.getElementById('callStatus');
        statusEl.classList.add('d-none');
        timerEl.classList.remove('d-none');

        callTimerInterval = setInterval(function() {
            const elapsed = Math.floor((Date.now() - callStartTime) / 1000);
            const mins = String(Math.floor(elapsed / 60)).padStart(2, '0');
            const secs = String(elapsed % 60).padStart(2, '0');
            timerEl.textContent = mins + ':' + secs;
        }, 1000);
    }

    window.endCall = function() {
        if (callTimerInterval) {
            clearInterval(callTimerInterval);
            callTimerInterval = null;
        }

        if (currentCall) {
            try { currentCall.hangup(); } catch(e) {}
            currentCall = null;
        }

        document.getElementById('callWidget').classList.add('d-none');
    };

    // Bind click events
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.click-to-call-btn');
        if (btn) {
            e.preventDefault();
            const phone = btn.dataset.phone;
            if (phone) startCall(phone);
        }
    });

    // Try init Stringee on load
    if (document.readyState === 'complete') {
        initStringee();
    } else {
        window.addEventListener('load', initStringee);
    }
})();
</script>
