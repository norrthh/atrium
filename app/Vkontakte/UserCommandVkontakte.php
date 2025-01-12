<?php

namespace App\Vkontakte;

use App\Core\Bot\BotCore;
use App\Models\User\User;
use App\Vkontakte\Bot\BotCommandMethod;
use Illuminate\Support\Facades\Log;

class UserCommandVkontakte extends BotCommandMethod
{
   protected array $userCommand = ['tickets'];
   public function filter(string $command): void
   {
      if (in_array($command, $this->userCommand)) {
         switch ($command) {
            case 'tickets':
               $user = User::query()->where('vkontakte_id', $this->user)->first();
               $message = '';

               if (!$user) {
                  $message = 'У вас не зарегестрирован аккаунт в приложение';
               } else {
                  $message  = "Количество ваших билетов на аккаунте " . $user->bilet . "шт";
               }

               $this->message->sendAPIMessage(
                  userId: $this->user_id,
                  message: $message,
                  conversation_message_id: $this->conversation_message_id
               );
               break;
            default:
//               Log::info('not found command');
               break;
         }
      } else {
         (new BotCore())->filterMessage(
            $this->messageText,
            $this->user_id,
            $this->conversation_message_id,
            $this->user,
            'vkontakte_id',
            $this->messageData['attachments'] && $this->messageData['attachments'][0]['type'] == 'sticker'
         );
      }
   }
}
