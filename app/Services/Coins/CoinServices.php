<?php

namespace App\Services\Coins;

use App\Models\Coins;
use App\Models\UserCoins;
use Carbon\Carbon;

class CoinServices
{
    public function getStatus(): bool
    {
        $user = UserCoins::query()->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->first();

        if (!$user or Carbon::parse($user->updated_at)->diffInHours(now()) >= 24) {
            return true;
        }

        return false;
    }

    public function getTime(): array|string
    {
        $user = UserCoins::query()->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->first();

        if (!$user) {
            return 'now';
        }

        $lastCollected = Carbon::parse($user->updated_at)->setTimezone('Europe/Moscow'); // время последнего сбора
        $now = Carbon::now('Europe/Moscow'); // текущее время

        $timePassed = $lastCollected->diffInSeconds($now);

        // 24 часа в секундах
        $totalSeconds = 24 * 3600;

        // Если прошло меньше 24 часов, рассчитываем оставшееся время
        if ($timePassed < $totalSeconds) {
            $secondsLeft = $totalSeconds - $timePassed;

            // Преобразуем оставшееся время в часы, минуты и секунды
            $hoursLeft = floor($secondsLeft / 3600);
            $minutesLeft = floor(($secondsLeft % 3600) / 60);
            $secondsLeft = $secondsLeft % 60;

            return [
                'hours' => $hoursLeft - 1,
                'minutes' => 59 - $minutesLeft,
                'seconds' => 59 - $secondsLeft
            ];
        }

        // Если прошло 24 часа и более, возвращаем статус "now"
        return 'now';
    }



    public function getDay(): int
    {
        $user = UserCoins::query()->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->first();

        if (!$user) {
            return 1;
        }


        $time = $this->getTime();

        if ($time == 'now') {
            return min($user->coin_id + 1, 9);
        }

        return $user->coin_id;
    }

    public function getCoin()
    {
        return Coins::query()->where('id', $this->getDay())->first()->count;
    }
}
