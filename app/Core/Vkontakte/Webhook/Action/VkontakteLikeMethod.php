<?php

namespace App\Core\Vkontakte\Webhook\Action;

use App\Core\Action\Coin\CoinInfoCore;
use App\Core\Action\UserCore;
use App\Core\Message\Message;
use App\Core\Method\VkontakteMethod;
use App\Core\Vkontakte\Webhook\EventServices;

class VkontakteLikeMethod extends UserCore
{
    /*
     *
     * object_id - $data['object']['object_id']
     * liker_id - $data['object']['liker_id']
     * type - $data['type']
     *
     * */
    public function addLike(array $data): void
    {
        if ($data['object']['object_type'] == 'post' && !$this->checkAction($data['object']['liker_id'], $data['type'], $data['object']['object_id'])) {
            $this->setCoin($data['object']['liker_id'], $data['type'], 'like', $data['object']['object_id'], 'vkontakte_id');

            (new VkontakteMethod())
                ->sendMessage(
                    $data['object']['liker_id'],
                    Message::getMessage('like_add', ['count' => (new CoinInfoCore())->getDataType('like')])
                );
        }
    }

    public function removeLike(array $data): void
    {
        if ($data['object']['object_type'] == 'post' && !$this->checkAction($data['object']['liker_id'], $data['type'], $data['object']['object_id'])) {
            $this->unsetCoin($data['object']['liker_id'], $data['type'], 'like', $data['object']['object_id'], 'vkontakte_id');

            (new VkontakteMethod())
                ->sendMessage(
                    $data['object']['liker_id'],
                    Message::getMessage('like_remove', ['count' => (new CoinInfoCore())->getDataType('like')])
                );
        }
    }
}
