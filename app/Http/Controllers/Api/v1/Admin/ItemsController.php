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
      return ItemResource::collection(Items::query()->where('id', '>', 2)->get());
   }

   public function all()
   {
      return Items::query()->get();
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
   public function edit(string $id)
   {
      //
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
