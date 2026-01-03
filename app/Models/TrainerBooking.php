<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerBooking extends Model
{
    protected $fillable = [
        'member_id',
        'trainer_id',
        'session_datetime',
        'duration_minutes',
        'sessions_count',
        'price_per_session',
        'total_price',
        'status',
        'paid_status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'session_datetime' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }
}
