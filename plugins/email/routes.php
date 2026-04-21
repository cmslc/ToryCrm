<?php
use Core\Router;
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    // Email Templates
    Router::get('email-templates', 'EmailTemplateController@index');
    Router::get('email-templates/create', 'EmailTemplateController@create');
    Router::post('email-templates/store', 'EmailTemplateController@store');
    Router::get('email-templates/{id}/edit', 'EmailTemplateController@edit');
    Router::post('email-templates/{id}/update', 'EmailTemplateController@update');
    Router::post('email-templates/{id}/delete', 'EmailTemplateController@delete');
    Router::get('email-templates/{id}/preview', 'EmailTemplateController@preview');
    Router::post('email-templates/{id}/send', 'EmailTemplateController@send');
    // Email client
    Router::get('email', 'EmailController@inbox');
    Router::get('email/compose', 'EmailController@compose');
    Router::post('email/send', 'EmailController@send');
    Router::post('email/sync', 'EmailController@sync');
    Router::get('email/settings', 'EmailController@settings');
    Router::post('email/settings/save', 'EmailController@saveAccount');
    Router::post('email/settings/test', 'EmailController@testAccount');
    Router::post('email/settings/{id}/delete', 'EmailController@deleteAccount');
    Router::post('email/settings/signature', 'EmailController@saveSignature');
    Router::post('email/bulk', 'EmailController@bulkAction');
    Router::get('email/templates', 'EmailController@templates');
    Router::post('email/templates/save', 'EmailController@saveTemplate');
    Router::post('email/templates/{id}/delete', 'EmailController@deleteTemplate');
    Router::get('email/download', 'EmailController@downloadAttachment');
    Router::get('email/{id}', 'EmailController@read');
    Router::post('email/{id}/star', 'EmailController@toggleStar');
    Router::post('email/{id}/trash', 'EmailController@moveToTrash');
    Router::post('email/{id}/delete', 'EmailController@delete');
});
