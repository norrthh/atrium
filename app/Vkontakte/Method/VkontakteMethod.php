<?php

namespace App\Vkontakte\Method;

use App\Models\User;
use App\Models\UserLogMethod;
use App\Vkontakte\VkontakteCoinsServices;
use App\Vkontakte\VkontakteMethodServices;

class VkontakteMethod
{
    public function storeLog(int $userID, string $typeAction, string $coin, string $objectID): void
    {
        UserLogMethod::query()->create([
            'user_id' => $userID,
            'method' => $typeAction,
            'amountValue' => $coin,
            'object_id' => $objectID
        ]);
    }

    public function checkAction(int $userID, string $typeAction, string $objectID): bool
    {
        return (bool)UserLogMethod::query()->where([['user_id', $userID], ['method', $typeAction], ['object_id', $objectID]])->first();
    }

    public function giveAmount(int $userID, string $coin): void
    {
        if (!User::query()->where('vkontakte_id', $userID)->first()) {
            $this->storeUser($userID);
        }

        User::query()->where('vkontakte_id', $userID)->update([
            'coin' => User::query()->where('vkontakte_id', $userID)->first()->coin + $coin
        ]);
    }

    public function resetAmount(int $userID, int $coin): void
    {
        if (!User::query()->where('vkontakte_id', $userID)->first()) {
            $this->storeUser($userID);
        }

        User::query()->where('vkontakte_id', $userID)->update([
            'coin' => User::query()->where('vkontakte_id', $userID)->first()->coin - $coin
        ]);
    }

    public function storeUser(int $userID): void
    {
        User::query()->create(['vkontakte_id' => $userID, 'nickname' => '']);
    }

    public function setCoin(int $userID, string $typeMethod, string $typeAction, string $objectID): void
    {
        $this->giveAmount($userID, (new VkontakteCoinsServices())->getDataType($typeAction));
        $this->storeLog(
            $userID,
            $typeMethod,
            (new VkontakteCoinsServices())->getDataType($typeAction),
            $objectID
        );

        app(VkontakteMethodServices::class)->sendMessage($userID, 'Вам начислено ' . (new VkontakteCoinsServices())->getDataType($typeAction) . ' койнов!');
    }

    public function unsetCoin(int $userID, string $typeMethod, string $typeAction, string $objectID): void
    {
        $this->resetAmount($userID, (new VkontakteCoinsServices())->getDataType($typeAction));
        $this->storeLog(
            $userID,
            $typeMethod,
            (new VkontakteCoinsServices())->getDataType($typeAction),
            $objectID
        );

        app(VkontakteMethodServices::class)->sendMessage($userID, 'С вас снят ' . (new VkontakteCoinsServices())->getDataType($typeAction) . ' койнов!');
    }
}
