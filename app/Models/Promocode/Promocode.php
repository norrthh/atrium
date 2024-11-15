<?php

namespace App\Models\Promocode;

use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
   protected $fillable = [
      'code',
      'type',
      'countPrize',
      'event_id',
   ];

   protected $casts = [
      'type' => 'array',
   ];
}
