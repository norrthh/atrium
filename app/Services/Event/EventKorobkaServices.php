<?php

namespace App\Services\Event;

use App\Models\EventKorobka;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Storage;

class EventKorobkaServices extends EventServices
{
    public function create(array $data)
    {
        $postMessage = str_replace('{twist_word}', $data['word'], $data['text']);
        $event = EventKorobka::query()->create($data);
        $this->sendMessageChannel($postMessage, $data['bg']['postImage']);
        $this->store($event->id, 1, 1, $postMessage);
    }

    protected function sendMessageChannel(string $message, string $url)
    {
        return TelegraphChat::query()
            ->where('chat_id', env('TELEGRAM_CHANNEL'))
            ->first()
            ->photo(Storage::path($url))
            ->html($message)
            ->send();
    }
}
