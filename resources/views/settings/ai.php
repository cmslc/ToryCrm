<?php
$pageTitle = 'Cấu hình API';
$groqKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?: '';
$geminiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
$gmapsKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? getenv('GOOGLE_MAPS_API_KEY') ?: '';
$hasGroq = !empty($groqKey);
$hasGmaps = !empty($gmapsKey);
$hasGemini = !empty($geminiKey);
$hasKey = $hasGroq || $hasGemini;
$activeProvider = $hasGroq ? 'Groq (Llama 3.3 70B)' : ($hasGemini ? 'Google Gemini 2.0 Flash' : 'Rule-based');
$maskedGroq = $hasGroq ? substr($groqKey, 0, 8) . '••••••' . substr($groqKey, -4) : '';
$maskedGemini = $hasGemini ? substr($geminiKey, 0, 8) . '••••••' . substr($geminiKey, -4) : '';

$aiStatus = 'inactive';
$aiModel = $activeProvider;
if ($hasKey) {
    try {
        if ($hasGroq) {
            $testUrl = "https://api.groq.com/openai/v1/models";
        } else {
            $testUrl = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $geminiKey;
        }
        $testResp = @file_get_contents($testUrl, false, stream_context_create(['http' => ['timeout' => 5, 'header' => $hasGroq ? "Authorization: Bearer {$groqKey}" : '']]));
        $aiStatus = ($testResp !== false) ? 'active' : 'error';
    } catch (\Exception $e) {
        $aiStatus = 'error';
    }
}

// Usage stats
$totalChats = \Core\Database::fetch("SELECT COUNT(*) as c FROM ai_chat_history WHERE tenant_id = ?", [$_SESSION['tenant_id'] ?? 1]);
$todayChats = \Core\Database::fetch("SELECT COUNT(*) as c FROM ai_chat_history WHERE tenant_id = ? AND DATE(created_at) = CURDATE()", [$_SESSION['tenant_id'] ?? 1]);
$userChats = \Core\Database::fetch("SELECT COUNT(DISTINCT user_id) as c FROM ai_chat_history WHERE tenant_id = ?", [$_SESSION['tenant_id'] ?? 1]);
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Cấu hình API & Dịch vụ</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
        <li class="breadcrumb-item active">API</li>
    </ol>
</div>

<div class="row">
    <div class="col-xl-8">
        <!-- Connection Status -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-md me-3">
                        <span class="avatar-title bg-<?= $aiStatus === 'active' ? 'success' : ($hasKey ? 'danger' : 'secondary') ?>-subtle rounded-circle">
                            <i class="ri-robot-line text-<?= $aiStatus === 'active' ? 'success' : ($hasKey ? 'danger' : 'secondary') ?> fs-22"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-1">AI Trợ lý</h5>
                        <p class="text-muted mb-0">
                            <?php if ($aiStatus === 'active'): ?>
                                <span class="badge bg-success">Đã kết nối</span> Model: <?= $aiModel ?>
                            <?php elseif ($hasKey): ?>
                                <span class="badge bg-danger">Lỗi kết nối</span> Kiểm tra lại API key
                            <?php else: ?>
                                <span class="badge bg-secondary">Chưa cấu hình</span> Đang dùng chế độ rule-based
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="<?= url('ai-chat') ?>" class="btn btn-soft-primary"><i class="ri-chat-3-line me-1"></i> Mở AI Chat</a>
                </div>
            </div>
        </div>

        <!-- API Keys -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">API Keys</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/ai/save') ?>">
                    <?= csrf_field() ?>

                    <!-- Groq (ưu tiên) -->
                    <div class="mb-4 p-3 border rounded <?= $hasGroq ? 'border-success' : '' ?>">
                        <div class="d-flex align-items-center mb-2">
                            <h6 class="mb-0 flex-grow-1"><i class="ri-speed-line me-1 text-success"></i> Groq <span class="badge bg-success-subtle text-success">Khuyên dùng</span></h6>
                            <?php if ($hasGroq): ?><span class="badge bg-success">Đang dùng</span><?php endif; ?>
                        </div>
                        <div class="input-group mb-2">
                            <input type="password" class="form-control" name="groq_api_key" id="groqKeyInput" value="<?= e($groqKey) ?>" placeholder="gsk_xxxxxxxxxxxxx...">
                            <button type="button" class="btn btn-soft-secondary" onclick="var i=document.getElementById('groqKeyInput');i.type=i.type==='password'?'text':'password'"><i class="ri-eye-line"></i></button>
                        </div>
                        <?php if ($hasGroq): ?>
                            <small class="text-success d-block mb-1"><i class="ri-check-line me-1"></i>Key: <?= e($maskedGroq) ?></small>
                        <?php endif; ?>
                        <small class="text-muted">Model: Llama 3.3 70B | Free: 30 req/phút, 14.400/ngày | <a href="https://console.groq.com/keys" target="_blank">Lấy key</a></small>
                    </div>

                    <!-- Gemini (backup) -->
                    <div class="mb-4 p-3 border rounded <?= !$hasGroq && $hasGemini ? 'border-primary' : '' ?>">
                        <div class="d-flex align-items-center mb-2">
                            <h6 class="mb-0 flex-grow-1"><i class="ri-google-line me-1 text-primary"></i> Google Gemini</h6>
                            <?php if (!$hasGroq && $hasGemini): ?><span class="badge bg-primary">Đang dùng</span><?php endif; ?>
                        </div>
                        <div class="input-group mb-2">
                            <input type="password" class="form-control" name="gemini_api_key" id="geminiKeyInput" value="<?= e($geminiKey) ?>" placeholder="AIzaSyxxxxxxxxxx...">
                            <button type="button" class="btn btn-soft-secondary" onclick="var i=document.getElementById('geminiKeyInput');i.type=i.type==='password'?'text':'password'"><i class="ri-eye-line"></i></button>
                        </div>
                        <?php if ($hasGemini): ?>
                            <small class="text-success d-block mb-1"><i class="ri-check-line me-1"></i>Key: <?= e($maskedGemini) ?></small>
                        <?php endif; ?>
                        <small class="text-muted">Model: Gemini 2.0 Flash | Free: 1.500 req/ngày | <a href="https://aistudio.google.com/apikey" target="_blank">Lấy key</a></small>
                    </div>

                    <div class="alert alert-light mb-3">
                        <i class="ri-information-line me-1"></i> Ưu tiên: <strong>Groq</strong> → Gemini → Rule-based. Chỉ cần 1 key là đủ.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cấu hình</button>
                        <button type="button" class="btn btn-soft-info" id="testBtn"><i class="ri-play-line me-1"></i> Test kết nối</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Google Maps API -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-map-pin-line me-1"></i> Google Maps API</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/ai/save') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="groq_api_key" value="<?= e($groqKey) ?>">
                    <input type="hidden" name="gemini_api_key" value="<?= e($geminiKey) ?>">
                    <div class="mb-3 p-3 border rounded <?= $hasGmaps ? 'border-success' : '' ?>">
                        <div class="d-flex align-items-center mb-2">
                            <h6 class="mb-0 flex-grow-1">Google Maps API Key</h6>
                            <?php if ($hasGmaps): ?><span class="badge bg-success">Đã cấu hình</span><?php endif; ?>
                        </div>
                        <div class="input-group mb-2">
                            <input type="password" class="form-control" name="google_maps_api_key" id="gmapsKeyInput" value="<?= e($gmapsKey) ?>" placeholder="AIzaSyxxxxxxxxxx...">
                            <button type="button" class="btn btn-soft-secondary" onclick="var i=document.getElementById('gmapsKeyInput');i.type=i.type==='password'?'text':'password'"><i class="ri-eye-line"></i></button>
                        </div>
                        <small class="text-muted">Dùng cho: Check-in bản đồ, geocoding địa chỉ, hiển thị vị trí KH. <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Lấy key tại Google Cloud Console</a></small>
                        <div class="mt-2">
                            <small class="text-muted">Cần bật APIs: Maps JavaScript API, Geocoding API, Places API</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button>
                </form>
            </div>
        </div>

        <!-- AI Behavior Config -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Cấu hình hành vi AI</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/ai/behavior') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">System Prompt (Vai trò AI)</label>
                        <textarea class="form-control" name="system_prompt" rows="4" placeholder="VD: Bạn là trợ lý bán hàng chuyên nghiệp..."><?= e($aiConfig['system_prompt'] ?? 'Bạn là ToryCRM AI - trợ lý thông minh cho hệ thống quản lý khách hàng. Trả lời ngắn gọn, chính xác, bằng tiếng Việt.') ?></textarea>
                        <small class="text-muted">Hướng dẫn AI cách trả lời. Thay đổi theo phong cách doanh nghiệp.</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nhiệt độ (Temperature)</label>
                            <input type="range" class="form-range" name="temperature" min="0" max="10" value="<?= ($aiConfig['temperature'] ?? 7) ?>" id="tempSlider">
                            <div class="d-flex justify-content-between text-muted fs-12">
                                <span>Chính xác</span>
                                <span id="tempValue"><?= number_format(($aiConfig['temperature'] ?? 7) / 10, 1) ?></span>
                                <span>Sáng tạo</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Độ dài tối đa (tokens)</label>
                            <select name="max_tokens" class="form-select">
                                <option value="256" <?= ($aiConfig['max_tokens'] ?? 500) == 256 ? 'selected' : '' ?>>Ngắn (256)</option>
                                <option value="500" <?= ($aiConfig['max_tokens'] ?? 500) == 500 ? 'selected' : '' ?>>Vừa (500)</option>
                                <option value="1000" <?= ($aiConfig['max_tokens'] ?? 500) == 1000 ? 'selected' : '' ?>>Dài (1000)</option>
                                <option value="2000" <?= ($aiConfig['max_tokens'] ?? 500) == 2000 ? 'selected' : '' ?>>Rất dài (2000)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="include_crm_context" value="1" <?= ($aiConfig['include_crm_context'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label">Gửi dữ liệu CRM cho AI (deals, KH, tasks...)</label>
                        </div>
                        <small class="text-muted">Khi bật, AI sẽ biết số liệu kinh doanh để trả lời chính xác hơn.</small>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="show_widget" value="1" <?= ($aiConfig['show_widget'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label">Hiện widget AI trên tất cả trang</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cấu hình</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <!-- Usage Stats -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Thống kê sử dụng</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Tổng tin nhắn</span>
                    <span class="fw-medium"><?= number_format($totalChats['c'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Hôm nay</span>
                    <span class="fw-medium"><?= number_format($todayChats['c'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Người dùng đã dùng</span>
                    <span class="fw-medium"><?= number_format($userChats['c'] ?? 0) ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Chế độ</span>
                    <span class="badge bg-<?= $hasKey ? 'success' : 'warning' ?>"><?= $hasKey ? 'Gemini AI' : 'Rule-based' ?></span>
                </div>
            </div>
        </div>

        <!-- Free Tier Info -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">So sánh providers</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Provider</th><th>Model</th><th>Free Tier</th></tr>
                    </thead>
                    <tbody>
                        <tr class="<?= $hasGroq ? 'table-success' : '' ?>">
                            <td><strong>Groq</strong> <span class="badge bg-success-subtle text-success">Nhanh nhất</span></td>
                            <td>Llama 3.3 70B</td>
                            <td>30 req/phút, 14.400/ngày</td>
                        </tr>
                        <tr class="<?= !$hasGroq && $hasGemini ? 'table-primary' : '' ?>">
                            <td><strong>Gemini</strong></td>
                            <td>2.0 Flash</td>
                            <td>15 req/phút, 1.500/ngày</td>
                        </tr>
                        <tr class="<?= !$hasKey ? 'table-warning' : '' ?>">
                            <td><strong>Rule-based</strong></td>
                            <td>Tích hợp sẵn</td>
                            <td>Không giới hạn</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Hành động</h5></div>
            <div class="card-body d-grid gap-2">
                <a href="<?= url('ai-chat') ?>" class="btn btn-soft-primary"><i class="ri-chat-3-line me-1"></i> Mở AI Chat</a>
                <form method="POST" action="<?= url('ai-chat/clear') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-soft-danger w-100" data-confirm="Xóa toàn bộ lịch sử chat AI?"><i class="ri-delete-bin-line me-1"></i> Xóa lịch sử chat</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('tempSlider')?.addEventListener('input', function() {
    document.getElementById('tempValue').textContent = (this.value / 10).toFixed(1);
});

document.getElementById('testBtn')?.addEventListener('click', function() {
    var groqKey = document.getElementById('groqKeyInput').value;
    var geminiKey = document.getElementById('geminiKeyInput').value;
    if (!groqKey && !geminiKey) { alert('Vui lòng nhập ít nhất 1 API key'); return; }
    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang test...';

    var testGroq = groqKey ? fetch('https://api.groq.com/openai/v1/models', {headers:{'Authorization':'Bearer '+groqKey}}).then(function(r){return r.json()}) : Promise.resolve(null);
    var testGemini = geminiKey ? fetch('https://generativelanguage.googleapis.com/v1beta/models?key='+geminiKey).then(function(r){return r.json()}) : Promise.resolve(null);

    Promise.all([testGroq, testGemini]).then(function(results) {
        var msgs = [];
        if (results[0]) msgs.push(results[0].data ? '✅ Groq OK' : '❌ Groq: ' + (results[0].error?.message || 'Lỗi'));
        if (results[1]) msgs.push(results[1].models ? '✅ Gemini OK' : '❌ Gemini: ' + (results[1].error?.message || 'Lỗi'));
        var allOk = msgs.every(function(m){return m.startsWith('✅')});
        btn.innerHTML = msgs.join(' | ');
        btn.className = allOk ? 'btn btn-success' : 'btn btn-warning';
    }).catch(function() {
        btn.innerHTML = '❌ Không kết nối được';
        btn.className = 'btn btn-danger';
    }).finally(function() { btn.disabled = false; setTimeout(function(){ btn.innerHTML = '<i class="ri-play-line me-1"></i> Test kết nối'; btn.className = 'btn btn-soft-info'; }, 5000); });
});
</script>
