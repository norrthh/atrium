<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserCoins extends Model
{
    protected $fillable = ['user_id', 'coin_id'];
}
