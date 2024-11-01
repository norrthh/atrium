<?php

namespace App\Vkontakte;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class VkontakteMethodServices
{
    protected string $vkKey;
    protected string $vkVersion;

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

}
