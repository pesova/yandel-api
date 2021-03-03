<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];
    
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Returns bank users tied to a particulat bank
     */
    public function bankUsers()
    {
        return $this->belongsTo('App\Models\BankUsers');
    }

    public function getLogoUrlAttribute($filename)
    {
        if(!$filename) return null;
        return config('app.url')."/storage/bank-logos/$filename";
    }
}
