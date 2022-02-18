<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    // For laravel passport
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'name', 'phone', 'password', 'bvn'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    /**
     * Modify passport to login with phone or email
     */
    public function findForPassport($identifier) {
        return $this->where('phone', $identifier)->orWhere('email', $identifier)->first();
    }
    
    /**
     * Direct relationship
     * - A user can have many bank accounts
     */
    public function bankAccounts()
    {
        return $this->hasMany(BankUser::class)->with('bank');
    }

    /**
     * Direct relationship
     * - A user can have many bank accounts
     */
    public function banks()
    {
        return $this->hasMany(BankUser::class)->with('bank');
    }
    
    /**
     * Direct relationship
     * - A user can have many cards
     */
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Direct relationship
     * - A user can have many wallets
     */
    public function userWallets()
    {
        return $this->hasMany(WalletUser::class);
    }

    /**
     * Direct relationship
     * - A user can have many transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Direct relationship
     * - A user can have many notifications
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Direct relationship
     * - A user can have many orders
     */
    public function orders()
    {
        return $this->hasMany('App\Models\Order');
    }

    /**
     * Get the user's bvn.
     *
     * @param  int  $value
     * @return int
     */
    public function getBvnAttribute($value)
    {
        if(empty($value)) return $value;
        $mask_number =  str_repeat("*", strlen($value)-4) . substr($value, -4);
        
        return $mask_number ?? 'NULL';
    }
    
    public function getAvatarUrlAttribute($filename)
    {
        if(!$filename) return null;
        return config('app.url')."/public/storage/profile_pics/$filename";
    }
}
