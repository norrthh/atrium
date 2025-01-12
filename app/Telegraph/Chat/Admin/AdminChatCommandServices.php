<?php

namespace App\Telegraph\Chat\Admin;

use App\Core\Bot\BotCore;
use App\Core\EventMethod\EventTelegramMethod;
use App\Core\Message\AdminCommands;
use App\Models\Chat\ChatLink;
use App\Models\Chat\ChatQuestion;
use App\Models\Chat\ChatWords;
use App\Models\User\User;
use App\Models\User\UserRole;
use App\Models\User\UserWarns;
use App\Telegraph\Method\UserMessageTelegramMethod;
use App\Telegraph\Method\UserTelegramMethod;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminChatCommandServices
{
   /**
    * @throws \Exception
    */
   public function command(string $text, string $chat_id, int $message_id, $admin_id): void
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();

      if (!$userRole) {
         return;
      }

      $adminCommand = new AdminCommands();

      $getInfoCommand = $adminCommand->getCommand($text);
      $command = $getInfoCommand['command'] ?? null;
      $parameters = $getInfoCommand['parameters'] ?? [];

//      TelegraphChat::query()->where('chat_id', 891954506)->first()->message(print_r($getInfoCommand, true))->send();

      if (!$command || !method_exists($this, $command)) {
         return;
      }

      if (in_array($command, $adminCommand->commandNotArg)) {
         if (in_array($command, ['addInfo', 'newm'])) {
            $this->{$command}($chat_id, $message_id, $getInfoCommand, $admin_id, $text);
         } else {
            $this->{$command}($chat_id, $message_id, $parameters, $admin_id, $text);
         }
      } else {
         if (!isset($parameters[0])) {
            (new UserMessageTelegramMethod())->replyWallComment($chat_id, 'Введите все аргументы команды. Пример: /'. $command . ' @norrth', $message_id);
            die();
         }

         $user_id = (new UserTelegramMethod())->getUserIdByUsername($parameters[0]);

         if ($user_id) {
            $user = User::query()->where('telegram_id', $user_id)->first();
            $this->{$command}($chat_id, $message_id, $parameters, $user, $admin_id, $user_id);
         } else {
            (new UserMessageTelegramMethod())->replyWallComment($chat_id, 'Пользователь не найден в системе', $message_id);
         }
      }
   }
   public function addmoder(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();

      if ($userRole->role == 2) {
         if (count($parameters) == 1) {
            (new BotCore())->addRole($user_id, 1, 'telegram_id');
            (new EventTelegramMethod())->replyWallComment($chat_id, 'Пользователю ' . $parameters[0] . ' был выдан доступ модератора', $message_id);
         }
      }
   }
   public function addadmin(string $chat_id, int $message_id, array $parameters, ?User $user, $admin_id, $user_id): void
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();

      if ($userRole->role == 2) {
         if (count($parameters) == 1) {
            (new BotCore())->addRole($user_id, 2, 'telegram_id');
            (new EventTelegramMethod())->replyWallComment($chat_id, 'Пользователю ' . $parameters[0] . ' был выдан доступ администратора', $message_id);
         }
      }
   }
   public function warn(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      $users = (new UserTelegramMethod())->getInfoUser($user, $user_id);
      $userWarn = UserWarns::query()->where($users)->first();
      $status = true;

      if ($userWarn) {
         UserWarns::query()->where($users)->increment('count', 1);

         if (($userWarn->count + 1) === 3) {
            $status = false;
            $this->akick($chat_id, $message_id, $parameters, $user, $admin_id, $user_id);
            UserWarns::query()->where($users)->delete();
         }
      } else {
         $users['count'] = 1;
         UserWarns::query()->create($users);
      }

      if ($status) {
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Пользователю {$parameters[0]} было выдано предупреждение", $message_id);
      }
   }
   public function mute(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      if (count($parameters) !== 2) {
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, 'Неверные данные. Пример: /mute @user 1', $message_id);
         return;
      }

      if (!is_numeric($parameters[1])) {
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, 'Неверные данные. Аргумент должен быть числом', $message_id);
         return;
      }

      (new BotCore())->mute((new UserTelegramMethod())->getInfoUser($user, $user_id), $parameters[1], 'telegram_id', $user_id);
      (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Пользователю {$parameters[0]} был выдан мут", $message_id);
   }
   public function kick(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      (new UserMessageTelegramMethod())->replyWallComment($chat_id, (new UserTelegramMethod())->kickUserFromChat($chat_id, $user_id), $message_id);
   }
   public function akick(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id)
   {
      (new BotCore())->akick($user, 'telegram', $user_id);
      (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Пользователь был удален из всех бесед", $message_id);
   }
   public function addInfo(string $chat_id, int $message_id, array $parameters, int $admin_id, string $text): void
   {
      (new EventTelegramMethod())->replyWallComment($chat_id, (new BotCore())->addInfo($parameters['param']), $message_id);
   }
   public function newm(string $chat_id, int $message_id, array $parameters, int $user_id, string $text): void
   {
      (new EventTelegramMethod())->replyWallComment($chat_id, (new BotCore())->newm($chat_id, explode('/newm', $text)[1]), $message_id);
   }
   public function staff(string $chat_id, int $message_id): void
   {
      $userRoles = UserRole::query()->where([['telegram_id', '!=', null]])->orderBy('role', 'desc')->get()->groupBy('role');;

      $result = $userRoles->map(function ($users, $role) {
         $names = '';

         foreach ($users as $user) {
            $user = (new UserTelegramMethod())->getUserId($user->telegram_id);
            $names .= "🏐 <a href='https://t.me/". $user['username'] ."'>". ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '') . "</a>\n";
         }

         return ($role == 1 ? '🎄🎄🎄🎄🎄Модераторы' : '🎄🎄🎄🎄🎄Администраторы') . "\n" . $names;
      })->join("\n");

      (new UserMessageTelegramMethod())->replyWallComment($chat_id, $result, $message_id, parseMode: 'html');
   }

   public function unwarn(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      $userWarn = UserWarns::query()->where('telegram_id', $user_id)->first();
      if($userWarn and $userWarn->count > 1) {
         UserWarns::query()->where('telegram_id', $user_id)->update(['count' => $userWarn->count - 1]);
      }

      (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Вы успешно сняли одно предупреждение", $message_id, parseMode: 'html');
   }

   public function unmute(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      (new UserMessageTelegramMethod())->replyWallComment($chat_id, (new BotCore())->unmute($user_id, 'telegram_id'), $message_id, parseMode: 'html');
   }

   public function unban(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id)
   {
      (new UserMessageTelegramMethod())->replyWallComment($chat_id, (new BotCore())->unban($user_id, 'telegram_id'), $message_id, parseMode: 'html');
   }

   public function delstaff(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id)
   {
      (new UserMessageTelegramMethod())->replyWallComment($chat_id, (new BotCore())->delstaff($user_id, 'telegram_id', $admin_id), $message_id, parseMode: 'html');
   }
}
