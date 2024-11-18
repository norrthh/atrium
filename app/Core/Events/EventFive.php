<?php

namespace App\Core\Events;

use App\Core\Message\Message;
use App\Core\Method\SocialMethod;
use App\Models\Event\Event;
use App\Models\Event\EventPromocode;
use App\Models\Event\EventSocialLogs;
use App\Models\User\User;

/*
 *  status -
 *     1 - опред коммент
 *     2 - любой коммент
 *
 * */

class EventFive extends EventsServices
{
   public function event($post_id, $user_id, $comment_id, $sendMessageUser, SocialMethod $socialMethod, $type): void
   {
//      $findEvent = Event::query()->where('post_id', $post_id)->first();
//
//      if ($findEvent) {
//         if ($this->checkLastMessage($post_id, $findEvent, $comment_id, $socialMethod)) {
//            if ($this->checkMailing($user_id, $findEvent, $post_id, $comment_id, $socialMethod)) {
//               if ($this->checkAttempt($user_id, $findEvent, $post_id, $comment_id, $socialMethod)) {
//                  switch ($findEvent->status) {
//                     case 2:
//                        if (EventSocialLogs::query()->where([['post_id', $post_id]])->count() >= $findEvent->countMessage + 1) {
//                           $getItem = EventPromocode::query()->where([['event_id', $findEvent->id]])->whereColumn('count_prize', '>', 'count_used')->first();
//                           if ($getItem) {
//                              $user = User::query()->where($type == 1 ? [['vkontakte_id', $user_id]] : [['telegram_id', $user_id]])->first();
//
//                              $this->giveItemUser($user->id, $findEvent->id, $getItem->prize_id, $getItem->count, 'Получил приз написал любое сообщение под постом', $socialMethod, $type);
//
//                              EventPromocode::query()->where('id', $getItem->id)->update([
//                                 'count_used' => $getItem->count_used + 1
//                              ]);
//
//                              $socialMethod->replyWallComment($post_id, Message::getMessage('event_win_prize'), $comment_id, $findEvent->bg['successBackground'] ?? null);
//                           } else {
//                              $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
//                           }
//                        } else {
//                           $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
//                        }
//                        break;
//                     case 1:
//                        $getItem = EventPromocode::query()->where([['event_id', $findEvent->id], ['code', $sendMessageUser], ['count_prize', '>', 'count_used']])->first();
//
//                        if ($getItem) {
//                           $user = User::query()->where($type == 1 ? [['vkontakte_id', $user_id]] : [['telegram_id', $user_id]])->first();
//
//                           $this->giveItemUser($user->id, $findEvent->id, $getItem->prize_id, $getItem->count, 'Получил приз написал любое сообщение под постом', $socialMethod, $type);
//
//                           EventPromocode::query()->where('id', $getItem->id)->update([
//                              'count_used' => $getItem->count_used + 1
//                           ]);
//
//                           $socialMethod->replyWallComment($post_id, Message::getMessage('event_win_prize'), $comment_id, $findEvent->bg['successBackground'] ?? null);
//                        } else {
//                           $socialMethod->replyWallComment($post_id, Message::getMessage('event_lose'), $comment_id);
////                  $socialMethod->closeWallComments($post_id);
//                        }
//                        break;
//                     default:
//                        break;
//                  }
//               }
//            }
//         }
//      }
   }
}
