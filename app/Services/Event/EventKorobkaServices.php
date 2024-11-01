<?php

namespace App\Services\Event;

use App\Models\EventKorobka;
use App\Vkontakte\Events\VkontakteEventsServices;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

class EventKorobkaServices extends EventServices
{
   public function create(array $data): void
   {
      $postMessage = str_replace('{twist_word}', $data['word'], $data['text']);
      $event = EventKorobka::query()->create($data);

      $this->sendMessageChannel($postMessage, $data['bg']['postImage']);
      $this->store($event->id, 1, 1, $postMessage);
   }

   public function vkontakte(array $data)
   {
      $event = EventKorobka::query()->create($data);
      $postMessage = str_replace('{twist_word}', $event->word, $event->text);

      $this->store($event->id,
         2,
         1,
         $postMessage,
         $this->sendMessageGroup($event->bg['postImage'], $postMessage)['response']['post_id']
      );
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

   public function sendMessageGroup($filePath, $message)
   {
     $client = new Client();

      $test = $client->get('https://api.vk.com/method/wall.post', [
            'query' => [
               'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
               'message' => $message,
               'attachments' => (new VkontakteEventsServices())->uploadPhoto($filePath),
               'access_token' => env('VKONTAKTE_TOKEN'),
               'v' => env('VKONTAKTE_VERSION'),
            ]
         ]);

      return json_decode($test->getBody()->getContents(), true);
   }
}
