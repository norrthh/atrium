<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index(): JsonResponse
   {
        $lastNotification = Notification::query()->orderBy('id', 'desc')->with('item')->first();
        $data = [];

        if (!Cache::has('notification_' . $lastNotification->id . '_user_id=' . auth()->user()->id)) {
            $data = [
                'notification' => $lastNotification,
                'status' => true
            ];
        } else {
            $data = [
                'status' => false
            ];
        }

        return response()->json($data);
   }

    public function ready(): void
    {
//        $lastNotification = Notification::query()->orderBy('id', 'desc')->where('status', 0)->first();
//        Cache::forever('notification_' . $lastNotification->id . '_user_id=' . auth()->user()->id, 1);
   }

   /**
    * Store a newly created resource in storage.
    */

   public function store(Request $request)
   {
      $request->validate([
         'description' => ['required', 'string'],
         'href' => ['required', 'string'],
         'time' => ['required', 'int'],
         'attempts' => ['required'],
         'image' => ['required', 'string'],
      ]);

      $notificationOld = Notification::query()->orderBy('id', 'desc')->first();

      $notification = Notification::query()->create([
         'description' => $request->get('description'),
         'href' => $request->get('href'),
         'time' => $request->get('time'),
         'image' => $request->get('image'),
      ]);

      foreach ($request->get('attempts') as $item) {
         NotificationItems::query()->create([
            'notification_id' => $notification->id,
            'item_id' => $item['idItem'],
            'count' => $item['count'],
         ]);
      }

      if ($notificationOld) {
          Notification::query()->where('id', $notificationOld->id)->delete();
      }

      return response()->json([
         'message' => 'success'
      ]);
   }

   public function destroy(string $id): JsonResponse
   {
      Notification::query()->where('id', $id)->delete();

      return response()->json([
         'message' => 'success'
      ]);
   }
}
