<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    /*
    |--------------------------------------------------------------------------
    | NOTIFICATION ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'notifications', 'middleware'=>'auth:api'], function(){
        Route::get('/', [NotificationController::class, 'getNotifications']);
        Route::put('/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/', [NotificationController::class, 'deleteNotifications']);
        Route::delete('/{id}', [NotificationController::class, 'deleteNotifications']);
    });

});


