<?php
$pageTitle = 'Cấu hình AI';
$currentKey = $_ENV['GEMINI_API_KEY'] ?? '';
$hasKey = !empty($currentKey);
$maskedKey = $hasKey ? substr($currentKey, 0, 8) . '••••••••' . substr($currentKey, -4) : '';

// Test connection
$aiStatus = 'inactive';
$aiModel = 'Gemini 2.0 Flash';
if ($hasKey) {
    try {
        $testUrl = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $currentKey;
        $testResp = @file_get_contents($testUrl, false, stream_context_create(['http' => ['timeout' => 5]]));
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
    <h4 class="mb-0">Cấu hình AI Trợ lý</h4>
    <ol class="breadcrumb m-0">
        <li class="breadcrumb-item"><a href="<?= url('settings') ?>">Cài đặt</a></li>
        <li class="breadcrumb-item active">AI</li>
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
                        <h5 class="mb-1">Google Gemini AI</h5>
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

        <!-- API Key Config -->
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">API Key</h5></div>
            <div class="card-body">
                <form method="POST" action="<?= url('settings/ai/save') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Gemini API Key</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="gemini_api_key" id="apiKeyInput"
                                value="<?= e($currentKey) ?>" placeholder="Nhập API key từ Google AI Studio...">
                            <button type="button" class="btn btn-soft-secondary" onclick="var i=document.getElementById('apiKeyInput');i.type=i.type==='password'?'text':'password'">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                        <?php if ($hasKey): ?>
                            <small class="text-success mt-1 d-block"><i class="ri-check-line me-1"></i>Key hiện tại: <?= e($maskedKey) ?></small>
                        <?php endif; ?>
                        <small class="text-muted mt-1 d-block">
                            <i class="ri-information-line me-1"></i>Lấy API key miễn phí tại
                            <a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <select name="ai_model" class="form-select">
                            <option value="gemini-2.0-flash" selected>Gemini 2.0 Flash (Miễn phí - Nhanh)</option>
                            <option value="gemini-2.0-flash-lite">Gemini 2.0 Flash Lite (Miễn phí - Rất nhanh)</option>
                            <option value="gemini-1.5-pro">Gemini 1.5 Pro (Miễn phí giới hạn - Thông minh hơn)</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="ri-save-line me-1"></i> Lưu cấu hình</button>
                        <button type="button" class="btn btn-soft-info" id="testBtn"><i class="ri-play-line me-1"></i> Test kết nối</button>
                    </div>
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
            <div class="card-header"><h5 class="card-title mb-0">Giới hạn miễn phí</h5></div>
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading"><i class="ri-information-line me-1"></i>Google Gemini Free Tier</h6>
                    <ul class="mb-0 ps-3">
                        <li>15 requests / phút</li>
                        <li>1.500 requests / ngày</li>
                        <li>1 triệu tokens / phút</li>
                        <li>Không cần thẻ tín dụng</li>
                    </ul>
                </div>
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
    var key = document.getElementById('apiKeyInput').value;
    if (!key) { alert('Vui lòng nhập API key trước'); return; }
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang test...';
    var btn = this;
    fetch('https://generativelanguage.googleapis.com/v1beta/models?key=' + key)
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.models) {
                btn.innerHTML = '<i class="ri-check-line me-1"></i> Kết nối thành công!';
                btn.className = 'btn btn-success';
            } else {
                btn.innerHTML = '<i class="ri-close-line me-1"></i> Lỗi: ' + (d.error?.message || 'Key không hợp lệ');
                btn.className = 'btn btn-danger';
            }
        })
        .catch(function() {
            btn.innerHTML = '<i class="ri-close-line me-1"></i> Không kết nối được';
            btn.className = 'btn btn-danger';
        })
        .finally(function() { btn.disabled = false; setTimeout(function(){ btn.innerHTML = '<i class="ri-play-line me-1"></i> Test kết nối'; btn.className = 'btn btn-soft-info'; }, 3000); });
});
</script>
