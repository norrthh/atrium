<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Coins;
use App\Models\User;
use App\Models\UserCoins;
use App\Services\Coins\CoinServices;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoinsController extends Controller
{
    public function index(Request $request, CoinServices $services)
    {
        return response()->json([
            'coins' => Coins::query()->get(),
            'status' => $services->getStatus(),
            'time' => $services->getTime(),
            'day' => $services->getDay(),
        ]);
    }

    public function getCoins(CoinServices $services)
    {
        if ($services->getTime() == 'now') {
            UserCoins::query()->create([
                'user_id' => auth()->user()->id,
                'coin_id' => $services->getDay() ,
            ]);

            User::query()->where('id', auth()->user()->id)->update([
                'coin' => $services->getCoin() + auth()->user()->coin
            ]);

            return response()->json(['coin' => $services->getCoin()]);
        }
    }
}
