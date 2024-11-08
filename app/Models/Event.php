<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
       'post_id',
       'social_type',
       'event_id',
       'eventType',
       'postMessage',
       'word',
       'countAttempt',
       'bg',
       'subscribe',
       'subscribe_mailing',
       'timeForAttempt',
       'cumebackPlayer',
       'text',
       'states',
       'attempts',
       'uploadStatus',
       'countMessage',
    ];

   protected $casts = [
      'bg' => 'array',
      'cumebackPlayer' => 'array',
      'states' => 'array',
      'attempts' => 'array',
      'uploadStatus' => 'array',
   ];

    public function korobka()
    {
        return $this->hasOne(EventKorobka::class, 'id', 'event_id');
    }
}
