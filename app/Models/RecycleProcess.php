<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecycleProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'waste_request_id',
        'recycle_status',
        'sorted_material',
        'sorting_officer_id',
        'sorting_completed_at',
        'recycled_output',
        'recycling_operator_id',
        'recycling_completed_at',
        'notes',
        'sorted_by',
        'sorted_at',
    ];

    public function wasteRequest()
    {
        return $this->belongsTo(WasteRequest::class);
    }

    public function sortingOfficer()
    {
        return $this->belongsTo(User::class, 'sorting_officer_id');
    }

    public function recyclingOperator()
    {
        return $this->belongsTo(User::class, 'recycling_operator_id');
    }

    public function cityCorporation()
    {
        return $this->belongsTo(CityCorporation::class, 'city_corporation_id');
    }

    public function recycleItems()
    {
        return $this->hasMany(RecycleItem::class, 'recycle_process_id');
    }

    public function images()
    {
        return $this->hasMany(RecycleImages::class);
    }

}
