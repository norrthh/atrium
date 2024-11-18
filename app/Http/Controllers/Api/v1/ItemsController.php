<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Items\Items;
use Illuminate\Http\Request;

class ItemsController extends Controller
{
   public function getShop(Request $request)
   {
        $request->validate([
            'type' => ['required', 'string']
        ]);

//        return WithdrawItems::query()->where([['typeView', 1], ['type', $request->type]])->orWhere([['typeView', 3], ['type', $request->type]])->get();
   }

   public function getEvent()
   {
      return Items::query()->get();
   }
}
