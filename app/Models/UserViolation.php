<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserViolation extends Model
{
    protected $fillable = [
       'vkontakte_id',
       'telegram_id',
       'violations',
    ];
}
