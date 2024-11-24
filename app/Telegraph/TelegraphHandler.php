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
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\DTO\InlineQuery;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\DTO\Reaction;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use Illuminate\Http\Request;

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
         } else
            $this->chat->message('Вы должны подписаться на телеграмм канал @atriumru, чтобы продолжить дальше')->send();
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

   public function handle(Request $request, TelegraphBot $bot): void
   {
      try {
         $this->bot = $bot;

         $this->request = $request;

         Log::info(print_r($request->all(), 1));

         if ($this->request->has('message')) {
            /* @phpstan-ignore-next-line */
            $this->message = Message::fromArray($this->request->input('message'));
            $this->handleMessage();

            return;
         }

         if ($this->request->has('edited_message')) {
            /* @phpstan-ignore-next-line */
            $this->message = Message::fromArray($this->request->input('edited_message'));
            $this->handleMessage();

            return;
         }

         if ($this->request->has('channel_post')) {
            /* @phpstan-ignore-next-line */
            $this->message = Message::fromArray($this->request->input('channel_post'));
            $this->handleMessage();

            return;
         }

         if ($this->request->has('message_reaction')) {
            /* @phpstan-ignore-next-line */
            $this->reaction = Reaction::fromArray($this->request->input('message_reaction'));
            $this->handleReaction();

            return;
         }


         if ($this->request->has('callback_query')) {
            /* @phpstan-ignore-next-line */
            $this->callbackQuery = CallbackQuery::fromArray($this->request->input('callback_query'));
            $this->handleCallbackQuery();
         }

         if ($this->request->has('inline_query')) {
            /* @phpstan-ignore-next-line */
            $this->handleInlineQuery(InlineQuery::fromArray($this->request->input('inline_query')));
         }
      } catch (Throwable $throwable) {
         $this->onFailure($throwable);
      }
   }
}
