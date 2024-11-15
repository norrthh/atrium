<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
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
      //
   }
}
