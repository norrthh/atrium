<?php

namespace App\Services;

use App\Models\User\UserLogItems;

class LogItemServices
{
   public function addLog(int $user_id, int $event_id, string $text): void
   {
      UserLogItems::query()->create([
         'user_id' => $user_id,
         'event_id' => $event_id,
         'action' => $text
      ]);
   }
}
