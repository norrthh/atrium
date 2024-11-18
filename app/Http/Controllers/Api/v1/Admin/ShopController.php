<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Items\ShopItems;
use Illuminate\Http\Request;

class ShopController extends Controller
{
   public function index(Request $request)
   {
      return ShopItems::query()
         ->where('category', $request->get('category'))
         ->get();
   }

   public function store(Request $request)
   {
      $request->validate([
         'item_id' => ['required', 'int'],
         'item_type' => ['required', 'array'],
         'price' => ['required', 'int'],
         'count' => ['required', 'int'],
         'category' => ['required', 'int'],
      ]);

      $item = ShopItems::query()->create($request->all());
      return $item;
   }

   public function destroy(string $id)
   {
      ShopItems::query()->where('id', $id)->delete();
      return response()->json(['success' => true]);
   }
}
