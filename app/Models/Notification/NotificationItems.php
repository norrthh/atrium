<?php

namespace App\Models\Notification;

use App\Models\Items\Items;
use Illuminate\Database\Eloquent\Model;

class NotificationItems extends Model
{
   protected $fillable = [
      'item_id',
      'notification_id',
      'count'
   ];

   public function item()
   {
      return $this->hasOne(Items::class, 'id', 'item_id');
   }
}
