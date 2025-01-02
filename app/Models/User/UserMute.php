<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserMute extends Model
{
   protected $fillable = ['telegram_id', 'vkontakte_id', 'time'];
}
