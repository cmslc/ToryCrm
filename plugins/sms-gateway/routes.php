<?php
use Core\Router;
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('integrations/sms', 'SmsGatewayController@show');
});
