<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponCurrencyType extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_available' => 'boolean'
    ];

    protected $hidden = [
        'buy_margin',
        'sell_margin'
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function getSellRateAttribute($sell_rate)
    {
        $margin = $this->sell_margin/100 * $sell_rate;
        
        return  (float) $sell_rate - $margin;
    }

    public function getBuyRateAttribute($buy_rate)
    {
        $margin = $this->buy_margin/100 * $buy_rate;
        
        return  (float) $buy_rate + $margin;
    }
}
