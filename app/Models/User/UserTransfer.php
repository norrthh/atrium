<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserTransfer extends Model
{
   protected $fillable = [
      'user_id', 'from', 'to', 'code', 'status'
   ];
}
