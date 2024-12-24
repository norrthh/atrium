<?php

namespace App\Core\Bot;

use App\Models\ChatLink;
use App\Models\ChatQuestion;
use App\Models\Chats;
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
         Log::info('analyzeText: ' . json_encode($analyzeText));
         if (isset($analyzeText['status']) && $analyzeText['status'] and !UserRole::query()->where('telegram_id', $user_id)->exists()) {
            if (isset($analyzeText['type']) && $analyzeText['type'] == 'links' or isset($analyzeText['type']) && $analyzeText['type'] == 'words') {
               $this->akick(User::query()->where($column, $user_id)->first(), ($column == 'telegram_id' ? 'telegram' : 'vkontakte'), $user_id);
            }
            if (isset($analyzeText['answer'])) {
               if ($column == 'telegram_id') {
                  (new UserMessageTelegramMethod())->replyWallComment($chat_id, $analyzeText['answer'], $message_id);
               } else {
                  (new Message())->sendAPIMessage(userId: $chat_id, message: $analyzeText['answer'], conversation_message_id: $message_id);
               }
            }
         }
      } else {
//         Log::info('user_Id:' . $user_id . 'peer_id:' . $chat_id);
         if ($column == 'telegram_id') {
            (new UserMessageTelegramMethod())->deleteMessage($chat_id, $message_id);
         } else {
            (new Message())->deleteMessage($message_id, $chat_id);
         }
      }
   }
}
