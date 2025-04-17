<?php

namespace App\Telegraph;

use App\Core\Bot\BotCore;
use App\Core\Message\AdminCommands;
use App\Facades\WithdrawUser;
use App\Models\Chat\Chats;
use App\Models\Chat\ChatSetting;
use App\Models\Task\TaskItems;
use App\Models\Task\Tasks;
use App\Models\User\User;
use App\Models\User\UserBan;
use App\Models\User\UserBilet;
use App\Models\User\UserTask;
use App\Services\BotFilterMessageServices;
use App\Telegraph\Chat\TelegramChatCommandServices;
use App\Telegraph\Message\TelegraphMessage;
use App\Telegraph\Method\UserMessageTelegramMethod;
use App\Telegraph\Method\UserTelegramMethod;
use App\Telegraph\Referral\TelegraphReferralHandler;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Stringable;

class TelegraphHandler extends WebhookHandler
{
   public function start(): void
   {
      $explode = explode(' ', $this->message->text());

      if (!isset($this->callbackQuery) && $this->message->text() and count($explode) >= 2) {
         $token = $explode[1];

         $response = Http::withHeaders([
            'User-Agent' => 'TelegramAtriumBot'
         ])->asForm()->post('https://files.atrm.gg/atrium_service/', [
            'request' => '46fea46540997ee85c5f6583446e44f21822ba72539e7c4e2513c0_crpt',
            'user_id' => $this->message->from()->id(),
            'token'   => $token,
         ]);

         if ($response->successful()) {
            $data = $response->json();
            switch ($data['code']) {
               case 201:
                  $this->chat->message('создан')->send();
                  break;
               case 200:
                  $this->chat->message('токен обновлен')->send();
                  break;
               case 400:
                  $this->chat->message('ошибка 400')->send();
                  break;
               default:
                  $this->chat->message('ошибка default')->send();
                  break;
            }
         } else {
            $this->chat->message('Произошла ошибка, попробуйте позже')->send();
         }

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
      $userBan = UserBan::query()->where('telegram_id', $this->message->from()->id())->first();
      $userMessageTelegram = new UserMessageTelegramMethod();

      if ($userBan) {
         (new UserTelegramMethod())->kickUserFromChat($this->message->chat()->id(), $this->message->from()->id());
         $userMessageTelegram->replyWallComment($this->message->chat()->id(), 'Этот пользователь заблокирован');
      } else {
         $welcomeMessage = ChatSetting::query()->where('chat_id', $this->message->chat()->id())->first();

         if ($welcomeMessage) {
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
   }

   /**
    * @throws \Exception
    */
   protected function handleCommand(Stringable $text): void
   {
      [$command, $parameter] = $this->parseCommand($text);

      if ((new BotFilterMessageServices())->checkMute($this->message->from()->id(), 'telegram_id')) {
         $chatCommand = (new TelegramChatCommandServices());

         if ((new AdminCommands())->checkCommand($text)) {
            $chatCommand->commands($text, $this->message->chat()->id(), $this->message->id(), $this->message->from()->id());
         } else {
            if (!$this->canHandle($command)) {
               $this->chat->message("Команда не найдена, но вы можете открыть приложение в личном сообщение бота - @atriumappbot :)")->send();

               return;
            } else {
               $this->$command($parameter);
            }
         }
      } else {
         (new BotFilterMessageServices())->deleteMessage($this->message->id(), $this->message->chat()->id(), 'telegram_id');
      }
   }

   public function tickets(): void
   {
      if (!Chats::query()->where('chat_id', $this->message->chat()->id())->exists()) {
         $user = User::query()->where('telegram_id', $this->message->from()->id())->first();

         if (!$user) {
            $message = "У вас не зарегестрирован аккаунт в приложение. \n\nНапишите /start в личном сообщение бота - @atriumappbot";
         } else {
            $userBilets = UserBilet::query()->where('users_id', $user->id)->get();
            if (count($userBilets) == 0) {
               $message = 'У вас отсутствуют билеты';
            } else {
               $message = "Ваши билеты:\n";

               foreach ($userBilets as $bilet) {
                  $message .= "\n№ " . $bilet->id;
               }
            }
         }

         (new UserMessageTelegramMethod())->replyWallComment($this->message->chat()->id(), $message, $this->message->id());
      } else {
         (new UserMessageTelegramMethod())->replyWallComment(
            $this->message->chat()->id(),
            'Командной можно воспользоваться только в личном сообщение бота',
            $this->message->id()
         );
      }
   }
}
