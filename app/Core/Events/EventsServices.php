<?php

namespace App\Core\Events;

use App\Core\Message\Message;
use App\Core\Method\SocialMethod;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventSocialLogs;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class EventsServices
{
   public function events(array $data): void
   {
      $findEvent = Event::query()->where('post_id', $data['object']['post_id'])->first();
      if ($findEvent) {
         switch ($findEvent->eventType) {
            case 1:
               (new EventOne())->event($data);
               break;
            default:
               break;
         }
      }
   }
   public function logUser(int $userID, int $postID, int $eventID): void
   {
      EventSocialLogs::query()->create([
         'user_id' => $userID,
         'post_id' => $postID,
         'event_id' => $eventID
      ]);
   }
   public function calculatePrize(int $totalComments, int $baseThreshold): bool
   {
      $prizeChances = [
         10 => 0.10,
         20 => 0.20,
         30 => 0.30,
         40 => 0.40,
         50 => 0.50,
         60 => 0.60,
         70 => 0.70,
         80 => 0.80,
         90 => 0.90,
         100 => 1.0,
      ];

      $prizeChance = 0;

      foreach ($prizeChances as $offset => $chance) {
         if ($totalComments <= $baseThreshold + $offset) {
            $prizeChance = $chance;
            break;
         }
      }

      return mt_rand() / mt_getrandmax() < $prizeChance;
   }
   public function winPrize(int $userID, int $eventID, array $prizes, SocialMethod $socialMethod): bool
   {
      $prize = [];

      foreach ($prizes as $item) {
         if (!EventPrize::query()->where('user_id', $userID)->where('event_id', $eventID)->whereJsonContains('prize', $item)->first()) {
            $prize = [
               'user_id' => $userID,
               'event_id' => $eventID,
               'prize' => $item
            ];

            $socialMethod->sendMessage($userID, Message::getMessage('prize_gift', ['name' => $item['name'], 'count' => $item['count'] ?? 1]));
            break;
         }
      }

      if (count($prize) > 0) {
         EventPrize::query()->create($prize);
         return true;
      }

      return false;
   }
}
