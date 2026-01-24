<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainerPackage extends Model
{
    protected $fillable = [
        'name',
        'package_type',
        'sessions_count',
        'duration_months',
        'price',
    ];

    protected $casts = [
        'sessions_count' => 'integer',
        'duration_months' => 'integer',
        'price' => 'decimal:2',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(TrainerBooking::class);
    }
}
