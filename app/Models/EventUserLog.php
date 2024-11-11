<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventUserLog extends Model
{
    protected $fillable = ['user_id', 'event_id', 'type', 'status'];
}
