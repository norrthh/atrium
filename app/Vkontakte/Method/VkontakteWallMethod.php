<?php

namespace App\Vkontakte\Method;

class VkontakteWallMethod extends VkontakteMethod
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
            $this->setCoin($data['object']['from_id'], $data['type'], 'comment', $data['object']['post_id']);
        }
    }

    public function removeComment(array $data): void
    {
        if (!$this->checkAction($data['object']['deleter_id'], $data['type'], $data['object']['id'])) {
            $this->unsetCoin($data['object']['deleter_id'], $data['type'], 'comment', $data['object']['id']);
        }
    }

    public function repost(array $data): void
    {
        if (!$this->checkAction($data['object']['from_id'], $data['type'], $data['object']['id'])) {
            $this->setCoin($data['object']['from_id'], $data['type'], 'wall', $data['object']['id']);
        }
    }
}
