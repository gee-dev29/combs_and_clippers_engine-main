<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class DisputeResolution extends Model

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }
{ 
    protected $fillable = ['dispute_id', 'transcode', 'merchant_comment', 'customer_comment', 'arbitrator_comment', 'resolution_desc', 'sitting_date', 'next_sitting_date'];
    
    protected $table = 'dispute_resolutions';
    
}
