<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AuctionController extends Controller
{
   public function index()
   {
      //
   }

   public function store(Request $request)
   {
      $request->validate([
         'item_id' => ['required', 'int'],
         'item_type' => ['required', 'array'],
         'start_price' => ['required', 'int'],
         'time' => ['required', 'int'],
      ]);

      $data = $request->all();
      $data['auction_end_time'] =  Carbon::now()->addHours((int) $request->get('time'));

      Auction::query()->create($data);

      return response()->json(['success']);
   }

   public function destroy(string $id)
   {
      Auction::query()->where('id', $id)->delete();
      return response()->json(['success' => true]);
   }
}
