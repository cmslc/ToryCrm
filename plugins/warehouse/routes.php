<?php
use Core\Router;
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
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
});
