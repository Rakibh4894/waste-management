<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $table = 'wards';
    protected $fillable = ['city_corporation_id', 'number', 'status'];
    public $timestamps = false;

    public function cityCorporation()
    {
        return $this->belongsTo(CityCorporation::class, 'city_corporation_id');
    }


}
