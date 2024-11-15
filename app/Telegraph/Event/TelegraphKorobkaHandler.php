<?php

namespace App\Telegraph\Event;

use App\Models\Event\Event;
use App\Models\LogMessage;
use App\Services\LogServices;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use Illuminate\Support\Facades\Storage;

class TelegraphKorobkaHandler extends WebhookHandler
{
    protected WebhookHandler $handler;
    public function __construct(WebhookHandler $handler)
    {
        $this->handler = $handler;
    }

    public function korobka()
    {
        $model = Event::query()->where([['post_id' , $this->handler->message->messageThreadId()], ['status', 0]])->take(1)->orderBy('id', 'desc')->with('korobka')->first();
        $countMessage = LogMessage::query()->where('post_id', $this->handler->message->messageThreadId())->count();

        if($model->korobka->countMessage <= $countMessage + 1) {

        } else {
            $this->handler->chat->photo(Storage::path('telgeram/xfHh6MITPMFnHAcJq7jkBrA1rHJJs5tcXk0FfXaf.png'))->reply($this->handler->messageId)->send();
        }

        LogServices::log($this->handler->message->from()->id(), $this->handler->message->messageThreadId(), 1);
    }
}
