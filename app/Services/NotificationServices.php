<?php

namespace App\Services;

use App\Http\Resources\NotificationResource;
use App\Models\Notification\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class NotificationServices
{
   public function getNotification()
   {
      $notification = Notification::query()
         ->orderBy('id', 'desc')
         ->where('status', 0)
         ->with('item')
         ->first();

      $data = [
         'status' => false
      ];

      if ($notification) {
         $hoursThreshold = (int)$notification->time;
         $hoursSinceCreated = Carbon::parse($notification->created_at)->diffInHours(now());

         if ($hoursSinceCreated < $hoursThreshold) {
            if (!Cache::has('notification_' . $notification->id . '_user_id1=' . auth()->user()->id)) {
               $data = [
                  'data' => new NotificationResource($notification),
                  'status' => true
               ];
            }
         } else {
            Notification::query()->where('id', $notification->id)->update(['status' => 1]);
         }
      }

      return $data;
   }
}
