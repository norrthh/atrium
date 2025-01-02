<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserViolation extends Model
{
    protected $fillable = [
       'vkontakte_id',
       'telegram_id',
       'violations',
    ];
}
