<?php

namespace App\Telegraph\Chat\Admin;

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
use DefStudio\Telegraph\DTO\Chat;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminChatCommandServices
{
   /**
    * @throws \Exception
    */
   public function command(string $text, string $chat_id, int $message_id, $admin_id)
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();

      if ($userRole) {
         $getInfoCommand = (new AdminCommands())->getCommand($text);

         if ($getInfoCommand['command']) {
            if (!method_exists($this, $getInfoCommand['command'])) {
               return false;
            }

            $user_id = $this->getUserIdByUsername($getInfoCommand['parameters'][0]);

            if (in_array($getInfoCommand['command'], ['addInfo', 'newm', 'links', 'words', 'questions'])) {
               $this->{$getInfoCommand['command']}($chat_id, $message_id, $getInfoCommand['parameters'], $admin_id, $text);
            } elseif ($user_id) {
               $user = User::query()->where('telegram_id', $user_id)->first();
               $this->{$getInfoCommand['command']}($chat_id, $message_id, $getInfoCommand['parameters'], $user, $admin_id, $user_id);
            } else {
               (new EventTelegramMethod())->replyWallComment($chat_id, 'Введите все аргументы команды', $message_id);
            }
         }
      }
   }

   public function addmoder(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();

      if ($userRole->role == 2) {
         if (count($parameters) == 1) {
            $userRole = UserRole::query()->where('telegram_id', $user_id)->first();

            $userUpdate = $this->getInfoUser($user, $user_id, 'Вам был выдан доступ модератора в беседах Atrium');
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

            $userUpdate = $this->getInfoUser($user, $user_id, 'Вам был выдан доступ модератора в беседах Atrium');
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

   public function warn(string $chat_id, int $message_id, array $parameters, ?User $user, $admin_id, $user_id): void
   {
      $users = $this->getInfoUser($user, $user_id, 'Вам было выдано предупреждение в беседах Atrium');

      (new EventTelegramMethod())->replyWallComment($chat_id, 'Пользователю ' . $parameters[0] . ' был выдан предупреждение', $message_id);

      $userWarn = UserWarns::query()->where($users)->first();
      if ($userWarn) {
         UserWarns::query()->where($users)->increment('count', 1);
         if ($userWarn->count + 1 == 3) {
            $this->kick($chat_id, $message_id, $parameters, $user, $user_id);
         }
      } else {
         $users['count'] = 1;
         UserWarns::query()->create($users);
      }
   }

   public function mute(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, $user_id): void
   {
      if (count($parameters) != 2) {
         (new EventTelegramMethod())->replyWallComment($chat_id, 'Вы ввели неверные данные, проверьте пробелы. Пример: /mute @user 1', $message_id);
         die();
      }

      $users = $this->getInfoUser($user, $user_id, 'Вы были замучены в беседах Atrium');
      $users['time'] = $parameters[1];
      UserMute::query()->create($users);

      (new EventTelegramMethod())->replyWallComment($chat_id, 'Пользователю ' . $parameters[0] . ' был выдан мут', $message_id);
   }

   public function kick(string $chat_id, int $message_id, array $parameters, ?User $user, $admin_id): void
   {
      (new EventTelegramMethod())->replyWallComment($chat_id, (new EventTelegramMethod())->kickUserFromChat($chat_id, $admin_id), $message_id);
   }

   public function akick(string $chat_id, int $message_id, array $parameters, $user, $admin_id, $user_id): void
   {
      $chats = Chats::query()->get();
      foreach ($chats as $chat) {
         if ($user and $chat->messanger == 'vkontakte' and $user->vkontakte_id) {
            (new EventVkontakteMethod())->kickUserFromChat($chat->chat_id, $user->vkontakte_id);
         } else {
            (new EventTelegramMethod())->kickUserFromChat($chat->chat_id, $user_id);
         }
      }

      (new EventTelegramMethod())->replyWallComment($chat_id, 'Пользователь ' . $parameters[0] . ' был исключен из всех бесед', $message_id);
   }

   /**
    * @throws \Exception
    */
   public function addInfo(string $chat_id, int $message_id, array $parameters, ?User $user, $admin_id, $user_id): void
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();
      if ($userRole->role == 2) {
         if (count($parameters) != 2) {
            (new EventTelegramMethod())->replyWallComment(
               $chat_id,
               "
               Вы ввели неверные данные, проверьте пробелы. Пример: /addInfo {text} {type}.
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
//                  (new EventTelegramMethod())->replyWallComment($chat_id, $chats->chat_id, $message_id);
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


   public function newm($chat_id, $message_id, $parameters, $admin_id, $text): void
   {
      $userRole = UserRole::query()->where('telegram_id', $admin_id)->first();
      if ($userRole->role == 2) {
         $text2 = preg_replace('~/newm\s?~', '', $text);
         if ($text2) {
            ChatSetting::query()->update(['welcome_message' => $text2]);
            (new EventTelegramMethod())->replyWallComment($chat_id, "Вы успешно обновили привественное сообщение", $message_id);
         } else {
            (new EventTelegramMethod())->replyWallComment($chat_id, "Введите привественное сообщение", $message_id);
         }
      }
   }

   public function links($chat_id, $message_id, $parameters, $user_id, $text)
   {
//      (new EventTelegramMethod())->replyWallComment($chat_id, count($parameters), $message_id);
      if ($parameters[0] == '') {
         $linkZ = '';
         foreach (ChatLink::query()->get() as $link) {
            $linkZ .= "\n" . $link->text;
         }

         (new EventTelegramMethod())->replyWallComment($chat_id, "Доступные ссылки:\n" . $linkZ, $message_id);
      } else {
         if ($parameters[0] != '') {
            ChatLink::query()->create([
               'text' => $parameters[0]
            ]);

            (new EventTelegramMethod())->replyWallComment($chat_id, "Вы успешно добавили ссылку", $message_id);
         }
      }
   }

   /**
    * @throws \Exception
    */
   public function words($chat_id, $message_id, $parameters, $user_id, $text)
   {
      if ($parameters[0] == '') {
         $linkZ = '';
         foreach (ChatWords::query()->get() as $link) {
            $linkZ .= "\n" . $link->word;
         }

         (new EventTelegramMethod())->replyWallComment($chat_id, "Запрещенные слова:\n" . $linkZ, $message_id);
      } else {
         if ($parameters[0] != '') {
            ChatWords::query()->create([
               'word' => $parameters[0]
            ]);

            (new EventTelegramMethod())->replyWallComment($chat_id, "Вы успешно запретили слово", $message_id);
         }
      }
   }


   public function questions($chat_id, $message_id, $parameters, $user_id, $text)
   {
      if ($parameters[0] == '') {
         $linkZ = '';

         foreach (ChatQuestion::query()->get() as $question) {
            $linkZ .= "\nВопрос:" . $question->question . "\nОтвет:" . $question->answer . "\n";
         }

         (new EventTelegramMethod())->replyWallComment($chat_id, $linkZ == '' ? 'Не заполнено' : $linkZ, $message_id);
      } else {
         if ($parameters[0] != '') {
            $text2 = preg_replace('~/questions\s?~', '', $text);
            if ($text2) {
               Cache::put('admin_' . $user_id, ['step' => 1, 'question' => $text2]);
               (new EventTelegramMethod())->replyWallComment($chat_id, "Введите ответ на вопрос", $message_id);
            } else {
               (new EventTelegramMethod())->replyWallComment($chat_id, "Введите вопрос", $message_id);
            }
         }
      }
   }

   public function extractMention(string $input): ?string
   {
      $pattern = '/@(\w+)/';

      if (preg_match($pattern, $input, $matches)) {
         return $matches[1];
      }

      return null;
   }

   public function getInfoUser(?User $user, int $admin_id, string $message): array
   {
      $userUpdate = [
         'telegram_id' => $admin_id,
      ];

      if ($user and $user->vkontakte_id) {
         $userUpdate['vkontakte_id'] = $user->vkontakte_id;
         (new EventVkontakteMethod())->sendMessage($user->vkontakte_id, $message);
      }

      return $userUpdate;
   }

   public function getUserIdByUsername(string $username)
   {
      $response = file_get_contents("https://api.telegram.org/bot" . env('TELEGRAM_TOKEN') . "/getChat?chat_id={$username}");
      $data = json_decode($response, true);

      // Проверяем успешность ответа
      if (isset($data['result']['id'])) {
         return $data['result']['id']; // Возвращаем user_id
      }

      // Если ошибка, возвращаем текст ошибки
      return $data['description'] ?? 'Ошибка: Пользователь не найден.';
   }
}
