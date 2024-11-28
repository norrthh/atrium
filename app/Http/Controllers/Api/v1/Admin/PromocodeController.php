<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromocodeResource;
use App\Models\Promocode\Promocode;
use App\Services\EventPromocodeServices;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromocodeController extends Controller
{
   public function store(Request $request, EventPromocodeServices $eventPromocodeServices)
   {
      $request->validate([
         'type_id' => ['required', 'int'],
         'name' => ['required'],
         'expiration' => ['required', 'array'],
         'expiration.*.type' => ['required', 'int'],
         'expiration.*.value' => ['required', 'int'],
         'prizes' => ['required', 'array'],
         'selectAccessPrize' => ['required', 'int'],
         'text' => ['nullable', 'string', 'required_if:type_id,2'],
         'image' => ['nullable', 'string', 'required_if:type_id,2'],
         'social' => ['string'],
      ]);

      return response()->json($eventPromocodeServices->create($request->all()));
   }

   public function index()
   {
      $promocodes = Promocode::query()->with('item')->get();

      if (count($promocodes) == 0) {
         return response()->json([]);
      }

      return PromocodeResource::collection($promocodes);
   }

   public function destroy($id): JsonResponse
   {
      Promocode::query()->where('id', $id)->delete();
      return response()->json([]);
   }
}
