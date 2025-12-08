<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecycleImages extends Model
{
    protected $fillable = [
        'recycle_process_id',   // foreign key linking to recycle_processes table
        'image_path',         // file path or filename of uploaded image
    ];
    public function recycleProcess()
    {
        return $this->belongsTo(RecycleProcess::class, 'recycle_process_id');
    }

}
