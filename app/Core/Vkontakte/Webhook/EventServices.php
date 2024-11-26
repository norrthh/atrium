<?php

namespace App\Core\Vkontakte\Webhook;

use App\Core\Message\Message;
use App\Core\EventMethod\EventSocialMethod;
use App\Models\Event\Event;
use App\Models\Event\EventUsers;
use App\Models\User\User;

class EventServices
{
   public function addAttempt(int $user_id, int $post_id, int $typeAction, EventSocialMethod $socialMethod): void
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
                  EventUsers::query()->where('id', $findAttempt->id)->update(['countAttempt' => $findAttempt->countAttempt + $event->like['count']]);
//                     $this->storeLog($event->id, $user->id, 2);
                  $socialMethod->sendMessage($user_id, Message::getMessage('addAttemptLike', ['count' => $event->like['count']]));
               }
               break;
            case 2:
               if ($event->repost) {
                  EventUsers::query()->where('id', $findAttempt->id)->update(['countAttempt' => $findAttempt->countAttempt + $event->repost['count']]);
//                     $this->storeLog($event->id, $user->id, 2);
                  $socialMethod->sendMessage($user_id, Message::getMessage('addAttemptRepost', ['count' => $event->repost['count']]));
               }
               break;
            default:
               break;
         }
      }
   }
}
