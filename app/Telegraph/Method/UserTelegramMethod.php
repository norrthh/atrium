<?php

namespace App\Telegraph\Method;

use App\Core\EventMethod\EventVkontakteMethod;
use App\Models\User\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class UserTelegramMethod
{
   public function kickUserFromChat(int $chatId, int $userId): string
   {
      $client = new Client(['base_uri' => 'https://api.telegram.org']);

      try {
         $response = $client->post('/bot' . env('TELEGRAM_TOKEN') . '/kickChatMember', [
            'form_params' => [
               'chat_id' => $chatId,
               'user_id' => $userId,
            ],
         ]);
      } catch (GuzzleException $e) {
         Log::info('Error in kickChatMember Telegram: '. $e->getMessage());
         return $e->getMessage();
      }

      if ($response->getStatusCode() !== 200) {
         return 'Ошибка при исключении пользователя из беседы' . $response->getBody();
      }

      return 'Вы успешно исключили пользователя';
   }

   public function getUserIdByUsername(string $username): ?int
   {
      $response = file_get_contents("https://api.telegram.org/bot" . env('TELEGRAM_TOKEN') . "/getChat?chat_id={$username}");
      $data = json_decode($response, true);

      return $data['result']['id'] ?? null;
   }

   public function getInfoUser(?User $user, int $user_id): array
   {
      $userUpdate = ['telegram_id' => $user_id];

      if ($user && $user->vkontakte_id) {
         $userUpdate['vkontakte_id'] = $user->vkontakte_id;
      }

      return $userUpdate;
   }
}
