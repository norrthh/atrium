<?php

namespace App\Services;

use App\Core\EventMethod\EventVkontakteMethod;
use App\Http\Resources\UserResource;
use App\Models\User\User;
use App\Services\Telegram\TelegramMethodServices;
use Illuminate\Support\Facades\Log;
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
         if (!isset($data['nickname']) or $data['nickname'] == '') {
            return [
               'status' => false,
               'message' => 'Чтобы продолжить необходимо указать никнейм в настройках телеграмм'
            ];
         }

         $user = User::query()->where('telegram_id', $data['telegram_id'])->first();

         if ($user) {
            $user->update([
               'username_telegram' => $data['nickname'],
               'avatar_telegram' => $data['avatar_telegram'] ?? '',
            ]);
         }
      }

      if (isset($data['vkontakte_id'])) {
         $user = User::query()->where('vkontakte_id', $data['vkontakte_id'])->first();
         if ($user) {
            $user->update(['avatar' => $data['avatar'] ?? '', 'username_vkontakte' => $data['nickname']]);
         }
      }

      if (!$user) {
         $user['nickname'] = '';
         if (isset($data['telegram_id'])) {
            $user['telegram_id'] = $data['telegram_id'];
            $user['username_telegram'] = $data['nickname'];
            $user['avatar_telegram'] = $data['avatar_telegram'] ?? '';
         }

         if (isset($data['vkontakte_id'])) {
            $user['vkontakte_id'] = $data['vkontakte_id'];
            $user['username_vkontakte'] = $data['nickname'];
            $user['avatar'] = $data['avatar'] ?? '';
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
            'vk_donut' => (new EventVkontakteMethod())->checkVkDonutSubscription($data['vkontakte_id'] ?? 0),
            'notification' => (new NotificationServices())->getNotification(isset($data['vkontakte_id']) ? 'vkontakte' : 'telegram'),
            'telegram_check' => auth()->user()->telegram_id && $this->checkSubscriptionTelegramAndNickname(),
         ];
      }

      return $user;
   }

   protected function checkSubscriptionTelegramAndNickname(): bool
   {
      $subscription = (new TelegramMethodServices())->getChatMember(auth()->user()->telegram_id);

      if ($subscription && isset($subscription['result']) && $subscription['result']['status'] != 'left' or auth()->user()->username_telegram == '') {
         return true;
      }

      return false;
   }
}
