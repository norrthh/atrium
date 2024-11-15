<?php

namespace App\Models\Event;

use Illuminate\Database\Eloquent\Model;

class EventSocialLogs extends Model
{
    protected $fillable = ['event_id', 'user_id', 'post_id'];
}
