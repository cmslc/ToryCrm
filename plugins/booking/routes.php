<?php
use Core\Router;
// Public
Router::get('book/{slug}', 'BookingController@publicPage');
Router::get('book/{slug}/slots', 'BookingController@getAvailableSlots');
Router::post('book/{slug}', 'BookingController@bookSlot');
// Admin
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('bookings', 'BookingController@index');
    Router::get('bookings/create', 'BookingController@create');
    Router::post('bookings/store', 'BookingController@store');
    Router::get('bookings/{id}/edit', 'BookingController@edit');
    Router::post('bookings/{id}/update', 'BookingController@update');
    Router::post('bookings/{id}/delete', 'BookingController@delete');
});
