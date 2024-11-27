<?php

namespace App\Vkontakte\Bot;

class BotCommandOtherMethod extends BotCommandMethod
{
   public function other(): void
   {
      if (isset($this->vkData['object']['message']['payload'])) {
         $payload = json_decode($this->vkData['object']['message']['payload'])[0];

         switch ($payload) {
            case 'main':
               (new BotCommandMainMethod($this->vkData))->start();
               break;
            case 'support':
               (new BotCommandSupportMethod($this->vkData))->support();
               break;
         }
      } else {
         $this->message->sendAPIMessage(
            userId: $this->user_id,
            message: 'Такой команды не существует. Перенаправляю в меню... 😃',
         );

         (new BotCommandMainMethod($this->vkData))->start();
      }
   }
}
