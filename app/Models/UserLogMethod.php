<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLogMethod extends Model
{
    protected $fillable = ['user_id', 'method', 'amountValue', 'object_id'];
}
