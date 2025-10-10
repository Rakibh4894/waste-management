<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityCorporation extends Model
{
    protected $table = 'city_corporations';
    protected $fillable = [
        'title',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function wards()
    {
        return $this->hasMany(Ward::class, 'city_corporation_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
