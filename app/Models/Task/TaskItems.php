<?php

namespace App\Models\Task;

use App\Models\Items\Items;
use Illuminate\Database\Eloquent\Model;

class TaskItems extends Model
{
    protected $fillable = [
       'item_id',
       'task_id',
       'count',
    ];

   public function item()
   {
      return $this->hasOne(Items::class, 'id', 'item_id');
    }
}
