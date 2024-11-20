<?php

namespace App\Telegraph;

use App\Models\Event\Event;
use App\Telegraph\Event\TelegraphKorobkaHandler;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Stringable;

class TelegraphHandler extends WebhookHandler
{
    public function start()
    {
        if (env('TELEGRAM_CHANNEL') != $this->chat->chat_id) {
            $this->chat->message('test')->keyboard(
               Keyboard::make()->button('Перейти в мини приложение')->webApp('https://telegram.atrium-bot.ru/')
            )->send();
        }
    }

    public function handleChatMessage(Stringable $text): void
    {

    }
}
