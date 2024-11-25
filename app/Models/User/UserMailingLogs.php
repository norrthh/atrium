<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserMailingLogs extends Model
{
   protected $fillable = [
      'mailing_id',
      'telegraph_id',
      'response',
      'status',
   ];

   protected $casts = [
      'response' => 'array',
   ];

}
