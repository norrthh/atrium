<?php

namespace App\Telegraph\Method;

use GuzzleHttp\Client;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserMessageTelegramMethod
{
   /**
    * @throws ConnectionException
    * @throws \Exception
    */
   public function replyWallComment(int $postId, string $message, int $commentId = null, $image = null, string $parseMode = null): void
   {
      $telegramBotToken = env('TELEGRAM_TOKEN');

      $data = [
         'chat_id' => $postId,
         'disable_web_page_preview' => true,
      ];

      if ($commentId) {
         $data['reply_to_message_id'] = $commentId;
      }

      if ($parseMode) {
         $data['parse_mode'] = $parseMode;
      }

      if ($image) {
         $data['caption'] = $message;
         $response =  Http::attach(
            'photo', fopen(Storage::disk('public')->path($image), 'r'), basename($image)
         )->post("https://api.telegram.org/bot{$telegramBotToken}/sendPhoto", $data);
      } else {
         $data['text'] = $message;
         $response = Http::post("https://api.telegram.org/bot{$telegramBotToken}/sendMessage", $data);
      }

      if ($response->failed()) {
         Log::error("Telegram API error: " . $response->body());
         throw new \Exception("Telegram API error: " . $response->body());
      }
   }

   public function deleteMessage(int $chatId, int $messageId): void
   {
      Http::post("https://api.telegram.org/bot". env('TELEGRAM_TOKEN') ."/deleteMessage", [
         'chat_id' => $chatId,
         'message_id' => $messageId
      ]);
   }
}
