<?php

namespace App\Services;

/*
 *  typeEvent
 *  1 - korobka
 *
 * */

use App\Core\Method\VkontakteMethod;
use App\Models\Event;
use App\Models\EventPrize;

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
      $data['status'] = $data['type'] == 5 ? $data['typeActivate'] : 0;
      $event = $this->store($data);

      if ($data['type'] == 5) {
         (new EventPromocodeServices())->promocode($event, $data['attempts']);
      } else {
         $this->storePrizes($data['attempts'], $event->id, $data['word']);
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
         'postMessage' => $data['postMessage'],
         'status' => $data['status'],
         'like' => $data['like'],
         'repost' => $data['repost'],
      ]);
   }

   protected function storePrizes(array $prizes, int $event_id, string $word): void
   {
      foreach ($prizes as $prize) {
         EventPrize::query()->create([
            'event_id' => $event_id,
            'withdraw_items_id' => $prize['name']['id'],
            'count_prize' => $prize['count'],
            'word' => $prize['word'] ?? $word
         ]);
      }
   }
}
