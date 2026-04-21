<?php
use Core\Router;

Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('workflows', 'WorkflowController@index');
    Router::get('workflows/create', 'WorkflowController@create');
    Router::post('workflows/store', 'WorkflowController@store');
    Router::get('workflows/{id}/edit', 'WorkflowController@edit');
    Router::post('workflows/{id}/update', 'WorkflowController@update');
    Router::post('workflows/{id}/delete', 'WorkflowController@delete');
    Router::post('workflows/{id}/toggle', 'WorkflowController@toggleActive');
    Router::get('workflows/{id}/logs', 'WorkflowController@logs');

    Router::get('automation', 'AutomationController@index');
    Router::get('automation/create', 'AutomationController@create');
    Router::post('automation/store', 'AutomationController@store');
    Router::get('automation/{id}/logs', 'AutomationController@logs');
    Router::post('automation/{id}/toggle-active', 'AutomationController@toggleActive');
    Router::post('automation/{id}/delete', 'AutomationController@delete');
});
