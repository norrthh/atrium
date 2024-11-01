<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventUsers extends Model
{
    protected $fillable = [
        'event_id', 'user_id', 'countAttempt'
    ];
}
