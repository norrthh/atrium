<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWarns extends Model
{
    protected $fillable = ['telegram_id', 'vkontakte_id', 'count'];
}
