<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserBan extends Model
{
    protected $fillable = [
       'vkontakte_id',
       'telegram_id',
    ];
}
