<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FRONTEND
    |--------------------------------------------------------------------------
    |
    | Application will be publicly available on the URL below
    */

    'FRONTEND_URL' => env('FRONTEND_URL'),

    /*
    |--------------------------------------------------------------------------
    | Login Throttling
    |--------------------------------------------------------------------------
    |
    | This determines whether application will lockout a user after a set
    | number of attempts.
    */
    'THROTTLE_LOGIN' => true,

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Default timeouts for performing certain actions
    */

    'API_TIMEOUT' => 300,

    /*
    |--------------------------------------------------------------------------
    | Token Expiration
    |--------------------------------------------------------------------------
    |
    | Default token expiration for registeration tokens
    */

    'TOKENS_EXPIRES_IN' => 600,

    /*
    |--------------------------------------------------------------------------
    | Pagination Limit
    |--------------------------------------------------------------------------
    |
    | Sets the default limit to be applied for pagination
    */

    'PAGE_LIMIT' => 10,
];
