<?php

namespace App\Core\EventMethod;

use App\Vkontakte\Method\Message;
use App\Vkontakte\Method\Subscribe;
use App\Vkontakte\Method\User;
use GuzzleHttp\Client;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class EventVkontakteMethod implements EventSocialMethod
{
    public function sendMessage(int $userId, string $message): Response
    {
        return (new Message([]))->sendAPIMessage(userId: $userId, message: $message);
    }

    public function sendWallMessage($filePath, $message)
    {
        return json_decode((new Client())->get('https://api.vk.com/method/wall.post', [
            'query' => [
                'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
                'message' => $message,
                'attachments' => $this->uploadPhoto($filePath),
                'access_token' => env('VKONTAKTE_TOKEN'),
                'v' => env('VKONTAKTE_VERSION'),
            ]
        ])->getBody()->getContents(), true);
    }

    public function uploadPhoto(string $imagePath): string
    {
        return (new Message([]))->uploadAPIPhoto($imagePath);
    }

    public function closeWallComments(int $postId, int $user_id = null)
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

        $data = [
            'form_params' => [
                'owner_id' => '-' . env('VKONTAKTE_GROUP_ID'),
                'post_id' => $postId,
                'message' => $message,
                'reply_to_comment' => $commentId,
                'access_token' => env('VKONTAKTE_TOKEN'),
                'v' => env('VKONTAKTE_VERSION'),
            ],
        ];

        if ($image) {
            $data['form_params']['attachments'] = $this->uploadPhoto($image);
        }

        $client->post('https://api.vk.com/method/wall.createComment', $data);
    }

    public function checkSubscriptionMailing(int $userId): bool
    {
        return true;
    }

    public function checkSubscriptionGroup(int $userId): bool
    {
        return (new Subscribe())->group($userId);
    }
    public function checkVkDonutSubscription(int $userId): bool
    {
        return (new Subscribe())->donate($userId);
    }
}
