<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteRequest extends Model
{
    
    protected $fillable = [
        'area',
        'address',
        'latitude',
        'longitude',
        'waste_type',
        'quantity',
        'description',
        'pickup_date',
        'pickup_time',
        'notes',
        'status',
    ];

    public function images()
    {
        return $this->hasMany(WasteRequestImage::class);
    }
}
