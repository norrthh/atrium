<?php

namespace App\Models\Task;

use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
   protected $fillable = [
      'typeSocial',
      'typeTask',
      'href',
      'description',
      'access',
   ];
}
