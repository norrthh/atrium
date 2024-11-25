<?php

namespace App\Services;

use App\Models\User\User;
use App\Models\User\WithdrawUsers;

class WithdrawUserServices
{
   public function store(string $item_id, string $count, int $user_id = null)
   {
      switch ($item_id) {
         case 1:
            User::query()->where('id', $user_id ?: auth()->user()->id)->update([
               'coin' => User::query()->where('id', $user_id ?: auth()->user()->id)->first()->coin + $count,
               'coins_week' => User::query()->where('id', $user_id ?: auth()->user()->id)->first()->coins_week + $count,
            ]);
            break;
         case 2:
            User::query()->where('id', $user_id ?: auth()->user()->id)->update([
               'bilet' => User::query()->where('id', $user_id ?: auth()->user()->id)->first()->bilet + $count
            ]);
            break;
         default:
            return WithdrawUsers::query()->create([
               'user_id' => $user_id ?: auth()->user()->id,
               'item_id' => $item_id,
               'count' => $count
            ]);
            break;
      }
   }
}
