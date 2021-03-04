<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    use SoftDeletes;
    
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
    /**
     * Direct relationship
     * - A wallet has many wallet users
     * - Get user wallets of a wallet type
     */
    public function walletUsers()
    {
        return $this->hasMany(WalletUser::class);
    }

    public function balance()
    {
        return $this->hasOne(WalletUser::class);
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }

    public function transactions()
    {
        return $this->morphMany('App\Models\Transaction', 'source');
    }
}
