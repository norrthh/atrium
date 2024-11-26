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

            $keyboard = $this->keyboard->createInlineKeyboard([
               ['button1', ['test' => 'test1']]
            ]);

            $keyboard2 = $this->keyboard->createRegularKeyboard([
               ['button2', ['test' => 'test1']]
            ]);

            $response2 = $this->message->sendAPIMessage(
               userId: $this->user_id,
               message: 'hello',
               keyboard: $keyboard
            );

            $response = $this->message->sendAPIMessage(
               userId: $this->user_id,
               keyboard: $keyboard2
            );

            Log::info($response);
            Log::info($response2);
            break;
         default:
            $this->message->sendAPIMessage($this->user_id, 'unknown command');
            break;
      }
   }
}
