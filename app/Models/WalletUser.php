<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class WalletUser extends Model
{
    use HasFactory;

    use SoftDeletes;
    
    protected $guarded = [];

    /** 
     * Inverse relationship
     * - A wallet belongs to one user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Inverse relationship 
     * - A user can have many wallets
     */
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
    
    /**
     * Direct relationship
     * - A users wallet can perform many deposits
     */
    public function deposits()
    {
        return $this->morphMany('App\Models\Transaction', 'destination');
    }

    /**
     * Direct relationship
     * - A users wallet can perform many withdrawals
     */
    public function withdrawals()
    {
        return $this->morphMany('App\Models\Transaction', 'source');
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }
}
