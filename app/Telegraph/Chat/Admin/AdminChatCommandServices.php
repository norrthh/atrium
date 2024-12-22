<?php

namespace App\Telegraph\Chat\Admin;

use App\Core\Bot\BotCore;
use App\Core\EventMethod\EventTelegramMethod;
use App\Core\EventMethod\EventVkontakteMethod;
use App\Core\Message\AdminCommands;
use App\Models\ChatLink;
use App\Models\ChatQuestion;
use App\Models\Chats;
use App\Models\ChatSetting;
use App\Models\ChatWords;
use App\Models\User\User;
use App\Models\UserMute;
use App\Models\UserRole;
use App\Models\UserWarns;
use App\Telegraph\Method\UserMessageTelegramMethod;
use App\Telegraph\Method\UserTelegramMethod;
use DefStudio\Telegraph\DTO\Chat;
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

      $getInfoCommand = (new AdminCommands())->getCommand($text);
      $command = $getInfoCommand['command'] ?? null;
      $parameters = $getInfoCommand['parameters'] ?? [];

      if (!$command || !method_exists($this, $command)) {
         return;
      }

      $user_id = (new UserTelegramMethod())->getUserIdByUsername($parameters[0] ?? '');

      if (in_array($command, ['addInfo', 'newm', 'links', 'words', 'questions'])) {
         $this->{$command}($chat_id, $message_id, $parameters, $admin_id, $text);
      } elseif ($user_id) {
         $user = User::query()->where('telegram_id', $user_id)->first();
         $this->{$command}($chat_id, $message_id, $parameters, $user, $admin_id, $user_id);
      } else {
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, 'Введите все аргументы команды', $message_id);
      }
   }

   /**
    * @throws \Exception
    */
   public function addmoder(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();

      if ($userRole->role == 2) {
         if (count($parameters) == 1) {
            $userRole = UserRole::query()->where('telegram_id', $user_id)->first();

            $userUpdate = (new UserTelegramMethod())->getInfoUser($user, $user_id, 'Вам был выдан доступ модератора в беседах Atrium');
            $userUpdate['role'] = 1;

            if (!$userRole) {
               UserRole::query()->create($userUpdate);
            } else {
               UserRole::query()->where('telegram_id', $user_id)->update($userUpdate);
            }

            (new EventTelegramMethod())->replyWallComment($chat_id, 'Пользователю ' . $parameters[0] . ' был выдан доступ модератора', $message_id);
         }
      }
   }

   public function addadmin(string $chat_id, int $message_id, array $parameters, ?User $user, $admin_id, $user_id): void
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();

      if ($userRole->role == 2) {
         if (count($parameters) == 1) {
            $userRole = UserRole::query()->where('telegram_id', $user_id)->first();

            $userUpdate = (new UserTelegramMethod())->getInfoUser($user, $user_id, 'Вам был выдан доступ модератора в беседах Atrium');
            $userUpdate['role'] = 2;

            if (!$userRole) {
               UserRole::query()->create($userUpdate);
            } else {
               UserRole::query()->where('telegram_id', $user_id)->updateOrCreate($userUpdate);
            }

            (new EventTelegramMethod())->replyWallComment($chat_id, 'Пользователю ' . $parameters[0] . ' был выдан доступ администратора', $message_id);
         }
      }
   }

   /**
    * @throws \Exception
    */
   public function warn(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      $users = (new UserTelegramMethod())->getInfoUser($user, $user_id, 'Вам было выдано предупреждение в беседах Atrium');

      (new UserMessageTelegramMethod())->replyWallComment($chat_id, "Пользователю {$parameters[0]} было выдано предупреждение", $message_id);

      $userWarn = UserWarns::query()->where($users)->first();

      if ($userWarn) {
         UserWarns::query()->where($users)->increment('count', 1);

         if (($userWarn->count + 1) === 3) {
            $this->akick($chat_id, $message_id, $parameters, $user, $admin_id, $user_id);
            UserWarns::query()->where($users)->delete();
         }
      } else {
         $users['count'] = 1;
         UserWarns::query()->create($users);
      }
   }

   /**
    * @throws \Exception
    */
   public function mute(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      if (count($parameters) !== 2) {
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, 'Неверные данные. Пример: /mute @user 1', $message_id);
         return;
      }

      if (is_numeric($parameters[1])) {
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, 'Неверные данные. Аргумент должен быть числом', $message_id);
         return ;
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

   /**
    * @throws \Exception
    */
   public function addInfo(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();
      if ($userRole->role == 2) {
         if (count($parameters) != 2) {
            (new EventTelegramMethod())->replyWallComment(
               $chat_id,
               "
               Вы ввели неверные данные, проверьте пробелы. Пример: /addInfo {type} {text}.
               \n{type}:\n1 - Одна беседа Вконтакнте\n1.1 - Первая беседа вконтакте (и тд)\n2 - Все беседы ВК\n3 - Одна беседа Telegram\n3.1 - Первая беседа Telegram (и тд)\n4 - Все беседы Telegram\n5 - Все беседы
            ",
               $message_id
            );
            die();
         }

         $text = $parameters[0];
         $type = $parameters[1];

         if ($type != 2 and $type != 4 and $type != 5) {
            $explode = explode('.', $type);
            if (count($explode) == 2) {
               if ($explode[0] == 1) {
                  $chats = Chats::query()->where('messanger', 'vkontakte')->get();
                  $chats = $chats[$explode[1] - 1];

                  if ($chats) {
                     (new EventVkontakteMethod())->sendMessage($chats->chat_id, $text);
                  } else {
                     (new EventTelegramMethod())->replyWallComment($chat_id, 'Беседа не найдена', $message_id);
                     die();
                  }
               }

               if ($explode[0] == 3) {
                  $chats = Chats::query()->where('messanger', 'telegram')->get();
                  $chats = $chats[$explode[1] - 1];
                  if ($chats) {
                     (new EventTelegramMethod())->sendMessage($chats->chat_id, $text);
                  } else {
                     (new EventTelegramMethod())->replyWallComment($chat_id, 'Беседа не найдена', $message_id);
                     die();
                  }
               }
            }
         }

         if ($type == 2) {
            $chats = Chats::query()->where('messanger', 'vkontakte')->get();
            foreach ($chats as $chat) {
               (new EventVkontakteMethod())->sendMessage($chat->chat_id, $text);
            }
         }
         if ($type == 4) {
            $chats = Chats::query()->where('messanger', 'telegram')->get();
            foreach ($chats as $chat) {
               (new EventTelegramMethod())->sendMessage($chat->chat_id, $text);
            }
         }
         if ($type == 5) {
            $chats = Chats::query()->get();
            foreach ($chats as $chat) {
               if ($chat->messanger == 'vkontakte') {
                  (new EventVkontakteMethod())->sendMessage($chat->chat_id, $text);
               } else {
                  (new EventTelegramMethod())->sendMessage($chat->chat_id, $text);
               }
            }
         }

         (new EventTelegramMethod())->replyWallComment($chat_id, "Вы успешно отправили сообщения в разные беседы", $message_id);
      }
   }

   /**
    * @throws \Exception
    */
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

   /**
    * @throws \Exception
    */
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

   /**
    * @throws \Exception
    */
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
}
