<?php

namespace App\Core\Vkontakte\Webhook;

use App\Core\Events\EventsServices;
use App\Core\Method\VkontakteMethod;
use App\Core\Vkontakte\Webhook\Action\VkontakteLikeMethod;
use App\Core\Vkontakte\Webhook\Action\VkontakteWallMethod;

class VkontakteWebhook
{
   public function webhook(array $data): void
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
            (new EventsServices())->events($data['object']['post_id'], $data['object']['from_id'], $data['object']['id'], $data['object']['text'], (new VkontakteMethod()));
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
