<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trainer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'specialty',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(TrainerBooking::class);
    }

    public function pricing(): HasOne
    {
        return $this->hasOne(TrainerPricing::class);
    }
}
