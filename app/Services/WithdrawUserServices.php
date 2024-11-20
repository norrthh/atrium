<?php

namespace App\Services;

use App\Models\WithdrawUsers;

class WithdrawUserServices
{
   public function store(int $item_id, int $count, int $user_id = null)
   {
//      for ($i = 0; $i < $count; $i++) {
         return WithdrawUsers::query()->create([
            'user_id' => $user_id ?: auth()->user()->id,
            'item_id' => $item_id,
            'count' => $count
         ]);
//      }
   }
}
