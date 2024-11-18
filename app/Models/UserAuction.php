<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAuction extends Model
{
    protected $fillable = [
         'user_id',
         'auction_id',
         'value',
    ];
}
