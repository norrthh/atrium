<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['post_id', 'type', 'event_id', 'eventType', 'postMessage'];

    public function korobka()
    {
        return $this->hasOne(EventKorobka::class, 'id', 'event_id');
    }
}
