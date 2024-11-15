<?php

namespace App\Http\Controllers\Api\v1;

use App\Core\Events\EventsServices;
use App\Http\Controllers\Controller;
use App\Models\Event\EventPromocode;
use App\Models\Event\EventPromocodeActivate;
use Illuminate\Http\Request;

class PromocodeController extends Controller
{
   public function activate(Request $request)
   {
      $eventPromocodes = EventPromocode::query()
         ->where('code', $request->get('coupon'))
         ->whereColumn('count_prize', '>', 'count_used')
         ->get();

      $availablePromocodes = $eventPromocodes->filter(function ($promocode) {
         return !EventPromocodeActivate::query()->where([
            ['event_promocodes_id', $promocode->id],
            ['user_id', auth()->user()->id]
         ])->exists();
      });

      if ($availablePromocodes->isNotEmpty()) {
         $eventPromocode = $availablePromocodes->first();

         (new EventsServices())->giveItemUser(
            user_id: auth()->user()->id,
            event_id: $eventPromocode->event_id,
            item_id: $eventPromocode->prize_id,
            count: $eventPromocode->count,
            actionText: 'Активация промокода',
         );

         EventPromocodeActivate::query()->create([
            'event_promocodes_id' => $eventPromocode->id,
            'user_id' => auth()->user()->id
         ]);

         $eventPromocode->update([
            'count_used' => $eventPromocode->count_used + 1
         ]);

         // Обновляем статус, если лимит исчерпан
         if ($eventPromocode->count_used + 1 == $eventPromocode->count_prize) {
            $eventPromocode->update(['status' => 1]);
         }

         return response()->json([
            'message' => 'Промокод активирован',
            'status' => 200
         ]);
      } else {
         return response()->json([
            'message' => 'Доступные промокоды не найдены или уже активированы вами',
            'status' => 404
         ]);
      }

   }
}
