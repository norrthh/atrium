<?php

namespace App\Services\Event;

use App\Models\EventKorobka;
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

   public function vkontakte()
   {
      $event = EventKorobka::query()->orderBy('id', 'desc')->first();
//        return $event->bg['postImage'];
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
      // 1. Получаем URL для загрузки файла
      $uploadServerResponse = $client->get('https://api.vk.com/method/photos.getWallUploadServer', [
         'query' => [
            'access_token' => env('VKONTAKTE_USER_TOKEN'),
            'v' => env('VKONTAKTE_VERSION'),
            'owner_id' => env('VKONTAKTE_GROUP_ID'),
         ],
      ]);

      $uploadServer = json_decode($uploadServerResponse->getBody(), true)['response']['upload_url'];

      // 2. Загружаем файл на полученный URL
      $uploadResponse = $client->post($uploadServer, [
         'multipart' => [
            [
               'name' => 'photo',
               'contents' => fopen(Storage::path($filePath), 'r'), // Открываем файл для чтения
            ],
         ],
      ]);

      $uploadResult = json_decode($uploadResponse->getBody(), true);

      // 3. Сохраняем фото на сервере ВКонтакте и получаем attachment_id
      $savePhotoResponse = $client->get('https://api.vk.com/method/photos.saveWallPhoto', [
         'form_params' => [
            'group_id' => env('VKONTAKTE_GROUP_ID'),
            'access_token' => env('VKONTAKTE_USER_TOKEN'),
            'v' => env('VKONTAKTE_VERSION'),
            'photo' => $uploadResult['photo'],
            'server' => $uploadResult['server'],
            'hash' => $uploadResult['hash'],
         ],
      ]);

      $photoData = json_decode($savePhotoResponse->getBody(), true)['response'][0];

      // Формируем attachment_id для использования в wall.post
      $attachmentId = 'photo' . $photoData['owner_id'] . '_' . $photoData['id'];

      // 4. Публикуем пост на стене с загруженным изображением
      $test = $client->get('https://api.vk.com/method/wall.post',
         [
            'form_params' => [
               'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
               'message' => $message,
               'attachments' => $attachmentId,
               'access_token' => env('VKONTAKTE_TOKEN'),
               'v' => env('VKONTAKTE_VERSION'),
            ]
         ]);

      return json_decode($test->getBody()->getContents(), true);
   }
}
