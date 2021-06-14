<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'currencyTypes:id,coupon_id,name,buy_rate,sell_rate'
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_visible' => 'boolean'
    ];

    protected $hidden = [
        'buy_margin',
        'sell_margin'
    ];

    public function currencyTypes()
    {
        return $this->hasMany(CouponCurrencyType::class);
    }

    
    public function getImageUrlAttribute($filename)
    {
        if(!$filename) return null;
        return config('app.url')."/public/storage/coupons/$filename";
    }
}
