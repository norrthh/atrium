<?php

namespace App\Core\Events;

use App\Core\Message\Message;
use App\Core\Method\SocialMethod;
use App\Models\Event\Event;
use App\Models\Event\EventPrize;
use App\Models\Event\EventSocialLogs;
use App\Models\User\User;

class EventFour extends EventsServices
{
   public function event($post_id, $user_id, $comment_id, $sendMessageUser, SocialMethod $socialMethod, $typeSocial): void
   {
      $findEvent = Event::query()->where('post_id', $post_id)->first();

      if ($findEvent) {
         if (EventPrize::query()->where([['event_id', $findEvent->id], ['status', 0]])->exists()) {
            if ($sendMessageUser == $findEvent->word) {
               if ($this->checkLastMessage($post_id, $findEvent, $comment_id, $socialMethod)) {
                  if ($this->checkMailing($user_id, $findEvent, $post_id, $comment_id, $socialMethod)) {
                     if ($this->checkAttempt($user_id, $findEvent, $post_id, $comment_id, $socialMethod)) {
                        $this->logUser($user_id, $post_id, $findEvent->id);
                        if (EventSocialLogs::query()->where([['post_id', $post_id]])->count() >= $findEvent->countMessage + 1) {
                           if ($this->containsWord($findEvent->word, $sendMessageUser)) {
                              if ($this->winPrize(User::query()->where($typeSocial == 1 ? [['vkontakte_id', $user_id]] : [['telegram_id', $user_id]])->first()->id, $findEvent->id, $findEvent->attempts, $socialMethod, $sendMessageUser, $typeSocial)) {
                                 $socialMethod->replyWallComment($post_id, Message::getMessage('event_win_prize'), $comment_id, $findEvent->bg['successBackground']);
                              } else {
                                 $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
                              }
                           } else {
                              $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
                           }
                        } else {
                           $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
                        }
                     }
                  }
               }
            }
         } else {
            $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
            $socialMethod->closeWallComments($post_id);
         }
      }
   }
}
