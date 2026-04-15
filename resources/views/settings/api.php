<?php
$pageTitle = 'Cấu hình API';
$deepseekKey = $_ENV['DEEPSEEK_API_KEY'] ?? getenv('DEEPSEEK_API_KEY') ?: '';
$openrouterKey = $_ENV['OPENROUTER_API_KEY'] ?? getenv('OPENROUTER_API_KEY') ?: '';
$groqKey = $_ENV['GROQ_API_KEY'] ?? getenv('GROQ_API_KEY') ?: '';
$geminiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
$gmapsKey = $_ENV['GOOGLE_MAPS_API_KEY'] ?? getenv('GOOGLE_MAPS_API_KEY') ?: '';
$hasDeepSeek = !empty($deepseekKey);
$hasOpenRouter = !empty($openrouterKey);
$hasGroq = !empty($groqKey);
$hasGmaps = !empty($gmapsKey);
$hasGemini = !empty($geminiKey);
$hasKey = $hasGroq || $hasGemini;
$apiEnabled = $aiConfig['api_enabled'] ?? [];
$isEnabled = function($key) use ($apiEnabled) {
    // Mặc định bật nếu chưa cấu hình
    return !isset($apiEnabled[$key]) || $apiEnabled[$key];
};

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

        <!-- API Keys - Compact -->
        <div class="card">
            <div class="card-header p-2"><h5 class="card-title mb-0">API Keys</h5></div>
            <div class="card-body p-0">
                <form method="POST" action="<?= url('settings/api/save') ?>">
                    <?= csrf_field() ?>
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:160px">Dịch vụ</th>
                                <th>API Key</th>
                                <th style="width:80px">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $apis = [
                                ['key'=>'deepseek_api_key', 'id'=>'ds', 'name'=>'DeepSeek', 'icon'=>'ri-brain-line', 'color'=>'success', 'value'=>$deepseekKey, 'has'=>$hasDeepSeek, 'enabled'=>$isEnabled('deepseek'), 'placeholder'=>'sk-xxx...', 'hint'=>'Châu Á', 'url'=>'https://platform.deepseek.com/api_keys'],
                                ['key'=>'openrouter_api_key', 'id'=>'or', 'name'=>'OpenRouter', 'icon'=>'ri-global-line', 'color'=>'info', 'value'=>$openrouterKey, 'has'=>$hasOpenRouter, 'enabled'=>$isEnabled('openrouter'), 'placeholder'=>'sk-or-v1-xxx...', 'hint'=>'Free', 'url'=>'https://openrouter.ai/keys'],
                                ['key'=>'groq_api_key', 'id'=>'gq', 'name'=>'Groq', 'icon'=>'ri-speed-line', 'color'=>'warning', 'value'=>$groqKey, 'has'=>$hasGroq, 'enabled'=>$isEnabled('groq'), 'placeholder'=>'gsk_xxx...', 'hint'=>'Nhanh', 'url'=>'https://console.groq.com/keys'],
                                ['key'=>'gemini_api_key', 'id'=>'gm', 'name'=>'Gemini', 'icon'=>'ri-google-line', 'color'=>'primary', 'value'=>$geminiKey, 'has'=>$hasGemini, 'enabled'=>$isEnabled('gemini'), 'placeholder'=>'AIzaSy...', 'hint'=>'Google', 'url'=>'https://aistudio.google.com/apikey'],
                                ['key'=>'google_maps_api_key', 'id'=>'mp', 'name'=>'Google Maps', 'icon'=>'ri-map-pin-line', 'color'=>'danger', 'value'=>$gmapsKey, 'has'=>$hasGmaps, 'enabled'=>$isEnabled('google_maps'), 'placeholder'=>'AIzaSy...', 'hint'=>'Bản đồ', 'url'=>'https://console.cloud.google.com/apis/credentials'],
                            ];
                            foreach ($apis as $api):
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="<?= $api['icon'] ?> text-<?= $api['color'] ?> fs-16"></i>
                                        <div>
                                            <span class="fw-medium"><?= $api['name'] ?></span>
                                            <div class="text-muted fs-12"><?= $api['hint'] ?> · <a href="<?= $api['url'] ?>" target="_blank" class="text-decoration-none">Lấy key</a></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="<?= $api['key'] ?>" id="key_<?= $api['id'] ?>" value="<?= e($api['value']) ?>" placeholder="<?= $api['placeholder'] ?>">
                                        <button type="button" class="btn btn-soft-secondary" onclick="var i=document.getElementById('key_<?= $api['id'] ?>');i.type=i.type==='password'?'text':'password'"><i class="ri-eye-line"></i></button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                        <input class="form-check-input" type="checkbox" name="api_enabled[<?= $api['id'] ?>]" value="1" <?= $api['enabled'] ? 'checked' : '' ?> <?= !$api['has'] ? 'disabled title="Nhập API key trước"' : '' ?>>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="p-3 border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="ri-information-line me-1"></i>Ưu tiên: DeepSeek → OpenRouter → Groq → Gemini → Rule-based</small>
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tra cứu MST Config -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0"><i class="ri-building-2-line me-1"></i> Tra cứu mã số thuế</h5></div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="ri-information-line me-1"></i> Hệ thống tự động tra cứu thông tin doanh nghiệp khi nhập MST. Kết quả được cache 24 giờ.
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Nguồn dữ liệu</th><th>URL</th><th>Ưu tiên</th><th>Trạng thái</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="fw-medium">VietQR API</span>
                                    <div class="text-muted fs-12">Miễn phí, không cần API key</div>
                                </td>
                                <td><code class="fs-12">api.vietqr.io/v2/business/{mst}</code></td>
                                <td><span class="badge bg-primary">Ưu tiên 1</span></td>
                                <td><span class="badge bg-success-subtle text-success">Hoạt động</span></td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="fw-medium">OpenAPI.vn</span>
                                    <div class="text-muted fs-12">Fallback khi VietQR lỗi</div>
                                </td>
                                <td><code class="fs-12">api.openapi.vn/company/{mst}</code></td>
                                <td><span class="badge bg-secondary">Ưu tiên 2</span></td>
                                <td><span class="badge bg-success-subtle text-success">Hoạt động</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <h6 class="mb-2">Thống kê cache</h6>
                    <?php
                    $cacheCount = 0;
                    try { $cacheCount = (int)(\Core\Database::fetch("SELECT COUNT(*) as c FROM tax_lookup_cache")['c'] ?? 0); } catch (\Exception $e) {}
                    ?>
                    <div class="d-flex gap-3">
                        <div class="text-muted">Đã cache: <strong><?= number_format($cacheCount) ?></strong> doanh nghiệp</div>
                        <form method="POST" action="<?= url('settings/api/clear-tax-cache') ?>" class="d-inline" data-confirm="Xóa toàn bộ cache tra cứu MST?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-soft-danger py-0 px-2 fs-12"><i class="ri-delete-bin-line me-1"></i>Xóa cache</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Behavior Config -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Cấu hình hành vi AI</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/api/behavior') ?>">
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
                    <span class="badge bg-<?= ($hasDeepSeek || $hasOpenRouter || $hasKey) ? 'success' : 'warning' ?>"><?= $hasDeepSeek ? 'DeepSeek' : ($hasOpenRouter ? 'OpenRouter' : ($hasGroq ? 'Groq' : ($hasGemini ? 'Gemini' : 'Rule-based'))) ?></span>
                </div>
            </div>
        </div>

        <!-- Free Tier Info -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">So sánh providers</h5></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Provider</th><th>Model</th><th>Gói miễn phí</th></tr>
                    </thead>
                    <tbody>
                        <tr class="<?= $hasDeepSeek ? 'table-success' : '' ?>">
                            <td><strong>DeepSeek</strong> <span class="badge bg-success-subtle text-success">Châu Á</span></td>
                            <td>DeepSeek Chat</td>
                            <td>Trả phí, rẻ</td>
                        </tr>
                        <tr class="<?= $hasOpenRouter ? 'table-info' : '' ?>">
                            <td><strong>OpenRouter</strong></td>
                            <td>Llama 3.3 70B</td>
                            <td>Free, auto-retry</td>
                        </tr>
                        <tr class="<?= $hasGroq ? 'table-success' : '' ?>">
                            <td><strong>Groq</strong> <span class="badge bg-warning-subtle text-warning">Nhanh</span></td>
                            <td>Llama 3.3 70B</td>
                            <td>30 req/phút</td>
                        </tr>
                        <tr class="<?= $hasGemini ? 'table-primary' : '' ?>">
                            <td><strong>Gemini</strong></td>
                            <td>2.0 Flash</td>
                            <td>15 req/phút</td>
                        </tr>
                        <tr class="<?= !$hasDeepSeek && !$hasOpenRouter && !$hasKey ? 'table-warning' : '' ?>">
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
// Auto-enable toggle when key is entered, disable when cleared
document.querySelectorAll('input[type="password"][id^="key_"]').forEach(function(input) {
    var id = input.id.replace('key_', '');
    var toggle = document.querySelector('input[name="api_enabled[' + id + ']"]');
    if (!toggle) return;
    input.addEventListener('input', function() {
        if (this.value.trim()) {
            toggle.disabled = false;
            toggle.checked = true;
        } else {
            toggle.checked = false;
            toggle.disabled = true;
        }
    });
});

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
