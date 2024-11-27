<?php

namespace App\Vkontakte\Bot;

use App\Vkontakte\Method\Keyboard;
use App\Vkontakte\Method\Message;
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
         case 'Начать':
         case 'меню':
         case 'Меню':
         case 'начать':
         case 'старт':
            (new BotCommandMainMethod($this->vkData))->start();
            break;
         case 'СКАЧАТЬ ИГРУ':
            (new BotCommandMainMethod($this->vkData))->download();;
            break;
         case 'ПОМОЩЬ ПО ПРИЛОЖЕНИЮ';
            (new BotCommandSupportMethod($this->vkData))->support();
            break;
         case '🙄 Как получать монетки и билеты?':
            (new BotCommandSupportMethod($this->vkData))->howGet();
            break;
         case '👾 Не могу привязать аккаунт TG или VK':
            (new BotCommandSupportMethod($this->vkData))->connectSocial();
            break;
         case 'Привязываю в VK':
            (new BotCommandSupportMethod($this->vkData))->connectVK();
            break;
         case 'Привязываю в Telegram':
            (new BotCommandSupportMethod($this->vkData))->connectTG();
            break;
         case '🌟 Как попасть в ТОП игроков?':
            (new BotCommandSupportMethod($this->vkData))->sendTopPlayersInfo();
            break;
         case 'Аукционы и магазин пустые':
            (new BotCommandSupportMethod($this->vkData))->explainAuctionsAndShop();
            break;
         case 'Получать новости и подарки 💓':
            (new BotCommandPrizeMethod($this->vkData))->sendThankYouMessage();
            break;
         case '1000 монет':
         case 'BMW M5 F90 АСХАБА':
         case 'MERCEDES GTS ВЕНГАЛБИ':
         case 'BMW M4 ЛИТВИНА':
            (new BotCommandPrizeMethod($this->vkData))->sendBonusInfo();
            break;
         case 'free':
            (new BotCommandPrizeMethod($this->vkData))->sendCarChoiceMessage();
            break;
         case 'Актуальные вакансии':
            (new BotCommandVacancyMethod($this->vkData))->sendVacancyInfo();
            break;
         default:
            (new BotCommandOtherMethod($this->vkData))->other();
            break;
      }
   }
}
