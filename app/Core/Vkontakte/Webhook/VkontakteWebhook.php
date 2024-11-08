<?php

namespace App\Core\Vkontakte\Webhook;

use App\Core\Vkontakte\Webhook\Action\VkontakteLikeMethod;
use App\Core\Vkontakte\Webhook\Action\VkontakteWallMethod;

class VkontakteWebhook
{
    public function webhook(array $data): string
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
//                (new VkontakteEventsServices())->events($data);
                break;
            case 'wall_reply_delete':
                (new VkontakteWallMethod())->removeComment($data);
                break;
            case 'wall_repost':
                (new VkontakteWallMethod())->repost($data);
                break;
        }

        return 'ok';
    }
}
