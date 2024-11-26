<?php

namespace App\Core\Vkontakte\Webhook\Action;

use App\Core\Vkontakte\Bot\BotCommandMethod;

class VkontakteMessageMethod
{
   public function message(array $data): void
   {
      (new BotCommandMethod($data))->command();
   }
}
