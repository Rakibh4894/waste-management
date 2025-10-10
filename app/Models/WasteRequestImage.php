<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteRequestImage extends Model
{
    protected $fillable = [
        'waste_request_id',   // foreign key linking to waste_requests table
        'image_path',         // file path or filename of uploaded image
    ];
    public function wasteRequest()
    {
        return $this->belongsTo(WasteRequest::class);
    }

}
