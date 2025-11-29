<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'request_date',
        'waste_type',
        'waste_description',
        'estimated_weight',
        'hazardous',
        'priority',
        'city_corporation_id',
        'ward_id',
        'zone_name',
        'address',
        'latitude',
        'longitude',
        'assigned_to',
        'assigned_team_id',
        'pickup_date',
        'completion_date',
        'status',
        'remarks',
        'photo_before',
        'photo_after',
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => 'pending',
        'hazardous' => false,
    ];

    /**
     * Relationships
     */

    // Request submitted by a user (citizen)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Waste request handled by an employee
    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function cityCorporation()
    {
        return $this->belongsTo(CityCorporation::class, 'city_corporation_id');
    }

    public function assignedCollector()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // If requests are managed by teams
    public function assignedTeam()
    {
        return $this->belongsTo(Team::class, 'assigned_team_id');
    }

    // Link to the ward table
    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    // Related uploaded images (optional)
    public function images()
    {
        return $this->hasMany(WasteRequestImage::class);
    }

    /**
     * Accessors / Mutators (optional)
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
    

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

}
