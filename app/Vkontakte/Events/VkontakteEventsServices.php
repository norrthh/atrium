<?php

namespace App\Vkontakte\Events;

use App\Models\Event;
use App\Models\EventPrize;
use App\Models\EventVkontakteLog;
use App\Vkontakte\VkontakteCoinsServices;
use App\Vkontakte\VkontakteMethodServices;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VkontakteEventsServices
{
   public function events(array $data): void
   {
      $findEvent = Event::query()->where('post_id', $data['object']['post_id'])->first();
      if ($findEvent) {
         switch ($findEvent->eventType) {
            case 1:
               (new VkontakteEventKorobkaServices())->korobka($data);
               break;
            default:
               break;
         }
      }
   }

   public function logUser(int $userID, int $postID, int $eventID): void
   {
      EventVkontakteLog::query()->create([
         'user_id' => $userID,
         'post_id' => $postID,
         'event_id' => $eventID
      ]);
   }

   public function replyToComment(int $postId, string $message, int $commentId, $image = null)
   {
      $client = new Client();

      if (!$image) {
         $client->post('https://api.vk.com/method/wall.createComment', [
            'form_params' => [
               'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
               'post_id' => $postId,
               'message' => $message,
               'reply_to_comment' => $commentId,
               'access_token' => env('VKONTAKTE_TOKEN'),
               'v' => env('VKONTAKTE_VERSION'),
            ],
         ]);
      } else {
         $client->post('https://api.vk.com/method/wall.createComment', [
            'form_params' => [
               'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
               'post_id' => $postId,
               'message' => $message,
               'reply_to_comment' => $commentId,
               'attachments' => $this->uploadPhoto($image),
               'access_token' => env('VKONTAKTE_TOKEN'),
               'v' => env('VKONTAKTE_VERSION'),
            ],
         ]);
      }
   }

   private function uploadPhoto(string $imagePath)
   {
      $client = new Client();

      // Получаем данные для загрузки
      $response = $client->post('https://api.vk.com/method/photos.getWallUploadServer', [
         'form_params' => [
            'access_token' => env('VKONTAKTE_USER_TOKEN'),
            'v' => env('VKONTAKTE_VERSION'),
         ],
      ]);

      $uploadServer = json_decode($response->getBody(), true)['response']['upload_url'];

      // Загружаем изображение
      $photoResponse = $client->post($uploadServer, [
         'multipart' => [
            [
               'name' => 'file',
               'contents' => fopen(Storage::path($imagePath), 'r'),
            ],
         ],
      ]);

      $uploadedPhoto = json_decode($photoResponse->getBody(), true);

      // Сохраняем загруженное изображение на стену
      $saveResponse = $client->post('https://api.vk.com/method/photos.saveWallPhoto', [
         'form_params' => [
            'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
            'access_token' => env('VKONTAKTE_USER_TOKEN'),
            'v' => env('VKONTAKTE_VERSION'),
            'server' => $uploadedPhoto['server'],
            'photo' => $uploadedPhoto['photo'],
            'hash' => $uploadedPhoto['hash'],
         ],
      ]);

      $savedPhoto = json_decode($saveResponse->getBody(), true);

      // Возвращаем идентификатор загруженного изображения
      return 'photo' . $savedPhoto['response'][0]['owner_id'] . '_' . $savedPhoto['response'][0]['id'];
   }


   public function checkSubscriptionMailing(int $userId): bool
   {
      return true;
   }

   public function checkSubscriptionGroup(int $userId): bool
   {
      $client = new Client();

      $response = $client->get('https://api.vk.com/method/groups.isMember', [
         'query' => [
            'user_id' => $userId,
            'group_id' => env('VKONTAKTE_GROUP_ID'),
            'access_token' => env('VKONTAKTE_TOKEN'), // Групповой токен
            'v' => env('VKONTAKTE_VERSION'),
         ],
      ]);

      $result = json_decode($response->getBody(), true);

      if (isset($result['response'])) {
         return $result['response'] == 1;
      }

      return false;
   }

   public function calculatePrize(int $totalComments, $baseThreshold): bool
   {
      return true;
      $prizeChance = 0;

      if ($totalComments <= $baseThreshold + 10) {
         $prizeChance = 0.10; // 10% шанс
      }

      if ($totalComments <= $baseThreshold + 20) {
         $prizeChance = 0.20;
      }

      if ($totalComments <= $baseThreshold + 30) {
         $prizeChance = 0.30;
      }

      if ($totalComments <= $baseThreshold + 40) {
         $prizeChance = 0.40;
      }

      if ($totalComments <= $baseThreshold + 50) {
         $prizeChance = 0.50;
      }

      if ($totalComments <= $baseThreshold + 60) {
         $prizeChance = 0.60;
      }
      if ($totalComments <= $baseThreshold + 70) {
         $prizeChance = 0.70;
      }

      if ($totalComments <= $baseThreshold + 80) {
         $prizeChance = 0.80;
      }

      if ($totalComments <= $baseThreshold + 90) {
         $prizeChance = 0.90;
      }

      if ($totalComments <= $baseThreshold + 100) {
         $prizeChance = 1;
      }

      if (mt_rand() / mt_getrandmax() < $prizeChance) {
         return true;
      }

      return false;
   }

   public function winPrize(int $userID, int $eventID, array $prizes): bool
   {
      $prize = [];

      foreach ($prizes as $item) {
         if (!EventPrize::query()->where('user_id', $userID)->where('event_id', $eventID)->whereJsonContains('prize', $item)->first()) {
            $prize = [
               'user_id' => $userID,
               'event_id' => $eventID,
               'prize' => $item
            ];

            app(VkontakteMethodServices::class)->sendMessage($userID, 'Вам выпал приз ' . $item['name'] . ' количество ' . ($item['count'] ?? 1 ). ' штук!');

            break;
         }
      }

      if (count($prize) > 0) {
         EventPrize::query()->create($prize);
         return true;
      }

      return false;
   }

   public function closeComments(int $postId)
   {
      $client = new Client();

      $response = $client->post('https://api.vk.com/method/wall.closeComments', [
         'form_params' => [
            'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'), // ID группы с минусом для группового поста
            'post_id' => $postId, // ID поста, который нужно закрыть для комментариев
            'access_token' => env('VKONTAKTE_TOKEN'),
            'v' => env('VKONTAKTE_VERSION'),
         ],
      ]);

      return json_decode($response->getBody()->getContents(), true);
   }
}
