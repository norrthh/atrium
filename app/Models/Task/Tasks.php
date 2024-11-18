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
      'status'
   ];

   protected $casts = [
      'access' => 'array',
   ];

   public function items()
   {
      return $this->hasMany(TaskItems::class, 'task_id', 'id')->with('item');
   }
}
