<?php

namespace App\Vkontakte;

use App\Models\User\User;
use App\Vkontakte\Bot\BotCommandMethod;
use Illuminate\Support\Facades\Log;

class UserCommandVkontakte extends BotCommandMethod
{
   protected array $userCommand = ['tickets'];
   public function filter(string $command): void
   {
      Log::info($command);
      if (in_array($command, $this->userCommand)) {
         switch ($command) {
            case 'tickets':
               Log::info($this->user);
               $user = User::query()->where('vkontakte_id', $this->user)->first();
               $message = '';

               if (!$user) {
                  $message = 'У вас не зарегестрирован аккаунт в приложение';
               } else {
                  $message  = "Количество ваших билетов на аккаунте " . $user->bilet . "шт";
               }

               Log::info($message);

               $this->message->sendAPIMessage(
                  userId: $this->user_id,
                  message: $message,
                  conversation_message_id: $this->conversation_message_id
               );
               break;
            default:
               Log::info('not found command');
               break;
         }
      }
   }
}
