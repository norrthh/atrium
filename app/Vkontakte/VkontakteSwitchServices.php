<?php

namespace App\Vkontakte;

use App\Vkontakte\Events\VkontakteEventsServices;
use App\Vkontakte\Method\VkontakteLikeMethod;
use App\Vkontakte\Method\VkontakteMethod;
use App\Vkontakte\Method\VkontakteWallMethod;
use Illuminate\Support\Facades\Log;

class VkontakteSwitchServices
{
    public function switchData(array $data): void
    {
        switch ($data['type']) {
            case 'like_remove':
                (new VkontakteLikeMethod())->removeLike($data);
                break;
            case 'like_add':
                (new VkontakteLikeMethod())->addLike($data);
                break;
            case 'wall_reply_new':
                (new VkontakteWallMethod())->addComment($data);
                (new VkontakteEventsServices())->events($data);
                break;
            case 'wall_reply_delete':
                (new VkontakteWallMethod())->removeComment($data);
                break;
            case 'wall_repost':
                (new VkontakteWallMethod())->repost($data);
                break;
        }
    }
}
