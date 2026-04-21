<?php
use Core\Router;
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('pdf-pro', 'ExportPdfProController@show');
});
