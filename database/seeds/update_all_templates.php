<?php
require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();
$pdo = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);

$header = '<table style="width:100%;border-collapse:collapse;margin-bottom:10px">
<tbody><tr>
<td style="width:15%;vertical-align:top;padding:5px"><img alt="" src="https://daloivnt.getflycrm.com/preview/file?m=upload_product&amp;f=logodaloi.png&amp;obj=3184" style="max-height:80px" /></td>
<td style="width:85%;vertical-align:top;padding:5px;font-size:12px;line-height:1.4">
<p style="margin:0;font-weight:bold">THIẾT KẾ - CUNG CẤP - THI CÔNG NỘI THẤT VĂN PHÒNG</p>
<p style="margin:0"><strong>Office:</strong> 348A Giải Phóng Phương Liệt - Thanh Xuân - Hà Nội</p>
<p style="margin:0"><strong>Project room:</strong> 352 Giai Phong Street - Ha Noi</p>
<p style="margin:0"><strong>Tel:</strong> 024. 36 68 68 89 - 024. 36 68 68 65 | <strong>Fax:</strong> 024. 36 68 68 08</p>
<p style="margin:0"><strong>Website:</strong> noithathoaphat.com.vn - noithatfami.com.vn - noithat190.com</p>
</td></tr></tbody></table>';

$title_default = '<h2 style="text-align:center;font-size:20px;text-transform:uppercase;margin:12px 0">BÁO GIÁ NỘI THẤT</h2>';
$title_agent = '<h2 style="text-align:center;font-size:20px;text-transform:uppercase;margin:12px 0">BÁO GIÁ</h2>';

$info = '<table style="width:100%;border-collapse:collapse;margin-bottom:8px;font-size:13px">
<tbody>
<tr><td style="width:55%;padding:3px 0"><strong>Kính gửi:</strong> {{a.account_name}}</td><td style="width:45%;padding:3px 0"><strong>Người thực hiện:</strong> {{assigned_name}}</td></tr>
<tr><td style="padding:3px 0"><strong>Người nhận:</strong> {{a.last_contact_honorific}} {{contact_name}}</td><td style="padding:3px 0"><strong>Email:</strong> {{assigned_email}}</td></tr>
<tr><td style="padding:3px 0"><strong>Địa chỉ:</strong> {{quote_account_address}}</td><td style="padding:3px 0"><strong>SĐT:</strong> {{assigned_phone}}</td></tr>
<tr><td style="padding:3px 0"><strong>ĐT:</strong> {{contact_phone}} | <strong>Email:</strong> {{contact_email}}</td><td style="padding:3px 0"><strong>Ngày BG:</strong> {{quote_date}}</td></tr>
<tr><td style="padding:3px 0"><strong>Địa chỉ lắp đặt:</strong> {{quote_project_address}}</td><td style="padding:3px 0"><strong>Số báo giá:</strong> {{quote_code}}</td></tr>
</tbody></table>';

$slogan_default = '<p style="text-align:center;font-size:11px;font-weight:bold;margin:10px 0 6px;text-transform:uppercase">Chúng tôi trân trọng xin gửi tới Quý khách hàng bản báo giá cho các sản phẩm như sau</p>';
$slogan_project = '<p style="text-align:center;font-size:11px;font-weight:bold;margin:10px 0 6px;text-transform:uppercase">Cảm ơn Quý khách hàng đã lựa chọn sản phẩm của Công ty chúng tôi!</p>';
$slogan_agent = '<p style="text-align:center;font-size:11px;font-weight:bold;margin:10px 0 6px;text-transform:uppercase">Chúng tôi trân trọng xin gửi tới Quý khách hàng bản báo giá cho các sản phẩm như bảng đính kèm theo đây</p>';

$prow_no_vat = '<tr>
<td style="text-align:center">{{p.no}}</td>
<td><strong>{{p.name}}</strong><br/><span style="font-size:11px">{{p.l_desc}}</span></td>
<td style="text-align:center">{{p.avatar:90xauto}}</td>
<td style="text-align:center">{{p.unit}}</td>
<td style="text-align:center">{{p.qty}}</td>
<td style="text-align:right">{{p.cost}}</td>
<td style="text-align:right">{{p.revenue}}</td>
</tr>';

$prow_vat = '<tr>
<td style="text-align:center">{{p.no}}</td>
<td><strong>{{p.name}}</strong><br/><span style="font-size:11px">{{p.l_desc}}</span></td>
<td style="text-align:center">{{p.avatar:90xauto}}</td>
<td style="text-align:center">{{p.unit}}</td>
<td style="text-align:center">{{p.qty}}</td>
<td style="text-align:right">{{p.cost}}</td>
<td style="text-align:center">{{p.vatp}}</td>
<td style="text-align:right">{{p.revenue}}</td>
</tr>';

$table_no_vat = '<table style="width:100%;border-collapse:collapse;font-size:12px" border="1" cellpadding="5">
<thead><tr style="background:#f0f0f0">
<th style="width:4%;text-align:center">STT</th><th style="width:34%">Tên sản phẩm</th><th style="width:12%;text-align:center">Ảnh SP</th><th style="width:8%;text-align:center">Đơn vị</th><th style="width:8%;text-align:center">SL</th><th style="width:12%;text-align:right">Đơn giá</th><th style="width:14%;text-align:right">Thành tiền</th>
</tr></thead><tbody>' . $prow_no_vat . '
<tr style="background:#f9f9f9"><td></td><td colspan="5" style="text-align:right"><strong>Tổng</strong></td><td style="text-align:right;font-weight:bold">{{sales}}</td></tr>
<tr><td></td><td colspan="5" style="text-align:right">Thuế VAT</td><td style="text-align:right">{{vat_amount}}</td></tr>
<tr style="background:#f0f0f0"><td></td><td colspan="5" style="text-align:right"><strong>Tổng cộng</strong></td><td style="text-align:right;font-weight:bold;font-size:14px">{{revenue}}</td></tr>
</tbody></table>';

$table_vat = '<table style="width:100%;border-collapse:collapse;font-size:12px" border="1" cellpadding="5">
<thead><tr style="background:#f0f0f0">
<th style="width:4%;text-align:center">STT</th><th style="width:30%">Tên sản phẩm</th><th style="width:10%;text-align:center">Ảnh SP</th><th style="width:8%;text-align:center">Đơn vị</th><th style="width:8%;text-align:center">SL</th><th style="width:12%;text-align:right">Đơn giá</th><th style="width:8%;text-align:center">VAT (%)</th><th style="width:14%;text-align:right">Thành tiền</th>
</tr></thead><tbody>' . $prow_vat . '
<tr style="background:#f9f9f9"><td></td><td colspan="6" style="text-align:right"><strong>Tổng</strong></td><td style="text-align:right;font-weight:bold">{{sales}}</td></tr>
<tr><td></td><td colspan="6" style="text-align:right">Thuế VAT</td><td style="text-align:right">{{vat_amount}}</td></tr>
<tr style="background:#f0f0f0"><td></td><td colspan="6" style="text-align:right"><strong>Tổng cộng</strong></td><td style="text-align:right;font-weight:bold;font-size:14px">{{revenue}}</td></tr>
</tbody></table>';

$footer = '<p style="font-weight:bold;margin:12px 0 4px">Nội dung, điều khoản đi kèm:</p>
<div>{{quote_content}}</div>
<p style="text-align:center;font-weight:bold;margin:12px 0 4px">Quý khách vui lòng không đổi, trả lại sản phẩm khi đã đặt hàng</p>
<p style="text-align:center;font-weight:bold;margin:0 0 15px">CÁM ƠN QUÝ KHÁCH ĐÃ QUAN TÂM ĐẾN DỊCH VỤ CỦA CÔNG TY CHÚNG TÔI !</p>
<table style="width:100%;margin-top:25px;font-size:13px"><tbody>
<tr><td style="width:33%;text-align:center;font-weight:bold">KHÁCH HÀNG</td><td style="width:34%;text-align:center;font-weight:bold">NGƯỜI LẬP BÁO GIÁ</td><td style="width:33%;text-align:center;font-weight:bold">DUYỆT BÁO GIÁ</td></tr>
<tr><td style="text-align:center;padding-top:55px"></td><td style="text-align:center;padding-top:55px">{{assigned_name}}</td><td style="text-align:center;padding-top:55px"></td></tr>
</tbody></table>';

$templates = [
    2  => $header . $title_default . $info . $slogan_default . $table_no_vat . $footer,
    3  => $header . $title_default . $info . $slogan_default . $table_no_vat . $footer,
    6  => $header . $title_default . $info . $slogan_default . $table_no_vat . $footer,
    7  => $header . $title_agent   . $info . $slogan_agent   . $table_no_vat . $footer,
    8  => $header . $title_default . $info . $slogan_default . $table_no_vat . $footer,
    9  => $header . $title_default . $info . $slogan_default . $table_no_vat . $footer,
    10 => $header . $title_default . $info . $slogan_project . $table_no_vat . $footer,
    11 => $header . $title_default . $info . $slogan_default . $table_vat    . $footer,
    12 => $header . $title_default . $info . $slogan_default . $table_vat    . $footer,
    13 => $header . $title_default . $info . $slogan_default . $table_vat    . $footer,
    14 => $header . $title_default . $info . $slogan_default . $table_vat    . $footer,
    15 => $header . $title_default . $info . $slogan_default . $table_vat    . $footer,
    16 => $header . $title_default . $info . $slogan_default . $table_vat    . $footer,
    17 => $header . $title_default . $info . $slogan_default . $table_vat    . $footer,
    18 => $header . $title_default . $info . $slogan_default . $table_vat    . $footer,
    19 => $header . $title_default . $info . $slogan_default . $table_vat    . $footer,
];

$stmt = $pdo->prepare('UPDATE document_templates SET content = ? WHERE id = ?');
foreach ($templates as $id => $content) {
    $stmt->execute([$content, $id]);
    echo "Updated ID $id - " . strlen($content) . " bytes\n";
}
echo "\nDone! " . count($templates) . " templates updated.\n";
