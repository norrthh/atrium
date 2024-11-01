<?php

namespace App\Vkontakte\Method;

use App\Vkontakte\VkontakteCoinsServices;
use App\Vkontakte\VkontakteMethodServices;

class VkontakteLikeMethod extends VkontakteMethod
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
            $this->setCoin($data['object']['liker_id'], $data['type'], 'like', $data['object']['object_id']);
        }
    }

    public function removeLike(array $data): void
    {
        if ($data['object']['object_type'] == 'post' && !$this->checkAction($data['object']['liker_id'], $data['type'], $data['object']['object_id'])) {
            $this->unsetCoin($data['object']['liker_id'], $data['type'], 'like', $data['object']['object_id']);
        }
    }
}
