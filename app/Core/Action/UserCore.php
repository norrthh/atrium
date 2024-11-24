<?php

namespace App\Core\Action;

use App\Core\Action\Coin\CoinInfoCore;
use App\Models\User\User;
use App\Models\User\UserLogMethod;

class UserCore
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
   public function giveAmount(int $userID, string $coin, string $typeSocial): void
   {
      if (!User::query()->where($typeSocial, $userID)->first()) {
         $this->storeUser($userID, $typeSocial);
      }

      User::query()->where($typeSocial, $userID)->update([
         'coin' => User::query()->where($typeSocial, $userID)->first()->coin + $coin
      ]);
   }
   public function resetAmount(int $userID, int $coin, string $typeSocial): void
   {
      if (!User::query()->where($typeSocial, $userID)->first()) {
         $this->storeUser($userID, $typeSocial);
      }

      User::query()->where($typeSocial, $userID)->update([
         'coin' => User::query()->where($typeSocial, $userID)->first()->coin - $coin
      ]);
   }
   public function storeUser(int $userID, string $typeSocial): void
   {
      User::query()->create([$typeSocial => $userID, 'nickname' => '']);
   }

   public function setCoin(string $userID, string $typeMethod, string $typeAction, string $objectID, string $typeSocial): void
   {
      $this->giveAmount($userID, (new CoinInfoCore())->getDataType($typeAction), $typeSocial);
      $this->storeLog($userID, $typeMethod, (new CoinInfoCore())->getDataType($typeAction), $objectID);
//      app(VkontakteMethodCore::class)->sendMessage($userID, 'Вам начислено ' . (new VkontakteCoinsServices())->getDataType($typeAction) . ' койнов!');
   }
   public function unsetCoin(int $userID, string $typeMethod, string $typeAction, string $objectID, string $typeSocial): void
   {
      $this->resetAmount($userID, (new CoinInfoCore())->getDataType($typeAction), $typeSocial);
      $this->storeLog($userID, $typeMethod, (new CoinInfoCore())->getDataType($typeAction), $objectID);
//      app(VkontakteMethodCore::class)->sendMessage($userID, 'С вас снят ' . (new VkontakteCoinsServices())->getDataType($typeAction) . ' койнов!');
   }
}
