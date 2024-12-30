<?php

namespace App\Services;

use App\Core\Bot\BotCore;
use App\Models\ChatLink;
use App\Models\ChatQuestion;
use App\Models\ChatWords;
use App\Models\User\User;
use App\Models\UserMute;
use App\Models\UserRole;
use App\Models\UserViolation;
use App\Telegraph\Method\UserMessageTelegramMethod;
use App\Telegraph\Method\UserTelegramMethod;
use App\Vkontakte\Method\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BotFilterMessageServices
{
   public function filterMessage(string $text, string $chat_id, int $message_id, int $user_id, string $column, bool $sticker = false): void
   {
      $columnTable = $column == 'telegram_id' ? 'telegram' : 'vkontakte';
      if ($this->checkMute($user_id, $column)) {
         if ($text) {
            if (!$sticker) {
               $analyzeText = $this->analyzeText($text);
            } else {
               $analyzeText = [
                  'status' => true,
                  'type' => 'sticker'
               ];
            }

            Log::info('analyze' . print_r($analyzeText, true));

            if (isset($analyzeText['status']) && $analyzeText['status']) {
               if (!UserRole::query()->where($column, $user_id)->exists()) {
                  if (in_array($analyzeText['type'], ['sticker', 'links', 'words', 'tag'])) {
                     $violations = $this->updateUserViolations($user_id, $column);

                     $userUpom = $this->getUserInfo($user_id, $columnTable);

                     if ($violations->violations <= 3) {
                        $this->sendMessage($chat_id, $this->getViolationError($analyzeText['type'], $userUpom, $violations->violations), $column);
                     }  elseif ($violations->violations > 3) {
                        $this->sendMessage($chat_id, "Пользователь {$userUpom} был исключён за нарушение правила чата", $column);
                        $this->kickUser($user_id, $column);
                        UserViolation::query()->where('id', $violations->id)->delete();
                     }

                     $this->deleteMessage($message_id, $chat_id, $column);
                  }
               }
               if (isset($analyzeText['answer'])) {
                  $this->sendMessage($chat_id, $analyzeText['answer'], $message_id);
               }
            }
         }
      } else {
         $this->deleteMessage($message_id, $chat_id, $column);
      }
   }

   public function analyzeText(?string $text = null): array
   {
      $chatWords = ChatWords::query()->get();
      $chatQuestions = ChatQuestion::query()->get();

      $normalizedText = mb_strtolower(trim($text), 'UTF-8');

      Log::debug('text: ' . $normalizedText);
      if (str_contains($normalizedText, '@')) {
         return [
            'status' => true,
            'type' => 'tag'
         ];
      }

      $url = $this->normalizeUrl($normalizedText);
      if ($url['filterVar']) {
         if (ChatLink::query()->where('text', 'LIKE', "%{$url['host']}%")->exists()) {
            return [
               'status' => false,
            ];
         } else {
            return [
               'status' => true,
               'type' => 'links'
            ];
         }
      }

      foreach ($chatWords as $chatWord) {
         $normalizedWord = preg_replace('/\s+/', ' ', mb_strtolower(trim($chatWord->word), 'UTF-8')); // аналогично для слова

         if (str_contains($normalizedText, $normalizedWord)) {
            return [
               'status' => true,
               'type' => 'words',
            ];
         }
      }
      foreach ($chatQuestions as $chatQuestion) {
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

   protected function getUserInfo(int $user_ID, string $column): string
   {
      $string = '';

      if ($column == 'telegram') {
         $user = (new UserTelegramMethod())->getUserId($user_ID);
         $string = "@" . $user['username'];
      } else {
         $string = '@id' . $user_ID;
      }

      return $string;
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
   private function updateUserViolations(int $user_id, string $column): UserViolation
   {
      $violation = UserViolation::query()->firstOrCreate([$column => $user_id]);
      $violation->increment('violations');
      return $violation;
   }
   private function deleteMessage(int $message_id, string $chat_id, string $column): void
   {
      if ($column === 'telegram_id') {
         (new UserMessageTelegramMethod())->deleteMessage($chat_id, $message_id);
      } else {
         (new Message())->deleteMessage($message_id, $chat_id);
      }
   }
   private function sendMessage(string $chat_id, string $message, string $column): void
   {
      if ($column === 'telegram_id') {
         (new UserMessageTelegramMethod())->replyWallComment($chat_id, $message);
      } else {
         (new Message())->sendAPIMessage(userId: $chat_id, message: $message);
      }
   }
   private function kickUser(int $user_id, string $column): void
   {
      if (!UserRole::query()->where($column, $user_id)->exists()) {
         (new BotCore())->akick(User::query()->where($column, $user_id)->first(), ($column == 'telegram_id' ? 'telegram' : 'vkontakte'), $user_id);
      }
   }
   protected function normalizeUrl(string $url): array
   {
      // Если протокол не указан, добавляем https://
      if (!parse_url($url, PHP_URL_SCHEME)) {
         $url = 'https://' . $url;
      }

      // Разбираем URL
      $parsedUrl = parse_url($url);
      $host = $parsedUrl['host'] ?? $parsedUrl['path'];

      // Убираем 'www.' из начала хоста, если оно есть
      $normalizedHost = preg_replace('/^www\./', '', $host);

      // Нормализуем URL с https://
      $normalizedUrl = 'https://' . $normalizedHost;

      // Проверка на валидность домена и расширения (.com, .ru и т.д.)
      $validExtensions = ['.com', '.ru', '.org', '.net', '.io']; // Можно добавить нужные расширения
      $isValidDomain = false;

      // Проверяем, заканчивается ли домен на одно из нужных расширений
      foreach ($validExtensions as $extension) {
         if (str_ends_with($normalizedHost, $extension)) {
            $isValidDomain = true;
            break;
         }
      }

      // Логирование
      Log::info('normalizedUrl: ' . $normalizedUrl);
      Log::info('normalizedHost: ' . $normalizedHost);
      Log::info('filterVar: ' . ($isValidDomain ? 'Yes' : 'No'));

      return [
         'filterVar' => $isValidDomain,  // Возвращаем информацию о том, валиден ли домен
         'host' => $normalizedHost,
      ];
   }
   protected function getViolationError(string $type, string $userUpom, string|int $violations): string
   {
      if ($violations < 3) {
         $error = "Пользователь {$userUpom}, вы нарушаете правила чата.";
      } else {
         $error = "Пользователь {$userUpom}, это последнее предупреждение.";
      }

      switch ($type) {
         case 'sticker':
            $error .= "В данной беседе запрещено отправлять стикеры.";
            break;
            case 'links':
               $error .= "В данной беседе запрещено отправлять ссылки.";
               break;
            case 'words':
               $error .= "В данной беседе запрещено отправлять запрещенные слова.";
               break;
            case 'tag':
               $error .= "В данной беседе запрещено упоминать кого-то.";
               break;
      }

      if ($violations < 3) {
         $error .= "Это {$violations} предупреждение.";
      } else {
         $error .= "Это последнее предупреждение.";
      }

      return $error;
   }
}
