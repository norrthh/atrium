<?php

namespace App\Vkontakte\Webhook;

use App\Core\EventMethod\EventVkontakteMethod;
use App\Core\Events\EventsServices;
use App\Vkontakte\Bot\BotCommandMainMethod;
use App\Vkontakte\Webhook\Hook\VkontakteDonateMethod;
use App\Vkontakte\Webhook\Hook\VkontakteGroupMethod;
use App\Vkontakte\Webhook\Hook\VkontakteLikeMethod;
use App\Vkontakte\Webhook\Hook\VkontakteMessageMethod;
use App\Vkontakte\Webhook\Hook\VkontakteWallMethod;

class VkontakteWebhook
{
   public function webhook(array $data): void
   {
      if (isset($data['object']['from_id']) < 0) {
         return;
      }

      switch ($data['type']) {
         case 'like_remove':
            (new VkontakteLikeMethod())->removeLike($data);
            break;
         case 'like_add':
            (new VkontakteLikeMethod())->addLike($data);
            (new EventServices())->addAttempt($data['object']['liker_id'], $data['object']['object_id'], 1, new EventVkontakteMethod());
            break;
         case 'wall_reply_new':
            (new VkontakteWallMethod())->addComment($data);
            (new EventsServices())->events($data['object']['post_id'], $data['object']['from_id'], $data['object']['id'], $data['object']['text'], (new EventVkontakteMethod()));
            break;
         case 'wall_reply_delete':
            (new VkontakteWallMethod())->removeComment($data);
            break;
         case 'wall_repost':
            (new VkontakteWallMethod())->repost($data);
            break;
         case 'group_join':
            (new VkontakteGroupMethod())->groupJoin($data);
            (new BotCommandMainMethod($data))->start();
            break;
         case 'donut_subscription_prolonged':
         case 'donut_subscription_create':
            (new VkontakteDonateMethod())->premium($data);
            break;
         case 'donut_subscription_expired':
         case 'donut_subscription_cancelled':
            (new VkontakteDonateMethod())->removePremium($data);
            break;
         case 'message_new':
            (new VkontakteMessageMethod())->message($data);
            break;
         default:
            break;
      }
   }
}
