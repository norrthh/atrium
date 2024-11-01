<?php

namespace App\Vkontakte\Events;

use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventVkontakteLog;

class VkontakteEventKorobkaServices extends VkontakteEventsServices
{
   public function korobka(array $data): void
   {
      $findEvent = Event::query()->where('post_id', $data['object']['post_id'])->with('korobka')->first();

      if ($findEvent) {
         if (count($findEvent->korobka->attempts) > EventPrize::query()->where('event_id', $data['object']['post_id'])->count()) {
            if ($data['object']['text'] == $findEvent->korobka->word) {
               $this->logUser($data['object']['from_id'], $data['object']['post_id'], $findEvent->id);

               if ($findEvent->korobka->subscribe and !$this->checkSubscriptionGroup($data['object']['from_id'])) {
                  $this->replyToComment($data['object']['post_id'], 'Для участния необходимо подписаться на группу', $data['object']['id']);
                  die();
               }

               if ($findEvent->korobka->subscribe_mailing and !$this->checkSubscriptionMailing($data['object']['from_id'])) {
                  $this->replyToComment($data['object']['post_id'], 'Для участния необходимо подписаться на группу', $data['object']['id']);
                  die();
               }

               if (EventVkontakteLog::query()->where([['post_id', $data['object']['post_id']]])->count() >= $findEvent->korobka->countMessage + 1) {
                  if ($this->calculatePrize(EventVkontakteLog::query()->where('post_id', $data['object']['post_id'])->count(), $findEvent->korobka->countAttempt)) {
                     if ($this->winPrize($data['object']['from_id'], $data['object']['post_id'], $findEvent->korobka->attempts)) {
                        $this->replyToComment($data['object']['post_id'], 'Вы выиграли ' . $findEvent->korobka->text, $data['object']['id'], $findEvent->korobka->bg['successBackground']);
                     } else {
                        $this->replyToComment($data['object']['post_id'], 'Вы не выйграли ничего', $data['object']['id']);
                     }
                  } else {
                     $this->replyToComment($data['object']['post_id'], 'Вы не выйграли ничего', $data['object']['id']);
                  }
               } else {
                  $this->replyToComment($data['object']['post_id'], 'Вы не выйграли ничего', $data['object']['id']);
               }
            }
         } else {
            $this->replyToComment($data['object']['post_id'], 'Вы не выйграли ничего', $data['object']['id']);
            $this->closeComments($data['object']['post_id']);
         }
      }
   }
}
