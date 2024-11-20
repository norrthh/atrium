<?php

namespace App\Core\Method;

use GuzzleHttp\Client;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class VkontakteMethod implements SocialMethod
{
   protected $vkKey;
   protected $vkVersion;

   public function __construct()
   {
      $this->vkKey = env('VKONTAKTE_TOKEN');
      $this->vkVersion = env('VKONTAKTE_VERSION');
   }

   public function sendMessage(int $userId, string $message): Response
   {
      return Http::get('https://api.vk.com/method/messages.send', [
         'user_id' => $userId,
         'message' => $message,
         'random_id' => rand(),
         'access_token' => $this->vkKey,
         'v' => $this->vkVersion,
      ]);
   }

   public function sendWallMessage($filePath, $message)
   {
      $client = new Client();

      $test = $client->get('https://api.vk.com/method/wall.post', [
         'query' => [
            'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
            'message' => $message,
            'attachments' => $this->uploadPhoto($filePath),
            'access_token' => env('VKONTAKTE_TOKEN'),
            'v' => env('VKONTAKTE_VERSION'),
         ]
      ]);

      return json_decode($test->getBody()->getContents(), true);
   }

   public function uploadPhoto(string $imagePath): string
   {
      $client = new Client();

      $response = $client->post('https://api.vk.com/method/photos.getWallUploadServer', [
         'form_params' => [
            'access_token' => env('VKONTAKTE_USER_TOKEN'),
            'v' => env('VKONTAKTE_VERSION'),
         ],
      ]);

      $uploadServer = json_decode($response->getBody(), true)['response']['upload_url'];

      $photoResponse = $client->post($uploadServer, [
         'multipart' => [
            [
               'name' => 'file',
               'contents' => fopen(Storage::disk('public')->path($imagePath), 'r'),
            ],
         ],
      ]);

      $uploadedPhoto = json_decode($photoResponse->getBody(), true);
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

      return 'photo' . $savedPhoto['response'][0]['owner_id'] . '_' . $savedPhoto['response'][0]['id'];
   }

   public function closeWallComments(int $postId)
   {
      $client = new Client();

      $response = $client->post('https://api.vk.com/method/wall.closeComments', [
         'form_params' => [
            'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
            'post_id' => $postId,
            'access_token' => env('VKONTAKTE_TOKEN'),
            'v' => env('VKONTAKTE_VERSION'),
         ],
      ]);

      return json_decode($response->getBody()->getContents(), true);
   }

   public function replyWallComment(int $postId, string $message, int $commentId, $image = null): void
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
               'attachments' => $image ? $this->uploadPhoto($image) : null,
               'access_token' => env('VKONTAKTE_TOKEN'),
               'v' => env('VKONTAKTE_VERSION'),
            ],
         ]);
      }
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

   public function checkVkDonutSubscription(int $userId): bool
   {
      $url = "https://api.vk.com/method/donut.isDon";
      $params = [
         'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
         'user_id' => $userId,
         'access_token' => env('VKONTAKTE_USER_TOKEN'),
         'v' => env('VKONTAKTE_VERSION')
      ];

      // Отправляем запрос
      $response = file_get_contents($url . '?' . http_build_query($params));
      $data = json_decode($response, true);

      if (isset($data['response']) && $data['response'] == 1) {
         return true; // Пользователь подписан
      } elseif (isset($data['error'])) {
         throw new \Exception("Ошибка API: " . $data['error']['error_msg']);
      }

      return false;
   }
}
