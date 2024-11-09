<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPrize extends Model
{
   protected $fillable = [
      'user_id',
      'event_id',
      'prize',
      'word'
   ];

   protected $casts = [
      'prize' => 'array',
   ];
}
