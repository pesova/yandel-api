<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This value determines which of the following gateway to use.
    | You can switch to a different driver at runtime.
    |
    */
    'default' => env('PAYMENT_GATEWAY', 'paystack'),

    /*
    |--------------------------------------------------------------------------
    | List of Drivers
    |--------------------------------------------------------------------------
    |
    | These are the list of drivers supported for payment processing.
    | You can change the name. Then you'll have to change
    | it in the map array too.
    |
    */
    'drivers' => [
        'paystack' => [
            'public_key' => env('PAYSTACK_PUBLIC_KEY'),
            'private_key' => env('PAYSTACK_PRIVATE_KEY'),
            'encryption_key' => env('PAYSTACK_ENCRYPTION_KEY'),
            'base_url' => env('PAYSTACK_URL', 'https://api.paystack.co'),
            'CALLBACK_PAYMENT_URL' => env('CALLBACK_PAYMENT_URL')
        ],

        'rave' => [
            'public_key' => env('RAVE_PUBLIC_KEY'),
            'private_key' => env('RAVE_PRIVATE_KEY'),
            'encryption_key' => env('RAVE_ENCRYPTION_KEY'),
            'base_url' => env('RAVE_URL'),
        ],

        'onepipe' => [
            'public_key' => env('ONEPIPE_PUBLIC_KEY'),
            'private_key' => env('ONEPIPE_PRIVATE_KEY'),
            'encryption_key' => env('ONEPIPE_ENCRYPTION_KEY'),
            'base_url' => env('ONEPIPE_URL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Maps
    |--------------------------------------------------------------------------
    |
    | This is the array of Classes that maps to Payment Gateway Drivers above.
    | You can create your own driver if you like and add the
    | config in the drivers array and the class to use for
    | here with the same name. You will have to extend
    | \App\Contracts\PaymentGatewayInterface in your driver.
    |
    */
    'map' => [
        'paystack' => \App\Services\Payment\Drivers\Paystack::class,
    ],
];