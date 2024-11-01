<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventVkontakteLog extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'event_id',
    ];
}
