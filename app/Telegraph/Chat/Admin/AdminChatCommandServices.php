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
   public function command(string $text, string $chat_id, int $message_id, $admin_id)
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

      $user_id = $this->getUserIdByUsername($parameters[0] ?? '');

      if (in_array($command, ['addInfo', 'newm', 'links', 'words', 'questions'])) {
         $this->{$command}($chat_id, $message_id, $parameters, $admin_id, $text);
      } elseif ($user_id) {
         $user = User::query()->where('telegram_id', $user_id)->first();
         $this->{$command}($chat_id, $message_id, $parameters, $user, $admin_id, $user_id);
      } else {
         (new EventTelegramMethod())->replyWallComment($chat_id, 'Введите все аргументы команды', $message_id);
      }
   }

   public function addRole(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id, int $role): void
   {
      $adminRole = UserRole::query()->where('telegram_id', $admin_id)->first();

      if ($adminRole->role !== 2 || count($parameters) !== 1) {
         return;
      }

      $userRole = UserRole::query()->where('telegram_id', $user_id)->first();
      $userUpdate = $this->getInfoUser($user, $user_id, "Вам был выдан доступ уровня $role в беседах Atrium");
      $userUpdate['role'] = $role;

      if ($userRole) {
         UserRole::query()->where('telegram_id', $user_id)->update($userUpdate);
      } else {
         UserRole::query()->create($userUpdate);
      }

      $roleName = $role === 2 ? 'администратора' : 'модератора';
      (new EventTelegramMethod())->replyWallComment($chat_id, "Пользователю {$parameters[0]} был выдан доступ $roleName", $message_id);
   }

   public function warn(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      $users = $this->getInfoUser($user, $user_id, 'Вам было выдано предупреждение в беседах Atrium');

      (new EventTelegramMethod())->replyWallComment($chat_id, "Пользователю {$parameters[0]} было выдано предупреждение", $message_id);

      $userWarn = UserWarns::query()->where($users)->first();

      if ($userWarn) {
         $newCount = $userWarn->count + 1;
         UserWarns::query()->where($users)->increment('count', 1);

         if ($newCount === 3) {
            $this->kick($chat_id, $message_id, $parameters, $user, $admin_id);
         }
      } else {
         $users['count'] = 1;
         UserWarns::query()->create($users);
      }
   }

   public function mute(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      if (count($parameters) !== 2) {
         (new EventTelegramMethod())->replyWallComment($chat_id, 'Неверные данные. Пример: /mute @user 1', $message_id);
         return;
      }

      $users = $this->getInfoUser($user, $user_id, 'Вы были замучены в беседах Atrium');
      $users['time'] = $parameters[1];
      UserMute::query()->create($users);

      (new EventTelegramMethod())->replyWallComment($chat_id, "Пользователю {$parameters[0]} был выдан мут", $message_id);
   }

   public function kick(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id): void
   {
      (new EventTelegramMethod())->replyWallComment($chat_id, (new EventTelegramMethod())->kickUserFromChat($chat_id, $admin_id), $message_id);
   }

   /**
    * @throws \Exception
    */
   public function addInfo(string $chat_id, int $message_id, array $parameters, ?User $user, int $admin_id, int $user_id): void
   {
      $adminRole = UserRole::query()->where('telegram_id', $admin_id)->first();

      if ($adminRole->role !== 2 || count($parameters) !== 2) {
         (new EventTelegramMethod())->replyWallComment(
            $chat_id,
            "Неверные данные. Пример: /addInfo {text} {type}.",
            $message_id
         );
         return;
      }

      $text = $parameters[0];
      $type = $parameters[1];

      $this->dispatchMessageToChats($chat_id, $message_id, $text, $type);
      (new EventTelegramMethod())->replyWallComment($chat_id, "Сообщения успешно отправлены", $message_id);
   }

   private function dispatchMessageToChats(string $chat_id, int $message_id, string $text, int $type): void
   {
      $chats = Chats::query()->get();

      if ($type === 2 || $type === 4 || $type === 5) {
         foreach ($chats as $chat) {
            $this->sendMessageToChat($chat, $text);
         }
         return;
      }

      $explode = explode('.', (string)$type);
      if (count($explode) === 2) {
         $messanger = $explode[0] == 1 ? 'vkontakte' : 'telegram';
         $chatIndex = $explode[1] - 1;
         $chat = Chats::query()->where('messanger', $messanger)->skip($chatIndex)->first();

         if ($chat) {
            $this->sendMessageToChat($chat, $text);
         } else {
            (new EventTelegramMethod())->replyWallComment($chat_id, 'Беседа не найдена', $message_id);
         }
      }
   }

   private function sendMessageToChat(Chats $chat, string $text): void
   {
      if ($chat->messanger === 'vkontakte') {
         (new EventVkontakteMethod())->sendMessage($chat->chat_id, $text);
      } else {
         (new EventTelegramMethod())->sendMessage($chat->chat_id, $text);
      }
   }

   public function links(string $chat_id, int $message_id, array $parameters, int $user_id, string $text): void
   {
      if (empty($parameters[0])) {
         $links = ChatLink::query()->get()->pluck('text')->implode("\n");
         (new EventTelegramMethod())->replyWallComment($chat_id, "Доступные ссылки:\n$links", $message_id);
      } else {
         ChatLink::query()->create(['text' => $parameters[0]]);
         (new EventTelegramMethod())->replyWallComment($chat_id, "Вы успешно добавили ссылку", $message_id);
      }
   }

   public function words(string $chat_id, int $message_id, array $parameters, int $user_id, string $text): void
   {
      if (empty($parameters[0])) {
         $words = ChatWords::query()->get()->pluck('word')->implode("\n");
         (new EventTelegramMethod())->replyWallComment($chat_id, "Запрещенные слова:\n$words", $message_id);
      } else {
         ChatWords::query()->create(['word' => $parameters[0]]);
         (new EventTelegramMethod())->replyWallComment($chat_id, "Вы успешно запретили слово", $message_id);
      }
   }

   public function questions(string $chat_id, int $message_id, array $parameters, int $user_id, string $text): void
   {
      if (empty($parameters[0])) {
         $questions = ChatQuestion::query()->get()->map(function ($q) {
            return "Вопрос: {$q->question}\nОтвет: {$q->answer}";
         })->implode("\n\n");
         (new EventTelegramMethod())->replyWallComment($chat_id, $questions ?: 'Не заполнено', $message_id);
      } else {
         $text = preg_replace('~/questions\s?~', '', $text);
         Cache::put("admin_{$user_id}", ['step' => 1, 'question' => $text]);
         (new EventTelegramMethod())->replyWallComment($chat_id, "Введите ответ на вопрос", $message_id);
      }
   }

   public function getUserIdByUsername(string $username): ?int
   {
      $response = file_get_contents("https://api.telegram.org/bot" . env('TELEGRAM_TOKEN') . "/getChat?chat_id={$username}");
      $data = json_decode($response, true);

      return $data['result']['id'] ?? null;
   }

   public function getInfoUser(?User $user, int $user_id, string $message): array
   {
      $userUpdate = ['telegram_id' => $user_id];

      if ($user && $user->vkontakte_id) {
         $userUpdate['vkontakte_id'] = $user->vkontakte_id;
         (new EventVkontakteMethod())->sendMessage($user->vkontakte_id, $message);
      }

      return $userUpdate;
   }
}
