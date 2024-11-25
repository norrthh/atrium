<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserActivatePromocode extends Model
{
    protected $fillable = [
        'user_id',
        'promocode_id'
    ];
}
