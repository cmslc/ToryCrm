<?php
$pdo = new PDO('mysql:host=localhost;dbname=torycrm', 'root', '');

$labels = [
    'first_name'=>1,'last_name'=>1,'full_name'=>1,'email'=>1,'phone'=>1,'mobile'=>1,
    'account_code'=>1,'position'=>1,'gender'=>1,'date_of_birth'=>1,'address'=>1,'city'=>1,
    'province'=>1,'district'=>1,'ward'=>1,'country'=>1,'description'=>1,'status'=>1,
    'customer_group'=>1,'referrer_code'=>1,'is_private'=>1,'avatar'=>1,'tax_code'=>1,
    'website'=>1,'fax'=>1,'latitude'=>1,'longitude'=>1,'company_name'=>1,'company_phone'=>1,
    'company_email'=>1,'industry'=>1,'company_size'=>1,'company_id'=>1,'source_id'=>1,
    'owner_id'=>1,'created_by'=>1,'created_at'=>1,'updated_at'=>1,'last_activity_at'=>1,
    'is_deleted'=>1,'deleted_at'=>1,'relation_id'=>1,'industry_id'=>1,'referrer_type'=>1,
    'total_revenue'=>1,'portal_token'=>1,'portal_password'=>1,'portal_active'=>1,
    'tenant_id'=>1,'id'=>1,'title'=>1,'name'=>1,'logo'=>1,'sku'=>1,'category_id'=>1,
    'type'=>1,'unit'=>1,'price'=>1,'cost_price'=>1,'tax_rate'=>1,'stock_quantity'=>1,
    'min_stock'=>1,'image'=>1,'is_active'=>1,'value'=>1,'stage_id'=>1,
    'expected_close_date'=>1,'actual_close_date'=>1,'priority'=>1,'loss_reason_category'=>1,
    'contact_id'=>1,'assigned_to'=>1,'due_date'=>1,'completed_at'=>1,'deal_id'=>1,
    'order_number'=>1,'subtotal'=>1,'tax_amount'=>1,'discount_amount'=>1,'discount_type'=>1,
    'transport_amount'=>1,'installation_amount'=>1,'total'=>1,'currency'=>1,'notes'=>1,
    'order_terms'=>1,'payment_status'=>1,'payment_method'=>1,'lading_code'=>1,
    'paid_amount'=>1,'issued_date'=>1,'approved_by'=>1,'approved_at'=>1,'contract_id'=>1,
    'order_source_id'=>1,'shipping_address'=>1,'shipping_contact'=>1,'shipping_phone'=>1,
    'shipping_province'=>1,'shipping_district'=>1,'lading_status'=>1,'warehouse_id'=>1,
    'payment_date'=>1,'commission_amount'=>1,'product_id'=>1,'order_id'=>1,
    'product_name'=>1,'quantity'=>1,'unit_price'=>1,'discount'=>1,'tax'=>1,'line_total'=>1,
    'start_date'=>1,'end_date'=>1,'recurring_value'=>1,'recurring_cycle'=>1,'auto_renew'=>1,
    'terms'=>1,'contract_number'=>1,'signed_date'=>1,'actual_value'=>1,'executed_amount'=>1,
    'installation_address'=>1,'contact_name'=>1,'parent_contract_id'=>1,
    'quote_number'=>1,'valid_until'=>1,'view_count'=>1,'last_viewed_at'=>1,
    'client_note'=>1,'accepted_at'=>1,'rejected_at'=>1,'rejection_reason'=>1,
    'discount_percent'=>1,'discount_after_tax'=>1,'shipping_fee'=>1,'shipping_percent'=>1,
    'shipping_after_tax'=>1,'shipping_note'=>1,'installation_fee'=>1,'installation_percent'=>1,
    'tracking_url'=>1,'sort_order'=>1,'deal_code'=>1,'task_code'=>1,'ticket_code'=>1,
    'content'=>1,'contact_phone'=>1,'contact_email'=>1,'campaign_id'=>1,'role'=>1,
    'department'=>1,'department_id'=>1,'position_id'=>1,'password'=>1,'last_login'=>1,
    'contract_code'=>1,'related_contract_id'=>1,'usage_type'=>1,
    'created_date'=>1,'actual_start_date'=>1,'actual_end_date'=>1,'location'=>1,
    'project'=>1,'quote_id'=>1,'party_a_company_id'=>1,'party_a_name'=>1,
    'party_a_address'=>1,'party_a_phone'=>1,'party_a_fax'=>1,'party_a_representative'=>1,
    'party_a_position'=>1,'party_a_bank_account'=>1,'party_a_bank_name'=>1,'party_a_tax_code'=>1,
    'party_b_name'=>1,'party_b_address'=>1,'party_b_phone'=>1,'party_b_fax'=>1,
    'party_b_representative'=>1,'party_b_position'=>1,'party_b_bank_account'=>1,
    'party_b_bank_name'=>1,'party_b_tax_code'=>1,'shipping_fee_percent'=>1,
    'apply_vat'=>1,'vat_percent'=>1,'vat_amount'=>1,'installation_fee_percent'=>1,
    'auto_create_order'=>1,'auto_notify_expiry'=>1,'auto_send_sms'=>1,'auto_send_email'=>1,
];

$tables = ['contacts','companies','deals','tasks','products','orders','order_items','quotations','contracts','tickets'];
$missing = [];

foreach ($tables as $t) {
    $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($cols as $col) {
        if (!isset($labels[$col])) {
            $missing[$col] = ($missing[$col] ?? '') . " $t";
        }
    }
}

if (empty($missing)) {
    echo "All columns have labels!\n";
} else {
    echo "Missing labels:\n";
    foreach ($missing as $col => $tables) {
        echo "  '$col' => '',  // used in:$tables\n";
    }
}
