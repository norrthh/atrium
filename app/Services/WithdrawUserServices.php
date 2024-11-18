<?php

namespace App\Services;

use App\Models\WithdrawUsers;

class WithdrawUserServices
{
   public function store(int $item_id, int $count): void
   {
      for ($i = 0; $i < $count; $i++) {
         WithdrawUsers::query()->create([
            'user_id' => auth()->user()->id,
            'item_id' => $item_id,
            'count' => 1
         ]);
      }
   }
}
