<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class TelegramMethodServices
{
   public function getChatMember($userId)
   {
      return Http::post("https://api.telegram.org/bot". env('TELEGRAM_TOKEN') ."/getChatMember", [
         'chat_id' => env('TELEGRAM_CHANNEL_ID'),
         'user_id' => $userId,
      ])->json();
   }
}
