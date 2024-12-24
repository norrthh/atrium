<?php

namespace App\Vkontakte\Bot;

use App\Core\Bot\BotCore;
use App\Core\EventMethod\EventVkontakteMethod;
use App\Core\Message\AdminCommands;
use App\Models\Chats;
use App\Models\ChatSetting;
use App\Vkontakte\Admin\AdminMethod;
use App\Vkontakte\Method\Keyboard;
use App\Vkontakte\Method\Message;
use App\Vkontakte\Method\User;
use Illuminate\Support\Facades\Log;

class BotCommandMethod
{
   protected Message $message;
   protected Keyboard $keyboard;
   protected User $userMethod;
   protected array $vkData;
   protected int $user = 0;
   protected int $user_id = 0;
   protected string $messageText;
   protected int $conversation_message_id;

   public function __construct(array $data)
   {
      $this->message = new Message();
      $this->keyboard = new Keyboard();

      $this->vkData = $data;

      Log::info('vk request data ' . isset($data['object']['message']) and isset($data['object']['message']['text']));

      if (isset($data['object']['message']) and isset($data['object']['message']['text'])) {
         $this->messageText = $data['object']['message']['text'];
      }

      $this->user_id = $data['object']['message']['peer_id'];
      $this->user = $data['object']['message']['from_id'];
      $this->conversation_message_id = $data['object']['message']['conversation_message_id'];

      $this->userMethod = new User(user_id: $this->user, chat_id: $this->user_id);
   }

   public function command(): void
   {
      if (isset($this->messageText) and $this->user_id > 0 && !Chats::query()->where([['messanger', 'vkontakte'], ['chat_id', $this->user_id]])->exists()) {
         switch ($this->messageText) {
            case in_array($this->messageText, ['/start', 'Начать', 'меню', 'Меню', 'начать', 'старт']):
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
            case in_array($this->messageText, ['1000 монет', 'BMW M5 F90 АСХАБА', 'MERCEDES GTS ВЕНГАЛБИ', 'BMW M4 ЛИТВИНА']):
               (new BotCommandPrizeMethod($this->vkData))->sendBonusInfo();
               break;
            case in_array($this->messageText, ["free", "freecar"]):
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
         if (!Chats::query()->where([['messanger', 'vkontakte'], ['chat_id', $this->user_id]])->exists()) {
            $this->message->sendAPIMessage(
               userId: $this->user_id,
               message: 'Такой кнопки не существует. Перенаправляю в меню... 😃',
            );
            (new BotCommandMainMethod($this->vkData))->start();
         } elseif (Chats::query()->where([['messanger', 'vkontakte'], ['chat_id', $this->user_id]])->exists()) {
            if (isset($this->vkData['object']['message']['action'])) {
               $actionMessage = $this->vkData['object']['message']['action'];
               if ($actionMessage['type'] == 'chat_invite_user' or $actionMessage['type'] == 'chat_invite_user_by_link' and $actionMessage['member_id']) {
                  $this->message->sendAPIMessage(
                     userId: $this->user_id,
                     message: ChatSetting::query()->first()->welcome_message,
                  );
               }
            } elseif ((new AdminCommands())->checkCommandVK($this->messageText)) {
               (new AdminMethod($this->vkData))->method();
            } else {
               (new BotCore())->filterMessage($this->messageText, $this->user_id, $this->conversation_message_id, $this->user, 'vkontakte_id');
            }
         }
      }
   }
}
