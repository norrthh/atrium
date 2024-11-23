<?php

namespace App\Core\Action\Coin;

use App\Models\Coins;
use App\Models\User\UserCoins;
use Carbon\Carbon;

class CoinCore
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
      $user = UserCoins::query()
         ->where('user_id', auth()->user()->id)
         ->orderBy('id', 'desc')
         ->first();

      if (!$user) {
         return 'now';
      }

      $lastCollected = Carbon::parse($user->updated_at)->setTimezone('Europe/Moscow');
      $now = Carbon::now('Europe/Moscow');

      $timePassed = $lastCollected->diffInSeconds($now);

      $totalSeconds = 24 * 3600;

      if ($timePassed < $totalSeconds) {
         $secondsLeft = $totalSeconds - $timePassed;

         $hoursLeft = (int) floor($secondsLeft / 3600);
         $minutesLeft = (int) floor(($secondsLeft % 3600) / 60);
         $secondsLeft = (int) ($secondsLeft % 60);

         return [
            'hours' => $hoursLeft,
            'minutes' => $minutesLeft,
            'seconds' => $secondsLeft,
         ];
      }

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
