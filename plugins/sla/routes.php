<?php
use Core\Router;
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('sla', 'SlaController@index');
    Router::get('sla/create', 'SlaController@create');
    Router::post('sla/store', 'SlaController@store');
    Router::get('sla/{id}/edit', 'SlaController@edit');
    Router::post('sla/{id}/update', 'SlaController@update');
    Router::post('sla/{id}/delete', 'SlaController@delete');
});
