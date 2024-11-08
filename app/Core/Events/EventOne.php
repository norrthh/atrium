<?php

namespace App\Core\Events;

use App\Core\Message\Message;
use App\Core\Method\SocialMethod;
use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventSocialLogs;
use App\Models\EventUsers;
use Illuminate\Support\Carbon;

class EventOne extends EventsServices
{
   public function event(array $data, SocialMethod $socialMethod): void
   {
      $findEvent = Event::query()->where('post_id', $data['object']['post_id'])->first();

      if ($findEvent) {
         $eventUser = EventUsers::query()->where([['user_id', $data['object']['from_id']], ['event_id', $findEvent->id]])->first();

         if ($eventUser && $eventUser->countAttempt > $findEvent->countAttempt) {
            $socialMethod->replyWallComment($data['object']['post_id'], Message::getMessage('event_limit_attempt'), $data['object']['id']);
            die();
         } elseif (!$eventUser) {
            EventUsers::query()->create([
               'user_id' => $data['object']['from_id'],
               'event_id' => $findEvent->id,
               'countAttempt' => $findEvent->countAttempt - 1
            ]);
         } elseif ($eventUser->countAttempt < $findEvent->countAttempt) {
            EventUsers::query()->where('id', $eventUser->id)->update([
               'countAttempt' => $eventUser->countAttempt - 1
            ]);
         }

         if (count($findEvent->attempts) > EventPrize::query()->where('event_id', $data['object']['post_id'])->count()) {
            if ($data['object']['text'] == $findEvent->word) {

               $lastMessage = EventSocialLogs::query()->where([['post_id', $data['object']['post_id']]])->orderBy('id', 'desc')->first();

               if ($lastMessage) {
                  $lastCollected = Carbon::parse($lastMessage->created_at)->setTimezone('Europe/Moscow');
                  $now = \Carbon\Carbon::now('Europe/Moscow');

                  if ($lastCollected->diffInSeconds($now) < $findEvent->timeForAttempt) {
                     $socialMethod->replyWallComment($data['object']['post_id'], Message::getMessage('event_last_message', ['timeForAttempt' => $findEvent->timeForAttempt]), $data['object']['id']);
                     die();
                  }
               }

               $this->logUser($data['object']['from_id'], $data['object']['post_id'], $findEvent->id);

               if ($findEvent->subscribe and !$socialMethod->checkSubscriptionGroup($data['object']['from_id'])) {
                  $socialMethod->replyWallComment($data['object']['post_id'], Message::getMessage('event_subscription', ['type' => 'группу']), $data['object']['id']);
                  die();
               }

               if ($findEvent->subscribe_mailing and !$socialMethod->checkSubscriptionMailing($data['object']['from_id'])) {
                  $socialMethod->replyWallComment($data['object']['post_id'], Message::getMessage('event_subscription', ['type' => 'группу']), $data['object']['id']);
                  die();
               }

               if (EventSocialLogs::query()->where([['post_id', $data['object']['post_id']]])->count() >= $findEvent->countMessage + 1) {
                  if ($this->calculatePrize(EventSocialLogs::query()->where('post_id', $data['object']['post_id'])->count(), $findEvent->countAttempt)) {
                     if ($this->winPrize($data['object']['from_id'], $data['object']['post_id'], $findEvent->attempts, $socialMethod)) {
                        $socialMethod->replyWallComment($data['object']['post_id'], Message::getMessage('event_win_prize'), $data['object']['id'], $findEvent->bg['successBackground']);
                     } else {
                        $socialMethod->replyWallComment($data['object']['post_id'], Message::getMessage('event_lose'), $data['object']['id']);
                     }
                  } else {
                     $socialMethod->replyWallComment($data['object']['post_id'], Message::getMessage('event_lose'), $data['object']['id']);
                  }
               } else {
                  $socialMethod->replyWallComment($data['object']['post_id'], Message::getMessage('event_lose'), $data['object']['id']);
               }
            }
         } else {
            $socialMethod->replyWallComment($data['object']['post_id'], Message::getMessage('event_lose'), $data['object']['id']);
            $socialMethod->closeWallComments($data['object']['post_id']);
         }
      }
   }
}
