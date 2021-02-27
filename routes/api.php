<?php

use App\Http\Controllers\AuthController;
// use App\Http\Controllers\UserController;
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
    | AUTHENTICATION ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'auth'], function(){
        // Guest Registration & Login
        Route::post('/otp', [AuthController::class, 'sendRegisterationOtp']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        // Guest Password reset
        Route::post('/password/reset', [AuthController::class, 'requestPasswordReset']);
        Route::get('/password/reset/{token}', [AuthController::class, 'findPasswordResetToken']);
        Route::put('/password/reset', [AuthController::class, 'resetPassword']);

        // Protected
        Route::group(['middleware'=>'auth:api'], function(){
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::put('/password/update', [AuthController::class, 'updatePassword']);
        });
    });

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

    Route::group(['prefix'=>'user', 'middleware'=>'auth:api'], function(){

        Route::put('/update', 'UserController@updateUser');

    });

    Route::get('user/{id}', 'UserController@getUserInfo');

});


