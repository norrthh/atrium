<?php

namespace App\Models\Event;

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
      'status',
      'states',
      'attempts',
      'uploadStatus',
      'countMessage',
      'like',
      'repost'
   ];

   protected $casts = [
      'bg' => 'array',
      'cumebackPlayer' => 'array',
      'states' => 'array',
      'attempts' => 'array',
      'uploadStatus' => 'array',
      'like' => 'array',
      'repost' => 'array',
   ];

}
