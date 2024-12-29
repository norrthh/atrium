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

         Log::info('getInfoCommand' . print_r($getInfoCommand, 1));

         $command = $getInfoCommand['command'] ?? null;
         $user_id = $getInfoCommand['id'];

         Log::info('user_id' . print_r($getInfoCommand, 1));

         if ($user_id) {
            $this->{$command}(user_id: $user_id, args: $getInfoCommand);
         } elseif (in_array($command, (new AdminCommands())->commandNotArg)) {
            Log::info(300);
            $this->{$command}(args: $getInfoCommand);
         } else {
            $this->message->sendAPIMessage(userId: $this->user_id, message: 'Пользователь не найден', conversation_message_id: $this->conversation_message_id);
         }
      }
   }
   public function staff($args): void
   {
      $userRoles = UserRole::query()->where([['vkontakte_id', '!=', null]])->orderBy('role', 'desc')->get();

      $result = $userRoles->groupBy('role')->map(function ($users, $role) {
         $names = '';
         Log::info('users ' . $users);
         foreach ($users as $userZ) {
            Log::info('user ' . $userZ);
            $userAccount = User::query()->where('vkontakte_id', $userZ->vkontakte_id)->first();

            // Проверяем, найден ли пользователь
            if ($userAccount) {
               $names .= '[id' . $userAccount->vkontakte_id . '|' . $userAccount->username_vkontakte . "]\n";
            }
         }

         return ($role == 1 ? 'Модераторы' : 'Администраторы') . "\n" . $names;
      })->join("\n");

      $this->message->sendAPIMessage(userId: $this->user_id, message: $result, conversation_message_id: $this->conversation_message_id);
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
         (new BotCore())->addRole($user_id, 1, 'vkontakte_id');
         $this->message->sendAPIMessage(userId: $this->user_id, message: 'Вы успешно выдали роль модератора', conversation_message_id: $this->conversation_message_id);
      }
   }
   public function addadmin(int $user_id, array $args): void
   {
      $userRole = UserRole::query()->where('vkontakte_id', $this->user)->first();
      if ($userRole->role == 2) {
         (new BotCore())->addRole($user_id, 2, 'vkontakte_id');
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
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: (new BotCore())->addInfo($args['text']),
         conversation_message_id: $this->conversation_message_id
      );
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
   protected function newm(array $args): void
   {
      Log::info('call newm');
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: (new BotCore())->newm($this->user_id, $args['other']),
         conversation_message_id: $this->conversation_message_id
      );
   }
}
