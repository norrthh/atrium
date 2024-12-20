<?php

namespace App\Telegraph\Chat\Telegram;

use App\Telegraph\Chat\Telegram\Admin\AdminChatCommandServices;
use App\Telegraph\Chat\Telegram\User\UserChatCommandServices;

class TelegramChatCommandServices
{
   protected array $commandList = ['/addmoder', '/addadmin', '/warn', '/mute', '/kick', '/akick', '/addinfo', '/newm'];

   /**
    * @throws \Exception
    */
   public function commands(string $text, string $chat_id, $message_id): void
   {
      if (in_array($text, $this->commandList)) {
         (new AdminChatCommandServices())->command($text, $social, $chat_id, $message_id);
      } else {
         (new UserChatCommandServices())->command($text, $social, $chat_id, $message_id);
      }
   }

   public function checkCommand(string $text): bool
   {
      return in_array($text, $this->commandList);
   }
}
