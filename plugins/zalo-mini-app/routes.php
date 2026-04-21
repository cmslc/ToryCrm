<?php
use Core\Router;

// Public webhook (no auth)
Router::post('webhooks/zalo', 'ZaloController@webhook');

Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('integrations/zalo', 'ZaloController@settings');
    Router::post('integrations/zalo', 'ZaloController@saveSettings');
    Router::post('integrations/zalo/send', 'ZaloController@send');
});
