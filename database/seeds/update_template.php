<?php
$pdo = new PDO('mysql:host=localhost;dbname=torycrm', 'root', '');
$content = file_get_contents('/tmp/ct_template.html');
$stmt = $pdo->prepare('UPDATE document_templates SET content = ? WHERE id = 1');
$stmt->execute([$content]);
echo 'Done, length: ' . strlen($content) . "\n";
