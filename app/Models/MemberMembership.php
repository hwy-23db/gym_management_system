<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberMembership extends Model
{
    protected $fillable = [
        'member_id',
        'membership_plan_id',
        'discount_percentage',
        'final_price',
        'start_date',
        'end_date',
        'is_expired',
        'is_on_hold',
        'hold_started_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_percentage' => 'decimal:2',
        'final_price' => 'decimal:2',
        'is_expired' => 'boolean',
        'is_on_hold' => 'boolean',
        'hold_started_at' => 'date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class, 'membership_plan_id');
    }
}
