<?php

namespace App\Models\Promocode;

use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
   protected $fillable = [
      'code',
      'expiration',
      'promo_type',
      'countPrize',
      'event_id',
   ];

   protected $casts = [
      'expiration' => 'array',
   ];
}
