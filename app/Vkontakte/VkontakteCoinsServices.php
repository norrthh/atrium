<?php

namespace App\Vkontakte;

class VkontakteCoinsServices
{
    public function data(): array
    {
        return [
            [
                'type' => 'like',
                'amount' => 1
            ],
            [
                'type' => 'wall', // repost
                'amount' => 5
            ],
            [
                'type' => 'comment',
                'amount' => 3,
            ]
        ];
    }

    public function getDataType(string $type): string
    {
        $filters = array_filter($this->data(), function ($item) use ($type) {
            return $item['type'] === $type;
        });

        foreach ($filters as $item) {
            return $item['amount'];
        }
    }
}
