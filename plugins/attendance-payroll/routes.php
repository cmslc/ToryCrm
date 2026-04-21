<?php
use Core\Router;

Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('attendance', 'AttendanceController@index');
    Router::post('attendance/check-in', 'AttendanceController@checkIn');
    Router::post('attendance/{id}/update', 'AttendanceController@update');
    Router::get('attendance/leaves', 'AttendanceController@leaves');
    Router::post('attendance/leaves/create', 'AttendanceController@createLeave');
    Router::post('attendance/leaves/{id}/approve', 'AttendanceController@approveLeave');
    Router::get('attendance/payroll', 'AttendanceController@payroll');
    Router::get('attendance/payroll/export', 'AttendanceController@exportPayroll');
    Router::get('attendance/payroll/{id}', 'AttendanceController@payrollDetail');
    Router::post('attendance/payroll/generate', 'AttendanceController@generatePayroll');
    Router::post('attendance/payroll/{id}/update', 'AttendanceController@updatePayroll');
    Router::post('attendance/payroll/{id}/confirm', 'AttendanceController@confirmPayroll');
    Router::post('attendance/payroll/{id}/paid', 'AttendanceController@markPaid');
    Router::post('attendance/payroll/bulk', 'AttendanceController@bulkConfirmPayroll');
    Router::get('attendance/payroll/history/{userId}', 'AttendanceController@payrollHistory');
    Router::get('attendance/advances', 'AttendanceController@advances');
    Router::post('attendance/advances/create', 'AttendanceController@createAdvance');
    Router::post('attendance/advances/{id}/approve', 'AttendanceController@approveAdvance');
});
