<?php

use Core\Router;

Router::group(['middleware' => 'ApiAuthMiddleware'], function () {
    // Contacts
    Router::get('api/v1/contacts', 'Api\ContactApiController@list');
    Router::get('api/v1/contact', 'Api\ContactApiController@detail');
    Router::post('api/v1/contact', 'Api\ContactApiController@create');
    Router::post('api/v1/contact/update', 'Api\ContactApiController@update');
    Router::post('api/v1/contact/delete', 'Api\ContactApiController@delete');
    Router::get('api/v1/contacts/search', 'Api\ContactApiController@search');

    // Deals
    Router::get('api/v1/deals', 'Api\DealApiController@list');
    Router::get('api/v1/deal', 'Api\DealApiController@detail');
    Router::post('api/v1/deal', 'Api\DealApiController@create');
    Router::post('api/v1/deal/update', 'Api\DealApiController@update');
    Router::post('api/v1/deal/stage', 'Api\DealApiController@updateStage');

    // Products
    Router::get('api/v1/products', 'Api\ProductApiController@list');
    Router::get('api/v1/product', 'Api\ProductApiController@detail');
    Router::post('api/v1/product', 'Api\ProductApiController@create');
    Router::post('api/v1/product/update', 'Api\ProductApiController@update');

    // Orders
    Router::get('api/v1/orders', 'Api\OrderApiController@list');
    Router::get('api/v1/order', 'Api\OrderApiController@detail');
    Router::post('api/v1/order', 'Api\OrderApiController@create');
    Router::post('api/v1/order/approve', 'Api\OrderApiController@approve');
    Router::post('api/v1/order/payment', 'Api\OrderApiController@payment');

    // Tickets
    Router::get('api/v1/ticket/categories', 'Api\TicketApiController@categories');
    Router::get('api/v1/ticket/statuses', 'Api\TicketApiController@statuses');
    Router::get('api/v1/tickets', 'Api\TicketApiController@list');
    Router::get('api/v1/ticket', 'Api\TicketApiController@detail');
    Router::post('api/v1/ticket', 'Api\TicketApiController@create');
    Router::post('api/v1/ticket/update', 'Api\TicketApiController@update');

    // Accounting integration (read-only, tenant-scoped)
    Router::get('api/v1/fund_accounts', 'Api\AccountingApiController@funds');
    Router::get('api/v1/fund_transactions', 'Api\AccountingApiController@fundTransactions');
    Router::get('api/v1/warehouses', 'Api\AccountingApiController@warehouses');
    Router::get('api/v1/stock_movements', 'Api\AccountingApiController@stockMovements');
    Router::get('api/v1/product_categories', 'Api\AccountingApiController@productCategories');
    Router::get('api/v1/order_items', 'Api\AccountingApiController@orderItems');
    Router::get('api/v1/purchase_orders', 'Api\AccountingApiController@purchaseOrders');
    Router::get('api/v1/purchase_order_items', 'Api\AccountingApiController@purchaseOrderItems');
    Router::get('api/v1/payrolls', 'Api\AccountingApiController@payrolls');
    Router::get('api/v1/attendances', 'Api\AccountingApiController@attendances');
    Router::get('api/v1/debts', 'Api\AccountingApiController@debts');
    Router::get('api/v1/debt_payments', 'Api\AccountingApiController@debtPayments');
});
