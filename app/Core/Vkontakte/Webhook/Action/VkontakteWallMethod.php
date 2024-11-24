<?php

namespace App\Core\Vkontakte\Webhook\Action;

use App\Core\Action\Coin\CoinInfoCore;
use App\Core\Action\UserCore;
use App\Core\Message\Message;
use App\Core\Method\VkontakteMethod;
use App\Core\Vkontakte\Webhook\EventServices;

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
        if (!$this->checkAction($data['object']['from_id'], $data['type'], $data['object']['post_id'])) {
            $this->setCoin($data['object']['from_id'], $data['type'], 'comment', $data['object']['post_id'], 'vkontakte_id');

           (new VkontakteMethod())
              ->sendMessage(
                 $data['object']['from_id'],
                 Message::getMessage('comment_add', ['count' => (new CoinInfoCore())->getDataType('like')])
              );
        }
    }

    public function removeComment(array $data): void
    {
        if (!$this->checkAction($data['object']['deleter_id'], $data['type'], $data['object']['id'])) {
            $this->unsetCoin($data['object']['deleter_id'], $data['type'], 'comment', $data['object']['id'], 'vkontakte_id');

//            (new VkontakteMethod())
//              ->sendMessage(
//                 $data['object']['from_id'],
//                 Message::getMessage('comment_remove', ['count' => (new CoinInfoCore())->getDataType('like')])
//              );
        }
    }

    public function repost(array $data): void
    {
        if (!$this->checkAction($data['object']['from_id'], $data['type'], $data['object']['id'])) {
            $this->setCoin($data['object']['from_id'], $data['type'], 'wall', $data['object']['id'], 'vkontakte_id');

            (new VkontakteMethod())
              ->sendMessage(
                 $data['object']['from_id'],
                 Message::getMessage('repost_add', ['count' => (new CoinInfoCore())->getDataType('like')])
              );

           (new EventServices())->addAttempt($data['object']['from_id'], $data['object']['id'], 2, new VkontakteMethod());
        }
    }
}
