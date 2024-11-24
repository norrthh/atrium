<?php

namespace App\Http\Controllers\Api\v1;

use App\Core\Action\Coin\CoinCore;
use App\Http\Controllers\Controller;
use App\Models\Coins;
use App\Models\User\User;
use App\Models\User\UserCoins;
use Illuminate\Http\JsonResponse;

class CoinsController extends Controller
{
    public function index(CoinCore $coinCore): JsonResponse
    {
        return response()->json([
            'coins' => Coins::query()->get(),
            'status' => $coinCore->getStatus(),
            'time' => $coinCore->getTime(),
            'day' => $coinCore->getDay(),
        ]);
    }

    public function getCoins(CoinCore $coinCore)
    {
        if ($coinCore->getTime() == 'now') {
            $userCoin = UserCoins::query()->where('user_id', auth()->user()->id)->first();

            if (!$userCoin) {
                UserCoins::query()->create([
                    'user_id' => auth()->user()->id,
                    'coin_id' => $coinCore->getDay(),
                ]);
            } else {
                UserCoins::query()->where('user_id', auth()->user()->id)->update([
                    'coin_id' => $coinCore->getDay() + 1,
                ]);
            }

            User::query()->where('id', auth()->user()->id)->update([
                'coin' => $coinCore->getCoin() + auth()->user()->coin
            ]);

            return response()->json(['coin' => $coinCore->getCoin(), 'time' => $coinCore->getTime()]);
        }

        return response()->json(['time' => $coinCore->getTime()]);
    }
}
