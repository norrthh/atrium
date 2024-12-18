<?php

namespace App\Telegraph;

use App\Facades\WithdrawUser;
use App\Models\Task\TaskItems;
use App\Models\Task\Tasks;
use App\Models\User\User;
use App\Models\User\UserTask;
use App\Services\Telegram\TelegramMethodServices;
use App\Telegraph\Message\TelegraphMessage;
use App\Telegraph\Referral\TelegraphReferralHandler;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\DTO\ChatJoinRequest;
use DefStudio\Telegraph\DTO\InlineQuery;
use DefStudio\Telegraph\DTO\Message;
use DefStudio\Telegraph\DTO\Reaction;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use Illuminate\Http\Request;

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

   public function return(): void
   {

   }
}
