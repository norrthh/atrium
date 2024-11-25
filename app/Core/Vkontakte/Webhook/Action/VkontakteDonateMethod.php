<?php

namespace App\Core\Vkontakte\Webhook\Action;

use App\Facades\WithdrawUser;
use App\Models\User\User;

class VkontakteDonateMethod
{
   public function premium(array $data)
   {
      $user = User::query()
         ->where('vkontakte_id', $data['object']['user_id'])
         ->first();

      if ($user) {
         User::query()
            ->where('vkontakte_id', $data['object']['user_id'])
            ->update([
               'bilet' => $user->bilet + 5,
               'isPremium' => true,
               'coin' => $user->coin + 5,
               'coins_week' => $user->coin + 5
            ]);

         WithdrawUser::store(3, 1, $user->id);
         WithdrawUser::store(4, 1, $user->id);
      }
   }

   public function removePremium(array $data)
   {
      $user = User::query()
         ->where('vkontakte_id', $data['object']['user_id'])
         ->first();

      if ($user) {
         User::query()
            ->where('vkontakte_id', $data['object']['user_id'])
            ->update([
               'isPremium' => false
            ]);
      }
   }
}
