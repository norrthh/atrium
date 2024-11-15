<?php

namespace App\Models\Event;

use Illuminate\Database\Eloquent\Model;

class EventCumbackPlayer extends Model
{
    protected $fillable = ['user_id', 'event_id'];
}
