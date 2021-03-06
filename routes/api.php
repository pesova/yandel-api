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
    | User ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'user', 'middleware'=>'auth:api'], function(){
        Route::get('/', 'UserController@getUserInfo');
        Route::put('/', 'UserController@updateUser');
        Route::post('/avatar', 'UserController@updateProfilePicture');
    });

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
    
    /*
    |--------------------------------------------------------------------------
    | CARD ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'cards', 'middleware'=>'auth:api'], function(){
        Route::get('/', 'CardController@listCards');
        Route::post('/', 'CardController@addCard');
        Route::get('/{card_id}', 'CardController@findCard');
        Route::delete('/{card_id}', 'CardController@deleteCard');
    });

    /*
    |--------------------------------------------------------------------------
    | WALLET ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'wallets', 'middleware'=>'auth:api'], function(){
        Route::get('/', 'WalletController@listUserWallets');
        Route::get('/{wallet_id}', 'WalletController@findWallet');
        Route::post('/deposit', 'WalletController@creditUserWallet');
    });

    /*
    |--------------------------------------------------------------------------
    | COUPON ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'coupons', 'middleware'=>'auth:api'], function(){
        Route::get('/', 'CouponController@listCoupons');
        Route::post('/sell', 'CouponController@sellCoupon');
    });

    /*
    |--------------------------------------------------------------------------
    | ORDER ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'orders', 'middleware'=>'auth:api'], function(){
        Route::get('/', 'OrderController@listOrders');
        Route::post('/buy', 'OrderController@buy');
        Route::post('/sell', 'OrderController@sell');
        Route::get('/{order_id}', 'OrderController@findOrder');
    });

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION ROUTES
    |--------------------------------------------------------------------------
    */
    Route::group(['prefix'=>'transactions', 'middleware'=>'auth:api'], function(){
        Route::get('/', 'TransactionController@listTransactions');
        Route::post('/deposit', 'TransactionController@deposit');
        Route::post('/withdraw', 'TransactionController@withdraw');
        Route::post('/transfer', 'TransactionController@transfer');
        Route::post('/validate', 'TransactionController@validateTransaction');
        Route::get('/{transaction_id}', 'TransactionController@findTransaction');
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

    Route::get('/legal', 'SettingController@getLegal');
    Route::get('paystack/callback', 'TransactionController@handlePayment');
});


