<?php

namespace App\Vkontakte\Method;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

   public function sendAPIMessage(int $userId = 0, string|bool $message = false, $keyboard = false, string|bool $attachment = false, ?int $conversation_message_id = null): Response
   {
      $params = [
         'access_token' => $this->vkKey,
         'v'            => $this->vkVersion,
         'random_id'    => rand(),
         'peer_id'      => $userId
      ];

      if ($conversation_message_id != 0) {
         $params['reply_to'] = $this->getMessageId($userId, $conversation_message_id);
      }

      if ($keyboard) {
         $params['keyboard'] = $keyboard;
      }

      if ($message) {
         $params['message'] = $message;
      }

      if ($attachment) {
         $params['attachment'] = $attachment;
      }

      $request = Http::get(self::VK_API_URL, $params);

//      Log::info('request send message vk' . print_r($request->body(), true));

      return $request;
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

   public function getMessageId(int $chat_id, int $conversation_message_id)
   {
      try {
         $response = Http::get("https://api.vk.com/method/messages.getByConversationMessageId", [
            "peer_id" => $chat_id,
            "conversation_message_ids" => $conversation_message_id,
            'access_token' => $this->vkKey,
            'v' => $this->vkVersion,
         ]);

         $response_data = $response->json();

         if (isset($response_data['response']['items'][0]['id'])) {
            return $response_data['response']['items'][0]['id']; // Глобальный ID сообщения
         } elseif (isset($response_data['error'])) {
            echo "Ошибка VK API: " . $response_data['error']['error_msg'] . "\n";
         }
      } catch (Exception $e) {
         echo "Ошибка при выполнении запроса: " . $e->getMessage() . "\n";
      }

      return null;
   }

   public function deleteMessage(int $message_id, int $peer_id): Response
   {
      $request = Http::get(
         'https://api.vk.com/method/messages.delete',
         [
            'cmids' => $message_id, // ID сообщения
            'delete_for_all' => 1, // Удалить для всех (опционально)
            'access_token' => $this->vkKey,
            'v' => $this->vkVersion,
            'peer_id' => $peer_id
         ]
      );

      return $request;
   }
}
