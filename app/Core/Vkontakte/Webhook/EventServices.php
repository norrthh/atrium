<?php

namespace App\Core\Vkontakte\Webhook;

use App\Core\Message\Message;
use App\Core\Method\SocialMethod;
use App\Models\Event;
use App\Models\EventUserLog;
use App\Models\EventUsers;
use App\Models\User;

class EventServices
{
   public function addAttempt(int $user_id, int $post_id, int $typeAction, SocialMethod $socialMethod): void
   {
      $event = Event::query()->where('post_id', $post_id)->first();
      $user = User::query()->where('vkontakte_id', $user_id)->first();

      if ($event) {
         $findAttempt = EventUsers::query()->where([['event_id', $event->id], ['user_id', $user->id]])->first();

         if (!$findAttempt) {
            $findAttempt = EventUsers::query()->create([
               'event_id' => $event->id,
               'user_id' => $user->id,
               'countAttempt' => $event->countAttempt,
            ]);
         }

         switch ($typeAction) {
            case 1:
               if ($event->like) {
                  if (!EventUserLog::query()->where([['event_id', $event->id], ['user_id', $user->id], ['type', 2]])->exists()) {
                     EventUsers::query()->where('id', $findAttempt->id)->update(['countAttempt' => $findAttempt->countAttempt + $event->like['count']]);
                     $this->storeLog($event->id, $user->id, 2);
                     $socialMethod->sendMessage($user_id, Message::getMessage('addAttemptLike', ['count' => $event->like['count']]));
                  }
               }
               break;
            case 2:
               if ($event->repost) {
                  if (!EventUserLog::query()->where([['event_id', $event->id], ['user_id', $user->id], ['type', 2]])->exists()) {
                     EventUsers::query()->where('id', $findAttempt->id)->update(['countAttempt' => $findAttempt->countAttempt + $event->repost['count']]);
                     $this->storeLog($event->id, $user->id, 2);
                     $socialMethod->sendMessage($user_id, Message::getMessage('addAttemptRepost', ['count' => $event->repost['count']]));
                  }
               }
               break;
            default:
               break;
         }
      }
   }

   protected function storeLog(int $event_id, int $user_id, int $typeAction): void
   {
      EventUserLog::query()->create([
         'event_id' => $event_id,
         'user_id' => $user_id,
         'type' => $typeAction,
         'status' => 1
      ]);
   }
}
