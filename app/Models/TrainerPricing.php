<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerPricing extends Model
{
    protected $table = 'trainer_pricing';

    protected $fillable = [
        'trainer_id',
        'price_per_session',
    ];

    public $timestamps = true;

    const CREATED_AT = null;
    const UPDATED_AT = 'updated_at';
}
