<?php

namespace App\Http\Controllers\Api\v1;

use App\Core\Events\EventsServices;
use App\Facades\WithdrawUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\PromocodeResource;
use App\Models\Event\EventPromocode;
use App\Models\Event\EventPromocodeActivate;
use App\Models\Promocode\Promocode;
use App\Models\Promocode\PromocodeItem;
use App\Models\UserActivatePromocode;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PromocodeController extends Controller
{
   // $promocode->expiration - 1 - count use , 2 - time
   public function index(Request $request)
   {
      $promocode = Promocode::query()->where('code', $request->get('code'))->first();

      if (!$promocode) {
         return response()->json([
            'message' => 'Такой промокод не существует',
            'status' => 404
         ]);
      }

      if (UserActivatePromocode::query()->where([['promocode_id', $promocode->id], ['user_id', auth()->user()->id]])->first()) {
         return response()->json([
            'message' => 'Вы уже активировали этот промокод',
            'status' => 403
         ]);
      }

      $expiration = $promocode->expiration[0];
      switch ($expiration['type']) {
         case 1:
            if ($expiration['value'] < UserActivatePromocode::query()->where('promocode_id', $promocode->id)->count()) {
               return response()->json([
                  'message' => 'Промокод исчерпан',
                  'status' => 403
               ]);
            }

            return response()->json([
               'message' => 'Промокод активирован',
               'item' => PromocodeResource::collection(PromocodeItem::query()->where('promocode_id', $promocode->id)->with('item')->get()),
               'select' => $promocode->countPrize,
               'status' => 200,
               'promo_id' => $promocode->id
            ]);
         case 2:
            if ($expiration['value'] < Carbon::parse($promocode->created_at)->diffInHours(now())) {
               return response()->json([
                  'message' => 'Промокод исчерпан',
                  'status' => 403
               ]);
            }

            return response()->json([
               'message' => 'Промокод активирован',
               'item' => PromocodeResource::collection(PromocodeItem::query()->where('promocode_id', $promocode->id)->with('item')->get()),
               'select' => $promocode->countPrize,
               'status' => 200,
               'promo_id' => $promocode->id
            ]);
         default:
      };
   }

   public function activate(Request $request)
   {
      $promocode = Promocode::query()->where('id', $request->get('promo_id'))->first();

      if (!$promocode) {
         return response()->json([
            'message' => 'Такой промокод не существует'
         ], 404);
      }

      if (UserActivatePromocode::query()->where([['promocode_id', $promocode->id], ['user_id', auth()->user()->id]])->first()) {
         return response()->json([
            'message' => 'Вы уже активировали этот промокод'
         ], 403);
      }

      if (count($request->get('items')) > $promocode->countPrize) {
         return response()->json([
            'message' => 'Вы не можете выбрать более ' . $promocode->countPrize . ' предметов',
            'status' => 100
         ]);
      }

      foreach ($request->get('items') as $item) {
         $promocodeItem = PromocodeItem::query()->where([['promocode_id', $promocode->id], ['id', $item['id']]])->first();
         if ($promocodeItem) {
            WithdrawUser::store($promocodeItem->item_id, $promocodeItem->count);
         }
      }

      UserActivatePromocode::query()->create([
         'promocode_id' => $promocode->id,
         'user_id' => auth()->user()->id
      ]);

      return response()->json([
         'message' => 'Промокод активирован',
      ]);
   }
}
