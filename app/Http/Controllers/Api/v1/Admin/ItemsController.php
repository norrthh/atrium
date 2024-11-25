<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Items\Items;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      $items = Items::query()->where('id', '>', 4)->get();

      if(count($items) == 0 ) {
         return response()->json([]);
      }

      return ItemResource::collection($items);
   }

   public function all()
   {
      $items = Items::query()->get();

      if(count($items) == 0 ) {
         return response()->json([]);
      }

      return ItemResource::collection($items);
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request): JsonResponse
   {
      Items::query()->create([
         'name' => $request->get('name'),
         'idItem' => $request->get('idItem'),
         'icon' => $request->get('icon'),
         'skin' => $request->get('skin')
      ]);

      return response()->json(['message' => 'success']);
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(Request $request)
   {
      $item = Items::query()->where('id', $request->get('id'))->first();
      if($item) {
         Items::query()->where('id', $request->get('id'))->update([
            'name' => $request->get('name') ?? $item->name,
            'idItem' => $request->get('idItem') ?? $item->idItem,
            'icon' => $request->get('icon') ?? $item->img,
            'skin' => $request->get('skin') ?? $item->skin
         ]);
      }
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(string $id)
   {
      Items::query()->where('id', $id)->delete();
      return response()->json(['success' => true]);
   }

   public function coinbilet()
   {
      return Items::query()->where('id', '<=', 2)->get();
   }
}
