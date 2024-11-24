<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\LastActivity;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ActivityUserController extends Controller
{
    public function now(Request $request)
    {
        return UserResource::collection(
           User::query()
              ->orderBy('coin', 'desc')
              ->where('username_telegram', '!=', '')
              ->orWhere('username_vkontakte', '!=', '')
              ->take(5)
              ->get()
        );
    }

    public function last(): Collection
    {
        return LastActivity::query()->with('user')->take(5)->get();
    }
}
