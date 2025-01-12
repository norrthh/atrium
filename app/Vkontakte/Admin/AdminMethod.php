<?php

namespace App\Vkontakte\Admin;

use App\Core\Bot\BotCore;
use App\Core\Message\AdminCommands;
use App\Models\Chat\ChatLink;
use App\Models\Chat\ChatQuestion;
use App\Models\Chat\ChatWords;
use App\Models\User\User;
use App\Models\User\UserRole;
use App\Models\User\UserWarns;
use App\Telegraph\Method\UserMessageTelegramMethod;
use App\Vkontakte\Bot\BotCommandMethod;
use Illuminate\Support\Facades\Cache;
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

         if ($user_id) {
            $this->{$command}(user_id: $user_id, args: $getInfoCommand);
         } elseif (in_array($command, (new AdminCommands())->commandNotArg)) {
            $this->{$command}(args: $getInfoCommand);
         } else {
            $this->message->sendAPIMessage(userId: $this->user_id, message: 'Перепроверьте все аргументы, они должны быть валидными. Пример: /'. $command .' @username', conversation_message_id: $this->conversation_message_id);
         }
      }
   }
   public function staff($args): void
   {
      $userRoles = UserRole::query()->where([['vkontakte_id', '!=', null]])->orderBy('role', 'desc')->get();

      $result = $userRoles->groupBy('role')->map(function ($users, $role) {
         $names = '';
         foreach ($users as $userZ) {
            $userAccount = User::query()->where('vkontakte_id', $userZ->vkontakte_id)->first();

            if ($userAccount) {
               $names .= '🏐 [id' . $userAccount->vkontakte_id . '|' . $userAccount->username_vkontakte . "]\n";
            }
         }

         return ($role == 1 ? '🎄🎄🎄🎄🎄Модераторы' : '🎄🎄🎄🎄🎄Администраторы') . "\n" . $names;
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
//      Log::info('call akick');
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
      $message = 'Заполните все аргументы. /addInfo 1231';
      if (isset($args['other'])) {
         $message = (new BotCore())->addInfo($args['other']);
      }
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: $message,
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
      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: (new BotCore())->newm($this->user_id, $args['other']),
         conversation_message_id: $this->conversation_message_id
      );
   }

   protected function unwarn(int $user_id, array $args)
   {
      $userWarn = UserWarns::query()->where('vkontakte_id', $args['id'])->first();
      if($userWarn and $userWarn->count > 1) {
         UserWarns::query()->where('vkontakte_id', $args['id'])->update(['count' => $userWarn->count - 1]);
      }

      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: "Вы успешно сняли одно предупреждение",
         conversation_message_id: $this->conversation_message_id
      );
   }

   public function links(array $args): void
   {
      if (empty($args['other'])) {
         $links = ChatLink::query()->pluck('text')->implode("\n");
         $message = "Доступные ссылки:\n" . ($links ?: "Нет доступных ссылок");
      } else {
         ChatLink::query()->create(['text' => $args['other']]);
         $message = "Вы успешно добавили ссылку";
      }

      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: $message,
         conversation_message_id: $this->conversation_message_id
      );
   }

   public function words(array $args): void
   {
      if (empty($args['other'])) {
         $words = ChatWords::query()->pluck('word')->toArray();
         $result = array_map(fn($chunk) => implode(',', $chunk), array_chunk($words, 100));

         $this->message->sendAPIMessage(
            userId: $this->user_id,
            message: "Заблокированные слова:\n",
            conversation_message_id: $this->conversation_message_id
         );

         foreach ($result as $wordsGroup) {
            $this->message->sendAPIMessage(
               userId: $this->user_id,
               message: $wordsGroup,
               conversation_message_id: $this->conversation_message_id
            );
         }
      } else {
         ChatWords::query()->create(['word' => $args['other']]);
         $this->message->sendAPIMessage(
            userId: $this->user_id,
            message: "Вы успешно запретили слово: {$args['other']}",
            conversation_message_id: $this->conversation_message_id
         );
      }
   }

   public function questions(array $args): void
   {
      if (empty($args['other'])) {
         $questions = ChatQuestion::query()->get()->map(function ($q) {
            return "Вопрос: {$q->question}\nОтвет: {$q->answer}";
         })->implode("\n\n");

         $message = $questions ?: "Список вопросов пуст.";
      } else {
         Cache::put("admin_{$this->user}", ['step' => 1, 'question' => $args['other']]);
         $message = "Введите ответ на вопрос: {$args['other']}";
      }

      $this->message->sendAPIMessage(
         userId: $this->user_id,
         message: $message,
         conversation_message_id: $this->conversation_message_id
      );
   }

}
