<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Token extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'channel', 'subscriber', 'event', 'token', 'verified', 'expires_at',
    ];

    protected $hidden = ['token'];

    protected $casts = [
        'verified'=>'boolean',
    ];
}
