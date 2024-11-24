<?php

namespace App\Telegraph;

use App\Facades\WithdrawUser;
use App\Models\Event\Event;
use App\Models\Task\TaskItems;
use App\Models\Task\Tasks;
use App\Models\User\User;
use App\Models\UserTask;
use App\Services\Telegram\TelegramMethodServices;
use App\Telegraph\Message\TelegraphMessage;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;

class TelegraphHandler extends WebhookHandler
{
   public function start()
   {
      $subscription = (new TelegramMethodServices())->getChatMember($this->message->from()->id());
      if ($subscription && isset($subscription['result']) && $subscription['result']['status'] != 'left' and $this->message->from()->username() != '') {
         $this->chat->message('Открыть мини приложение')->keyboard(
            Keyboard::make()->button('Перейти в мини приложение')->webApp('https://telegram.atrium-bot.ru/')
         )->send();
      } else {
         if ($this->message->from()->username() == '') {
            $this->chat->message('У вас должен быть установлен username в настройках, чтобы запустить приложение')->send();
         } else {
            $this->chat->message(print_r($subscription, 1))->send();
            $this->chat->message('Вы должны подписаться на телеграмм канал @atriumru, чтобы продолжить дальше')->send();
         }
      }
   }
   public function handleChatMessage(Stringable $text): void
   {
      Log::info('reply0');
      (new TelegraphMessage($this))->message();
   }

   public function handleChatMemberJoined(\DefStudio\Telegraph\DTO\User $member): void
   {
      $user = User::query()->where('telegram_id', $member->id())->first();
      if ($user) {
         $task = Tasks::query()->where([['social_id', $this->message->chat()->id()], ['status', 0], ['typeSocial', 2], ['typeTask', '2']])->first();
         if ($task) {
            if ($task->access['type'] == 1 and Carbon::parse($task->created_at)->diffInMinutes(now()) >= $task->access['value']) {
               return ;
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
}
