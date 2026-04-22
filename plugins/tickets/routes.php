<?php
use Core\Router;

Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('tickets', 'TicketController@index');
    Router::get('tickets/create', 'TicketController@create');
    Router::post('tickets/store', 'TicketController@store');
    Router::get('tickets/{id}', 'TicketController@show');
    Router::get('tickets/{id}/edit', 'TicketController@edit');
    Router::post('tickets/{id}/update', 'TicketController@update');
    Router::post('tickets/{id}/comment', 'TicketController@comment');
    Router::post('tickets/{id}/delete', 'TicketController@delete');
    Router::post('tickets/{id}/quick-update', 'TicketController@quickUpdate');

    // SLA policies (part of the Tickets plugin)
    Router::get('sla', 'SlaController@index');
    Router::get('sla/create', 'SlaController@create');
    Router::post('sla/store', 'SlaController@store');
    Router::get('sla/{id}/edit', 'SlaController@edit');
    Router::post('sla/{id}/update', 'SlaController@update');
    Router::post('sla/{id}/delete', 'SlaController@delete');
});
