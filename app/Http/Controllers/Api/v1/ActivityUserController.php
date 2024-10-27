<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\LastActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ActivityUserController extends Controller
{
    public function now(Request $request)
    {
        return User::query()->orderBy('coin', 'desc')->take(20)->get();
    }

    public function last(): Collection
    {
        return LastActivity::query()->with('user')->take(20)->get();
    }
}
