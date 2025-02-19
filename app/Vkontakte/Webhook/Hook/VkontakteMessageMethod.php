<?php

namespace App\Vkontakte\Webhook\Hook;

use App\Vkontakte\Bot\BotCommandMethod;
use Illuminate\Support\Facades\Log;

class VkontakteMessageMethod
{
   public function message(array $data): void
   {
      if(isset($data['object']['message']['action']) and $data['object']['message']['action']['type'] == 'chat_invite_user') {
         (new VkontakteChatInviteMethod())->chatInvite($data);
      } else {
         (new BotCommandMethod($data))->command();
      }
   }
}
