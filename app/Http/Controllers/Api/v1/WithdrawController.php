<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\WithdrawItems;
use App\Models\WithdrawUsers;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    public function withdraw(Request $request): Collection
    {
        $request->validate([
            'type' => ['required', 'in:car,skin,aks,money'],
        ]);

        return WithdrawItems::query()->where('type', $request->get('type'))->get();
    }

    public function meWithdraw(): Collection
    {
        return WithdrawUsers::query()->where('user_id', auth()->user()->id)->with(['item'])->get();
    }

    public function allWithdraw(): Collection
    {
        return WithdrawUsers::query()->with(['item', 'user'])->get();
    }
}
