<?php

namespace App\Core\Events;

use App\Core\Message\Message;
use App\Core\Method\SocialMethod;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventSocialLogs;
use App\Models\EventUsers;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EventThree extends EventsServices
{
   public function event($post_id, $user_id, $comment_id, $sendMessageUser, SocialMethod $socialMethod): void
   {
      $findEvent = Event::query()->where('post_id', $post_id)->first();
//      Log::info(print_r($findEvent, 1));
      if ($findEvent) {
         $lastMessage = EventSocialLogs::query()->where([['post_id', $post_id]])->orderBy('id', 'desc')->first();

         if ($lastMessage) {
            $lastCollected = Carbon::parse($lastMessage->created_at)->setTimezone('Europe/Moscow');
            $now = Carbon::now('Europe/Moscow');

            if ($lastCollected->diffInSeconds($now) < $findEvent->timeForAttempt) {
               $socialMethod->replyWallComment($post_id, Message::getMessage('event_last_message', ['timeForAttempt' => $findEvent->timeForAttempt]), $comment_id);
               return;
            }
         }

         $eventUser = EventUsers::query()->where([['user_id', $user_id], ['event_id', $findEvent->id]])->first();

         if ($eventUser && $eventUser->countAttempt <= 0) {
            $socialMethod->replyWallComment($post_id, Message::getMessage('event_limit_attempt'), $comment_id);
            return;
         } elseif (!$eventUser) {
            EventUsers::query()->create([
               'user_id' => $user_id,
               'event_id' => $findEvent->id,
               'countAttempt' => $findEvent->countAttempt - 1
            ]);
         } elseif ($eventUser->countAttempt < $findEvent->countAttempt) {
            EventUsers::query()->where('id', $eventUser->id)->update([
               'countAttempt' => $eventUser->countAttempt - 1
            ]);
         }

         $this->logUser($user_id, $post_id, $findEvent->id);

         if ($findEvent->subscribe and !$socialMethod->checkSubscriptionGroup($user_id)) {
            $socialMethod->replyWallComment($post_id, Message::getMessage('event_subscription', ['type' => 'группу']), $comment_id);
            return;
         }

         if ($findEvent->subscribe_mailing and !$socialMethod->checkSubscriptionMailing($user_id)) {
            $socialMethod->replyWallComment($post_id, Message::getMessage('event_subscription', ['type' => 'группу']), $comment_id);
            return;
         }

         if (count($findEvent->attempts) > EventPrize::query()->where('event_id', $post_id)->count()) {
            if ($this->containsWord($findEvent->word, $sendMessageUser)) {
               if ($this->winPrize($user_id, $post_id, $findEvent->attempts, $socialMethod, $sendMessageUser)) {
                  $socialMethod->replyWallComment($post_id, Message::getMessage('event_win_prize'), $comment_id, $findEvent->bg['successBackground']);
               } else {
                  $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
               }
            } else {
               $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
            }
         } else {
            $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
            $socialMethod->closeWallComments($post_id);
         }
      }
   }
}
