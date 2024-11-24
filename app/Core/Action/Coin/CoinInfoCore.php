<?php

namespace App\Core\Action\Coin;

class CoinInfoCore
{
    public function data(): array
    {
        return [
            [
                'type' => 'like',
                'amount' => 1
            ],
            [
                'type' => 'wall', // посты
                'amount' => 5
            ],
            [
                'type' => 'comment',
                'amount' => 1,
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

        return 'false';
    }
}
