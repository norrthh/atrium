<?php

namespace App\Core\EventMethod;

use App\Telegraph\Method\UserMessageTelegramMethod;
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

   /**
    * @throws ConnectionException
    */
   public function replyWallComment(int $postId, string $message, int $commentId, $image = null, $parseMode = '')
   {
      (new UserMessageTelegramMethod())->replyWallComment($postId, $message, $commentId, $image, $parseMode);
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
