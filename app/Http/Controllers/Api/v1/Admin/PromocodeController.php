<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Services\EventPromocodeServices;
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
}
