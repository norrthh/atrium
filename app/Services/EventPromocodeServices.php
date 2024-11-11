<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventPromocode;

class EventPromocodeServices
{
   public function promocode(Event $event, array $prizes)
   {
      foreach ($prizes as $prize) {
         if ($prize['name']) {
            $this->store($event->id, $prize['promcoode'] ?? '', $prize['count'] ?? 0, $prize['countActivatePromocode'] ?? 0, $prize['name']['id']);
         }
      }
   }

   protected function store($event_id, $code, $count, $count_activate, $prize_id)
   {
      return EventPromocode::query()->create([
         'event_id' => $event_id,
         'prize_id' => $prize_id,
         'code' => $code,
         'count' => $count,
         'count_prize' => $count_activate,
         'count_used' => 0
      ]);
   }
}
