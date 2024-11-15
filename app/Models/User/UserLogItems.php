<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserLogItems extends Model
{
   protected $fillable = [
      'user_id',
      'event_id',
      'withdraw_items_id',
      'count',
      'action'
   ];
}
