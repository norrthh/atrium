<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPromocodeActivate extends Model
{
   protected $fillable = [
      'event_promocodes_id',
      'user_id',
   ];
}
