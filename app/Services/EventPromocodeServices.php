<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPromocode;

class EventPromocodeServices
{
   public function promocode(Event $event, array $prizes)
   {
      foreach ($prizes as $prize) {
         $this->store($event->id, $prize['promcoode'] ?? '', $prize['countActivatePromocode']);
      }
   }

   protected function store($event_id, $code, $count, $count_activate)
   {
      return EventPromocode::query()->create([
         'event_id' => $event_id,
         'code' => $code,
         'count' => $count,
         'count_prize' => $count_activate,
      ]);
   }
}
