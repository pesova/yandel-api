<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_type', 'currency', 'coupon_type', 'volume', 'rate', 'unit_price', 'total_payable', 'fee', 'remark'];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }


}
