<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPrize extends Model
{
   protected $fillable = [
      'event_id',
      'withdraw_items_id',
      'count_prize',
      'word',
      'status'
   ];

   protected $casts = [
      'prize' => 'array',
   ];
}
