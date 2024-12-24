<?php

namespace App\Telegraph\Chat\User;

use App\Core\EventMethod\EventTelegramMethod;
use App\Core\EventMethod\EventVkontakteMethod;
use App\Models\ChatLink;
use App\Models\ChatQuestion;
use App\Models\Chats;
use App\Models\ChatWords;
use App\Models\User\User;
use App\Models\UserMute;
use App\Models\UserRole;
use App\Telegraph\Method\UserMessageTelegramMethod;
use App\Telegraph\Method\UserTelegramMethod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserChatCommandServices
{
   /**
    * @throws \Exception
    */
   public function filter(string $text, string $chat_id, $message_id, $user_id): void
   {
      if ($this->checkMute($user_id)) {
         $analyzeText = $this->analyzeText($text);
         if (isset($analyzeText['status']) && $analyzeText['status'] and !UserRole::query()->where('telegram_id', $user_id)->exists()) {
            if (isset($analyzeText['type']) && $analyzeText['type'] == 'links' or isset($analyzeText['type']) && $analyzeText['type'] == 'words') {
               $user = User::query()->where('telegram_id', $user_id)->first();
               if ($user) {
                  if ($user->vkontakte_id) {
                     foreach (Chats::query()->get() as $link) {
                        if ($link->messanger == 'vkontakte') {
                           (new \App\Vkontakte\Method\User(chat_id: $link->chat_id))->kickUserFromChat($user->vkontakte_id);
                        } else {
                           (new UserTelegramMethod())->kickUserFromChat($link->chat_id, $user_id);
                        }
                     }
                  } else {
                     foreach (Chats::query()->where('messanger', 'telegram')->get() as $link) {
                        (new UserTelegramMethod())->kickUserFromChat($link->chat_id, $user_id);
                     }
                  }

                  (new UserMessageTelegramMethod())->deleteMessage($chat_id, $message_id);
               } else {
                  foreach (Chats::query()->where('messanger', 'telegram')->get() as $link) {
                     (new UserTelegramMethod())->kickUserFromChat($link->chat_id, $user_id);
                  }
               }
            }
            if (isset($analyzeText['answer'])) {
               (new UserMessageTelegramMethod())->replyWallComment($chat_id, $analyzeText['answer'], $message_id);
            }
         }
      } else {
         (new UserMessageTelegramMethod())->deleteMessage($chat_id, $message_id);
      }
   }
}
