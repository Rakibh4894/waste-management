<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyBillAmount extends Model
{
    protected $table = 'monthly_bill_amounts';

    protected $fillable = [
        'city_corporation_id',
        'ward_id',
        'amount',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function cityCorporation(): BelongsTo
    {
        return $this->belongsTo(CityCorporation::class, 'city_corporation_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
}
