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
        'notes',
    ];

    protected $casts = [
        'session_datetime' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }
}
