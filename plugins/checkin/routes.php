<?php
use Core\Router;
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('checkins', 'CheckinController@index');
    Router::get('checkins/create', 'CheckinController@create');
    Router::get('checkins/map', 'CheckinController@map');
    Router::get('checkins/my', 'CheckinController@myCheckins');
    Router::post('checkins/store', 'CheckinController@store');
    Router::get('checkins/{id}', 'CheckinController@show');
});
