<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;

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

      Auction::query()->create($request->all());

      return response()->json(['success']);
   }

   public function destroy(string $id)
   {
      Auction::query()->where('id', $id)->delete();
      return response()->json(['success' => true]);
   }
}
