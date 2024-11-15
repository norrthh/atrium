<?php

namespace App\Models\Withdrawl;

use Illuminate\Database\Eloquent\Model;

class WithdrawItems extends Model
{
    protected $fillable = ['name', 'icon', 'price', 'type', 'typeView'];
}
