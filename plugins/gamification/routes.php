<?php
use Core\Router;
Router::group(['middleware' => ['TenantMiddleware', 'AuthMiddleware', 'CsrfMiddleware']], function () {
    Router::get('leaderboard', 'GamificationController@leaderboard');
    Router::get('achievements', 'GamificationController@achievements');
});
