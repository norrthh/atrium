<?php

namespace App\Vkontakte\Admin;

use App\Core\Bot\BotCore;
use App\Core\EventMethod\EventTelegramMethod;
use App\Core\EventMethod\EventVkontakteMethod;
use App\Core\Message\AdminCommands;
use App\Models\Chats;
use App\Models\User\User;
use App\Models\UserMute;
use App\Models\UserRole;
use App\Models\UserWarns;
use App\Telegraph\Method\UserTelegramMethod;
use App\Vkontakte\Bot\BotCommandMethod;
use Illuminate\Support\Facades\Log;

class AdminMethod extends BotCommandMethod
{
   public function method(): void
   {
      $userRole = UserRole::query()->where('vkontakte_id', $this->user)->first();

      if ($userRole) {
         $getInfoCommand = (new AdminCommands())->checkCommandVK($this->messageText);

         $command = $getInfoCommand['command'] ?? null;
         $user_id = $getInfoCommand['id'];

         Log::info('user_id' . print_r($getInfoCommand, 1));

         if ($user_id) {
            $this->{$command}(user_id: $user_id, args: $getInfoCommand);
         } elseif (in_array($command, (new AdminCommands())->commandNotArg)) {
            $this->{$command}(args: $getInfoCommand);
         } else {
            $this->message->sendAPIMessage(userId: $this->user_id, message: 'Пользователь не найден', conversation_message_id: $this->conversation_message_id);
         }
      }
   }

   public function staff($args): void
   {
      $userRoles = UserRole::query()->where([['vkontakte_id', '!=', null]])->orderBy('role', 'desc')->get();
      $usersFilter = [];

      foreach ($userRoles as $role) {
         $user = User::query()->where('vkontakte_id', $role->vkontakte_id)->first();

         $usersFilter[] = ($role->role == 2 ? 'Администратор ' : 'Модератор ') . '[id' . $role->vkontakte_id . '|' . $user->username_vkontakte . ']';
      }

//      Log::info(print_r($usersFilter, 1));
      $this->message->sendAPIMessage(userId: $this->user_id, message: implode("<br>", $usersFilter), conversation_message_id: $this->conversation_message_id);
   }

   public function kick($user_id, array $args): void
   {
      $this->userMethod->kickUserFromChat($user_id);
      $this->message->sendAPIMessage(userId: $this->user_id, message: 'Пользователь исключён из беседы', conversation_message_id: $this->conversation_message_id);
   }

   public function addmoder(int $user_id, array $args): void
   {
      $userRole = UserRole::query()->where('vkontakte_id', $this->user)->first();
      if ($userRole->role == 2) {
         $this->addRole($user_id, 1);
         $this->message->sendAPIMessage(userId: $this->user_id, message: 'Вы успешно выдали роль модератора', conversation_message_id: $this->conversation_message_id);
      }
   }

   public function addadmin(int $user_id, array $args): void
   {
      $userRole = UserRole::query()->where('vkontakte_id', $this->user)->first();
      if ($userRole->role == 2) {
         $this->addRole($user_id, 2);
         $this->message->sendAPIMessage(userId: $this->user_id, message: 'Вы успешно выдали роль администратора', conversation_message_id: $this->conversation_message_id);
      }
   }

   public function warn(int $user_id, array $args): void
   {
      $data = $this->userData($user_id);
      $notification = true;
      $userWarn = UserWarns::query()->where([['vkontakte_id', $user_id]])->first();
      if ($userWarn) {
         UserWarns::query()->where([['vkontakte_id', $user_id]])->increment('count', 1);
         if (($userWarn->count + 1) >= 3) {
            $this->akick(user_id: $user_id, args: $args);
            UserWarns::query()->where([['vkontakte_id', $user_id]])->delete();
            $notification = false;
         }
      } else {
         $data['count'] = 1;
         UserWarns::query()->create($data);
      }

      if ($notification) {
         $this->message->sendAPIMessage(userId: $this->user_id, message: 'Вы успешно выдали предупреждение', conversation_message_id: $this->conversation_message_id);
      }
   }

   public function akick(int $user_id, array $args): void
   {
      Log::info('call akick');
      (new BotCore())->akick(
         User::query()->where('vkontakte_id', $user_id)->first(),
         'vkontakte',
         $user_id
      );

      $this->message->sendAPIMessage(userId: $this->user_id, message: 'Пользователь был удален из всех бесед', conversation_message_id: $this->conversation_message_id);
   }

   public function mute(int $user_id, array $args): void
   {
      if ($args['other'] == '' or !is_numeric($args['other'])) {
         $this->message->sendAPIMessage(userId: $this->user_id, message: 'Неверные данные. Аргумент должен быть числом', conversation_message_id: $this->conversation_message_id);
         return;
      }

      (new BotCore())->mute($this->userData($user_id), $args['other'], 'vkontakte_id', $user_id);
      $this->message->sendAPIMessage(userId: $this->user_id, message: 'Вы успешно выдали мут', conversation_message_id: $this->conversation_message_id);
   }

   public function addInfo(array $args): void
   {
      $info = (new AdminCommands())->parseFirstArg($args['other']);

      if ($info['first_arg'] != '' and $info['remaining'] != '') {
         $text = $info['remaining'];
         $type = $info['first_arg'];

         if ($type != 2 and $type != 4 and $type != 5) {
            $explode = explode('.', $type);
            if (count($explode) == 2) {
               if ($explode[0] == 1) {
                  $chats = Chats::query()->where('messanger', 'vkontakte')->get();
                  $chats = $chats[$explode[1] - 1];

                  if ($chats) {
                     $this->message->sendAPIMessage(
                        userId: $chats->chat_id,
                        message: $text,
                     );
                  } else {
                     $this->message->sendAPIMessage(
                        userId: $this->user_id,
                        message: 'Беседа не найдена',
                        conversation_message_id: $this->conversation_message_id
                     );
                     die();
                  }
               }

               if ($explode[0] == 3) {
                  $chats = Chats::query()->where('messanger', 'telegram')->get();
                  $chats = $chats[$explode[1] - 1];
                  if ($chats) {
                     (new EventTelegramMethod())->sendMessage($chats->chat_id, $text);
                  } else {
                     $this->message->sendAPIMessage(
                        userId: $this->user_id,
                        message: 'Беседа не найдена',
                        conversation_message_id: $this->conversation_message_id
                     );
                     die();
                  }
               }
            } else {
               $this->message->sendAPIMessage(
                  userId: $this->user_id,
                  message: "
                     Вы ввели неверные данные, проверьте пробелы. Пример: /addInfo {type} {text}.
                     \n{type}:\n1 - Одна беседа Вконтакнте\n1.1 - Первая беседа вконтакте (и тд)\n2 - Все беседы ВК\n3 - Одна беседа Telegram\n3.1 - Первая беседа Telegram (и тд)\n4 - Все беседы Telegram\n5 - Все беседы
                  ",
                  conversation_message_id: $this->conversation_message_id
               );
               die();
            }
         } elseif ($type == 2) {
            foreach (Chats::query()->where('messanger', 'vkontakte')->get() as $chat) {
               $this->message->sendAPIMessage(
                  userId: $chat->chat_id,
                  message: $text,
               );
            }
         } elseif ($type == 4) {
            foreach (Chats::query()->where('messanger', 'telegram')->get() as $chat) {
               (new EventTelegramMethod())->sendMessage($chat->chat_id, $text);
            }
         } elseif ($type == 5) {
            foreach (Chats::query()->get() as $chat) {
               if ($chat->messanger == 'vkontakte') {
                  $this->message->sendAPIMessage(
                     userId: $chat->chat_id,
                     message: $text,
                  );
               } else {
                  (new EventTelegramMethod())->sendMessage($chat->chat_id, $text);
               }
            }
         } else {
            $this->message->sendAPIMessage(
               userId: $this->user_id,
               message: 'Введите валидные аргументы',
               conversation_message_id: $this->conversation_message_id
            );

            die();
         }

         $this->message->sendAPIMessage(
            userId: $this->user_id,
            message: "Ваше сообщение отправлено",
            conversation_message_id: $this->conversation_message_id
         );
      } else {
         $this->message->sendAPIMessage(
            userId: $this->user_id,
            message: "
               Вы ввели неверные данные, проверьте пробелы. Пример: /addInfo {type} {text}.
               \n{type}:\n1 - Одна беседа Вконтакнте\n1.1 - Первая беседа вконтакте (и тд)\n2 - Все беседы ВК\n3 - Одна беседа Telegram\n3.1 - Первая беседа Telegram (и тд)\n4 - Все беседы Telegram\n5 - Все беседы
            ",
            conversation_message_id: $this->conversation_message_id
         );
      }
   }

   protected function addRole(int $user_id, int $role): void
   {
      $user = User::query()->where('vkontakte_id', $user_id)->first();

      $roleData = [
         'vkontakte_id' => $user_id,
         'role' => $role
      ];

      if ($user and $user->telegram_id) {
         $roleData['telegram_id'] = $user->telegram_id;
      }

      UserRole::query()->updateOrCreate(['vkontakte_id' => $user_id], $roleData);
   }

   protected function userData(int $user_id): array
   {
      $data = ['vkontakte_id' => $user_id];
      $userFind = User::query()->where('vkontakte_id', $user_id)->first();

      if ($userFind and $userFind->telegram_id) {
         $data['telegram_id'] = $userFind->telegram_id;
      }

      return $data;
   }
}
