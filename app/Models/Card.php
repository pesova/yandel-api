<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;
    
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = ['token', 'deleted_at'];

    /**
     * Direct relationship
     * - A card can perform many deposits
     */
    public function deposits()
    {
        return $this->hasMany('App\Models\Deposit');
    }

    public function user()
    {
        return $this->belongsTo(User::class );
    }
}
