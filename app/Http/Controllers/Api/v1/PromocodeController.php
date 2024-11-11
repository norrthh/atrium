<?php

namespace App\Http\Controllers\Api\v1;

use App\Core\Events\EventsServices;
use App\Core\Method\VkontakteMethod;
use App\Http\Controllers\Controller;
use App\Models\EventPromocode;
use App\Models\EventPromocodeActivate;
use Illuminate\Http\Request;

class PromocodeController extends Controller
{
   public function activate(Request $request)
   {
      $eventPromocode = EventPromocode::query()->where('code', $request->get('coupon'))->first();
      if ($eventPromocode) {
         if ($eventPromocode->count_prize > $eventPromocode->count_used) {
            if (!EventPromocodeActivate::query()->where([['event_promocodes_id', $eventPromocode->id], ['user_id', auth()->user()->id]])->first()) {
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

               EventPromocode::query()->where('id', $eventPromocode->id)->update([
                  'count_used' => $eventPromocode->count_used + 1
               ]);

               if ($eventPromocode->count_used + 1 == $eventPromocode->count_prize) {
                  EventPromocode::query()->where('id', $eventPromocode->id)->update([
                     'status' => 1
                  ]);
               }

               return response()->json([
                  'message' => 'Промокод активирован',
               ]);
            } else {
               return response()->json([
                  'message' => 'Вы уже активировали промокод',
                  'status' => 404
               ]);
            }
         } else {
            return response()->json([
               'message' => 'Количество активации у промокода исчерпано',
               'status' => 404
            ]);
         }
      } else {
         return response()->json([
            'message' => 'Промокод не найден',
            'status' => 404
         ]);
      }
   }
}
