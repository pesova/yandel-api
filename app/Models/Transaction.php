<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    
    use SoftDeletes;
    
    protected $guarded = [];

    protected $hidden = ['gateway_response'];


    protected $fillable = [
        'reference',
        'user_id',
        'type',
        'source_type',
        'source_id',
        'destination_type',
        'destination_id',
        'amount',
        'fees',
        'balance',
        'remark',
        'status',
        'gateway_response',
        'retry_count',
    ];

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
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Inverse relationship
     */
    public function destination()
    {
        return $this->morphTo();
    }

    /**
     * Using model events to create transaction references
     * 
     // TODO: Put this in a seperate event/listeners file
     */
    public static function boot()
    {
        parent::boot();
        
        self::creating(function ($model) {
            $model->reference = $model->reference ?? random_strings();
        });
    }
}
