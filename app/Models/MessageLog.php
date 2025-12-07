<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageLog extends Model
{
    protected $table = 'message_logs';

    protected $fillable = [
        'user_id',
        'type',
        'to',
        'message',
        'subject',
        'status',
        'error'
    ];
}
