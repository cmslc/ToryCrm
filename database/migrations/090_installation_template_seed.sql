-- Seed a default "installation" document template per tenant, matching the VNT paper form
-- Skip tenants that already have an installation template.
INSERT INTO document_templates (tenant_id, type, name, description, content, is_default, is_active, created_by, created_at, updated_at)
SELECT t.id, 'installation', 'Giấy yêu cầu thi công (VNT)', 'Mẫu mặc định theo form VNT',
CONCAT(
'<table style="width:100%;border-collapse:collapse;margin-bottom:10px">',
'<tr><td style="width:60%;vertical-align:top;border:none">',
'<div style="border-bottom:1px dashed #666;padding-bottom:10px">',
'{{company_logo}}',
'<div style="font-size:11px;line-height:1.4"><strong style="font-size:13px">{{company_name}}</strong><br>',
'{{company_address}}<br>',
'T: {{company_phone}} - F: {{company_fax}}<br>',
'Website: {{company_website}} - Email: {{company_email}}</div></div>',
'</td><td style="vertical-align:top;border:none">&nbsp;</td></tr></table>',
'<p style="text-align:right;font-style:italic;margin:6px 0">Hà Nội, {{today_text}}</p>',
'<h2 style="text-align:center;font-size:20px;letter-spacing:1px;margin:14px 0 10px">GIẤY YÊU CẦU THI CÔNG (VNT)</h2>',
'<table style="width:100%;border-collapse:collapse;margin-bottom:10px"><tr>',
'<td style="vertical-align:top;border:none;font-size:13px;line-height:1.8">',
'<div><span style="display:inline-block;width:170px;font-weight:600">Người yêu cầu:</span><strong>{{requester_name}} - {{requester_phone}}</strong></div>',
'<div><span style="display:inline-block;width:170px;font-weight:600">Đơn vị thi công:</span><strong>{{contractor}}</strong></div>',
'<div><span style="display:inline-block;width:170px;font-weight:600">Địa chỉ lắp đặt:</span>{{installation_address}}</div>',
'<div><span style="display:inline-block;width:170px;font-weight:600">Tên khách hàng:</span>{{customer_contact_name}}: {{customer_contact_phone}}</div>',
'<div><span style="display:inline-block;width:170px;font-weight:600">Thời gian yêu cầu thi công:</span><strong>{{requested_date}}</strong></div>',
'</td>',
'<td style="vertical-align:top;width:210px;border:none">',
'<div style="border:1px solid #333;padding:6px 10px;font-size:12px">',
'<div style="margin:2px 0"><strong>Số CF:</strong> {{cf_number}}</div>',
'<div style="margin:2px 0"><strong>Mã KH:</strong> {{customer_code}}</div>',
'<div style="margin:2px 0"><strong>Bộ phận:</strong> {{department}}</div>',
'</div>',
'</td></tr></table>',
'<h3 style="text-align:center;font-size:14px;margin:14px 0 8px">NỘI DUNG YÊU CẦU THI CÔNG</h3>',
'{{items_table}}',
'<div style="border:1px solid #333;padding:8px 12px;margin-top:8px;min-height:90px">',
'<div style="margin:4px 0"><strong>Người nhận yêu cầu:</strong></div>',
'<div style="margin:4px 0">- Thời gian tiến hành thi công: {{execution_date}}</div>',
'<div style="margin:4px 0">- Người thi công: {{installer_name}}</div>',
'<div style="margin:4px 0">- Báo cáo tình trạng hàng hóa của thợ: {{condition_report}}</div>',
'</div>',
'<table style="width:100%;margin-top:20px;border-collapse:collapse"><tr>',
'<td style="width:33%;text-align:center;border:1px solid #333;padding:10px;min-height:110px;vertical-align:top"><u><strong>Người yêu cầu</strong></u><div style="margin-top:70px;font-weight:bold;text-decoration:underline">{{requester_name}}</div></td>',
'<td style="width:34%;text-align:center;border:1px solid #333;padding:10px;min-height:110px;vertical-align:top"><u><strong>Điều phối</strong></u><div style="margin-top:70px">&nbsp;</div></td>',
'<td style="width:33%;text-align:center;border:1px solid #333;padding:10px;min-height:110px;vertical-align:top"><u><strong>Cán bộ thi công</strong></u><div style="margin-top:70px;font-weight:bold;text-decoration:underline">{{installer_name}}</div></td>',
'</tr></table>'
),
1, 1, NULL, NOW(), NOW()
FROM tenants t
WHERE NOT EXISTS (
    SELECT 1 FROM document_templates dt WHERE dt.tenant_id = t.id AND dt.type = 'installation'
);
