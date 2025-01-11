<?php

namespace App\Vkontakte\Webhook\Hook;

use App\Core\Action\Coin\CoinInfoCore;
use App\Core\Action\UserCore;
use App\Core\EventMethod\EventVkontakteMethod;
use App\Core\Message\Message;
use App\Models\User\UserRole;
use App\Models\User\UserWarns;
use App\Services\BotFilterMessageServices;
use App\Vkontakte\Webhook\EventServices;
use Illuminate\Support\Facades\Log;

class VkontakteWallMethod extends UserCore
{
    /*
     *
     * object_id - $data['object']['id']
     * liker_id - $data['object']['from_id']
     * type - $data['type']
     *
     * */

    public function addComment(array $data): void
    {
       $vkontakteMethod = new EventVkontakteMethod();

        if (!$this->checkAction($data['object']['from_id'], $data['type'], $data['object']['post_id'])) {
            $this->setCoin($data['object']['from_id'], $data['type'], 'comment', $data['object']['post_id'], 'vkontakte_id');

           $vkontakteMethod
              ->sendMessage(
                 $data['object']['from_id'],
                 Message::getMessage('comment_add', ['count' => (new CoinInfoCore())->getDataType('comment')])
              );
        }

        Log::info(print_r($data, 1));

       $filterMessage = (new BotFilterMessageServices());

       $analyzeText = $filterMessage->analyzeText($data['object']['text']);
       if (isset($analyzeText['answer'])) {
          $vkontakteMethod->replyWallComment(
             $data['object']['post_id'],
             $analyzeText['answer'],
             $data['object']['id']
          );
       }
    }

    public function removeComment(array $data): void
    {
        if (!$this->checkAction($data['object']['deleter_id'], $data['type'], $data['object']['id'])) {
            $this->unsetCoin($data['object']['deleter_id'], $data['type'], 'comment', $data['object']['id'], 'vkontakte_id');

            (new EventVkontakteMethod())
              ->sendMessage(
                 $data['object']['from_id'],
                 Message::getMessage('comment_remove', ['count' => (new CoinInfoCore())->getDataType('comment')])
              );
        }
    }

    public function repost(array $data): void
    {
        if (!$this->checkAction($data['object']['from_id'], $data['type'], $data['object']['copy_history'][0]['id'])) {
            $this->setCoin($data['object']['from_id'], $data['type'], 'wall', $data['object']['copy_history'][0]['id'], 'vkontakte_id');

            (new EventVkontakteMethod())
               ->sendMessage(
                 $data['object']['from_id'],
                 Message::getMessage('repost_add', ['count' => (new CoinInfoCore())->getDataType('wall')])
               );

           (new EventServices())->addAttempt($data['object']['from_id'], $data['object']['copy_history'][0]['id'], 2, new EventVkontakteMethod());
        }
    }
}
