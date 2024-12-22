<?php

namespace App\Telegraph\Method;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserMessageTelegramMethod
{
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

   public function deleteMessage(int $chatId, int $messageId): void
   {
      $client = new Client([
         'base_uri' => "https://api.telegram.org/bot" . env('TELEGRAM_TOKEN')
      ]);

      $client->post('deleteMessage', [
         'json' => [
            'chat_id' => $chatId,
            'message_id' => $messageId
         ]
      ]);
   }
}
