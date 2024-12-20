<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMute extends Model
{
   protected $fillable = ['telegram_id', 'vkontakte_id', 'time'];
}
