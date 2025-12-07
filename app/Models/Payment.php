<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'ward_id',
        'city_corporation_id',
        'payment_month',
        'amount',
        'late_fee',
        'status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'payment_month' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }

    public function cityCorporation(): BelongsTo
    {
        return $this->belongsTo(CityCorporation::class, 'city_corporation_id');
    }

    // helpers
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
