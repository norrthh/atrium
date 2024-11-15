<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationItems;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
//        return Notif
   }

   /**
    * Store a newly created resource in storage.
    */

   public function store(Request $request): JsonResponse
   {
      $request->validate([
         'description' => ['required', 'string'],
         'href' => ['required', 'string'],
         'time' => ['required', 'int'],
         'prizes' => ['required'],
         'image' => ['required', 'string'],
      ]);

      $notificationOld = Notification::query()->orderBy('id', 'desc')->first();

      $notification = Notification::query()->create([
         'description' => $request->get('description'),
         'href' => $request->get('href'),
         'time' => $request->get('time'),
         'image' => $request->get('image'),
      ]);

      foreach ($request->get('prizes') as $item) {
         NotificationItems::query()->create([
            'notification_id' => $notification->id,
            'item_id' => $item['name']['id'],
            'count' => $item['count'],
         ]);
      }

      Notification::query()->where('id', $notificationOld->id)->delete();

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
