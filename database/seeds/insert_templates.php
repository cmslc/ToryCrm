<?php
$pdo = new PDO('mysql:host=localhost;dbname=torycrm', 'root', '');

$templates = [
    ['Mẫu hợp đồng mua bán đơn giản', 'Mẫu ngắn gọn cho đơn hàng nhỏ, 5 điều khoản cơ bản', '/tmp/ct_simple.html'],
    ['Mẫu HĐ cung cấp & lắp đặt nội thất', 'Mẫu đầy đủ 9 điều cho dự án lắp đặt, có nghiệm thu và phạt vi phạm', '/tmp/ct_service.html'],
];

foreach ($templates as $t) {
    $content = file_get_contents($t[2]);
    $stmt = $pdo->prepare("INSERT INTO document_templates (tenant_id, type, name, description, content, is_default, is_active, created_by) VALUES (1, 'contract', ?, ?, ?, 0, 1, 1)");
    $stmt->execute([$t[0], $t[1], $content]);
    echo "Inserted: {$t[0]} (" . strlen($content) . " bytes)\n";
}
