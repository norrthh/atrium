<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LastActivityUserResource;
use App\Http\Resources\UserActivityResource;
use App\Http\Resources\UserResource;
use App\Models\LastActivity;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ActivityUserController extends Controller
{
    public function now(Request $request)
    {
        return UserActivityResource::collection(
           User::query()
              ->orderBy('coins_week', 'desc')
              ->where('username_telegram', '!=', '')
              ->orWhere('username_vkontakte', '!=', '')
              ->take(5)
              ->get()
        );
    }

    public function last(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
       return LastActivityUserResource::collection(
          LastActivity::query()
             ->orderBy('count', 'desc')
             ->get()
       );
    }
}
