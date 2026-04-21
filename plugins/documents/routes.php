<?php
use Core\Router;
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('documents', 'DocumentController@index');
    Router::post('documents/upload', 'DocumentController@upload');
    Router::get('documents/{id}/download', 'DocumentController@download');
    Router::post('documents/{id}/delete', 'DocumentController@delete');
});
