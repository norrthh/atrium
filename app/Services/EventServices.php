<?php

namespace App\Services;

/*
 *  typeEvent
 *  1 - korobka
 *
 * */

use App\Models\Event;

class EventServices
{
   public function eventVkontakte(array $data, string $type): void
   {
      $postMessage = $data['message']['text'];
      $data[] = [
         'social_type' => $type,
         'postMessage' => $postMessage,
      ];

      $this->store($data);
   }

   public function store(array $data): void
   {
      Event::query()->create([
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