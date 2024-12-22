<?php

namespace App\Core\Bot;

use App\Models\Chats;
use App\Models\User\User;
use App\Models\UserMute;
use App\Telegraph\Method\UserTelegramMethod;
use Illuminate\Support\Facades\Log;

class BotCore
{
    public function mute(array $user, int $time, string $table, int $user_id): void
    {
        $data = $user;
        $data['time'] = $time;

        $userMute = UserMute::query()->where($table, $user_id)->first();

        if ($userMute) {
            UserMute::query()->where($table, $user_id)->update($data);
        } else {
            UserMute::query()->create($data);
        }
    }

    public function akick(?User $user, string $methodCall, int $user_id): void
    {
        Log::info(
            'methodCall: ' . $methodCall . ' user_id: ' . $user_id
        );
        foreach (Chats::query()->get() as $item) {
            if ($item->messanger == $methodCall) {
                Log::info(
                    'methodCall: ' . $methodCall . ' chat_id: ' . $item->chat_id . "interation: " . $item->messanger
                );
                if ($methodCall == 'telegram') {
                    (new UserTelegramMethod())->kickUserFromChat($item->chat_id, $user_id);
                } else {
                    (new \App\Vkontakte\Method\User(chat_id: $item->chat_id))->kickUserFromChat($user_id);
                }
            } else {
                if ($user) {
                    if ($item->messanger == 'vkontakte' and $user->vkontakte_id) {
                        (new \App\Vkontakte\Method\User(chat_id: $item->chat_id))->kickUserFromChat($user->vkontakte_id);
                    }

                    if ($item->messanger == 'telegram' and $user->telegram_id) {
                        (new UserTelegramMethod())->kickUserFromChat($item->chat_id, $user->telegram_id);
                    }
                }
            }
        }
    }
}
