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
$isEnabled = function($key) use ($apiEnabled) { return !isset($apiEnabled[$key]) || $apiEnabled[$key]; };

$aiStatus = 'inactive';
$aiModel = $activeProvider;
if ($hasKey) {
    try {
        $testUrl = $hasGroq ? "https://api.groq.com/openai/v1/models" : "https://generativelanguage.googleapis.com/v1beta/models?key=" . $geminiKey;
        $testResp = @file_get_contents($testUrl, false, stream_context_create(['http' => ['timeout' => 5, 'header' => $hasGroq ? "Authorization: Bearer {$groqKey}" : '']]));
        $aiStatus = ($testResp !== false) ? 'active' : 'error';
    } catch (\Exception $e) { $aiStatus = 'error'; }
}

$totalChats = \Core\Database::fetch("SELECT COUNT(*) as c FROM ai_chat_history WHERE tenant_id = ?", [$_SESSION['tenant_id'] ?? 1]);
$todayChats = \Core\Database::fetch("SELECT COUNT(*) as c FROM ai_chat_history WHERE tenant_id = ? AND DATE(created_at) = CURDATE()", [$_SESSION['tenant_id'] ?? 1]);
$cacheCount = 0;
try { $cacheCount = (int)(\Core\Database::fetch("SELECT COUNT(*) as c FROM tax_lookup_cache")['c'] ?? 0); } catch (\Exception $e) {}

$apis = [
    ['key'=>'deepseek_api_key', 'id'=>'ds', 'name'=>'DeepSeek', 'icon'=>'ri-brain-line', 'color'=>'success', 'value'=>$deepseekKey, 'has'=>$hasDeepSeek, 'enabled'=>$isEnabled('deepseek'), 'placeholder'=>'sk-xxx...', 'hint'=>'Châu Á · Trả phí, rẻ', 'url'=>'https://platform.deepseek.com/api_keys'],
    ['key'=>'openrouter_api_key', 'id'=>'or', 'name'=>'OpenRouter', 'icon'=>'ri-global-line', 'color'=>'info', 'value'=>$openrouterKey, 'has'=>$hasOpenRouter, 'enabled'=>$isEnabled('openrouter'), 'placeholder'=>'sk-or-v1-xxx...', 'hint'=>'Free · Auto-retry', 'url'=>'https://openrouter.ai/keys'],
    ['key'=>'groq_api_key', 'id'=>'gq', 'name'=>'Groq', 'icon'=>'ri-speed-line', 'color'=>'warning', 'value'=>$groqKey, 'has'=>$hasGroq, 'enabled'=>$isEnabled('groq'), 'placeholder'=>'gsk_xxx...', 'hint'=>'Nhanh · 30 req/phút', 'url'=>'https://console.groq.com/keys'],
    ['key'=>'gemini_api_key', 'id'=>'gm', 'name'=>'Gemini', 'icon'=>'ri-google-line', 'color'=>'primary', 'value'=>$geminiKey, 'has'=>$hasGemini, 'enabled'=>$isEnabled('gemini'), 'placeholder'=>'AIzaSy...', 'hint'=>'Google · 15 req/phút', 'url'=>'https://aistudio.google.com/apikey'],
    ['key'=>'google_maps_api_key', 'id'=>'mp', 'name'=>'Google Maps', 'icon'=>'ri-map-pin-line', 'color'=>'danger', 'value'=>$gmapsKey, 'has'=>$hasGmaps, 'enabled'=>$isEnabled('google_maps'), 'placeholder'=>'AIzaSy...', 'hint'=>'Bản đồ · Check-in', 'url'=>'https://console.cloud.google.com/apis/credentials'],
];
$activeTab = $_GET['tab'] ?? 'keys';
?>

<div class="page-title-box d-flex align-items-center justify-content-between">
    <h4 class="mb-0">Cấu hình API & Dịch vụ</h4>
</div>

<!-- Status Cards -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-<?= $aiStatus === 'active' ? 'success' : 'secondary' ?>-subtle rounded">
                        <i class="ri-robot-line text-<?= $aiStatus === 'active' ? 'success' : 'secondary' ?> fs-20"></i>
                    </span>
                </div>
                <div>
                    <p class="text-muted mb-0 fs-12">AI Trợ lý</p>
                    <h6 class="mb-0"><?= $aiStatus === 'active' ? $aiModel : 'Chưa kết nối' ?></h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-primary-subtle rounded"><i class="ri-chat-3-line text-primary fs-20"></i></span>
                </div>
                <div>
                    <p class="text-muted mb-0 fs-12">Tin nhắn AI</p>
                    <h6 class="mb-0"><?= number_format($totalChats['c'] ?? 0) ?> <span class="text-muted fw-normal fs-12">(<?= $todayChats['c'] ?? 0 ?> hôm nay)</span></h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-info-subtle rounded"><i class="ri-building-2-line text-info fs-20"></i></span>
                </div>
                <div>
                    <p class="text-muted mb-0 fs-12">Cache MST</p>
                    <h6 class="mb-0"><?= number_format($cacheCount) ?> doanh nghiệp</h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-body">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-sm flex-shrink-0">
                    <span class="avatar-title bg-<?= $hasGmaps ? 'success' : 'secondary' ?>-subtle rounded"><i class="ri-map-pin-line text-<?= $hasGmaps ? 'success' : 'secondary' ?> fs-20"></i></span>
                </div>
                <div>
                    <p class="text-muted mb-0 fs-12">Google Maps</p>
                    <h6 class="mb-0"><?= $hasGmaps ? 'Đã kết nối' : 'Chưa cấu hình' ?></h6>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link <?= $activeTab === 'keys' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tabKeys"><i class="ri-key-line me-1"></i> API Keys</a></li>
            <li class="nav-item"><a class="nav-link <?= $activeTab === 'tax' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tabTax"><i class="ri-building-2-line me-1"></i> Tra cứu MST</a></li>
            <li class="nav-item"><a class="nav-link <?= $activeTab === 'ai' ? 'active' : '' ?>" data-bs-toggle="tab" href="#tabAI"><i class="ri-robot-line me-1"></i> Cấu hình AI</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            <!-- Tab: API Keys -->
            <div class="tab-pane <?= $activeTab === 'keys' ? 'active' : '' ?>" id="tabKeys">
                <form method="POST" action="<?= url('settings/api/save') ?>">
                    <?= csrf_field() ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th style="width:180px">Dịch vụ</th><th>API Key</th><th style="width:80px" class="text-center">Bật/Tắt</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($apis as $api): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="<?= $api['icon'] ?> text-<?= $api['color'] ?> fs-18"></i>
                                            <div>
                                                <span class="fw-medium"><?= $api['name'] ?></span>
                                                <?php if ($api['has']): ?><i class="ri-check-line text-success ms-1"></i><?php endif; ?>
                                                <div class="text-muted fs-12"><?= $api['hint'] ?> · <a href="<?= $api['url'] ?>" target="_blank">Lấy key</a></div>
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
                                            <input class="form-check-input" type="checkbox" name="api_enabled[<?= $api['id'] ?>]" value="1" <?= $api['enabled'] ? 'checked' : '' ?> <?= !$api['has'] ? 'disabled' : '' ?>>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="ri-information-line me-1"></i>Ưu tiên AI: DeepSeek → OpenRouter → Groq → Gemini → Rule-based</small>
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu API Keys</button>
                    </div>
                </form>
            </div>

            <!-- Tab: Tra cứu MST -->
            <div class="tab-pane <?= $activeTab === 'tax' ? 'active' : '' ?>" id="tabTax">
                <div class="alert alert-info"><i class="ri-information-line me-1"></i> Tự động tra cứu thông tin doanh nghiệp khi nhập MST. Kết quả cache 24 giờ. Nếu nguồn 1 lỗi, tự chuyển sang nguồn 2.</div>
                <div class="table-responsive mb-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Nguồn dữ liệu</th><th>Endpoint</th><th>Ưu tiên</th><th>Chi phí</th></tr></thead>
                        <tbody>
                            <tr>
                                <td><span class="fw-medium">VietQR API</span></td>
                                <td><code class="fs-12">api.vietqr.io/v2/business/{mst}</code></td>
                                <td><span class="badge bg-primary">1</span></td>
                                <td><span class="badge bg-success-subtle text-success">Miễn phí</span></td>
                            </tr>
                            <tr>
                                <td><span class="fw-medium">OpenAPI.vn</span></td>
                                <td><code class="fs-12">api.openapi.vn/company/{mst}</code></td>
                                <td><span class="badge bg-secondary">2</span></td>
                                <td><span class="badge bg-success-subtle text-success">Miễn phí</span></td>
                            </tr>
                            <tr>
                                <td><span class="fw-medium">WifiCity</span></td>
                                <td><code class="fs-12">thongtindoanhnghiep.co/api/company/{mst}</code></td>
                                <td><span class="badge bg-secondary">3</span></td>
                                <td><span class="badge bg-success-subtle text-success">Miễn phí</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">Đã cache: <strong><?= number_format($cacheCount) ?></strong> doanh nghiệp</div>
                    <form method="POST" action="<?= url('settings/api/clear-tax-cache') ?>" class="d-inline" data-confirm="Xóa toàn bộ cache tra cứu MST?">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-soft-danger"><i class="ri-delete-bin-line me-1"></i>Xóa cache</button>
                    </form>
                </div>
            </div>

            <!-- Tab: Cấu hình AI -->
            <div class="tab-pane <?= $activeTab === 'ai' ? 'active' : '' ?>" id="tabAI">
                <form method="POST" action="<?= url('settings/api/behavior') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">System Prompt</label>
                        <textarea class="form-control" name="system_prompt" rows="3"><?= e($aiConfig['system_prompt'] ?? 'Bạn là ToryCRM AI - trợ lý thông minh cho hệ thống quản lý khách hàng. Trả lời ngắn gọn, chính xác, bằng tiếng Việt.') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nhiệt độ</label>
                            <input type="range" class="form-range" name="temperature" min="0" max="10" value="<?= ($aiConfig['temperature'] ?? 7) ?>" id="tempSlider">
                            <div class="d-flex justify-content-between text-muted fs-12"><span>Chính xác</span><span id="tempValue"><?= number_format(($aiConfig['temperature'] ?? 7) / 10, 1) ?></span><span>Sáng tạo</span></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Độ dài tối đa</label>
                            <select name="max_tokens" class="form-select">
                                <?php foreach ([256=>'Ngắn (256)',500=>'Vừa (500)',1000=>'Dài (1000)',2000=>'Rất dài (2000)'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($aiConfig['max_tokens'] ?? 500) == $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="include_crm_context" value="1" <?= ($aiConfig['include_crm_context'] ?? true) ? 'checked' : '' ?>><label class="form-check-label">Gửi dữ liệu CRM cho AI</label></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="show_widget" value="1" <?= ($aiConfig['show_widget'] ?? true) ? 'checked' : '' ?>><label class="form-check-label">Hiện widget AI trên tất cả trang</label></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('ai-chat') ?>" class="btn btn-soft-primary"><i class="ri-chat-3-line me-1"></i> Mở AI Chat</a>
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cấu hình</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[type="password"][id^="key_"]').forEach(function(input) {
    var id = input.id.replace('key_', '');
    var toggle = document.querySelector('input[name="api_enabled[' + id + ']"]');
    if (!toggle) return;
    input.addEventListener('input', function() {
        if (this.value.trim()) { toggle.disabled = false; toggle.checked = true; }
        else { toggle.checked = false; toggle.disabled = true; }
    });
});
document.getElementById('tempSlider')?.addEventListener('input', function() {
    document.getElementById('tempValue').textContent = (this.value / 10).toFixed(1);
});
</script>
