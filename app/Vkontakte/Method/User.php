<?php

namespace App\Vkontakte\Method;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class User
{
   protected int $chat_id = 0;
   protected int $user_id = 0;

   public function __construct(int $user_id = 0, int $chat_id = 0)
   {
      $this->user_id = $user_id;
      $this->chat_id = $chat_id;
   }

   public function kickUserFromChat($userKick_id): string
   {
      $client = new Client(['base_uri' => 'https://api.vk.com/method/']);

      try {
         $response = $client->post('messages.removeChatUser', [
            'form_params' => [
               'access_token' => env('VKONTAKTE_TOKEN'),
               'chat_id' => $this->chat_id - 2000000000,
               'user_id' => $userKick_id,
               'v' => env('VKONTAKTE_VERSION'),
            ],
         ]);

         $body = json_decode($response->getBody(), true);

         if (isset($body['error'])) {
            return "Error: " . $body['error']['error_msg'];
         }

         return "User removed successfully!";
      } catch (RequestException $e) {
         return "Request failed: " . $e->getMessage();
      } catch (GuzzleException $e) {
          Log::info('Error in kickChatMember Telegram: '. $e->getMessage());
          return $e->getMessage();
      }
   }

   public function getProfile(string $username): array
   {
      $url = 'https://api.vk.com/method/users.get';

      $request = [
         'user_ids' => $username,
         'access_token' => env('VKONTAKTE_TOKEN'),
         'v' => env('VKONTAKTE_VERSION'),
      ];

      $response = Http::get($url, $request);

      if ($response->failed()) {
         return [
            'status' => 'error',
            'message' => 'Ошибка при выполнении запроса к VK API',
            'data' => $response->json(),
         ];
      }

      $data = $response->json();

      if (isset($data['error'])) {
         return [
            'status' => 'error',
            'message' => $data['error']['error_msg'],
            'data' => [],
         ];
      }

      return [
         'status' => 'success',
         'message' => 'Информация о пользователе успешно получена',
         'data' => $data['response'][0],
      ];
   }
}
