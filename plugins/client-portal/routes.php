<?php
use Core\Router;
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('client-portal', 'ClientPortalController@show');
});
