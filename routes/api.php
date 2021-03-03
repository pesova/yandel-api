<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
        Route::post('/otp', 'AuthController@sendRegisterationOtp');
        Route::post('/register', 'AuthController@register');
        Route::post('/login', 'AuthController@login');

        // Guest Password reset
        Route::post('/password/reset', 'AuthController@requestPasswordReset');
        Route::get('/password/reset/{token}', 'AuthController@findPasswordResetToken');
        Route::put('/password/reset', 'AuthController@resetPassword');

        // Protected
        Route::group(['middleware'=>'auth:api'], function(){
            Route::post('/logout', 'AuthController@logout');
            Route::put('/password/update', 'AuthController@updatePassword');
            Route::post('/contact', 'SettingController@contactSupport');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | ORDER ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'orders', 'middleware'=>'auth:api'], function(){
        Route::get('/', 'TransactionController@list');
        Route::post('/buy', 'TransactionController@buy');
        Route::post('/sell', 'TransactionController@sell');
        Route::get('/{order_id}', 'TransactionController@find');
    });

    /*
    |--------------------------------------------------------------------------
    | NOTIFICATION ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'notifications', 'middleware'=>'auth:api'], function(){
        Route::get('/', 'NotificationController@getNotifications');
        Route::put('/read', 'NotificationController@markAsRead');
        Route::delete('/', 'NotificationController@deleteNotifications');
        Route::delete('/{id}', 'NotificationController@deleteNotifications');
    });

    Route::group(['prefix'=>'user', 'middleware'=>'auth:api'], function(){
        Route::get('/{id}', 'UserController@getUserInfo');
        Route::get('/{id}', 'UserController@getUserInfo');
        Route::put('/', 'UserController@updateUser');
        Route::post('/profile_pic', 'UserController@updateProfilePicture');
    });
    
    Route::get('/legal', 'SettingController@getLegal');


    /*
    |--------------------------------------------------------------------------
    | BANK ROUTES
    |--------------------------------------------------------------------------
    */
    Route::get('/list-banks', 'BankController@listBanks');
    Route::get('/banks/enquiry', 'BankController@bankAccountEnquiry');
    Route::group(['prefix'=>'/banks', 'middleware'=>'auth:api'], function(){
        Route::post('/', 'BankController@addBankAccount');
        Route::get('/', 'BankController@listBankAccounts');
        Route::get('/{bank_id}', 'BankController@findBankAccount');
        Route::delete('/{bank_id}', 'BankController@deleteBankAccount');
    });

});


