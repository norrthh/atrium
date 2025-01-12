<?php

namespace App\Vkontakte\Bot;

use App\Core\Bot\BotCore;
use App\Core\Message\AdminCommands;
use App\Http\Controllers\Api\v1\Admin\TaskController;
use App\Models\Chat\ChatQuestion;
use App\Models\Chat\Chats;
use App\Vkontakte\Admin\AdminMethod;
use App\Vkontakte\Method\Keyboard;
use App\Vkontakte\Method\Message;
use App\Vkontakte\Method\User;
use App\Vkontakte\UserCommandVkontakte;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BotCommandMethod
{
   protected Message $message;
   protected Keyboard $keyboard;
   protected User $userMethod;
   protected array $vkData;
   protected int $user = 0;
   protected int $user_id = 0;
   protected string $messageText = '';
   protected int $conversation_message_id = 0;
   protected array $messageData = [];

   public function __construct(array $data)
   {
      $this->message = new Message();
      $this->keyboard = new Keyboard();
      $this->vkData = $data;

      if (isset($data['object']['message'])) {
         $message = $data['object']['message'];
         $this->messageText = $message['text'] ?? '';
         $this->user_id = $message['peer_id'];
         $this->user = $message['from_id'];
         $this->conversation_message_id = $message['conversation_message_id'] ?? 0;
         $this->messageData = $message;
      }

      $this->userMethod = new User(user_id: $this->user, chat_id: $this->user_id);
   }

   public function command(): void
   {
//      Log::info('Processing command...');

      if (!$this->isChatRegistered()) {
         $this->filterMessageText();
         return;
      }

      $cache = Cache::get('admin_' . $this->user);
      if (!$cache) {
         if ($this->isCommand()) {
//            Log::info('isCommand is running...');
            $this->processCommand();
         } else {
//            Log::info('isCommand is not running...');
            $this->processMessage();
         }
      } else {
         ChatQuestion::query()->create([
            'question' => $cache['question'],
            'answer' => $this->messageText
         ]);

         Cache::delete('admin_' . $this->user);

         $this->message->sendAPIMessage(
            userId: $this->user_id,
            message: 'Успешно создан вопрос-ответ',
            conversation_message_id: $this->conversation_message_id
         );
      }
   }

   protected function isChatRegistered(): bool
   {
      return Chats::query()
         ->where('messanger', 'vkontakte')
         ->where('chat_id', $this->user_id)
         ->exists();
   }

   protected function isCommand(): bool
   {
      return (new AdminCommands())->checkCommandVK($this->messageText)['command'] ?? false;
   }

   protected function processCommand(): void
   {
      $adminCommands = new AdminCommands();
      $commandData = $adminCommands->checkCommandVK($this->messageText);

      if (isset($commandData['command'])) {
         $command = '/' . $commandData['command'];
         if (in_array($command, $adminCommands->commandList)) {
            (new AdminMethod($this->vkData))->method();
         } else {
            (new UserCommandVkontakte($this->vkData))->filter($commandData['command']);
         }
      }
   }

   protected function processMessage(): void
   {
      $botCore = new BotCore();
      $botCore->filterMessage(
         $this->messageText,
         $this->user_id,
         $this->conversation_message_id,
         $this->user,
         'vkontakte_id',
         sticker: $this->isSticker(),
         forwardMessage: $this->shouldForwardMessage()
      );
   }

   protected function filterMessageText(): void
   {
      $mainCommands = [
         '/start', 'Начать', 'меню', 'Меню', 'начать', 'старт'
      ];

      $supportCommands = [
         'ПОМОЩЬ ПО ПРИЛОЖЕНИЮ' => 'support',
         '🙄 Как получать монетки и билеты?' => 'howGet',
         '👾 Не могу привязать аккаунт TG или VK' => 'connectSocial',
         'Привязываю в VK' => 'connectVK',
         'Привязываю в Telegram' => 'connectTG',
         '🌟 Как попасть в ТОП игроков?' => 'sendTopPlayersInfo',
         'Аукционы и магазин пустые' => 'explainAuctionsAndShop',
      ];

      $prizeCommands = [
         'Получать новости и подарки 💓' => 'sendThankYouMessage',
         '1000 монет' => 'sendBonusInfo',
         'BMW M5 F90 АСХАБА' => 'sendBonusInfo',
         'free' => 'sendCarChoiceMessage',
      ];

      if (in_array($this->messageText, $mainCommands)) {
         (new BotCommandMainMethod($this->vkData))->start();
      } elseif (isset($supportCommands[$this->messageText])) {
         (new BotCommandSupportMethod($this->vkData))->{$supportCommands[$this->messageText]}();
      } elseif (isset($prizeCommands[$this->messageText])) {
         (new BotCommandPrizeMethod($this->vkData))->{$prizeCommands[$this->messageText]}();
      } else {
         $this->notFoundCommand();
      }
   }

   protected function notFoundCommand(): void
   {
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: 'Такой кнопки не существует. Перенаправляю в меню... 😃',
      );
      (new BotCommandMainMethod($this->vkData))->start();
   }

   protected function shouldForwardMessage(): bool
   {
      if (isset($this->messageData['attachments'])) {
         foreach ($this->messageData['attachments'] as $attachment) {
            if ($attachment['type'] === 'wall') {
               return true;
            }
         }
      }

      if (isset($this->messageData['fwd_messages'])) {
         $taskController = new TaskController();
         foreach (Chats::query()->where('messanger', 'vkontakte')->get() as $chat) {
            if (isset($this->messageData['fwd_messages'][0])) {
               if ($taskController->checkUserInChat($chat->chat_id, $this->messageData['fwd_messages'][0]['from_id'])) {
                  return false;
               }
            } else {
               return false;
            }
         }
         return true;
      }

      return false;
   }

   protected function isSticker(): bool
   {
      return isset($this->messageData['attachments'][0]['type']) &&
         $this->messageData['attachments'][0]['type'] === 'sticker';
   }
}
