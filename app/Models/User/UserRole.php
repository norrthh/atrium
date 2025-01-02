<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $fillable = ['telegram_id','vkontakte_id', 'role'];
}
