<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecycleItem extends Model
{
    protected $fillable = [
        'recycle_process_id',
        'item_type',
        'item_name',
        'weight',
        'quantity',
        'value',
        'notes',
        'created_at',
        'updated_at',
    ];

    public function recycleProcess()
    {
        return $this->belongsTo(RecycleProcess::class, 'recycle_process_id');
    }
}
