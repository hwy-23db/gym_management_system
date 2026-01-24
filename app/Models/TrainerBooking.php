<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerBooking extends Model
{
     protected $appends = [
        'member_phone',
        'trainer_phone',
    ];

    protected $fillable = [
        'member_id',
        'trainer_id',
        'trainer_package_id',
        'sessions_count',
        'price_per_session',
        'total_price',
        'status',
        'paid_status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    // App\Models\TrainerBooking.php

    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function trainerPackage(): BelongsTo
    {
        return $this->belongsTo(TrainerPackage::class);
    }

    public function getMemberPhoneAttribute(): ?string
    {
        return $this->member?->phone;
    }

    public function getTrainerPhoneAttribute(): ?string
    {
        return $this->trainer?->phone;
    }

}
