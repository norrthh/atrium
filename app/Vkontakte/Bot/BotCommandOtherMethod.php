<?php

namespace App\Vkontakte\Bot;

use Illuminate\Support\Facades\Log;

class BotCommandOtherMethod extends BotCommandMethod
{
   protected array $targetPromo = ["free", "freecar"];

   public function other(): void
   {
      if (isset($this->vkData['object']['message']['payload'])) {
         $payload = json_decode($this->vkData['object']['message']['payload'], true);

         if(isset($payload['name'])) {
            $this->message->sendAPIMessage(
               userId: $this->user_id,
               message: 'Такой кнопки не существует. Перенаправляю в меню... 😃',
            );

            (new BotCommandMainMethod($this->vkData))->start();
         } else {
            if ($payload) {
               switch ($payload[0]) {
                  case 'main':
                     (new BotCommandMainMethod($this->vkData))->start();
                     break;
                  case 'support':
                     (new BotCommandSupportMethod($this->vkData))->support();
                     break;
                  default:
                     $this->message->sendAPIMessage(
                        userId: $this->user_id,
                        message: 'Такой кнопки не существует. Перенаправляю в меню... 😃',
                     );

                     (new BotCommandMainMethod($this->vkData))->start();
               }
            }
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
