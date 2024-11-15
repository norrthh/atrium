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
         'typeCreate' => ['required', 'int'],
         'name' => ['required'],
         'type' => ['required', 'array'],
         'type.type' => [''],
         'type.value' => [''],
         'prizes' => ['required', 'array'],
         'selectAccessPrize' => ['int', 'required'],
         'text' => ['string', 'required_if:typeCreate,2'],
         'image' => ['string', 'required_if:typeCreate,2'],
         'social' => ['required', 'required'],
      ]);

      return $eventPromocodeServices->create($request->all());
   }
}
