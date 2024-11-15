<?php

namespace App\Core\Events;

use App\Core\Message\Message;
use App\Core\Method\SocialMethod;
use App\Models\Event\Event;
use App\Models\Event\EventPrize;
use App\Models\Event\EventSocialLogs;
use App\Models\Event\EventUsers;
use App\Models\User\User;
use App\Models\User\UserLogItems;
use App\Models\Withdrawl\WithdrawItems;
use App\Models\Withdrawl\WithdrawUsers;
use Illuminate\Support\Carbon;

class EventsServices
{
   public function events($post_id, $user_id, $comment_id, $sendMessageUser, SocialMethod $socialMethod): void
   {
      $findEvent = Event::query()->where('post_id', $post_id)->first();
      if ($findEvent) {
         switch ($findEvent->eventType) {
            case 1:
               (new EventOne())->event($post_id, $user_id, $comment_id, $sendMessageUser, $socialMethod, 1);
               break;
            case 2:
               (new EventTwo())->event($post_id, $user_id, $comment_id, $sendMessageUser, $socialMethod, 1);
               break;
            case 3:
               (new EventThree())->event($post_id, $user_id, $comment_id, $sendMessageUser, $socialMethod, 1);
               break;
            case 4:
               (new EventFour())->event($post_id, $user_id, $comment_id, $sendMessageUser, $socialMethod, 1);
               break;
            case 5:
               (new EventFive())->event($post_id, $user_id, $comment_id, $sendMessageUser, $socialMethod, 1);
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

   public function winPrize(int $userID, int $event_id, array $prizes, SocialMethod $socialMethod, string $word, $typeSocial): bool
   {
      $findPrize = EventPrize::query()->where([['event_id', $event_id], ['status', 0], ['word', $word]])->first();

      if ($findPrize) {
         $this->giveItemUser($userID, $event_id, $findPrize->withdraw_items_id, $findPrize->count_prize, 'Победитель мероприятия', $socialMethod, $typeSocial);
         EventPrize::query()->where('id', $findPrize->id)->update(['status' => 1]);
         return true;
      }

      return false;
   }

   public function containsWord($words, $word): bool
   {
      return in_array($word, array_map('trim', explode(',', $words)));
   }

   public function giveItemUser($user_id, int $event_id, int $item_id, int $count, string $actionText, SocialMethod $socialMethod = null, $type = null): void
   {
      WithdrawUsers::query()->create([
         'user_id' => $user_id,
         'withdraw_items_id' => $item_id,
         'count' => $count,
         'status' => 0
      ]);

      if ($type) {
         $user = User::query()->where('id', $user_id)->first();
         $withdraw = WithdrawItems::query()->where('id', $item_id)->first();

         $socialMethod->sendMessage(($type == 1 ? $user->vkontakte_id : $user->telegram_id), Message::getMessage('prize_gift', ['name' => $withdraw->name, 'count' => $count]));
      }

      UserLogItems::query()->create([
         'user_id' => $user_id,
         'event_id' => $event_id,
         'withdraw_items_id' => $item_id,
         'count' => $count,
         'action' => $actionText
      ]);
   }

   public function checkLastMessage(int $post_id, Event $event, int $comment_id, SocialMethod $socialMethod): bool
   {
      $lastMessage = EventSocialLogs::query()->where([['post_id', $post_id]])->orderBy('id', 'desc')->first();

      if ($lastMessage) {
         $lastCollected = Carbon::parse($lastMessage->created_at)->setTimezone('Europe/Moscow');
         $now = Carbon::now('Europe/Moscow');

         if ($lastCollected->diffInSeconds($now) < $event->timeForAttempt) {
            $socialMethod->replyWallComment($post_id, Message::getMessage('event_last_message', ['timeForAttempt' => $event->timeForAttempt]), $comment_id);
            return false;
         }
      }

      return true;
   }

   public function checkMailing(int $user_id, Event $event, int $post_id, int $comment_id, SocialMethod $socialMethod): bool
   {
      if ($event->subscribe and !$socialMethod->checkSubscriptionGroup($user_id)) {
         $socialMethod->replyWallComment($post_id, Message::getMessage('event_subscription', ['type' => 'группу']), $comment_id);
         return false;
      }

      if ($event->subscribe_mailing and !$socialMethod->checkSubscriptionMailing($user_id)) {
         $socialMethod->replyWallComment($post_id, Message::getMessage('event_subscription', ['type' => 'группу']), $comment_id);
         return false;
      }

      return true;
   }

   public function checkAttempt(int $user_id, Event $event, int $post_id, int $comment_id, SocialMethod $socialMethod): bool
   {
      $eventUser = EventUsers::query()->where([['user_id', $user_id], ['event_id', $event->id]])->first();

      if ($eventUser && $eventUser->countAttempt <= 0) {
         $socialMethod->replyWallComment($post_id, Message::getMessage('event_limit_attempt'), $comment_id);
         return false;
      } elseif (!$eventUser) {
         EventUsers::query()->create([
            'user_id' => $user_id,
            'event_id' => $event->id,
            'countAttempt' => $event->countAttempt - 1
         ]);
      } elseif ($eventUser->countAttempt < $event->countAttempt) {
         EventUsers::query()->where('id', $eventUser->id)->update([
            'countAttempt' => $eventUser->countAttempt - 1
         ]);
      }

      return true;
   }
}
