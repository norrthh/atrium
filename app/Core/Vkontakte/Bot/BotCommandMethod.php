<?php

namespace App\Core\Vkontakte\Bot;

use App\Core\Vkontakte\Method\Keyboard;
use App\Core\Vkontakte\Method\Message;
use Illuminate\Support\Facades\Log;

class BotCommandMethod
{
   protected Message $message;
   protected Keyboard $keyboard;
   protected array $vkData;
   protected int $user_id;

   public function __construct(array $data)
   {
      $this->message = new Message();
      $this->keyboard = new Keyboard();
      $this->vkData = $data;
      $this->user_id = $data['object']['message']['from_id'];
   }

   public function command(): void
   {
      switch ($this->vkData['object']['message']['text']) {
         case '/start':

            $keyboard = $this->keyboard->regularKeyboard(
               $this->keyboard->openLink('test', 'https://vk.com/'),
            );

            $response2 = $this->message->sendAPIMessage(
               userId: $this->user_id,
               message: 'hello',
               keyboard: $keyboard
            );

            Log::info($response2);
            break;
         default:
            $this->message->sendAPIMessage($this->user_id, 'unknown command');
            break;
      }
   }
}
