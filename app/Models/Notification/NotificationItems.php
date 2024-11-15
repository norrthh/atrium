<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class NotificationItems extends Model
{
   protected $fillable = [
      'item_id',
      'notification_id',
      'status'
   ];
}
