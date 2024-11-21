<?php

namespace App\Http\Controllers\Api\v1;

use App\Facades\WithdrawUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShopItemResource;
use App\Models\Items\ShopItems;
use App\Models\User\User;
use App\Models\WithdrawUsers;
use Illuminate\Http\Request;

class ShopController extends Controller
{
   public function index(Request $request)
   {
      $items = ShopItems::query()->where([['category', $request->get('type')], ['status', 0]])->with('item')->get();
      if (!$items) {
         return response()->json([]);
      }
      return ShopItemResource::collection($items);
   }

   public function buyItem(Request $request): \Illuminate\Http\JsonResponse
   {
      $shop = ShopItems::query()->where([['id', $request->get('id')], ['status', 0]])->first();
      if ($shop) {
         if ($shop->countActivate < $shop->count) {
            if (auth()->user()->coin >= $shop->price) {
               WithdrawUser::store($shop->item_id, 1);

               User::query()->where('id', auth()->user()->id)->update([
                  'coin' => auth()->user()->coin - $shop->price
               ]);

               ShopItems::query()->where('id', $shop->id)->update([
                  'countActivate' => $shop->countActivate + 1
               ]);

               return response()->json(['status' => true, 'message' => 'Вы успешно купили предмет']);
            } else {
               return response()->json(['status' => false, 'message' => 'Недостаточно монет']);
            }
         } else {
            return response()->json(['status' => false, 'message' => 'Предмет больше не доступен']);
         }
      }
      return response()->json(['status' => false, 'message' => 'Предмет не найден']);
   }
}
