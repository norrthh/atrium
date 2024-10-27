<?php

namespace App\Services\User;

use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UserAuthenticationServices
{
    /**
     * @throws ValidationException
     */
    public function authenticate(array $data)
    {
        $user = null;

        if (isset($data['telegram_id'])) {
            $user = User::query()->where('telegram_id', $data['telegram_id'])->first();
        }

        if (isset($data['vkontakte_id'])) {
            $user = User::query()->where('vkontakte_id', $data['vkontakte_id'])->first();
            if ($user) {
                $user->update(['avatar' => $data['avatar']]);
            }
        }

        if (!$user) {
            $user = ['nickname' => 'dev' . rand()];
            if (isset($data['telegram_id'])) {
                $user['telegram_id'] = $data['telegram_id'];
            }

            if (isset($data['vkontakte_id'])) {
                $user['vkontakte_id'] = $data['vkontakte_id'];
                $user['avatar'] = $data['avatar'];
            }

            if (isset($data['telegram_id']) or isset($data['vkontakte_id'])) {
                $user = User::query()->create($user);
            }
        }

        if ($user) {
            auth()->login($user, true);

            return [
                'user' => $user,
                'token' => $user->createToken('authToken')->plainTextToken
            ];
        }

        return $user;
    }
}
