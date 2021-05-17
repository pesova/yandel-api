<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'currencyTypes:id,coupon_id,name,buy_rate,sell_rate'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function currencyTypes()
    {
        return $this->belongsTo(CouponCurrencyType::class, 'coupon_currency_type_id');
    }

}
