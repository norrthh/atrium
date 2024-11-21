<?php

namespace App\Telegraph;

use App\Models\Event\Event;
use App\Services\Telegram\TelegramMethodServices;
use App\Telegraph\Event\TelegraphKorobkaHandler;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Stringable;

class TelegraphHandler extends WebhookHandler
{
   public function start()
   {
      $subscription = (new TelegramMethodServices())->getChatMember($this->message->from()->id());
      if ($subscription && isset($subscription['result']) && $subscription['result']['status'] != 'left' and $this->message->from()->username() != '') {
         $this->chat->message('test')->keyboard(
            Keyboard::make()->button('Перейти в мини приложение')->webApp('https://telegram.atrium-bot.ru/')
         )->send();
      } else {
         if ($this->message->from()->username() == '') {
            $this->chat->message('У вас должен быть установлен username в настройках, чтобы запустить приложение')->send();
         } else {
            $this->chat->message('Вы должны подписаться на телеграмм канал @asdasdsadsdaas, чтобы продолжить дальше')->send();
         }
      }
   }

   public function handleChatMessage(Stringable $text): void
   {

   }
}
