<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawItems extends Model
{
    protected $fillable = ['name', 'icon', 'price', 'type'];
}
