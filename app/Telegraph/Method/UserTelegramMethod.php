<?php

namespace App\Telegraph\Method;

use App\Core\EventMethod\EventVkontakteMethod;
use App\Models\Chat\Chats;
use App\Models\User\User;
use DefStudio\Telegraph\Models\TelegraphChat;
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
//         Log::info('Error in kickChatMember Telegram: ' . $e->getMessage());
         return $e->getMessage();
      }

      if ($response->getStatusCode() !== 200) {
         return 'Ошибка при исключении пользователя из беседы' . $response->getBody();
      }

      return 'Вы успешно исключили пользователя';
   }

   public function getUserIdByUsername(string $username): ?int
   {
      $username = ltrim($username, '@');

      $telegraph = TelegraphChat::query()->where('name', 'LIKE', '%' . $username . '%')->first();
      if ($telegraph) {
         return $telegraph->chat_id;
      }

      return null;
   }

   public function getUserId(int $userID)
   {
      $botToken = env('TELEGRAM_TOKEN');

      $client = new Client();
      $url = "https://api.telegram.org/bot$botToken/getChat";

      try {
         $response = $client->get($url, [
            'query' => [
               'chat_id' => $userID
            ]
         ]);

         $data = json_decode($response->getBody(), true);

         return $data['result'];
      } catch (\GuzzleHttp\Exception\ClientException $e) {
//         Log::info('getUserIdFail' . $e->getMessage());
         return null;
      }
   }

   public function getChatMember(int $userId): bool
   {
      $client = new Client();
      $url = "https://api.telegram.org/bot". env('TELEGRAM_TOKEN') ."/getChatMember";

      try {
         foreach (Chats::query()->where('messanger', 'telegram')->get() as $item) {
            $response = $client->get($url, [
               'query' => [
                  'user_id' => $userId,
                  'chat_id' => $item->chat_id,
               ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['ok']) && $data['ok'] === true) {
               $status = $data['result']['status'] ?? null;
               if (in_array($status, ['member', 'administrator', 'creator'])) {
                  return true;
               }
            }
         }
      } catch (GuzzleException $e) {
//         Log::info('getChatMemberFail' . $e->getMessage());
         return false;
      }

      return false;
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
