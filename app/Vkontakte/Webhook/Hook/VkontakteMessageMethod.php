<?php

namespace App\Vkontakte\Webhook\Hook;

use App\Vkontakte\Bot\BotCommandMethod;
use Illuminate\Support\Facades\Log;

class VkontakteMessageMethod
{
   public function message(array $data): void
   {

      (new BotCommandMethod($data))->command();
   }
}
