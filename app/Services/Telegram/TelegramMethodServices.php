<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;

class TelegramMethodServices
{
    public function getChatMember($userId, $chatId = null)
    {
        if (!$chatId) {
            $chatId = env('TELEGRAM_CHANNEL_ID');
        }

        return Http::post("https://api.telegram.org/bot" . $chatId . "/getChatMember", [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ])->json();
    }
}
