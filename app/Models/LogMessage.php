<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogMessage extends Model
{
    protected $fillable = ['user_id', 'post_id', 'typeMessage'];
}
