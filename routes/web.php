<?php

use Core\Router;

// Legal pages (public)
Router::get('terms', 'LegalController@terms');
Router::get('privacy', 'LegalController@privacy');

// Auth routes (guest only)
Router::get('login', 'AuthController@loginForm', ['GuestMiddleware']);
Router::post('login', 'AuthController@login', ['GuestMiddleware', 'CsrfMiddleware']);
Router::get('register', 'AuthController@registerForm', ['GuestMiddleware']);
Router::post('register', 'AuthController@register', ['GuestMiddleware', 'CsrfMiddleware']);
Router::get('forgot-password', 'AuthController@forgotForm', ['GuestMiddleware']);
Router::post('forgot-password', 'AuthController@forgot', ['GuestMiddleware']);

// Logout
Router::get('logout', 'AuthController@logout');

// Public webhook routes (no auth)
Router::post('webhooks/zalo', 'ZaloController@webhook');
Router::post('webhooks/voip', 'VoipController@callEvent');

// Payment gateway callbacks (public - no auth)
Router::get('payments/vnpay-return', 'PaymentController@vnpayReturn');
Router::get('payments/momo-return', 'PaymentController@momoReturn');
Router::post('webhooks/vnpay', 'PaymentController@vnpayIPN');
Router::post('webhooks/momo', 'PaymentController@momoIPN');

// Google Calendar OAuth callback (public)
Router::get('integrations/google-calendar/callback', 'GoogleCalendarController@callback');

// Booking public pages (no auth)
Router::get('book/{slug}', 'BookingController@publicPage');
Router::get('book/{slug}/slots', 'BookingController@getAvailableSlots');
Router::post('book/{slug}', 'BookingController@bookSlot');

// Public lead form (no auth)
Router::get('form/{slug}', 'LeadFormController@publicForm');
Router::post('form/{slug}/submit', 'LeadFormController@publicSubmit');

// Public quotation view (no auth)
Router::get('quote/{token}', 'QuotationController@publicView');
Router::post('quote/{token}/accept', 'QuotationController@publicAccept');
Router::post('quote/{token}/reject', 'QuotationController@publicReject');

// Client Portal (public routes - no auth middleware)
Router::get('portal/login', 'PortalController@login');
Router::post('portal/login', 'PortalController@authenticate');
Router::get('portal', 'PortalController@dashboard');
Router::get('portal/orders', 'PortalController@orders');
Router::get('portal/tickets', 'PortalController@tickets');
Router::get('portal/tickets/create', 'PortalController@createTicket');
Router::post('portal/tickets/store', 'PortalController@storeTicket');
Router::get('portal/logout', 'PortalController@logout');

// Protected routes
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    // Dashboard
    Router::get('', 'DashboardController@index');
    Router::get('dashboard', 'DashboardController@index');
    Router::post('insights/{id}/dismiss', 'DashboardController@dismissInsight');

    // AI Chat
    Router::get('ai-chat', 'AiChatController@index');
    Router::post('ai-chat/send', 'AiChatController@send');
    Router::get('ai-chat/history', 'AiChatController@history');
    Router::post('ai-chat/clear', 'AiChatController@clear');

    // Conversations (Hộp thư)
    Router::get('conversations', 'ConversationController@index');
    Router::get('conversations/create', 'ConversationController@create');
    Router::get('conversations/canned-responses', 'ConversationController@cannedResponses');
    Router::post('conversations/store', 'ConversationController@store');
    Router::get('conversations/{id}', 'ConversationController@show');
    Router::post('conversations/{id}/reply', 'ConversationController@reply');
    Router::post('conversations/{id}/assign', 'ConversationController@assign');
    Router::post('conversations/{id}/status', 'ConversationController@updateStatus');
    Router::post('conversations/{id}/star', 'ConversationController@star');

    // Check-in
    Router::get('checkins', 'CheckinController@index');
    Router::get('checkins/create', 'CheckinController@create');
    Router::get('checkins/map', 'CheckinController@map');
    Router::get('checkins/my', 'CheckinController@myCheckins');
    Router::post('checkins/store', 'CheckinController@store');
    Router::get('checkins/{id}', 'CheckinController@show');

    // Theme
    Router::post('theme/toggle', 'ThemeController@toggle');

    // Saved Views
    Router::get('saved-views/{module}', 'SavedViewController@index');
    Router::post('saved-views/store', 'SavedViewController@store');
    Router::post('saved-views/{id}/delete', 'SavedViewController@delete');
    Router::post('saved-views/{id}/default', 'SavedViewController@setDefault');

    // Contacts
    Router::get('contacts', 'ContactController@index');
    Router::get('contacts/trash', 'ContactController@trash');
    Router::get('contacts/create', 'ContactController@create');
    Router::post('contacts/bulk', 'ContactController@bulk');
    Router::get('contacts/check-duplicate', 'ContactController@checkDuplicate');
    Router::get('contacts/check-person-phone', 'ContactController@checkPersonPhone');
    Router::get('contacts/search-ajax', 'ContactController@searchAjax');
    Router::get('persons/search', 'PersonController@search');
    Router::get('persons/duplicates', 'PersonController@duplicates');
    Router::post('persons/merge', 'PersonController@merge');
    Router::get('persons/{id}', 'PersonController@show');
    Router::post('contacts/store', 'ContactController@store');
    Router::get('contacts/{id}', 'ContactController@show');
    Router::get('contacts/{id}/edit', 'ContactController@edit');
    Router::post('contacts/{id}/update', 'ContactController@update');
    Router::post('contacts/{id}/avatar', 'ContactController@updateAvatar');
    Router::post('contacts/{id}/delete', 'ContactController@delete');
    Router::post('contacts/{id}/restore', 'ContactController@restore');
    Router::post('contacts/{id}/change-owner', 'ContactController@changeOwner');
    Router::post('contacts/{id}/followers', 'ContactController@followers');
    Router::post('contacts/{id}/quick-update', 'ContactController@quickUpdate');
    Router::get('contacts/{id}/persons', 'ContactController@persons');

    // Deals (Pipeline)
    Router::get('deals', 'DealController@index');
    Router::get('deals/pipeline', 'DealController@pipeline');
    Router::get('deals/forecast', 'DealController@forecast');
    Router::get('deals/create', 'DealController@create');
    Router::post('deals/store', 'DealController@store');
    Router::get('deals/{id}', 'DealController@show');
    Router::get('deals/{id}/edit', 'DealController@edit');
    Router::post('deals/{id}/update', 'DealController@update');
    Router::post('deals/{id}/delete', 'DealController@delete');
    Router::post('deals/{id}/stage', 'DealController@updateStage');
    Router::post('deals/{id}/quick-update', 'DealController@quickUpdate');
    Router::post('deals/{id}/close', 'DealController@closeDeal');
    Router::post('deals/{id}/products', 'DealController@addProduct');
    Router::post('deals/{id}/products/{productId}/remove', 'DealController@removeProduct');

    // Tasks
    Router::get('tasks', 'TaskController@index');
    Router::get('tasks/kanban', 'TaskController@kanban');
    Router::get('tasks/calendar', 'TaskController@calendar');
    Router::get('tasks/calendar/events', 'TaskController@calendarEvents');
    Router::get('tasks/gantt', 'TaskController@gantt');
    Router::get('tasks/gantt/data', 'TaskController@ganttData');
    Router::get('tasks/export', 'TaskController@export');
    Router::get('tasks/templates', 'TaskController@templates');
    Router::post('tasks/templates/store', 'TaskController@storeTemplate');
    Router::post('tasks/templates/{id}/delete', 'TaskController@deleteTemplate');
    Router::get('tasks/templates/{id}/create', 'TaskController@createFromTemplate');
    Router::get('tasks/trash', 'TaskController@trash');
    Router::get('tasks/create', 'TaskController@create');
    Router::post('tasks/store', 'TaskController@store');
    Router::post('tasks/bulk', 'TaskController@bulk');
    Router::get('tasks/{id}', 'TaskController@show');
    Router::get('tasks/{id}/edit', 'TaskController@edit');
    Router::post('tasks/{id}/update', 'TaskController@update');
    Router::post('tasks/{id}/delete', 'TaskController@delete');
    Router::post('tasks/{id}/complete', 'TaskController@complete');
    Router::post('tasks/{id}/cancel', 'TaskController@cancel');
    Router::post('tasks/{id}/restore', 'TaskController@restore');
    Router::post('tasks/{id}/status', 'TaskController@updateStatus');
    Router::post('tasks/{id}/quick-update', 'TaskController@quickUpdate');
    Router::post('tasks/{id}/followers', 'TaskController@followers');
    Router::post('tasks/{id}/subtask', 'TaskController@addSubtask');
    Router::post('tasks/{id}/toggle-subtask', 'TaskController@toggleSubtask');
    Router::post('tasks/{id}/comment', 'TaskController@addComment');
    Router::post('tasks/{id}/comment/{commentId}/delete', 'TaskController@deleteComment');
    Router::post('tasks/{id}/timer/start', 'TaskController@startTimer');
    Router::post('tasks/{id}/timer/stop', 'TaskController@stopTimer');
    Router::post('tasks/{id}/time-log', 'TaskController@addTimeLog');
    Router::post('tasks/{id}/attachment', 'TaskController@uploadAttachment');
    Router::post('tasks/{id}/attachment/{attId}/delete', 'TaskController@deleteAttachment');
    Router::post('tasks/{id}/dependency', 'TaskController@addDependency');
    Router::post('tasks/{id}/dependency/{depId}/delete', 'TaskController@removeDependency');

    // Products
    Router::get('products/search-ajax', 'ProductController@searchAjax');
    Router::get('products', 'ProductController@index');
    Router::get('products/settings', 'ProductController@settings');
    Router::post('products/settings/category', 'ProductController@saveCategory');
    Router::post('products/settings/category/{id}/delete', 'ProductController@deleteCategory');
    Router::post('products/settings/manufacturer', 'ProductController@saveManufacturer');
    Router::post('products/settings/manufacturer/{id}/delete', 'ProductController@deleteManufacturer');
    Router::post('products/settings/origin', 'ProductController@saveOrigin');
    Router::post('products/settings/origin/{id}/delete', 'ProductController@deleteOrigin');
    Router::get('products/trash', 'ProductController@trash');
    Router::get('products/create', 'ProductController@create');
    Router::post('products/store', 'ProductController@store');
    Router::get('products/{id}', 'ProductController@show');
    Router::get('products/{id}/edit', 'ProductController@edit');
    Router::post('products/{id}/update', 'ProductController@update');
    Router::post('products/{id}/delete', 'ProductController@delete');
    Router::post('products/{id}/restore', 'ProductController@restore');

    // Orders
    Router::get('orders/pdf/{id}', 'OrderController@pdf');
    Router::get('orders/trash', 'OrderController@trash');
    Router::get('orders', 'OrderController@index');
    Router::get('orders/create', 'OrderController@create');
    Router::post('orders/store', 'OrderController@store');
    Router::get('orders/{id}', 'OrderController@show');
    Router::get('orders/{id}/edit', 'OrderController@edit');
    Router::post('orders/{id}/update', 'OrderController@update');
    Router::post('orders/{id}/followers', 'OrderController@followers');
    Router::post('orders/{id}/change-owner', 'OrderController@changeOwner');
    Router::post('orders/{id}/delete', 'OrderController@delete');
    Router::post('orders/{id}/approve', 'OrderController@approve');
    Router::post('orders/{id}/cancel', 'OrderController@cancel');
    Router::post('orders/{id}/restore', 'OrderController@restore');
    Router::post('orders/{id}/payment', 'OrderController@payment');
    Router::post('orders/{id}/status', 'OrderController@updateStatus');
    Router::post('orders/{id}/quick-update', 'OrderController@quickUpdate');
    Router::get('orders/{id}/pdf/invoice', 'OrderController@invoicePdf');
    Router::get('orders/{id}/pdf/quotation', 'OrderController@quotationPdf');

    // Calendar
    Router::get('calendar', 'CalendarController@index');
    Router::get('calendar/events', 'CalendarController@events');
    Router::get('calendar/create', 'CalendarController@create');
    Router::post('calendar/store', 'CalendarController@store');
    Router::get('calendar/{id}', 'CalendarController@show');
    Router::get('calendar/{id}/edit', 'CalendarController@edit');
    Router::post('calendar/{id}/update', 'CalendarController@update');
    Router::post('calendar/{id}/delete', 'CalendarController@delete');
    Router::post('calendar/{id}/complete', 'CalendarController@complete');

    // Notifications
    Router::get('notifications', 'NotificationController@index');
    Router::get('notifications/unread', 'NotificationController@unread');
    Router::post('notifications/mark-all-read', 'NotificationController@markAllRead');
    Router::get('notifications/{id}/read', 'NotificationController@markRead');
    Router::post('notifications/{id}/delete', 'NotificationController@delete');

    // SLA Policies
    Router::get('sla', 'SlaController@index');
    Router::get('sla/create', 'SlaController@create');
    Router::post('sla/store', 'SlaController@store');
    Router::get('sla/{id}/edit', 'SlaController@edit');
    Router::post('sla/{id}/update', 'SlaController@update');
    Router::post('sla/{id}/delete', 'SlaController@delete');

    // Tickets
    Router::get('tickets', 'TicketController@index');
    Router::get('tickets/create', 'TicketController@create');
    Router::post('tickets/store', 'TicketController@store');
    Router::get('tickets/{id}', 'TicketController@show');
    Router::get('tickets/{id}/edit', 'TicketController@edit');
    Router::post('tickets/{id}/update', 'TicketController@update');
    Router::post('tickets/{id}/comment', 'TicketController@comment');
    Router::post('tickets/{id}/delete', 'TicketController@delete');
    Router::post('tickets/{id}/quick-update', 'TicketController@quickUpdate');

    // Campaigns
    Router::get('campaigns', 'CampaignController@index');
    Router::get('campaigns/create', 'CampaignController@create');
    Router::post('campaigns/store', 'CampaignController@store');
    Router::get('campaigns/{id}', 'CampaignController@show');
    Router::get('campaigns/{id}/edit', 'CampaignController@edit');
    Router::post('campaigns/{id}/update', 'CampaignController@update');
    Router::post('campaigns/{id}/add-contact', 'CampaignController@addContact');
    Router::post('campaigns/{id}/delete', 'CampaignController@delete');

    // Email Templates
    Router::get('email-templates', 'EmailTemplateController@index');
    Router::get('email-templates/create', 'EmailTemplateController@create');
    Router::post('email-templates/store', 'EmailTemplateController@store');
    Router::get('email-templates/{id}/edit', 'EmailTemplateController@edit');
    Router::post('email-templates/{id}/update', 'EmailTemplateController@update');
    Router::post('email-templates/{id}/delete', 'EmailTemplateController@delete');
    Router::get('email-templates/{id}/preview', 'EmailTemplateController@preview');
    Router::post('email-templates/{id}/send', 'EmailTemplateController@send');

    // Internal Chat
    Router::get('chat/{entityType}/{entityId}', 'ChatController@getMessages');
    Router::post('chat/{entityType}/{entityId}', 'ChatController@postMessage');
    Router::post('chat/{id}/pin', 'ChatController@pinMessage');
    Router::post('chat/{id}/delete', 'ChatController@deleteMessage');
    Router::get('api-internal/users', 'ChatController@searchUsers');

    // Purchase Orders
    Router::get('purchase-orders', 'PurchaseOrderController@index');
    Router::get('purchase-orders/create', 'PurchaseOrderController@create');
    Router::post('purchase-orders/store', 'PurchaseOrderController@store');
    Router::get('purchase-orders/{id}', 'PurchaseOrderController@show');
    Router::get('purchase-orders/{id}/edit', 'PurchaseOrderController@edit');
    Router::post('purchase-orders/{id}/update', 'PurchaseOrderController@update');
    Router::post('purchase-orders/{id}/approve', 'PurchaseOrderController@approve');
    Router::post('purchase-orders/{id}/cancel', 'PurchaseOrderController@cancel');
    Router::post('purchase-orders/{id}/payment', 'PurchaseOrderController@payment');
    Router::post('purchase-orders/{id}/followers', 'PurchaseOrderController@followers');
    Router::post('purchase-orders/{id}/change-owner', 'PurchaseOrderController@changeOwner');
    Router::post('purchase-orders/{id}/delete', 'PurchaseOrderController@delete');

    // Quotations (Báo giá)
    Router::get('quotations', 'QuotationController@index');
    Router::get('quotations/create', 'QuotationController@create');
    Router::post('quotations/store', 'QuotationController@store');
    Router::get('quotations/{id}', 'QuotationController@show');
    Router::get('quotations/{id}/edit', 'QuotationController@edit');
    Router::post('quotations/{id}/update', 'QuotationController@update');
    Router::post('quotations/{id}/submit', 'QuotationController@submitForApproval');
    Router::post('quotations/{id}/approve', 'QuotationController@approve');
    Router::post('quotations/{id}/reject-approval', 'QuotationController@rejectApproval');
    Router::post('quotations/{id}/convert', 'QuotationController@convertToOrder');
    Router::post('quotations/{id}/create-contract', 'QuotationController@convertToContract');
    Router::post('quotations/{id}/followers', 'QuotationController@followers');
    Router::post('quotations/{id}/change-owner', 'QuotationController@changeOwner');
    Router::post('quotations/{id}/delete', 'QuotationController@delete');
    Router::post('quotations/{id}/attachment', 'QuotationController@uploadAttachment');
    Router::post('quotations/{id}/attachment/{attachId}/delete', 'QuotationController@deleteAttachment');
    Router::get('quotations/{id}/pdf', 'QuotationController@pdf');

    // Budgets (Ngân sách)
    Router::get('budgets', 'BudgetController@index');
    Router::get('budgets/create', 'BudgetController@create');
    Router::post('budgets/store', 'BudgetController@store');
    Router::get('budgets/{id}', 'BudgetController@show');
    Router::get('budgets/{id}/edit', 'BudgetController@edit');
    Router::post('budgets/{id}/update', 'BudgetController@update');
    Router::post('budgets/{id}/approve', 'BudgetController@approve');
    Router::post('budgets/{id}/close', 'BudgetController@close');

    // Commissions (Hoa hồng)
    Router::get('commissions', 'CommissionController@index');
    Router::get('commissions/rules', 'CommissionController@rules');
    Router::get('commissions/rules/create', 'CommissionController@createRule');
    Router::post('commissions/rules/store', 'CommissionController@storeRule');
    Router::get('commissions/rules/{id}/edit', 'CommissionController@editRule');
    Router::post('commissions/rules/{id}/update', 'CommissionController@updateRule');
    Router::post('commissions/rules/{id}/delete', 'CommissionController@deleteRule');
    Router::get('commissions/export', 'CommissionController@exportCsv');
    Router::get('commissions/my', 'CommissionController@myCommissions');
    Router::get('commissions/report', 'CommissionController@report');
    Router::post('commissions/{id}/approve', 'CommissionController@approve');
    Router::post('commissions/{id}/paid', 'CommissionController@markPaid');
    Router::post('commissions/bulk-approve', 'CommissionController@bulkApprove');
    Router::post('commissions/bulk-paid', 'CommissionController@bulkPaid');

    // Finance Reports (Báo cáo tài chính)
    Router::get('finance-reports', 'FinanceReportController@index');
    Router::get('finance-reports/profit-loss', 'FinanceReportController@profitLoss');
    Router::get('finance-reports/cash-flow', 'FinanceReportController@cashFlow');
    Router::get('finance-reports/aging', 'FinanceReportController@aging');

    // Debts (Công nợ)
    Router::get('debts', 'DebtController@index');
    Router::get('debts/create', 'DebtController@create');
    Router::get('debts/aging', 'DebtController@aging');
    Router::get('debts/by-contact', 'DebtController@byContact');
    Router::post('debts/store', 'DebtController@store');
    Router::get('debts/{id}', 'DebtController@show');
    Router::post('debts/{id}/payment', 'DebtController@addPayment');

    // Contracts (Hợp đồng)
    Router::get('contracts', 'ContractController@index');
    Router::get('contracts/create', 'ContractController@create');
    Router::post('contracts/store', 'ContractController@store');
    Router::get('contracts/{id}', 'ContractController@show');
    Router::get('contracts/{id}/edit', 'ContractController@edit');
    Router::post('contracts/{id}/update', 'ContractController@update');
    Router::post('contracts/{id}/approve', 'ContractController@approve');
    Router::post('contracts/{id}/start', 'ContractController@start');
    Router::post('contracts/{id}/complete', 'ContractController@complete');
    Router::post('contracts/{id}/cancel', 'ContractController@cancel');
    Router::post('contracts/{id}/create-order', 'ContractController@createOrder');
    Router::post('contracts/{id}/renew', 'ContractController@renew');
    Router::get('contracts/{id}/print', 'ContractController@print');
    Router::get('contracts/{id}/pdf', 'ContractController@pdf');
    Router::get('contracts/{id}/download-pdf', 'ContractController@downloadPdf');
    Router::post('contracts/{id}/email-pdf', 'ContractController@emailPdf');
    Router::post('contracts/{id}/attachment', 'ContractController@uploadAttachment');
    Router::post('contracts/{id}/attachment/{attachId}/delete', 'ContractController@deleteAttachment');
    Router::post('contracts/{id}/followers', 'ContractController@followers');
    Router::post('contracts/{id}/change-owner', 'ContractController@changeOwner');
    Router::post('contracts/{id}/delete', 'ContractController@delete');
    Router::post('contracts/{id}/comment', 'ContractController@comment');

    // Fund (Quỹ)
    Router::get('fund', 'FundController@index');
    Router::get('fund/create', 'FundController@create');
    Router::get('fund/pdf/{id}', 'FundController@pdf');
    Router::post('fund/store', 'FundController@store');
    Router::get('fund/{id}', 'FundController@show');
    Router::get('fund/{id}/edit', 'FundController@edit');
    Router::post('fund/{id}/update', 'FundController@update');
    Router::post('fund/{id}/confirm', 'FundController@confirm');
    Router::post('fund/{id}/cancel', 'FundController@cancel');
    Router::post('fund/{id}/delete', 'FundController@delete');

    // Warehouses (Kho)
    Router::get('warehouses', 'WarehouseController@index');
    Router::post('warehouses/store', 'WarehouseController@store');
    Router::get('warehouses/movements', 'WarehouseController@movements');
    Router::post('warehouses/movements/create', 'WarehouseController@createMovement');
    Router::get('warehouses/movements/{id}', 'WarehouseController@showMovement');
    Router::get('warehouses/checks', 'WarehouseController@checks');
    Router::post('warehouses/checks/create', 'WarehouseController@createCheck');
    Router::get('warehouses/checks/{id}', 'WarehouseController@showCheck');
    Router::post('warehouses/checks/{id}/update', 'WarehouseController@updateCheck');
    Router::post('warehouses/checks/{id}/complete', 'WarehouseController@completeCheck');
    Router::get('warehouses/report', 'WarehouseController@report');
    Router::get('warehouses/settings', 'WarehouseController@settings');
    Router::post('warehouses/settings', 'WarehouseController@saveSettings');
    Router::get('warehouses/{id}', 'WarehouseController@show');
    Router::post('warehouses/{id}/update', 'WarehouseController@update');
    Router::post('warehouses/{id}/delete', 'WarehouseController@delete');

    // Logistics (Plugin)
    Router::get('logistics', 'LogisticsController@index');
    Router::get('logistics/receive', 'LogisticsController@receive');
    Router::post('logistics/scan', 'LogisticsController@scan');
    Router::post('logistics/update-weight', 'LogisticsController@updateWeight');
    Router::get('logistics/packages', 'LogisticsController@packages');
    Router::post('logistics/packages/create', 'LogisticsController@createPackage');
    Router::get('logistics/packages/{id}', 'LogisticsController@showPackage');
    Router::get('logistics/orders', 'LogisticsController@orders');
    Router::get('logistics/orders/create', 'LogisticsController@createOrderForm');
    Router::post('logistics/orders/create', 'LogisticsController@createOrder');
    Router::get('logistics/orders/{id}', 'LogisticsController@showOrder');
    Router::get('logistics/orders/{id}/edit', 'LogisticsController@editOrder');
    Router::post('logistics/orders/{id}/update', 'LogisticsController@updateOrder');
    Router::post('logistics/orders/{id}/add-package', 'LogisticsController@addPackageToOrder');
    Router::post('logistics/orders/{id}/upload', 'LogisticsController@uploadOrderImage');
    Router::post('logistics/orders/{id}/delete', 'LogisticsController@deleteOrder');
    Router::post('logistics/confirm-wholesale', 'LogisticsController@confirmWholesale');
    Router::get('logistics/shipments', 'LogisticsController@shipments');
    Router::post('logistics/shipments/create', 'LogisticsController@createShipment');
    Router::post('logistics/shipments/create-from-orders', 'LogisticsController@createShipmentFromOrders');
    Router::post('logistics/shipments/create-from-bags', 'LogisticsController@createShipmentFromBags');
    Router::get('logistics/shipments/{id}', 'LogisticsController@showShipment');
    Router::post('logistics/shipments/{id}/status', 'LogisticsController@updateShipmentStatus');
    Router::post('logistics/shipments/{id}/add', 'LogisticsController@addToShipment');
    Router::post('logistics/shipments/{id}/add-orders', 'LogisticsController@addOrdersToShipment');
    Router::post('logistics/shipments/{id}/add-bags', 'LogisticsController@addBagsToShipment');
    Router::post('logistics/shipments/{id}/remove', 'LogisticsController@removeFromShipment');
    Router::get('logistics/deliveries', 'LogisticsController@deliveries');
    Router::post('logistics/deliveries/create', 'LogisticsController@createDelivery');
    Router::post('logistics/deliveries/{id}/mark', 'LogisticsController@markDelivered');
    Router::get('logistics/calculator', 'LogisticsController@shippingCalculator');
    Router::post('logistics/calculator/store', 'LogisticsController@saveRate');
    Router::post('logistics/calculator/{id}/delete', 'LogisticsController@deleteRate');
    Router::get('logistics/reports', 'LogisticsController@reports');
    Router::get('logistics/settings', 'LogisticsController@settings');
    Router::post('logistics/settings', 'LogisticsController@saveSettings');
    Router::get('logistics/bags', 'LogisticsController@bags');
    Router::post('logistics/bags/create', 'LogisticsController@createBag');
    Router::get('logistics/bags/{id}', 'LogisticsController@showBag');
    Router::post('logistics/bags/{id}/seal', 'LogisticsController@sealBag');
    Router::post('logistics/bags/{id}/update', 'LogisticsController@updateBag');
    Router::post('logistics/bags/{id}/delete', 'LogisticsController@deleteBag');
    Router::post('logistics/bags/{id}/scan', 'LogisticsController@scanToBag');
    Router::post('logistics/bags/{id}/remove-package', 'LogisticsController@removeFromBag');

    // Booking Links
    Router::get('bookings', 'BookingController@index');
    Router::get('bookings/create', 'BookingController@create');
    Router::post('bookings/store', 'BookingController@store');
    Router::get('bookings/{id}/edit', 'BookingController@edit');
    Router::post('bookings/{id}/update', 'BookingController@update');
    Router::post('bookings/{id}/delete', 'BookingController@delete');

    // Gamification
    Router::get('leaderboard', 'GamificationController@leaderboard');
    Router::get('achievements', 'GamificationController@achievements');

    // User Management
    Router::get('users', 'UserController@index');
    Router::get('users/create', 'UserController@create');
    Router::get('users/export', 'UserController@exportUsers');
    Router::post('users/store', 'UserController@store');
    Router::post('users/bulk-action', 'UserController@bulkAction');
    Router::get('users/{id}/edit', 'UserController@edit');
    Router::get('users/{id}/quick-view', 'UserController@quickView');
    Router::post('users/{id}/update', 'UserController@update');
    Router::post('users/{id}/toggle-active', 'UserController@toggleActive');
    Router::post('users/{id}/delete', 'UserController@delete');
    Router::post('users/{id}/reset-password', 'UserController@resetPassword');

    // Webhooks
    Router::get('webhooks', 'WebhookController@index');
    Router::get('webhooks/create', 'WebhookController@create');
    Router::post('webhooks/store', 'WebhookController@store');
    Router::get('webhooks/{id}', 'WebhookController@show');
    Router::post('webhooks/{id}/toggle', 'WebhookController@toggleActive');
    Router::post('webhooks/{id}/delete', 'WebhookController@delete');

    // Call Logs
    Router::get('call-logs', 'CallLogController@index');
    Router::get('call-logs/create', 'CallLogController@create');
    Router::post('call-logs/store', 'CallLogController@store');
    Router::post('call-logs/{id}/delete', 'CallLogController@delete');

    // Reports
    Router::get('reports', 'ReportController@index');
    Router::get('reports/customers', 'ReportController@customers');
    Router::get('reports/revenue', 'ReportController@revenue');
    Router::get('reports/deals', 'ReportController@deals');
    Router::get('reports/orders', 'ReportController@orders');
    Router::get('reports/tasks', 'ReportController@tasks');
    Router::get('reports/staff', 'ReportController@staff');

    // Global Search
    Router::get('search', 'SearchController@index');

    // Import / Export
    Router::get('import-export', 'ImportExportController@index');
    Router::post('import-export/import-contacts', 'ImportExportController@importContacts');
    Router::post('import-export/import-products', 'ImportExportController@importProducts');
    Router::get('import-export/export-contacts', 'ImportExportController@exportContacts');
    Router::get('import-export/export-products', 'ImportExportController@exportProducts');
    Router::get('import-export/template/{type}', 'ImportExportController@downloadTemplate');

    // Workflows
    Router::get('workflows', 'WorkflowController@index');
    Router::get('workflows/create', 'WorkflowController@create');
    Router::post('workflows/store', 'WorkflowController@store');
    Router::get('workflows/{id}/edit', 'WorkflowController@edit');
    Router::post('workflows/{id}/update', 'WorkflowController@update');
    Router::post('workflows/{id}/delete', 'WorkflowController@delete');
    Router::post('workflows/{id}/toggle', 'WorkflowController@toggleActive');
    Router::get('workflows/{id}/logs', 'WorkflowController@logs');

    // Automation
    Router::get('automation', 'AutomationController@index');
    Router::get('automation/create', 'AutomationController@create');
    Router::post('automation/store', 'AutomationController@store');
    Router::get('automation/{id}/logs', 'AutomationController@logs');
    Router::post('automation/{id}/toggle-active', 'AutomationController@toggleActive');
    Router::post('automation/{id}/delete', 'AutomationController@delete');

    // Email Plugin
    Router::get('email', 'EmailController@inbox');
    Router::get('email/compose', 'EmailController@compose');
    Router::post('email/send', 'EmailController@send');
    Router::post('email/sync', 'EmailController@sync');
    Router::get('email/settings', 'EmailController@settings');
    Router::post('email/settings/save', 'EmailController@saveAccount');
    Router::post('email/settings/test', 'EmailController@testAccount');
    Router::post('email/settings/{id}/delete', 'EmailController@deleteAccount');
    Router::post('email/settings/signature', 'EmailController@saveSignature');
    Router::post('email/bulk', 'EmailController@bulkAction');
    Router::get('email/templates', 'EmailController@templates');
    Router::post('email/templates/save', 'EmailController@saveTemplate');
    Router::post('email/templates/{id}/delete', 'EmailController@deleteTemplate');
    Router::get('email/download', 'EmailController@downloadAttachment');
    Router::get('email/{id}', 'EmailController@read');
    Router::post('email/{id}/star', 'EmailController@toggleStar');
    Router::post('email/{id}/trash', 'EmailController@moveToTrash');
    Router::post('email/{id}/delete', 'EmailController@delete');

    // Documents
    Router::get('documents', 'DocumentController@index');
    Router::post('documents/upload', 'DocumentController@upload');
    Router::get('documents/{id}/download', 'DocumentController@download');
    Router::post('documents/{id}/delete', 'DocumentController@delete');

    // Attendance & Payroll
    Router::get('attendance', 'AttendanceController@index');
    Router::post('attendance/check-in', 'AttendanceController@checkIn');
    Router::post('attendance/{id}/update', 'AttendanceController@update');
    Router::get('attendance/leaves', 'AttendanceController@leaves');
    Router::post('attendance/leaves/create', 'AttendanceController@createLeave');
    Router::post('attendance/leaves/{id}/approve', 'AttendanceController@approveLeave');
    Router::get('attendance/payroll', 'AttendanceController@payroll');
    Router::get('attendance/payroll/export', 'AttendanceController@exportPayroll');
    Router::get('attendance/payroll/{id}', 'AttendanceController@payrollDetail');
    Router::post('attendance/payroll/generate', 'AttendanceController@generatePayroll');
    Router::post('attendance/payroll/{id}/update', 'AttendanceController@updatePayroll');
    Router::post('attendance/payroll/{id}/confirm', 'AttendanceController@confirmPayroll');
    Router::post('attendance/payroll/{id}/paid', 'AttendanceController@markPaid');
    Router::post('attendance/payroll/bulk', 'AttendanceController@bulkConfirmPayroll');
    Router::get('attendance/payroll/history/{userId}', 'AttendanceController@payrollHistory');
    Router::get('attendance/advances', 'AttendanceController@advances');
    Router::post('attendance/advances/create', 'AttendanceController@createAdvance');
    Router::post('attendance/advances/{id}/approve', 'AttendanceController@approveAdvance');

    // System Info
    Router::get('system-info', 'SystemInfoController@index');

    // Lead Forms
    Router::get('lead-forms', 'LeadFormController@index');
    Router::get('lead-forms/create', 'LeadFormController@create');
    Router::post('lead-forms/store', 'LeadFormController@store');
    Router::get('lead-forms/{id}/edit', 'LeadFormController@edit');
    Router::post('lead-forms/{id}/update', 'LeadFormController@update');
    Router::post('lead-forms/{id}/delete', 'LeadFormController@delete');
    Router::get('lead-forms/{id}/embed', 'LeadFormController@embed');
    Router::get('lead-forms/{id}/submissions', 'LeadFormController@submissions');

    // Tags
    Router::get('tags', 'TagController@index');
    Router::get('tags/search', 'TagController@search');
    Router::post('tags/store', 'TagController@store');
    Router::post('tags/assign', 'TagController@assign');
    Router::post('tags/{id}/update', 'TagController@update');
    Router::post('tags/{id}/delete', 'TagController@delete');

    // Duplicates
    Router::get('duplicates', 'DuplicateController@index');
    Router::post('duplicates/scan', 'DuplicateController@scan');
    Router::post('duplicates/{id}/merge', 'DuplicateController@merge');
    Router::post('duplicates/{id}/ignore', 'DuplicateController@ignore');

    // Activities (static routes MUST be before {id} routes)
    Router::get('activities/feed', 'ActivityController@feed');
    Router::get('activities/calendar', 'ActivityController@calendar');
    Router::get('activities', 'ActivityController@index');
    Router::post('activities/store', 'ActivityController@store');
    Router::post('activities/{id}/react', 'ActivityController@react');
    Router::post('activities/{id}/reply', 'ActivityController@reply');
    Router::get('activities/{id}/edit', 'ActivityController@edit');
    Router::post('activities/{id}/update', 'ActivityController@update');
    Router::post('activities/{id}/delete', 'ActivityController@delete');

    // Billing
    Router::get('billing', 'BillingController@index');
    Router::get('billing/plans', 'BillingController@plans');
    Router::post('billing/subscribe', 'BillingController@subscribe');
    Router::get('billing/invoices', 'BillingController@invoices');
    Router::post('billing/cancel', 'BillingController@cancelSubscription');

    // Help Center
    Router::get('help', 'HelpController@index');
    Router::get('help/search', 'HelpController@search');
    Router::get('help/category/{slug}', 'HelpController@category');
    Router::get('help/article/{slug}', 'HelpController@article');
    Router::post('help/{id}/helpful', 'HelpController@helpful');

    // Onboarding
    Router::get('onboarding', 'OnboardingController@welcome');
    Router::post('onboarding/complete', 'OnboardingController@complete');

    // Plugins / Marketplace
    Router::get('plugins', 'PluginController@installed');
    Router::get('plugins/marketplace', 'PluginController@marketplace');
    Router::post('plugins/{id}/install', 'PluginController@install');
    Router::post('plugins/{id}/uninstall', 'PluginController@uninstall');
    Router::post('plugins/{id}/toggle', 'PluginController@toggleActive');
    Router::get('plugins/{id}/configure', 'PluginController@configure');
    Router::post('plugins/{id}/configure', 'PluginController@saveConfig');

    // Integrations
    Router::get('integrations', 'IntegrationController@index');
    Router::get('integrations/zalo', 'ZaloController@settings');
    Router::post('integrations/zalo', 'ZaloController@saveSettings');
    Router::post('integrations/zalo/send', 'ZaloController@send');
    Router::get('integrations/voip', 'VoipController@settings');
    Router::post('integrations/voip', 'VoipController@saveSettings');
    Router::post('integrations/voip/call', 'VoipController@makeCall');
    Router::get('integrations/voip/token', 'VoipController@token');

    // Google Calendar Integration
    Router::get('integrations/google-calendar', 'GoogleCalendarController@settings');
    Router::post('integrations/google-calendar', 'GoogleCalendarController@saveSettings');
    Router::get('integrations/google-calendar/connect', 'GoogleCalendarController@connect');
    Router::post('integrations/google-calendar/disconnect', 'GoogleCalendarController@disconnect');
    Router::post('integrations/google-calendar/sync', 'GoogleCalendarController@sync');

    // Payment Gateway Settings
    Router::get('integrations/vnpay', 'PaymentController@vnpaySettings');
    Router::post('integrations/vnpay', 'PaymentController@saveVNPaySettings');
    Router::get('integrations/momo', 'PaymentController@momoSettings');
    Router::post('integrations/momo', 'PaymentController@saveMoMoSettings');

    // Payment Checkout
    Router::get('payments/{invoiceId}/checkout', 'PaymentController@checkout');
    Router::post('payments/{invoiceId}/vnpay', 'PaymentController@processVNPay');
    Router::post('payments/{invoiceId}/momo', 'PaymentController@processMoMo');

    // Custom Fields
    Router::get('custom-fields', 'CustomFieldController@index');
    Router::get('custom-fields/create', 'CustomFieldController@create');
    Router::post('custom-fields/store', 'CustomFieldController@store');
    Router::get('custom-fields/{id}/edit', 'CustomFieldController@edit');
    Router::post('custom-fields/{id}/update', 'CustomFieldController@update');
    Router::post('custom-fields/{id}/delete', 'CustomFieldController@delete');
    Router::post('custom-fields/reorder', 'CustomFieldController@reorder');

    // Approvals
    Router::get('approvals', 'ApprovalController@index');
    Router::get('approvals/pending', 'ApprovalController@pending');
    // Merge requests (thêm người LH vào KH trùng)
    Router::post('merge-requests/store', 'MergeRequestController@store');
    Router::post('merge-requests/{id}/approve', 'MergeRequestController@approve');
    Router::post('merge-requests/{id}/reject', 'MergeRequestController@reject');
    Router::get('approvals/create', 'ApprovalController@create');
    Router::post('approvals/store', 'ApprovalController@store');
    Router::post('approvals/{id}/approve', 'ApprovalController@approve');
    Router::post('approvals/{id}/reject', 'ApprovalController@reject');

    // Departments
    Router::get('departments', 'DepartmentController@index');
    Router::get('departments/org-chart', 'DepartmentController@orgChart');
    Router::get('departments/kpi-comparison', 'DepartmentController@kpiComparison');
    Router::post('departments/store', 'DepartmentController@store');
    Router::post('departments/bulk-move', 'DepartmentController@bulkMove');
    Router::get('departments/{id}', 'DepartmentController@show');
    Router::get('departments/{id}/members', 'DepartmentController@members');
    Router::post('departments/{id}/update', 'DepartmentController@update');
    Router::post('departments/{id}/delete', 'DepartmentController@delete');
    Router::post('departments/{id}/kpi', 'DepartmentController@saveKpi');
    Router::post('departments/{id}/members/add', 'DepartmentController@addMember');
    Router::post('departments/{id}/members/{userId}/remove', 'DepartmentController@removeMember');
    Router::post('departments/{id}/positions', 'DepartmentController@savePosition');
    Router::post('departments/{id}/positions/{posId}/delete', 'DepartmentController@deletePosition');

    // Settings
    Router::get('settings', 'SettingController@index');
    Router::post('settings/profile', 'SettingController@updateProfile');
    Router::post('settings/avatar', 'SettingController@updateAvatar');
    Router::post('settings/password', 'SettingController@updatePassword');
    Router::get('settings/widgets', 'SettingController@widgets');
    Router::post('settings/widgets', 'SettingController@saveWidgets');
    Router::get('settings/api', 'SettingController@ai');
    Router::post('settings/api/save', 'SettingController@saveAi');
    Router::post('settings/api/behavior', 'SettingController@saveAiBehavior');
    Router::post('settings/api/clear-tax-cache', 'SettingController@clearTaxCache');
    Router::get('settings/api-keys', 'SettingController@apiKeys');
    Router::post('settings/api-keys/create', 'SettingController@createApiKey');
    Router::post('settings/api-keys/{id}/delete', 'SettingController@deleteApiKey');
    Router::get('settings/data-definition', 'DataDefinitionController@index');
    Router::get('settings/data-definition/{module}', 'DataDefinitionController@show');
    Router::post('settings/data-definition/{module}/update-field', 'DataDefinitionController@updateField');
    Router::post('settings/data-definition/{module}/delete-field', 'DataDefinitionController@deleteField');
    Router::post('settings/data-definition/{module}/toggle-show', 'DataDefinitionController@toggleShowInList');
    Router::get('settings/positions', 'PositionController@index');
    Router::post('settings/positions/store', 'PositionController@store');
    Router::post('settings/positions/{id}/update', 'PositionController@update');
    Router::post('settings/positions/{id}/delete', 'PositionController@delete');
    Router::get('settings/permissions', 'PermissionGroupController@index');
    Router::post('settings/perm-groups/store', 'PermissionGroupController@store');
    Router::post('settings/perm-groups/{id}/update', 'PermissionGroupController@update');
    Router::post('settings/perm-groups/{id}/delete', 'PermissionGroupController@destroy');
    Router::post('settings/perm-groups/{id}/save-perms', 'PermissionGroupController@savePermissions');
    Router::get('settings/perm-groups/{id}/panel', 'PermissionGroupController@getPanel');
    Router::post('settings/perm-groups/{id}/add-user', 'PermissionGroupController@addUser');
    Router::post('settings/perm-groups/{id}/remove-user', 'PermissionGroupController@removeUser');
    Router::post('settings/perm-groups/{id}/clone', 'PermissionGroupController@clone');
    Router::get('settings/permissions-legacy', 'SettingController@permissions');
    Router::post('settings/permissions-legacy', 'SettingController@savePermissions');
    Router::get('settings/audit-log', 'SettingController@auditLog');
    Router::get('settings/white-label', 'WhiteLabelController@settings');
    Router::post('settings/white-label', 'WhiteLabelController@save');

    // Company Profiles (Thông tin công ty)
    Router::get('settings/company-profiles', 'CompanyProfileController@index');
    Router::post('settings/company-profiles/store', 'CompanyProfileController@store');
    Router::post('settings/company-profiles/{id}/update', 'CompanyProfileController@update');
    Router::post('settings/company-profiles/{id}/delete', 'CompanyProfileController@delete');

    // Document Templates (Mẫu báo giá, hợp đồng)
    Router::get('settings/document-templates', 'DocumentTemplateController@index');
    Router::get('settings/document-templates/create', 'DocumentTemplateController@create');
    Router::post('settings/document-templates/store', 'DocumentTemplateController@store');
    Router::get('settings/document-templates/{id}/edit', 'DocumentTemplateController@edit');
    Router::post('settings/document-templates/{id}/update', 'DocumentTemplateController@update');
    Router::post('settings/document-templates/{id}/delete', 'DocumentTemplateController@delete');
    Router::post('settings/document-templates/{id}/toggle', 'DocumentTemplateController@toggle');

    // Contact Statuses
    Router::get('settings/contact-statuses', 'ContactStatusController@index');
    Router::post('settings/contact-statuses/store', 'ContactStatusController@store');
    Router::post('settings/contact-statuses/reorder', 'ContactStatusController@reorder');
    Router::post('settings/contact-statuses/{id}/update', 'ContactStatusController@update');
    Router::post('settings/contact-statuses/{id}/delete', 'ContactStatusController@delete');
    Router::post('settings/contact-statuses/{id}/default', 'ContactStatusController@setDefault');
    Router::post('settings/contact-statuses/{id}/toggle-active', 'ContactStatusController@toggleActive');
    Router::get('api/tax-lookup', 'TaxLookupController@lookup');

    // Getfly Sync
    Router::get('settings/getfly-sync', 'GetflySyncController@index');
    Router::post('settings/getfly-sync/save-config', 'GetflySyncController@saveConfig');
    Router::get('settings/getfly-sync/test-api', 'GetflySyncController@testApi');
    Router::post('settings/getfly-sync/sync', 'GetflySyncController@sync');
    Router::post('settings/getfly-sync/sync-tasks-page', 'GetflySyncController@syncTasksPage');
    Router::post('settings/getfly-sync/sync-accounts-page', 'GetflySyncController@syncAccountsPage');
    Router::post('settings/getfly-sync/sync-products-page', 'GetflySyncController@syncProductsPage');
    Router::post('settings/getfly-sync/sync-orders-page', 'GetflySyncController@syncOrdersPage');
    Router::post('settings/contact-sources/store', 'ContactStatusController@storeSource');
    Router::post('settings/contact-sources/reorder', 'ContactStatusController@reorderSources');
    Router::post('settings/contact-sources/{id}/update', 'ContactStatusController@updateSource');
    Router::post('settings/contact-sources/{id}/delete', 'ContactStatusController@deleteSource');
});
