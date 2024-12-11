<?php

namespace App\Http\Resources;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LastActivityUserResource extends JsonResource
{
   /**
    * Transform the resource into an array.
    *
    * @return array<string, mixed>
    */
   public function toArray(Request $request): array
   {
      $user = User::query()->where('id', $this->user_id)->first();

      $avatar = $user->avatar_telegram ?: ($user->avatar ?: '/ayazik/no_image.png');
      return [
         'id' => $user->id,
         'username' => $user->username_telegram ?: ($user->username_vkontakte ?: $user->nickname),
         'nickname' => $user->nickname,
         'coin' => $this->count ?? 0,
         'avatar' => filter_var($avatar, FILTER_VALIDATE_URL) ? $avatar : request()->root() . $avatar,
         'created_at' => $user->created_at,
         'connect_social' => $user->telegram_id and $user->vkontakte_id,
         'tg_name' => $user->username_telegram,
         'vk_name' => $user->username_vkontakte
      ];
   }
}
