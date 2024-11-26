<?php

namespace App\Core\Vkontakte\Method;

use GuzzleHttp\Client;

class Subscribe
{
   public function donate(int $userId): bool
   {
      $url = "https://api.vk.com/method/donut.isDon";
      $params = [
         'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
         'user_id' => $userId,
         'access_token' => env('VKONTAKTE_USER_TOKEN'),
         'v' => env('VKONTAKTE_VERSION')
      ];

      $response = file_get_contents($url . '?' . http_build_query($params));
      $data = json_decode($response, true);

      if (isset($data['response']) && $data['response'] == 1) {
         return true; // Пользователь подписан
      } elseif (isset($data['error'])) {
         throw new \Exception("Ошибка API: " . $data['error']['error_msg']);
      }

      return false;
   }

   public function group(int $userId): bool
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
}
