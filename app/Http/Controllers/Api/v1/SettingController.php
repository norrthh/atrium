<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User\UserLogMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
   public function index(): JsonResponse
   {
      return response()->json([
         'reaction' => [
            'like' => UserLogMethod::query()->where([['method', 'like_add'], ['user_id', auth()->user()->vkontakte_id]])->count(),
            'comment' => $this->countLike(),
         ]
      ]);
   }

   protected function countLike():int
   {
      return UserLogMethod::query()->where([['method', 'wall_reply_new'], ['user_id', auth()->user()->vkontakte_id]])->count() + UserLogMethod::query()->where([['method', 'wall_reply_new'], ['user_id', auth()->user()->telegram_id]])->count();
   }
}
