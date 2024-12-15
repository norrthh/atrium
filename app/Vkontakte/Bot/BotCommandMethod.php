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
   protected int $user_id = 0;

   public function __construct(array $data)
   {
      $this->message = new Message();
      $this->keyboard = new Keyboard();
      $this->vkData = $data;

      if (isset($data['object']['message'])) {
         $this->user_id = $data['object']['message']['from_id'];
      }
   }

   public function command(): void
   {
      $messageText = $this->vkData['object']['message']['text'];
      if (
         isset($messageText) and $this->user_id > 0
         and $this->vkData['object']['message']['peer_id'] != env('VKONTAKTE_CHAT_ID')
         and $this->vkData['object']['message']['peer_id'] != env('VKONTAKTE_CHAT_ID2')
      ) {
         switch ($messageText) {
            case in_array($messageText, ['/start', 'Начать', 'меню', 'Меню', 'начать', 'старт']):
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
            case in_array($messageText, ['1000 монет', 'BMW M5 F90 АСХАБА', 'MERCEDES GTS ВЕНГАЛБИ', 'BMW M4 ЛИТВИНА']):
               (new BotCommandPrizeMethod($this->vkData))->sendBonusInfo();
               break;
            case in_array($messageText, ["free", "freecar"]):
               (new BotCommandPrizeMethod($this->vkData))->sendCarChoiceMessage();
               break;
            case 'Актуальные вакансии':
               (new BotCommandVacancyMethod($this->vkData))->sendVacancyInfo();
               break;
            default:
               (new BotCommandOtherMethod($this->vkData))->other();
               break;
         }
      } else {
         if ($this->vkData['object']['message']['peer_id'] != env('VKONTAKTE_CHAT_ID') and $this->vkData['object']['message']['peer_id'] != env('VKONTAKTE_CHAT_ID2')) {
            $this->message->sendAPIMessage(
               userId: $this->user_id,
               message: 'Такой кнопки не существует. Перенаправляю в меню... 😃',
            );

            (new BotCommandMainMethod($this->vkData))->start();
         }

         if ($messageText == 'peer') {
            $this->message->sendAPIMessage(
               userId: $this->user_id,
               message: 'peer_id: ' . $this->vkData['object']['message']['peer_id']
            );
         }
      }
   }
}
