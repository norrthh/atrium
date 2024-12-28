<?php

namespace App\Telegraph\Chat\Admin;

use App\Core\Bot\BotCore;
use App\Core\EventMethod\EventTelegramMethod;
use App\Core\Message\AdminCommands;
use App\Models\ChatLink;
use App\Models\ChatQuestion;
use App\Models\ChatWords;
use App\Models\User\User;
use App\Models\UserRole;
use App\Models\UserWarns;
use App\Telegraph\Method\UserMessageTelegramMethod;
use App\Telegraph\Method\UserTelegramMethod;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Client\ConnectionException;
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

      $getInfoCommand = (new AdminCommands())->getCommand($text);
      $command = $getInfoCommand['command'] ?? null;
      $parameters = $getInfoCommand['parameters'] ?? [];

      Log::info('command: ' . $command . ' parameters: ' . print_r($parameters, 1));

      if (!$command || !method_exists($this, $command)) {
         return;
      }

      if (!isset($parameters[0])) {
         if ($command == 'staff') {
            $this->staff($chat_id, $message_id);
            die();
         } else {
            (new UserMessageTelegramMethod())->replyWallComment($chat_id, 'Введите все аргументы команды', $message_id);
            die();
         }
      }

      $user_id = (new UserTelegramMethod())->getUserIdByUsername($parameters[0] ?? '');

      if (in_array($command, ['addInfo', 'newm', 'links', 'words', 'questions'])) {
         if (in_array($command, ['addInfo', 'newm'])) {
            $this->{$command}($chat_id, $message_id, $getInfoCommand['param'], $admin_id, $text);
         } else {
            $this->{$command}($chat_id, $message_id, $parameters, $admin_id, $text);
         }
      } elseif ($user_id) {
         $user = User::query()->where('telegram_id', $user_id)->first();
         $this->{$command}($chat_id, $message_id, $parameters, $user, $admin_id, $user_id);
      } else {
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, 'Пользователь не найден в системе', $message_id);
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
      (new UserMessageTelegramMethod())->replyWallComment($chat_id, print_r($parameters, 1), $message_id);
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

   public function addInfo(string $chat_id, int $message_id, string $parameters, int $admin_id, string $text): void
   {
      (new EventTelegramMethod())->replyWallComment($chat_id, (new BotCore())->addInfo($parameters), $message_id);
   }

   public function links(string $chat_id, int $message_id, array $parameters, int $user_id, string $text): void
   {
      if (empty($parameters[0])) {
         $links = ChatLink::query()->get()->pluck('text')->implode("\n");
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Доступные ссылки:\n$links", $message_id);
      } else {
         ChatLink::query()->create(['text' => $parameters[0]]);
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Вы успешно добавили ссылку", $message_id);
      }
   }

   public function words(string $chat_id, int $message_id, array $parameters, int $user_id, string $text): void
   {
      if (empty($parameters[0])) {
         $words = ChatWords::query()->get()->pluck('word')->implode("\n");
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Запрещенные слова:\n$words", $message_id);
      } else {
         ChatWords::query()->create(['word' => $parameters[0]]);
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Вы успешно запретили слово", $message_id);
      }
   }

   public function questions(string $chat_id, int $message_id, array $parameters, int $user_id, string $text): void
   {
      if (empty($parameters[0])) {
         $questions = ChatQuestion::query()->get()->map(function ($q) {
            return "Вопрос: {$q->question}\nОтвет: {$q->answer}";
         })->implode("\n\n");
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, $questions ?: 'Не заполнено', $message_id);
      } else {
         $text = preg_replace('~/questions\s?~', '', $text);
         Cache::put("admin_{$user_id}", ['step' => 1, 'question' => $text]);
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Введите ответ на вопрос", $message_id);
      }
   }

   public function newm(string $chat_id, int $message_id, string $parameters, int $user_id, string $text): void
   {
      (new EventTelegramMethod())->replyWallComment($chat_id, (new BotCore())->newm($chat_id, $parameters), $message_id);
   }

   /**
    * @throws ConnectionException
    */
   public function staff(string $chat_id, int $message_id): void
   {
      $userRoles = UserRole::query()->where([['telegram_id', '!=', null]])->orderBy('role', 'desc')->get()->groupBy('role');;

      $result = $userRoles->map(function ($users, $role) {
         $names = '';

         foreach ($users as $user) {
            $user = (new UserTelegramMethod())->getUserId($user->telegram_id);
            $names .= "> <a href='https://t.me/". $user['username'] ."'>". ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '') . "</a>\n";
         }

         return ($role == 1 ? 'Модераторы' : 'Администраторы') . "\n" . $names;
      })->join("\n");

      (new UserMessageTelegramMethod())->replyWallComment($chat_id, $result, $message_id, parseMode: 'html');
   }
}
