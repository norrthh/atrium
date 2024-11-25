<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WithdrawUserResource;
use App\Models\User\WithdrawUsers;
use App\Models\Withdraw\WithdrawItems;

class WithdrawController extends Controller
{
   public function index()
   {
      return WithdrawUserResource::collection(
         WithdrawUsers::query()
            ->where('status', 2)
            ->orderBy('id', 'desc')
            ->with(['item', 'user'])
            ->get()
      );
   }

   public function me()
   {
      return WithdrawUserResource::collection(
         WithdrawUsers::query()
            ->where([['user_id', auth()->user()->id], ['status', '>', 1]])
            ->orderBy('id', 'desc')
            ->with('item')
            ->get()
      );
   }
}
