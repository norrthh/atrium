<?php

namespace App\Services;

/*
 *  typeEvent
 *  1 - korobka
 *
 * */

use App\Core\Method\VkontakteMethod;
use App\Models\Event;

class EventServices
{
    public function eventVkontakte(array $data, string $type): void
    {
        switch ($data['type']) {
            case 3:
            case 4:
                $word = '';

                foreach ($data['attempts'] as $index => $item) {
                    $word .= ($index > 0 ? ', ' : '') . $item['word'];
                }

                $data['word'] = $word;
                break;
            case 5:
            default:
                break;
        }

        $postMessage = str_replace('{twist_word}', $data['word'], $data['text']);

        $data['social_type'] = $type;
        $data['postMessage'] = $postMessage;
        $data['post_id'] = (new VkontakteMethod())->sendWallMessage($data['bg']['postImage'], $postMessage)['response']['post_id'];
        $event = $this->store($data);

        if ($data['type'] == 5) {
            (new EventPromocodeServices())->promocode($event, $data['attempts']);
        }
    }

    public function store(array $data)
    {
        return Event::query()->create([
            'post_id' => $data['post_id'],
            'social_type' => $data['social_type'],
            'eventType' => $data['type'], // тип игры
            'word' => $data['word'], // слово
            'countAttempt' => $data['countAttempt'], // Количество попыток:
            'countMessage' => $data['countMessage'], // Количество попыток до выпадения приза:
            'bg' => $data['bg'], // картинки
            'subscribe' => $data['subscribe'],
            'subscribe_mailing' => $data['subscribe_mailing'],
            'timeForAttempt' => $data['timeForAttempt'], // Время между попытками:
            'cumebackPlayer' => $data['cumebackPlayer'], // Возвращать игроков в конкурс бонусными попытками:
            'text' => $data['text'],
            'states' => $data['states'],
            'attempts' => $data['attempts'],
            'postMessage' => $data['postMessage']
        ]);
    }
}
