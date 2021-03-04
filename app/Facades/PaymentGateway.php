<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class PaymentGateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'payment-gateway';
    }
}