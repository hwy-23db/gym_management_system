<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainerPackage extends Model
{
    protected $fillable = [
        'package_type',
        'quantity',
        'duration_unit',
        'price',
        'name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'display_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get only active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by package type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('package_type', $type);
    }
}
