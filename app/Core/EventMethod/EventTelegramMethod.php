<?php

namespace App\Core\EventMethod;

use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EventTelegramMethod implements EventSocialMethod
{
   public function sendMessage(int $userId, string $message): void
   {
      Log::info(print_r([$userId, $message], 1));
      TelegraphChat::query()->where('chat_id', $userId)->first()->message($message)->send();
   }

   /**
    * @throws ConnectionException
    * @throws \Exception
    */
   public function sendWallMessage($filePath, $message)
   {
      $telegramBotToken = env('TELEGRAM_TOKEN');
      $chatId = env('TELEGRAM_CHANNEL_ID');

      $url = "https://api.telegram.org/bot{$telegramBotToken}/sendPhoto";

      $response = Http::attach(
         'photo', fopen(Storage::disk('public')->path($filePath), 'r'), basename($filePath)
      )->post($url, [
         'chat_id' => $chatId,
         'caption' => $message,
      ]);

      if ($response->failed()) {
         throw new \Exception('Ошибка Telegram API: ' . $response->body());
      }

      return $response->json();
   }

   public function uploadPhoto(string $imagePath)
   {

   }

   public function closeWallComments(int $postId, int $user_id = null)
   {
      $telegramBotToken = env('TELEGRAM_BOT_TOKEN');
      $url = "https://api.telegram.org/bot{$telegramBotToken}/restrictChatMember";

      $response = Http::post($url, [
         'chat_id' => $postId,
         'user_id' => $user_id,
         'chatPermissions' => ['can_send_messages' => false, 'can_send_media_messages' => false, 'can_send_polls' => false, 'can_send_other_messages' => false],
         'until_date' => time() + 3600
      ]);

      if ($response->failed()) {
         throw new \Exception('Ошибка Telegram API: ' . $response->body());
      }

      return $response->json();
   }

   /**
    * @throws \Exception
    */
   public function replyWallComment(int $postId, string $message, int $commentId, $image = null)
   {
      $telegramBotToken = env('TELEGRAM_TOKEN');

      Log::info(print_r([
         'chat_id' => $postId,
         'text' => $message,
         'reply_to_message_id' => $commentId,
         'image' => $image,
      ], 1));

      if ($image) {
         $url = "https://api.telegram.org/bot{$telegramBotToken}/sendPhoto";
         $response =  Http::attach(
            'photo', // Поле для отправки файла
            fopen(Storage::disk('public')->path($image), 'r'), // Открываем файл
            basename($image) // Имя файла
         )->post($url, [
            'chat_id' => $postId, // ID получателя
            'caption' => $message, // Текст сообщения
            'reply_to_message_id' => $commentId,
         ]);
      } else {
         $url = "https://api.telegram.org/bot{$telegramBotToken}/sendMessage";
         $response = Http::post($url, [
            'chat_id' => $postId,
            'text' => $message,
            'reply_to_message_id' => $commentId,
         ]);
      }

      if ($response->failed()) {
         Log::error("Telegram API error: " . $response->body());
         throw new \Exception("Telegram API error: " . $response->body());
      }

      return $response->json();
   }

   public function checkSubscriptionGroup(int $userId): true
   {
      return true;
   }

   public function checkSubscriptionMailing(int $userId): true
   {
      return true;
   }

   public function checkVkDonutSubscription(int $userId): true
   {
      return true;
   }
}
