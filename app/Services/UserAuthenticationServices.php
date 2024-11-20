<?php

namespace App\Services;

use App\Core\Method\VkontakteMethod;
use App\Http\Resources\UserResource;
use App\Models\User\User;
use Illuminate\Validation\ValidationException;

class UserAuthenticationServices
{
   /**
    * @throws ValidationException
    * @throws \Exception
    */
   public function authenticate(array $data)
   {
      $user = null;

      if (isset($data['telegram_id'])) {
         $user = User::query()->where('telegram_id', $data['telegram_id'])->first();

         if ($user) {
            $user->update(['username_telegram' => $data['nickname']]);
         }
      }

      if (isset($data['vkontakte_id'])) {
         $user = User::query()->where('vkontakte_id', $data['vkontakte_id'])->first();
         if ($user) {
            $user->update(['avatar' => $data['avatar'], 'username_vkontakte' => $data['nickname']]);
         }
      }

      if (!$user) {
         if (isset($data['telegram_id'])) {
            $user['telegram_id'] = $data['telegram_id'];
            $user['username_telegram'] = $data['nickname'];
//            $user['avatar_telegram'] = $data['avatar_telegram'];
         }

         if (isset($data['vkontakte_id'])) {
            $user['vkontakte_id'] = $data['vkontakte_id'];
            $user['username_vkontakte'] = $data['nickname'];
            $user['avatar'] = $data['avatar'];
         }

         if (isset($data['telegram_id']) or isset($data['vkontakte_id'])) {
            $user = User::query()->create($user);
         }
      }

      if ($user) {
         auth()->login($user, true);

         return [
            'user' => new UserResource(auth()->user()),
            'token' => $user->createToken('authToken')->plainTextToken,
            'vk_donut' => (new VkontakteMethod())->checkVkDonutSubscription($data['vkontakte_id'] ?? 0),
            'notification' => (new NotificationServices())->getNotification()
         ];
      }

      return $user;
   }
}
