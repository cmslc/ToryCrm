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

// Protected routes
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    // Dashboard
    Router::get('', 'DashboardController@index');
    Router::get('dashboard', 'DashboardController@index');

    // Contacts
    Router::get('contacts', 'ContactController@index');
    Router::get('contacts/trash', 'ContactController@trash');
    Router::get('contacts/create', 'ContactController@create');
    Router::post('contacts/store', 'ContactController@store');
    Router::get('contacts/{id}', 'ContactController@show');
    Router::get('contacts/{id}/edit', 'ContactController@edit');
    Router::get('contacts/{id}/bonus-points', 'ContactController@bonusPoints');
    Router::post('contacts/{id}/update', 'ContactController@update');
    Router::post('contacts/{id}/delete', 'ContactController@delete');
    Router::post('contacts/{id}/restore', 'ContactController@restore');
    Router::post('contacts/{id}/change-owner', 'ContactController@changeOwner');
    Router::post('contacts/{id}/add-bonus-points', 'ContactController@addBonusPoints');

    // Companies
    Router::get('companies', 'CompanyController@index');
    Router::get('companies/create', 'CompanyController@create');
    Router::post('companies/store', 'CompanyController@store');
    Router::get('companies/{id}', 'CompanyController@show');
    Router::get('companies/{id}/edit', 'CompanyController@edit');
    Router::post('companies/{id}/update', 'CompanyController@update');
    Router::post('companies/{id}/delete', 'CompanyController@delete');

    // Deals (Pipeline)
    Router::get('deals', 'DealController@index');
    Router::get('deals/pipeline', 'DealController@pipeline');
    Router::get('deals/create', 'DealController@create');
    Router::post('deals/store', 'DealController@store');
    Router::get('deals/{id}', 'DealController@show');
    Router::get('deals/{id}/edit', 'DealController@edit');
    Router::post('deals/{id}/update', 'DealController@update');
    Router::post('deals/{id}/delete', 'DealController@delete');
    Router::post('deals/{id}/stage', 'DealController@updateStage');

    // Tasks
    Router::get('tasks', 'TaskController@index');
    Router::get('tasks/kanban', 'TaskController@kanban');
    Router::get('tasks/trash', 'TaskController@trash');
    Router::get('tasks/create', 'TaskController@create');
    Router::post('tasks/store', 'TaskController@store');
    Router::get('tasks/{id}', 'TaskController@show');
    Router::get('tasks/{id}/edit', 'TaskController@edit');
    Router::post('tasks/{id}/update', 'TaskController@update');
    Router::post('tasks/{id}/delete', 'TaskController@delete');
    Router::post('tasks/{id}/complete', 'TaskController@complete');
    Router::post('tasks/{id}/cancel', 'TaskController@cancel');
    Router::post('tasks/{id}/restore', 'TaskController@restore');
    Router::post('tasks/{id}/status', 'TaskController@updateStatus');

    // Products
    Router::get('products', 'ProductController@index');
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
    Router::post('orders/{id}/delete', 'OrderController@delete');
    Router::post('orders/{id}/approve', 'OrderController@approve');
    Router::post('orders/{id}/cancel', 'OrderController@cancel');
    Router::post('orders/{id}/restore', 'OrderController@restore');
    Router::post('orders/{id}/payment', 'OrderController@payment');
    Router::post('orders/{id}/status', 'OrderController@updateStatus');

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

    // Tickets
    Router::get('tickets', 'TicketController@index');
    Router::get('tickets/create', 'TicketController@create');
    Router::post('tickets/store', 'TicketController@store');
    Router::get('tickets/{id}', 'TicketController@show');
    Router::get('tickets/{id}/edit', 'TicketController@edit');
    Router::post('tickets/{id}/update', 'TicketController@update');
    Router::post('tickets/{id}/comment', 'TicketController@comment');
    Router::post('tickets/{id}/delete', 'TicketController@delete');

    // Campaigns
    Router::get('campaigns', 'CampaignController@index');
    Router::get('campaigns/create', 'CampaignController@create');
    Router::post('campaigns/store', 'CampaignController@store');
    Router::get('campaigns/{id}', 'CampaignController@show');
    Router::get('campaigns/{id}/edit', 'CampaignController@edit');
    Router::post('campaigns/{id}/update', 'CampaignController@update');
    Router::post('campaigns/{id}/add-contact', 'CampaignController@addContact');
    Router::post('campaigns/{id}/delete', 'CampaignController@delete');

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
    Router::post('purchase-orders/{id}/delete', 'PurchaseOrderController@delete');

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

    // User Management
    Router::get('users', 'UserController@index');
    Router::get('users/create', 'UserController@create');
    Router::post('users/store', 'UserController@store');
    Router::get('users/{id}/edit', 'UserController@edit');
    Router::post('users/{id}/update', 'UserController@update');
    Router::post('users/{id}/toggle-active', 'UserController@toggleActive');

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

    // Global Search
    Router::get('search', 'SearchController@index');

    // Import / Export
    Router::get('import-export', 'ImportExportController@index');
    Router::post('import-export/import-contacts', 'ImportExportController@importContacts');
    Router::post('import-export/import-products', 'ImportExportController@importProducts');
    Router::get('import-export/export-contacts', 'ImportExportController@exportContacts');
    Router::get('import-export/export-products', 'ImportExportController@exportProducts');
    Router::get('import-export/template/{type}', 'ImportExportController@downloadTemplate');

    // Automation
    Router::get('automation', 'AutomationController@index');
    Router::get('automation/create', 'AutomationController@create');
    Router::post('automation/store', 'AutomationController@store');
    Router::get('automation/{id}/logs', 'AutomationController@logs');
    Router::post('automation/{id}/toggle', 'AutomationController@toggleActive');
    Router::post('automation/{id}/delete', 'AutomationController@delete');

    // Activities
    Router::get('activities', 'ActivityController@index');
    Router::post('activities/store', 'ActivityController@store');

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

    // Settings
    Router::get('settings', 'SettingController@index');
    Router::post('settings/profile', 'SettingController@updateProfile');
    Router::post('settings/password', 'SettingController@updatePassword');
    Router::get('settings/widgets', 'SettingController@widgets');
    Router::post('settings/widgets', 'SettingController@saveWidgets');
    Router::get('settings/api-keys', 'SettingController@apiKeys');
    Router::post('settings/api-keys/create', 'SettingController@createApiKey');
    Router::post('settings/api-keys/{id}/delete', 'SettingController@deleteApiKey');
    Router::get('settings/permissions', 'SettingController@permissions');
    Router::post('settings/permissions', 'SettingController@savePermissions');
    Router::get('settings/audit-log', 'SettingController@auditLog');
});
