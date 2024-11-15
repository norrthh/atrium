<?php

namespace App\Models\Event;

use Illuminate\Database\Eloquent\Model;

class EventUserLog extends Model
{
    protected $fillable = ['user_id', 'event_id', 'type', 'status'];
}
