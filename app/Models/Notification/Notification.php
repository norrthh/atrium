<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Notification extends Model
{
   protected $fillable = [
      'description', 'href', 'time', 'image', 'status', 'type_social'
   ];

   public function item(): \Illuminate\Database\Eloquent\Relations\HasMany
   {
      return $this->hasMany(NotificationItems::class, 'notification_id', 'id')->with('item');
   }
}
