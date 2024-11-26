<?php

namespace App\Vkontakte\Webhook\Hook;

use App\Vkontakte\Bot\BotCommandMethod;

class VkontakteMessageMethod
{
   public function message(array $data): void
   {
      (new BotCommandMethod($data))->command();
   }
}
