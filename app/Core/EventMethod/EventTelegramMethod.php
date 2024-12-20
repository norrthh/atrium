<?php

namespace App\Core\EventMethod;

use DefStudio\Telegraph\Models\TelegraphChat;
use GuzzleHttp\Client;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EventTelegramMethod implements EventSocialMethod
{
   public function sendMessage(int $userId, string $message): void
   {
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

   public function closeWallComments(int $postId, int $user_id = null):void
   {

   }

   public function replyWallComment(int $postId, string $message, int $commentId, $image = null)
   {
      $telegramBotToken = env('TELEGRAM_TOKEN');

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

   public function kickUserFromChat(int $chatId, int $userId): string
   {
      $client = new Client(['base_uri' => 'https://api.telegram.org']);
      $response = $client->post('/bot' . env('TELEGRAM_TOKEN') . '/kickChatMember', [
         'form_params' => [
            'chat_id' => $chatId,
            'user_id' => $userId,
         ],
      ]);

      if ($response->getStatusCode() !== 200) {
         return 'Ошибка при исключении пользователя из беседы' . $response->getBody();
      }

      return 'Вы успешно исключили пользователя';
   }

   public function deleteMessage(int $chatId, int $messageId): void
   {
      $client = new Client([
         'base_uri' => "https://api.telegram.org/bot" . env('TELEGRAM_TOKEN')
      ]);

      $response = $client->post('deleteMessage', [
         'json' => [
            'chat_id' => $chatId,
            'message_id' => $messageId
         ]
      ]);
   }

}
