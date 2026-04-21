<?php
use Core\Router;

Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('custom-fields', 'CustomFieldController@index');
    Router::get('custom-fields/create', 'CustomFieldController@create');
    Router::post('custom-fields/store', 'CustomFieldController@store');
    Router::get('custom-fields/{id}/edit', 'CustomFieldController@edit');
    Router::post('custom-fields/{id}/update', 'CustomFieldController@update');
    Router::post('custom-fields/{id}/delete', 'CustomFieldController@delete');
    Router::post('custom-fields/reorder', 'CustomFieldController@reorder');
});
