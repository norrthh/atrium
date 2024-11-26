<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuctionResource;
use App\Models\Auction;
use App\Models\User\User;
use App\Models\User\UserAuction;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
   public function index()
   {
      $auctions = Auction::query()->with('item')->get();

      if (count($auctions) == 0) {
         return response()->json([]);
      }

      return AuctionResource::collection($auctions);
   }

   public function buy(Request $request)
   {
      $lastOrder = UserAuction::query()->where('auction_id', $request->get('id'))->orderBy('id', 'desc')->first();
      $amount = 0;
      if (!$lastOrder) {
         $lastOrder = Auction::query()->where('id', $request->get('id'))->first();

         if (!$lastOrder) {
            return response()->json([
               'message' => 'Auction not found',
               'status' => false
            ]);
         }

         $amount = $lastOrder->start_price;
      } else {
         $amount = $lastOrder->value;
      }

      if ($request->get('price') >= $amount + 10) {
         if (auth()->user()->coin >= $request->get('price')) {

            User::query()->where('id', auth()->user()->id)->update([
               'coin' => auth()->user()->coin - $request->get('price'),
            ]);

            UserAuction::query()->create([
               'user_id' => auth()->user()->id,
               'auction_id' => $request->get('id'),
               'value' => $request->get('price'),
            ]);

            return response()->json([
               'message' => 'Вы успешно сделали ставку',
               'status' => true
            ]);
         } else {
            return response()->json([
               'message' => 'Недостаточно монет',
               'status' => false
            ]);
         }
      } else {
         return response()->json([
            'message' => 'Новая ставка должна быть больше чем предыдушая',
            'status' => false
         ]);
      }

//      if ($lastOrder->price > $request->get('price')) {
   }
}
