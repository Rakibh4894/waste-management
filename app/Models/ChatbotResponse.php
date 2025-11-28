<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotResponse extends Model
{
    use HasFactory;

    protected $fillable = ['question', 'response'];
}
