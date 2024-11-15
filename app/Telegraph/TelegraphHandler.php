<?php

namespace App\Telegraph;

use App\Models\Event\Event;
use App\Telegraph\Event\TelegraphKorobkaHandler;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Stringable;

class TelegraphHandler extends WebhookHandler
{
    public function start()
    {
        if (env('TELEGRAM_CHANNEL') != $this->chat->chat_id) {
            $this->chat->message('test')->send();
        }
    }

    public function handleChatMessage(Stringable $text): void
    {
        if (env('TELEGRAM_CHANNEL') == $this->chat->chat_id) {
            $model = Event::query()->where([['postMessage', $text], ['type', 1], ['post_id', null], ['status', 0]])->take(1)->orderBy('id', 'desc');
            if ($model->exists()) {
                $model->update(['post_id' => $this->messageId]);
            }
        }

        if (env('TELEGRAM_CHANNEL_GROUP') == $this->chat->chat_id) {
            $model = Event::query()->where([['post_id' , $this->message->messageThreadId()], ['status', 0]])->take(1)->orderBy('id', 'desc')->first();
            switch ($model->type) {
                case 1:
                    (new TelegraphKorobkaHandler($this))->korobka();
                    break;
                default:
                    break;
            }
        }
    }
}
