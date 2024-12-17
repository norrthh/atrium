<?php

namespace App\Core\EventMethod;

use App\Vkontakte\Method\Message;
use App\Vkontakte\Method\Subscribe;
use GuzzleHttp\Client;
use Illuminate\Http\Client\Response;

class EventVkontakteMethod extends Message implements EventSocialMethod
{
   public function sendMessage(int $userId, string $message): Response
   {
      return $this->sendAPIMessage($userId, $message);
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
      return $this->uploadAPIPhoto($imagePath);
   }

   public function closeWallComments(int $postId)
   {
      $client = new Client();

      $response = $client->post('https://api.vk.com/method/wall.closeComments', [
         'form_params' => [
            'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
            'post_id' => $postId,
            'access_token' =>env('VKONTAKTE_TOKEN'),
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
               'access_token' =>env('VKONTAKTE_TOKEN'),
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
               'access_token' =>env('VKONTAKTE_TOKEN'),
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
      return (new Subscribe())->group($userId);
   }

   /**
    * @throws \Exception
    */
   public function checkVkDonutSubscription(int $userId): bool
   {
      return (new Subscribe())->donate($userId);
   }
}
