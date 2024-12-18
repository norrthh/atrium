<?php

namespace App\Services;

use App\Core\EventMethod\EventTelegramMethod;
use App\Core\EventMethod\EventVkontakteMethod;
use App\Models\Event\Event;
use App\Models\Event\EventPrize;
use Illuminate\Http\Client\ConnectionException;

class EventServices
{
   /**
    * @throws ConnectionException
    */
   public function eventVkontakte(array $data, string $type): void
   {
      $postMessage = str_replace('{twist_word}', $data['word'], $data['text']);
      $data['social_type'] = $data['social'] == 1 ? 'telegram' : 'vk';
      $data['postMessage'] = $postMessage;
      $data['post_id'] =
         $data['social'] == 2
            ? (new EventTelegramMethod())->sendWallMessage($data['bg']['postImage'], $postMessage)['result']['message_id']
            : (new EventVkontakteMethod())->sendWallMessage($data['bg']['postImage'], $postMessage)['response']['post_id'];
      $data['status'] = $data['type'] == 5 ? $data['typeActivate'] : 0;
      $event = $this->store($data);

      $this->storePrizes($data['attempts'], $event->id, $data['word'] ?? '');
   }

   public function store(array $data)
   {
      return Event::query()->create([
         'post_id' => $data['post_id'],
         'social_type' => $data['social_type'],
         'eventType' => $data['type'], // тип игры
         'word' => !empty($data['word']) ? $data['word'] : $this->getWord($data['attempts']), // слово
         'countAttempt' => $data['countAttempt'] ?? 0, // Количество попыток:
         'countMessage' => $data['countMessage'] ?? 0, // Количество попыток до выпадения приза:
         'bg' => $data['bg'] ?? ['bg1.png'], // картинки
         'subscribe' => $data['subscribe'] ?? 'not_required',
         'subscribe_mailing' => $data['subscribe_mailing'] ?? 'not_required',
         'timeForAttempt' => $data['timeForAttempt'] ?? 0, // Время между попытками:
         'cumebackPlayer' => $data['cumebackPlayer'] ?? 0, // Возвращать игроков в конкурс бонусными попытками:
         'text' => $data['text'],
         'states' => $data['states'] ?? [],
         'attempts' => $data['attempts'] ?? [],
         'postMessage' => $data['postMessage'] ?? '',
         'status' => $data['status'] ?? 0,
         'like' => $data['like'] ?? [],
         'repost' => $data['repost'] ?? [],
      ]);
   }

   protected function storePrizes(array $prizes, int $event_id, $word): void
   {
      foreach ($prizes as $prize) {
         if (isset($prize['name'])) {
            EventPrize::query()->create([
               'event_id' => $event_id,
               'items_id' => $prize['name']['id'],
               'count_prize' => $prize['count'],
               'word' => !empty($prize['word'])
                  ? $prize['word']
                  : (!empty($prize['number'])
                     ? $prize['number']
                     : $word),

            ]);
         }
      }
   }

   public function getWord(array $prizes): string
   {
      $words = [];

      foreach ($prizes as $prize) {
         if (isset($prize['name'])) {
            $words[] = !empty($prize['word'])
               ? $prize['word']
               : $prize['number'];
         }
      }
      return implode(', ', $words);
   }
}
