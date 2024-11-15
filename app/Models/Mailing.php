<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mailing extends Model
{
   protected $fillable = [
      'social',
      'text',
      'status'
   ];
}
