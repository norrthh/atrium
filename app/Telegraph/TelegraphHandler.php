<?php

namespace App\Telegraph;

use App\Core\EventMethod\EventTelegramMethod;
use App\Core\Message\AdminCommands;
use App\Facades\WithdrawUser;
use App\Models\ChatSetting;
use App\Models\Task\TaskItems;
use App\Models\Task\Tasks;
use App\Models\User\User;
use App\Models\User\UserTask;
use App\Telegraph\Chat\TelegramChatCommandServices;
use App\Telegraph\Message\TelegraphMessage;
use App\Telegraph\Method\UserMessageTelegramMethod;
use App\Telegraph\Referral\TelegraphReferralHandler;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class TelegraphHandler extends WebhookHandler
{
   public function start(): void
   {
      if ($this->message->from()->id() == 891954506) {
         $this->chat->message('Открыть мини приложение')
            ->keyboard(
               Keyboard::make()
                  ->button('Перейти в мини приложение')->webApp(env('APP_URL'))
                  ->button('Промокод')->action('promocode')
            )
            ->send();
      } else {
         $this->chat->message('Открыть мини приложение')
            ->keyboard(
               Keyboard::make()
                  ->button('Перейти в мини приложение')->webApp(env('APP_URL'))
            )
            ->send();
      }
   }

   /**
    * @throws \Exception
    */
   public function handleChatMessage(Stringable $text): void
   {
      (new TelegraphMessage($this))->message($text);
   }

   public function promocode(): void
   {
      (new TelegraphReferralHandler($this))->promocode();
   }

   public function promocode_user(): void
   {
      (new TelegraphReferralHandler($this))->promocode_user();
   }

   public function promocodeUserPrize(): void
   {
      (new TelegraphReferralHandler($this))->promocodeUserPrize();
   }

   public function promocodeUserPrizeActivate(): void
   {
      (new TelegraphReferralHandler($this))->promocodeUserPrizeActivate();
   }

   public function handleChatMemberJoined(\DefStudio\Telegraph\DTO\User $member): void
   {
      $welcomeMessage = ChatSetting::query()->where('chat_id', $this->message->chat()->id())->first();

      if ($welcomeMessage) {
         $userMessageTelegram = new UserMessageTelegramMethod();
         $userMessageTelegram->replyWallComment($this->message->chat()->id(), $welcomeMessage->welcome_message);
         $userMessageTelegram->deleteMessage($this->message->chat()->id(), $this->message->id());
      }

      $user = User::query()->where('telegram_id', $member->id())->first();
      if ($user) {
         $task = Tasks::query()->where([['social_id', $this->message->chat()->id()], ['status', 0], ['typeSocial', 2], ['typeTask', '2']])->first();
         if ($task) {
            if ($task->access['type'] == 1 and Carbon::parse($task->created_at)->diffInMinutes(now()) >= $task->access['value']) {
               return;
            } else {
               if (!UserTask::query()->where([['task_id', $task->id], ['user_id', $member->id()]])->exists()) {
                  UserTask::query()->create([
                     'task_id' => $task->id,
                     'user_id' => $user->id,
                  ]);

                  $taskItem = TaskItems::query()->where('task_id', $task->id)->first();
                  WithdrawUser::store($taskItem->item_id, $taskItem->count, $user->id);
               }
            }
         }
      }
   }

   /**
    * @throws \Exception
    */
   protected function handleCommand(Stringable $text): void
   {
      [$command, $parameter] = $this->parseCommand($text);

      $chatCommand = (new TelegramChatCommandServices());
      if ((new AdminCommands())->checkCommand($text)) {
         $chatCommand->commands($text, $this->message->chat()->id(), $this->message->id(), $this->message->from()->id());
      } else {
         if (!$this->canHandle($command)) {
            $this->handleUnknownCommand($text);

            return;
         } else {
            $this->$command($parameter);
         }
      }
   }

   public function tickets(): void
   {
      $user = User::query()->where('vkontakte_id', $this->message->from()->id())->first();

      if (!$user) {
         $message = 'У вас не зарегестрирован аккаунт в приложение';
      } else {
         $message  = "Количество ваших билетов на аккаунте " . $user->bilet . "шт";
      }

      (new UserMessageTelegramMethod())->replyWallComment($this->message->chat()->id(), $message, $this->message->id());
   }
}
