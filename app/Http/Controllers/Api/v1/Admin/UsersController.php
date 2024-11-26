<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UserSearchResource;
use App\Http\Resources\UserResource;
use App\Models\User\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
   public function search(Request $request)
   {
      $request->validate([
         'id' => ['required'],
         'type' => ['required', 'in:1,2']
      ]);

      if ($request->get('type') == 1) {
         $user = User::query()->where('vkontakte_id', $request->get('id'))->first();
      } else {
         $user = User::query()->where('telegram_id', $request->get('id'))->first();
      }

      return new UserSearchResource($user);
   }
}
