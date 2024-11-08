<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupons extends Model
{
    protected $fillable = [
        'code', 'prizes', 'countActivate', 'userActivate'
    ];

   protected $casts = [
      'prizes' => 'array',
   ];
}
