<?php

namespace App\Telegraph\Chat;

use App\Core\Bot\BotCore;
use App\Core\Message\AdminCommands;
use App\Models\User\UserRole;
use App\Telegraph\Chat\Admin\AdminChatCommandServices;
use App\Telegraph\Chat\User\UserChatCommandServices;
use Illuminate\Support\Facades\Log;

class TelegramChatCommandServices
{

   /**
    * @throws \Exception
    */
   public function commands(string $text, string $chat_id, int $message_id, int $user_id, ?bool $sticker = false, ?bool $forwardMessage = false): void
   {
      if ((new AdminCommands())->checkCommand($text) and UserRole::query()->where('telegram_id', $user_id)->exists()) {
         (new AdminChatCommandServices())->command($text, $chat_id, $message_id, $user_id);
      } else {
         (new BotCore())->filterMessage($text, $chat_id, $message_id, $user_id, 'telegram_id', $sticker, $forwardMessage);
      }
   }
}
