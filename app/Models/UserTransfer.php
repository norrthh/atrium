<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTransfer extends Model
{
   protected $fillable = [
      'user_id', 'from', 'to', 'code', 'status'
   ];
}
