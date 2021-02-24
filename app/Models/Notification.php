<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];
    
    /**
     * Inverse relationship
     * - A notification belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A notification belong to one of multiple services
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}
