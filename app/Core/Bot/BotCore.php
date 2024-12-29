<?php

namespace App\Core\Bot;

use App\Core\EventMethod\EventTelegramMethod;
use App\Core\Message\AdminCommands;
use App\Models\ChatLink;
use App\Models\ChatQuestion;
use App\Models\Chats;
use App\Models\ChatSetting;
use App\Models\ChatWords;
use App\Models\User\User;
use App\Models\UserMute;
use App\Models\UserRole;
use App\Telegraph\Method\UserMessageTelegramMethod;
use App\Telegraph\Method\UserTelegramMethod;
use App\Vkontakte\Method\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BotCore
{
   public function mute(array $user, int $time, string $table, int $user_id): void
   {
      $data = $user;
      $data['time'] = $time;

      $userMute = UserMute::query()->where($table, $user_id)->first();

      if ($userMute) {
         UserMute::query()->where($table, $user_id)->update($data);
      } else {
         UserMute::query()->create($data);
      }
   }

   public function akick(?User $user, string $methodCall, int $user_id): void
   {
      Log::info(
         'methodCall: ' . $methodCall . ' user_id: ' . $user_id
      );
      foreach (Chats::query()->get() as $item) {
         if ($item->messanger == $methodCall) {
            Log::info(
               'methodCall: ' . $methodCall . ' chat_id: ' . $item->chat_id . "interation: " . $item->messanger
            );
            if ($methodCall == 'telegram') {
               (new UserTelegramMethod())->kickUserFromChat($item->chat_id, $user_id);
            } else {
               (new \App\Vkontakte\Method\User(chat_id: $item->chat_id))->kickUserFromChat($user_id);
            }
         } elseif ($user) {
            if ($item->messanger == 'vkontakte' and $user->vkontakte_id) {
               (new \App\Vkontakte\Method\User(chat_id: $item->chat_id))->kickUserFromChat($user->vkontakte_id);
            }

            if ($item->messanger == 'telegram' and $user->telegram_id) {
               (new UserTelegramMethod())->kickUserFromChat($item->chat_id, $user->telegram_id);
            }
         }
      }
   }

   public function analyzeText(string $text): array
   {
      $chatLinks = ChatLink::query()->get();
      $chatWords = ChatWords::query()->get();
      $chatQuestions = ChatQuestion::query()->get();

      foreach ($chatLinks as $chatLink) {
         // Приводим обе строки к нижнему регистру и убираем лишние пробелы и невидимые символы
         $normalizedText = preg_replace('/\s+/', ' ', mb_strtolower(trim($text), 'UTF-8')); // mb_strtolower для корректной работы с русскими буквами
         $normalizedLinkText = preg_replace('/\s+/', ' ', mb_strtolower(trim($chatLink->text), 'UTF-8'));

         if (str_contains($normalizedText, $normalizedLinkText)) {
            return [
               'status' => true,
               'type' => 'links',
            ];
         }
      }

      foreach ($chatWords as $chatWord) {
         $normalizedText = preg_replace('/\s+/', ' ', mb_strtolower(trim($text), 'UTF-8')); // mb_strtolower для русских символов
         $normalizedWord = preg_replace('/\s+/', ' ', mb_strtolower(trim($chatWord->word), 'UTF-8')); // аналогично для слова

         if (str_contains($normalizedText, $normalizedWord)) {
            return [
               'status' => true,
               'type' => 'words',
            ];
         }
      }

      foreach ($chatQuestions as $chatQuestion) {
         $normalizedText = preg_replace('/\s+/', ' ', mb_strtolower(trim($text), 'UTF-8')); // mb_strtolower для русских символов
         $normalizedQuestion = preg_replace('/\s+/', ' ', mb_strtolower(trim($chatQuestion->question), 'UTF-8')); // аналогично для вопроса

         if (str_contains($normalizedText, $normalizedQuestion)) {
            return [
               'status' => true,
               'answer' => $chatQuestion->answer,
            ];
         }
      }

      return [
         'status' => false,
      ];
   }

   public function checkMute(int $user_id, string $column): bool
   {
      $mute = UserMute::query()->where($column, $user_id)->first();
      if ($mute) {
         $lastCollected = Carbon::parse($mute->updated_at)->setTimezone('Europe/Moscow');
         if ($lastCollected->diffInMinutes(Carbon::now('Europe/Moscow')) > $mute->time) {
            UserMute::query()->where($column, $user_id)->delete();
            return true;
         }
         return false;
      }
      return true;
   }

   public function filterMessage(string $text, string $chat_id, int $message_id, int $user_id, string $column): void
   {
      if ($this->checkMute($user_id, $column)) {
         $analyzeText = $this->analyzeText($text);
         Log::info('analyzeText: ' . print_r($analyzeText, 1));
         if (isset($analyzeText['status']) && $analyzeText['status']) {
//            if (isset($analyzeText['type']) && $analyzeText['type'] == 'links' or isset($analyzeText['type']) && $analyzeText['type'] == 'words') {
//               if (!UserRole::query()->where($column, $user_id)->exists()) {
//                  $this->akick(User::query()->where($column, $user_id)->first(), ($column == 'telegram_id' ? 'telegram' : 'vkontakte'), $user_id);
//               }
//            }
            if (isset($analyzeText['answer'])) {
               if ($column == 'telegram_id') {
                  (new UserMessageTelegramMethod())->replyWallComment($chat_id, $analyzeText['answer'], $message_id);
               } else {
                  (new Message())->sendAPIMessage(userId: $chat_id, message: $analyzeText['answer'], conversation_message_id: $message_id);
               }
            }
         }
      } else {
         if ($column == 'telegram_id') {
            (new UserMessageTelegramMethod())->deleteMessage($chat_id, $message_id);
         } else {
            (new Message())->deleteMessage($message_id, $chat_id);
         }
      }
   }

   public function addRole(int $user_id, int $role, string $table): void
   {
      $user = User::query()->where($table, $user_id)->first();

      $roleData = [
         $table => $user_id,
         'role' => $role
      ];

      if ($user and ($table == 'telegram_id' ? $user->telegram_id : $user->vkontakte_id)) {
         $roleData['telegram_id'] = $user->telegram_id;
      }

      UserRole::query()->updateOrCreate([$table => $user_id], $roleData);
   }

   public function addInfo(string $message): ?string
   {
      $info = $this->parseFirstArg($message);

      Log::info('addInfo: ' . print_r($info, 1));

      if ($info['first_arg'] != '' and $info['remaining'] != '') {
         $text = $info['remaining'];
         $type = $info['first_arg'];

         $vkMethod = new Message();

         if ($type != 2 and $type != 4 and $type != 5) {
            $explode = explode('.', $type);
            if (count($explode) == 2) {
               if ($explode[0] == 1) {
                  $chats = Chats::query()->where('messanger', 'vkontakte')->get();
                  $chats = $chats[$explode[1] - 1];

                  if ($chats) {
                     $vkMethod->sendAPIMessage(
                        userId: $chats->chat_id,
                        message: $text,
                     );
                  } else {
                     return 'Беседа не найдена';
                  }
               }

               if ($explode[0] == 3) {
                  $chats = Chats::query()->where('messanger', 'telegram')->get();
                  $chats = $chats[$explode[1] - 1];
                  if ($chats) {
                     (new EventTelegramMethod())->sendMessage($chats->chat_id, $text);
                  } else {
                     return 'Беседа не найдена';
                  }
               }
            } else {
               return "
                     Вы ввели неверные данные, проверьте пробелы. Пример: /addInfo {type} {text}.
                     \n{type}:\n1 - Одна беседа Вконтакнте\n1.1 - Первая беседа вконтакте (и тд)\n2 - Все беседы ВК\n3.1 - Первая беседа Telegram (и тд)\n4 - Все беседы Telegram\n5 - Все беседы
                  ";
            }
         } elseif ($type == 2) {
            foreach (Chats::query()->where('messanger', 'vkontakte')->get() as $chat) {
               $vkMethod->sendAPIMessage(
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
                  $vkMethod->sendAPIMessage(
                     userId: $chat->chat_id,
                     message: $text,
                  );
               } else {
                  (new EventTelegramMethod())->sendMessage($chat->chat_id, $text);
               }
            }
         } else {
            return 'Введите валидные аргументы';
         }

         return "Ваше сообщение отправлено";
      }

      return "
         Вы ввели неверные данные, проверьте пробелы. Пример: /addInfo {type} {text}.
         \n{type}:\n1.1 - Первая беседа вконтакте (и тд)\n2 - Все беседы ВК\n3 - Одна беседа Telegram\n3.1 - Первая беседа Telegram (и тд)\n4 - Все беседы Telegram\n5 - Все беседы
      ";
   }

   public function newm(int $chat_id, ?string $welcomeMessage = null): string
   {
      if (!$welcomeMessage) {
         return "Введите аргумент для заполнения текста /newm text";
      }

      if (mb_strlen($welcomeMessage) > 65536) {
         echo "Нельзя ввести более 65,536 символов";
      }

      ChatSetting::query()->updateOrCreate(['chat_id' => $chat_id], ['welcome_message' => $welcomeMessage]);
      return "Вы успешно обновили приветственное сообщение";
   }

   protected function parseFirstArg(string $input = ''): array
   {
      $result = [
         'first_arg' => null,
         'remaining' => null,
      ];

      if ($input and preg_match('/^\s*(\S+)\s+(.*)$/', $input, $matches)) {
         $result['first_arg'] = $matches[1]; // Первый аргумент (до первого пробела)
         $result['remaining'] = $matches[2]; // Остальная часть строки
      }

      return $result;
   }
}
