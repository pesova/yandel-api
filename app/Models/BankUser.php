<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class BankUser extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'bank_id',
        'nuban',
        'name'
    ];
    
    /**
     * Returns bank referenced by a bank user
     */
    public function bank()
    {
        return $this->belongsTo('App\Models\Bank');
    }

    /**
     * Returns the user who owns a particular bank
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
