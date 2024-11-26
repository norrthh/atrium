<?php

namespace App\Core\Vkontakte\Method;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Storage;

class Message
{
   protected string $vkKey;
   protected string $vkVersion;

   const VK_API_URL = 'https://api.vk.com/method/messages.send';
   const PHOTO_UPLOAD_URL = 'https://api.vk.com/method/photos.getWallUploadServer';
   const PHOTO_SAVE_URL = 'https://api.vk.com/method/photos.saveWallPhoto';

   public function __construct()
   {
      $this->vkKey = env('VKONTAKTE_TOKEN');
      $this->vkVersion = env('VKONTAKTE_VERSION');
   }

   public function sendAPIMessage(int $userId, string|bool $message = false, $keyboard = false): Response
   {
      $params = [
         'user_id' => $userId,
         'access_token' => $this->vkKey,
         'v' => $this->vkVersion,
         'random_id' => rand(),
      ];

      if ($keyboard) {
         $params['keyboard'] = $keyboard;
      }

      if ($message) {
         $params['message'] = $message;
      }

      // Используем POST вместо GET
      return Http::get(self::VK_API_URL, $params);
   }

   public function uploadAPIPhoto(string $imagePath): string
   {
      $client = new Client();

      // Получаем URL для загрузки
      $response = $client->post(self::PHOTO_UPLOAD_URL, [
         'form_params' => [
            'access_token' => env('VKONTAKTE_USER_TOKEN'),
            'v' => env('VKONTAKTE_VERSION'),
         ],
      ]);

      $uploadServer = json_decode($response->getBody(), true)['response']['upload_url'];

      // Загружаем фото
      $photoResponse = $client->post($uploadServer, [
         'multipart' => [
            [
               'name' => 'file',
               'contents' => fopen(Storage::disk('public')->path($imagePath), 'r'),
            ],
         ],
      ]);

      $uploadedPhoto = json_decode($photoResponse->getBody(), true);
      $saveResponse = $client->post(self::PHOTO_SAVE_URL, [
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
}
