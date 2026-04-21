<?php
/**
 * Lead Forms plugin routes.
 * Loaded by PluginLoader::loadRoutes() AFTER main routes/web.php.
 */

use Core\Router;

// Public endpoints (no auth)
Router::get('form/{slug}', 'LeadFormController@publicForm');
Router::post('form/{slug}/submit', 'LeadFormController@publicSubmit');

// Admin endpoints — use the same middleware stack as main routes/web.php
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('lead-forms', 'LeadFormController@index');
    Router::get('lead-forms/create', 'LeadFormController@create');
    Router::post('lead-forms/store', 'LeadFormController@store');
    Router::get('lead-forms/{id}/edit', 'LeadFormController@edit');
    Router::post('lead-forms/{id}/update', 'LeadFormController@update');
    Router::post('lead-forms/{id}/delete', 'LeadFormController@delete');
    Router::get('lead-forms/{id}/embed', 'LeadFormController@embed');
    Router::get('lead-forms/{id}/submissions', 'LeadFormController@submissions');
});
