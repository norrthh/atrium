<?php

namespace App\Core\Events;

use App\Core\Message\Message;
use App\Core\Method\SocialMethod;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventSocialLogs;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EventsServices
{
   public function events($post_id, $user_id, $comment_id, $sendMessageUser, SocialMethod $socialMethod): void
   {
      $findEvent = Event::query()->where('post_id', $post_id)->first();
      if ($findEvent) {
         switch ($findEvent->eventType) {
            case 1:
               (new EventOne())->event($post_id, $user_id, $comment_id, $sendMessageUser, $socialMethod);
               break;
            case 2:
               (new EventTwo())->event($post_id, $user_id, $comment_id, $sendMessageUser, $socialMethod);
               break;
            case 3:
               (new EventThree())->event($post_id, $user_id, $comment_id, $sendMessageUser, $socialMethod);
               break;
            case 4:
               (new EventFour())->event($post_id, $user_id, $comment_id, $sendMessageUser, $socialMethod);
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

   public function winPrize(int $userID, int $eventID, array $prizes, SocialMethod $socialMethod, string $word = null): bool
   {
      $prize = [];

      foreach ($prizes as $item) {
         if ($word) {
            if (!EventPrize::query()->where([['event_id', $eventID], ['user_id', $userID], ['word', $word]])->whereJsonContains('prize', $item)->first()) {
               $prize = [
                  'user_id' => $userID,
                  'event_id' => $eventID,
                  'prize' => $item,
                  'word' => $word
               ];

               $socialMethod->sendMessage($userID, Message::getMessage('prize_gift', ['name' => $item['name'], 'count' => $item['count'] ?? 1]));
               break;
            }
         }
         elseif (!EventPrize::query()->where('user_id', $userID)->where('event_id', $eventID)->whereJsonContains('prize', $item)->first()) {
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

   public function containsWord($words, $word): bool
   {
      return in_array($word, array_map('trim', explode(',', $words)));
   }
}
