<?php

namespace App\Services\Event;

use App\Models\Event;

/*
 *  typeEvent
 *  1 - korobka
 *
 * */

class EventServices
{
    public function store(int $eventID, int $type, int $typeEvent, string $message, $post_id = null): void
    {
        Event::query()->create([
            'event_id' => $eventID,
            'type' => $type,
            'eventType' => $typeEvent,
            'postMessage' => $message,
            'post_id' => $post_id
        ]);
    }
}
